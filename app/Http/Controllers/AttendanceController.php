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

        $query = Attendance::with('user')
            ->orderBy('date', 'desc');

        // Apply search by employee name
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('fname', 'like', '%' . $search . '%')
                  ->orWhere('lname', 'like', '%' . $search . '%')
                  ->orWhere('employee_code', 'like', '%' . $search . '%');
            });
        }

        // Filter by date range
        if ($dateFrom && $dateTo) {
            $query->whereBetween('date', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ]);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        $attendanceRecords = $query->paginate(15);

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
            'attendanceRecords',
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
                        'time_in' => null,
                        'time_out' => null,
                        'total_hours' => null,
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

        $summaryQuery = $this->buildSummaryQuery($selectedMonth, $employee);
        $summaryRecords = $summaryQuery->get();

        $employeeSummaries = $summaryRecords
            ->groupBy('user_id')
            ->map(function ($records) {
                $first = $records->first();
                $totalHours = (float) $records->sum(fn ($record) => $this->resolveAttendanceHours($record));

                return (object) [
                    'user_id' => $first->user_id,
                    'employee_code' => $first->user->employee_code ?? 'N/A',
                    'employee_name' => trim(($first->user->fname ?? '') . ' ' . ($first->user->lname ?? '')),
                    'position' => $first->user->position ?? 'N/A',
                    'present_count' => $records->where('status', 'Present')->count(),
                    'late_count' => $records->where('status', 'Late')->count(),
                    'absent_count' => $records->where('status', 'Absent')->count(),
                    'total_hours' => $totalHours,
                    'records' => $records,
                ];
            })
            ->sortBy('employee_name')
            ->values();

        return view('attendance.summary', compact('selectedMonth', 'employee', 'employeeSummaries'));
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

        $summaryRecords = $this->buildSummaryQuery($selectedMonth, $employee)->get();

        $employeeSummaries = $summaryRecords
            ->groupBy('user_id')
            ->map(function ($records) {
                $first = $records->first();
                $totalHours = (float) $records->sum(fn ($record) => $this->resolveAttendanceHours($record));

                return (object) [
                    'user_id' => $first->user_id,
                    'employee_code' => $first->user->employee_code ?? 'N/A',
                    'employee_name' => trim(($first->user->fname ?? '') . ' ' . ($first->user->lname ?? '')),
                    'position' => $first->user->position ?? 'N/A',
                    'present_count' => $records->where('status', 'Present')->count(),
                    'late_count' => $records->where('status', 'Late')->count(),
                    'absent_count' => $records->where('status', 'Absent')->count(),
                    'total_hours' => $totalHours,
                    'records' => $records,
                ];
            })
            ->sortBy('employee_name')
            ->values();

        $pdf = Pdf::loadView('attendance.summary-pdf', compact('selectedMonth', 'employee', 'employeeSummaries'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('attendance_summary_' . $selectedMonth . '.pdf');
    }

    private function buildSummaryQuery(?string $selectedMonth, ?string $employee)
    {
        $query = Attendance::with('user')
            ->orderBy('user_id')
            ->orderBy('date');

        if ($selectedMonth) {
            $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth);
            $query->whereYear('date', $monthDate->year)
                ->whereMonth('date', $monthDate->month);
        }

        if ($employee) {
            $query->whereHas('user', function ($q) use ($employee) {
                $q->where('fname', 'like', '%' . $employee . '%')
                    ->orWhere('lname', 'like', '%' . $employee . '%')
                    ->orWhere('employee_code', 'like', '%' . $employee . '%');
            });
        }

        return $query;
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
