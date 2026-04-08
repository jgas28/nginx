<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceSummaryExport;
use App\Models\Attendance;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $status = $request->input('status');
        $applyAttendanceFilters = function ($query) use ($dateFrom, $dateTo, $status) {
            if ($dateFrom && $dateTo) {
                $query->whereBetween('date', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay(),
                ]);
            } elseif ($dateFrom) {
                $query->whereDate('date', '>=', Carbon::parse($dateFrom)->toDateString());
            } elseif ($dateTo) {
                $query->whereDate('date', '<=', Carbon::parse($dateTo)->toDateString());
            }

            if ($status) {
                $query->where('status', $status);
            }
        };

        $attendanceGroups = User::query()
            ->where('status', '!=', 0)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($userQuery) use ($search) {
                    $userQuery->where('fname', 'like', '%' . $search . '%')
                        ->orWhere('lname', 'like', '%' . $search . '%')
                        ->orWhere('employee_code', 'like', '%' . $search . '%');
                });
            })
            ->whereHas('attendances', function ($query) use ($applyAttendanceFilters) {
                $applyAttendanceFilters($query);
            })
            ->with(['attendances' => function ($query) use ($applyAttendanceFilters) {
                $applyAttendanceFilters($query);
                $query->orderBy('date', 'desc');
            }])
            ->orderBy('fname')
            ->orderBy('lname')
            ->paginate(10)
            ->appends($request->query());

        $attendanceModalData = $attendanceGroups->getCollection()
            ->mapWithKeys(function ($user) {
                $records = $user->attendances->map(function ($record) {
                    $displayTimeIn = $record->time_in
                        ? $record->time_in->format('h:i A')
                        : (in_array($record->status, ['Present', 'Late'], true) ? '08:00 AM' : '-');
                    $displayTimeOut = $record->time_out
                        ? $record->time_out->format('h:i A')
                        : (in_array($record->status, ['Present', 'Late'], true) ? '05:00 PM' : '-');
                    $displayTotalHours = !is_null($record->total_hours)
                        ? (float) $record->total_hours
                        : (in_array($record->status, ['Present', 'Late'], true) ? 8.0 : 0.0);

                    return [
                        'date' => $record->date->format('M d, Y'),
                        'status' => $record->status,
                        'time_in' => $displayTimeIn,
                        'time_out' => $displayTimeOut,
                        'total_hours' => number_format($displayTotalHours, 2),
                        'remarks' => $record->remarks ?: '-',
                    ];
                })->values();

                return [
                    $user->id => [
                        'id' => $user->id,
                        'name' => trim($user->fname . ' ' . $user->lname),
                        'employee_code' => $user->employee_code,
                        'position' => $user->position ?: 'N/A',
                        'total_records' => $records->count(),
                        'present_count' => $records->where('status', 'Present')->count(),
                        'late_count' => $records->where('status', 'Late')->count(),
                        'absent_count' => $records->where('status', 'Absent')->count(),
                        'total_hours' => number_format($records->sum(fn ($record) => (float) $record['total_hours']), 2),
                        'records' => $records,
                    ],
                ];
            })
            ->all();

        // Calculate dashboard stats
        $totalRecords = Attendance::count();
        $presentCount = Attendance::where('status', 'Present')->count();
        $lateCount = Attendance::where('status', 'Late')->count();
        $absentCount = Attendance::where('status', 'Absent')->count();

        // Monthly stats (current month)
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $monthlyPresent = Attendance::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('status', 'Present')
            ->count();
        $monthlyLate = Attendance::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('status', 'Late')
            ->count();
        $monthlyAbsent = Attendance::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('status', 'Absent')
            ->count();

        return view('attendance.index', compact(
            'attendanceGroups',
            'attendanceModalData',
            'search',
            'dateFrom',
            'dateTo',
            'status',
            'totalRecords',
            'presentCount',
            'lateCount',
            'absentCount',
            'monthlyPresent',
            'monthlyLate',
            'monthlyAbsent'
        ));
    }

    public function create(Request $request)
    {
        $employees = User::where('status', '!=', 0)->orderBy('fname')->get();
        $selectedUser = null;
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $employeeDateOptions = collect();
        $selectedPresentDates = [];

        if ($request->filled('user_id')) {
            $selectedUser = User::find($request->input('user_id'));

            if ($selectedUser && $selectedMonth) {
                $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth);
                $startDate = $monthDate->copy()->startOfMonth();
                $endDate = $monthDate->copy()->endOfMonth();

                $attendanceByDate = Attendance::where('user_id', $selectedUser->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get()
                    ->keyBy(fn ($attendance) => $attendance->date->format('Y-m-d'));

                $employeeDateOptions = collect(CarbonPeriod::create($startDate, $endDate))
                    ->map(function ($date) use ($attendanceByDate) {
                        $dateKey = $date->format('Y-m-d');
                        $attendance = $attendanceByDate->get($dateKey);

                        return (object) [
                            'date' => $date->copy(),
                            'date_key' => $dateKey,
                            'is_present' => $attendance?->status === 'Present',
                            'status' => $attendance?->status,
                        ];
                    });

                $selectedPresentDates = $employeeDateOptions
                    ->filter(fn ($option) => $option->is_present)
                    ->pluck('date_key')
                    ->all();
            }
        }

        return view('attendance.create', compact(
            'employees',
            'selectedUser',
            'selectedMonth',
            'employeeDateOptions',
            'selectedPresentDates'
        ));
    }

    public function store(Request $request)
    {
        if ($request->input('mode') === 'employee_dates') {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'month' => 'required|date_format:Y-m',
                'present_dates' => 'nullable|array',
                'present_dates.*' => 'date',
            ]);

            $employee = User::findOrFail($request->user_id);
            $monthDate = Carbon::createFromFormat('Y-m', $request->month);
            $startDate = $monthDate->copy()->startOfMonth();
            $endDate = $monthDate->copy()->endOfMonth();
            $presentDates = collect($request->input('present_dates', []))
                ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
                ->unique()
                ->values();

            foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
                $dateKey = $date->format('Y-m-d');
                $isPresent = $presentDates->contains($dateKey);

                Attendance::updateOrCreate(
                    [
                        'user_id' => $employee->id,
                        'date' => $dateKey,
                    ],
                    [
                        'time_in' => $isPresent ? Carbon::createFromTimeString('08:00') : null,
                        'time_out' => $isPresent ? Carbon::createFromTimeString('17:00') : null,
                        'total_hours' => $isPresent ? 8.0 : 0.0,
                        'status' => $isPresent ? 'Present' : 'Absent',
                        'remarks' => null,
                    ]
                );
            }

            return redirect()->route('attendance.create', [
                'user_id' => $employee->id,
                'month' => $request->month,
            ])->with('success', 'Employee attendance dates saved successfully.');
        }

        $request->validate([
            'date' => 'required|date',
            'records' => 'required|array|min:1',
            'records.*.user_id' => 'required|exists:users,id',
            'records.*.time_in' => 'nullable|date_format:H:i',
            'records.*.time_out' => 'nullable|date_format:H:i',
            'records.*.status' => 'required|in:Present,Late,Absent',
            'records.*.remarks' => 'nullable|string',
        ]);

        foreach ($request->records as $record) {
            $timeIn = !empty($record['time_in']) ? Carbon::createFromFormat('H:i', $record['time_in']) : null;
            $timeOut = !empty($record['time_out']) ? Carbon::createFromFormat('H:i', $record['time_out']) : null;
            $totalHours = null;

            if ($timeIn && $timeOut) {
                $totalHours = $timeOut->diffInMinutes($timeIn) / 60;
            }

            Attendance::updateOrCreate(
                [
                    'user_id' => $record['user_id'],
                    'date' => $request->date,
                ],
                [
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'total_hours' => $totalHours,
                    'status' => $record['status'],
                    'remarks' => $record['remarks'] ?? null,
                ]
            );
        }

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance records saved successfully.');
    }

    public function summary(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $employee = $request->input('employee');
        $employeeId = $request->input('user_id');
        $hasEmployeeFilter = filled($employeeId) || filled($employee);
        $employees = User::where('status', '!=', 0)
            ->orderBy('fname')
            ->orderBy('lname')
            ->get();

        $employeeSummaries = collect();

        if ($hasEmployeeFilter) {
            $summaryQuery = $this->buildSummaryQuery($selectedMonth, $employee, $employeeId);
            $summaryRecords = $summaryQuery->get();
            $employeeSummaries = $this->buildEmployeeSummaries($summaryRecords, $selectedMonth, $employee, $employeeId);
        }

        return view('attendance.summary', compact(
            'selectedMonth',
            'employee',
            'employeeId',
            'employeeSummaries',
            'employees',
            'hasEmployeeFilter'
        ));
    }

    public function summaryExcel(Request $request)
    {
        return Excel::download(
            new AttendanceSummaryExport($request->only(['month', 'employee'])),
            'attendance_summary_' . ($request->input('month', now()->format('Y-m')) ?: now()->format('Y-m')) . '.xlsx'
        );
    }

    public function summaryPdf(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $employee = $request->input('employee');
        $employeeId = $request->input('user_id');

        $summaryRecords = $this->buildSummaryQuery($selectedMonth, $employee, $employeeId)->get();
        $employeeSummaries = $this->buildEmployeeSummaries($summaryRecords, $selectedMonth, $employee, $employeeId);

        $pdf = Pdf::loadView('attendance.summary-pdf', compact('selectedMonth', 'employee', 'employeeId', 'employeeSummaries'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('attendance_summary_' . $selectedMonth . '.pdf');
    }

    private function buildSummaryQuery(?string $selectedMonth, ?string $employee, $employeeId = null)
    {
        $query = Attendance::with('user')
            ->orderBy('user_id')
            ->orderBy('date');

        if ($selectedMonth) {
            $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth);
            $query->whereYear('date', $monthDate->year)
                ->whereMonth('date', $monthDate->month);
        }

        if ($employeeId) {
            $query->where('user_id', $employeeId);
        } elseif ($employee) {
            $query->whereHas('user', function ($q) use ($employee) {
                $q->where('fname', 'like', '%' . $employee . '%')
                    ->orWhere('lname', 'like', '%' . $employee . '%')
                    ->orWhere('employee_code', 'like', '%' . $employee . '%');
            });
        }

        return $query;
    }

    private function buildEmployeeSummaries($summaryRecords, string $selectedMonth, ?string $employee, $employeeId = null)
    {
        $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth);
        $monthPeriod = collect(CarbonPeriod::create($monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth()));
        $recordsByUser = $summaryRecords->groupBy('user_id');

        $employees = User::where('status', '!=', 0)
            ->when($employeeId, fn ($query) => $query->where('id', $employeeId))
            ->when(!$employeeId && $employee, function ($query) use ($employee) {
                $query->where(function ($userQuery) use ($employee) {
                    $userQuery->where('fname', 'like', '%' . $employee . '%')
                        ->orWhere('lname', 'like', '%' . $employee . '%')
                        ->orWhere('employee_code', 'like', '%' . $employee . '%');
                });
            })
            ->orderBy('fname')
            ->orderBy('lname')
            ->get();

        if ($employees->isEmpty() && $recordsByUser->isNotEmpty()) {
            $employees = $summaryRecords->pluck('user')->filter()->unique('id')->values();
        }

        return $employees->map(function ($employeeRecord) use ($recordsByUser, $monthPeriod) {
            $existingRecords = $recordsByUser->get($employeeRecord->id, collect())
                ->keyBy(fn ($record) => $record->date->format('Y-m-d'));

            $completeRecords = $monthPeriod->map(function ($date) use ($existingRecords, $employeeRecord) {
                $dateKey = $date->format('Y-m-d');
                $record = $existingRecords->get($dateKey);

                if ($record) {
                    return $record;
                }

                $placeholder = new Attendance();
                $placeholder->user_id = $employeeRecord->id;
                $placeholder->date = $date->copy();
                $placeholder->time_in = null;
                $placeholder->time_out = null;
                $placeholder->total_hours = 0.0;
                $placeholder->status = 'Absent';
                $placeholder->remarks = null;
                $placeholder->setRelation('user', $employeeRecord);

                return $placeholder;
            });

            $totalHours = (float) $completeRecords->sum(fn ($record) => $this->resolveAttendanceHours($record));

            return (object) [
                'user_id' => $employeeRecord->id,
                'employee_code' => $employeeRecord->employee_code ?? 'N/A',
                'employee_name' => trim(($employeeRecord->fname ?? '') . ' ' . ($employeeRecord->lname ?? '')),
                'position' => $employeeRecord->position ?? 'N/A',
                'present_count' => $completeRecords->where('status', 'Present')->count(),
                'late_count' => $completeRecords->where('status', 'Late')->count(),
                'absent_count' => $completeRecords->where('status', 'Absent')->count(),
                'total_hours' => $totalHours,
                'records' => $completeRecords,
            ];
        })->values();
    }

    private function resolveAttendanceHours(Attendance $attendance): float
    {
        if (!is_null($attendance->total_hours)) {
            return (float) $attendance->total_hours;
        }

        if ($attendance->status === 'Present') {
            return 8.0;
        }

        return 0.0;
    }
}
