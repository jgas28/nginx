<?php

namespace App\Http\Controllers;

use App\Exports\PayslipListExport;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class HRController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $month = $request->input('month');
        $compensationSearch = $request->input('comp_search');
        $availableUserColumns = $this->getAvailableUserCompensationColumns();

        $query = Payroll::with(['user', 'creator'])
            ->orderBy('created_at', 'desc');

        // Apply search by employee name
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('fname', 'like', '%' . $search . '%')
                  ->orWhere('lname', 'like', '%' . $search . '%')
                  ->orWhere('employee_code', 'like', '%' . $search . '%');
            });
        }

        // Filter by payroll status
        if ($status) {
            $query->where('payroll_status', $status);
        }

        // Filter by month
        if ($month) {
            $query->whereMonth('cutoff_from', $month);
        }

        $payrollRecords = $query->paginate(15, ['*'], 'payroll_page')
            ->appends($request->except('payroll_page'));

        // Calculate dashboard stats
        $totalPayrolls = Payroll::count();
        $pendingPayrolls = Payroll::where('payroll_status', 'Pending')->count();
        $approvedPayrolls = Payroll::where('payroll_status', 'Approved')->count();
        $paidPayrolls = Payroll::where('payroll_status', 'Paid')->count();

        // Financial stats
        $totalGrossSalary = Payroll::sum('gross_salary');
        $totalNetSalary = Payroll::sum('net_salary');
        $totalAllowances = Payroll::sum('total_allowance');
        $totalDeductions = Payroll::sum('total_deduction');

        // Current month stats
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $currentMonthPayrolls = Payroll::whereMonth('cutoff_from', $currentMonth)
            ->whereYear('cutoff_from', $currentYear)
            ->count();

        $employees = User::where('status', '!=', 0)
            ->when($compensationSearch, function ($query) use ($compensationSearch) {
                $query->where(function ($employeeQuery) use ($compensationSearch) {
                    $employeeQuery->where('fname', 'like', '%' . $compensationSearch . '%')
                        ->orWhere('lname', 'like', '%' . $compensationSearch . '%')
                        ->orWhere('employee_code', 'like', '%' . $compensationSearch . '%')
                        ->orWhere('position', 'like', '%' . $compensationSearch . '%');
                });
            })
            ->when(collect($availableUserColumns)->contains(true), function ($query) use ($availableUserColumns) {
                $priorityConditions = [];

                if ($availableUserColumns['daily_rate']) {
                    $priorityConditions[] = 'COALESCE(daily_rate, 0) > 0';
                }

                if ($availableUserColumns['monthly_salary']) {
                    $priorityConditions[] = 'COALESCE(monthly_salary, 0) > 0';
                }

                if ($availableUserColumns['sss_no']) {
                    $priorityConditions[] = "TRIM(COALESCE(sss_no, '')) <> ''";
                }

                if ($availableUserColumns['philhealth_no']) {
                    $priorityConditions[] = "TRIM(COALESCE(philhealth_no, '')) <> ''";
                }

                if ($availableUserColumns['tin_no']) {
                    $priorityConditions[] = "TRIM(COALESCE(tin_no, '')) <> ''";
                }

                if (!empty($priorityConditions)) {
                    $query->orderByRaw(
                        'CASE WHEN ' . implode(' OR ', $priorityConditions) . ' THEN 1 ELSE 0 END DESC'
                    );
                }
            })
            ->orderBy('fname')
            ->orderBy('lname')
            ->paginate(5, ['*'], 'employees_page')
            ->appends($request->except('employees_page'));

        if ($request->boolean('partial_compensation') || $request->ajax()) {
            return view('hr.partials.compensation-setup', compact(
                'employees',
                'search',
                'status',
                'month',
                'compensationSearch',
                'availableUserColumns'
            ));
        }

        return view('hr.index', compact(
            'payrollRecords',
            'search',
            'status',
            'month',
            'compensationSearch',
            'totalPayrolls',
            'pendingPayrolls',
            'approvedPayrolls',
            'paidPayrolls',
            'totalGrossSalary',
            'totalNetSalary',
            'totalAllowances',
            'totalDeductions',
            'currentMonthPayrolls',
            'employees',
            'availableUserColumns'
        ));
    }

    public function create(Request $request)
    {
        $employees = User::where('status', '!=', 0)->orderBy('fname')->get();
        $preview = null;

        if ($request->filled('user_id') && $request->filled('cutoff_from') && $request->filled('cutoff_to')) {
            $employee = User::find($request->user_id);

            if ($employee) {
                $selectedAttendanceDates = collect($request->input('selected_attendance_dates', []))
                    ->map(fn ($date) => (string) $date)
                    ->values()
                    ->all();

                $preview = $this->buildPayrollPreview(
                    $employee,
                    $request->cutoff_from,
                    $request->cutoff_to,
                    (float) $request->input('total_allowance', 0),
                    (float) $request->input('other_deduction', 0),
                    $selectedAttendanceDates,
                    (float) $request->input('incentive_amount', 0),
                    (float) $request->input('pagibig_deduction', 0),
                    (float) $request->input('cellphone_loan', 0),
                    (float) $request->input('gasul_fund', 0),
                    (float) $request->input('unreturn_budget', 0),
                    (float) $request->input('cash_bond', 0)
                );
            }
        }

        return view('hr.create', compact('employees', 'preview'));
    }

    public function payslips(Request $request)
    {
        $search = $request->input('search');
        $month = $request->input('month');
        $status = $request->input('status');
        $employeeId = $request->input('user_id');
        $employees = User::where('status', '!=', 0)
            ->orderBy('fname')
            ->orderBy('lname')
            ->get();

        $query = $this->buildPayslipListQuery($search, $month, $status, $employeeId);
        $payslips = $query->paginate(12)->withQueryString();

        return view('hr.payslips.index', compact('payslips', 'search', 'month', 'status', 'employeeId', 'employees'));
    }

    public function exportPayslipsExcel(Request $request)
    {
        return Excel::download(
            new PayslipListExport($request->only(['search', 'month', 'status', 'user_id'])),
            'payslips_' . ($request->input('month') ?: now()->format('Y-m')) . '.xlsx'
        );
    }

    public function exportPayslipsPdf(Request $request)
    {
        $search = $request->input('search');
        $month = $request->input('month');
        $status = $request->input('status');
        $employeeId = $request->input('user_id');

        $payslips = $this->buildPayslipListQuery($search, $month, $status, $employeeId)->get();

        $pdf = Pdf::loadView('hr.payslips.list-pdf', compact('payslips', 'search', 'month', 'status', 'employeeId'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('payslips_' . ($month ?: now()->format('Y-m')) . '.pdf');
    }

    public function showPayslip(Payroll $payroll)
    {
        $payroll->load(['user', 'creator']);

        $attendanceRecords = Attendance::where('user_id', $payroll->user_id)
            ->whereBetween('date', [$payroll->cutoff_from, $payroll->cutoff_to])
            ->orderBy('date')
            ->get();

        $payslip = $this->buildPayslipData($payroll, $attendanceRecords);

        return view('hr.payslips.show', compact('payslip'));
    }

    public function downloadPayslipPdf(Payroll $payroll)
    {
        $payroll->load(['user', 'creator']);

        $attendanceRecords = Attendance::where('user_id', $payroll->user_id)
            ->whereBetween('date', [$payroll->cutoff_from, $payroll->cutoff_to])
            ->orderBy('date')
            ->get();

        $payslip = $this->buildPayslipData($payroll, $attendanceRecords);

        $pdf = Pdf::loadView('hr.payslips.pdf', compact('payslip'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($payroll->payroll_no . '_payslip.pdf');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'            => 'required|exists:users,id',
            'cutoff_from'        => 'required|date',
            'cutoff_to'          => 'required|date',
            'total_allowance'    => 'required|numeric|min:0',
            'incentive_amount'   => 'nullable|numeric|min:0',
            'pagibig_deduction'  => 'nullable|numeric|min:0',
            'cellphone_loan'     => 'nullable|numeric|min:0',
            'gasul_fund'         => 'nullable|numeric|min:0',
            'unreturn_budget'    => 'nullable|numeric|min:0',
            'cash_bond'          => 'nullable|numeric|min:0',
            'other_deduction'    => 'nullable|numeric|min:0',
            'selected_attendance_dates'   => 'nullable|array',
            'selected_attendance_dates.*' => 'date',
        ]);
        
        $employee = User::findOrFail($request->user_id);

        app(\App\Support\AuditContext::class)->tag('payroll.created', "Created Payroll for {$employee->fname} {$employee->lname}");

        $existingPayroll = Payroll::where('user_id', $employee->id)
            ->whereDate('cutoff_from', $request->cutoff_from)
            ->whereDate('cutoff_to', $request->cutoff_to)
            ->exists();

        if ($existingPayroll) {
            return back()
                ->withErrors(['cutoff_to' => 'Payroll for this employee and cutoff period already exists.'])
                ->withInput();
        }

        $preview = $this->buildPayrollPreview(
            $employee,
            $request->cutoff_from,
            $request->cutoff_to,
            (float) $request->total_allowance,
            (float) $request->input('other_deduction', 0),
            collect($request->input('selected_attendance_dates', []))->map(fn ($date) => (string) $date)->all(),
            (float) $request->input('incentive_amount', 0),
            (float) $request->input('pagibig_deduction', 0),
            (float) $request->input('cellphone_loan', 0),
            (float) $request->input('gasul_fund', 0),
            (float) $request->input('unreturn_budget', 0),
            (float) $request->input('cash_bond', 0)
        );

        Payroll::create([
            'payroll_no'             => $this->generatePayrollNumber(),
            'user_id'                => $request->user_id,
            'cutoff_from'            => $request->cutoff_from,
            'cutoff_to'              => $request->cutoff_to,
            'total_days_worked'      => $preview['total_days_worked'],
            'total_hours_worked'     => $preview['total_hours_worked'],
            'total_late_minutes'     => 0,
            'total_undertime_minutes'=> 0,
            'total_overtime_hours'   => 0,
            'compensation_basis'     => $preview['compensation_basis'],
            'base_rate'              => $preview['base_rate'],
            'gross_salary'           => $preview['gross_salary'],
            'incentive_amount'       => $preview['incentive_amount'],
            'total_allowance'        => $preview['total_allowance'],
            'sss_deduction'          => $preview['sss_deduction'],
            'pagibig_deduction'      => $preview['pagibig_deduction'],
            'philhealth_deduction'   => $preview['philhealth_deduction'],
            'tax_deduction'          => $preview['tax_deduction'],
            'cellphone_loan'         => $preview['cellphone_loan'],
            'gasul_fund'             => $preview['gasul_fund'],
            'unreturn_budget'        => $preview['unreturn_budget'],
            'cash_bond'              => $preview['cash_bond'],
            'other_deduction'        => $preview['other_deduction'],
            'total_deduction'        => $preview['total_deduction'],
            'net_salary'             => $preview['net_salary'],
            'payroll_status'         => 'Pending',
            'created_by'             => Auth::id(),
        ]);

        return redirect()->route('hr.index')
            ->with('success', 'Payroll record created successfully.');
    }

    public function updateDailyRates(Request $request)
    {
        $availableUserColumns = $this->getAvailableUserCompensationColumns();
        $enabledColumns = collect($availableUserColumns)->filter()->keys()->values();
        $redirectParameters = collect($request->only([
            'search',
            'status',
            'month',
            'comp_search',
            'employees_page',
        ]))
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        if ($enabledColumns->isEmpty()) {
            return redirect()->route('hr.index', $redirectParameters)->with(
                'error',
                'Compensation setup cannot be saved yet because the users table is missing the HR compensation columns. Run the compensation migrations first.'
            );
        }

        $validationRules = [];

        if ($availableUserColumns['daily_rate']) {
            $validationRules['daily_rates'] = 'required|array';
            $validationRules['daily_rates.*'] = 'nullable|numeric|min:0';
        }

        if ($availableUserColumns['monthly_salary']) {
            $validationRules['monthly_salaries'] = 'nullable|array';
            $validationRules['monthly_salaries.*'] = 'nullable|numeric|min:0';
        }

        if ($availableUserColumns['sss_no']) {
            $validationRules['sss_nos'] = 'nullable|array';
        }

        if ($availableUserColumns['philhealth_no']) {
            $validationRules['philhealth_nos'] = 'nullable|array';
        }

        if ($availableUserColumns['tin_no']) {
            $validationRules['tin_nos'] = 'nullable|array';
        }

        if (!empty($validationRules)) {
            $request->validate($validationRules);
        }

        app(\App\Support\AuditContext::class)->tag('hr_compensation.updated', 'Updated Employee Compensation Rates');

        $userIds = collect([
            array_keys($request->input('daily_rates', [])),
            array_keys($request->input('monthly_salaries', [])),
            array_keys($request->input('sss_nos', [])),
            array_keys($request->input('philhealth_nos', [])),
            array_keys($request->input('tin_nos', [])),
        ])->flatten()->unique()->filter()->values();

        if ($userIds->isEmpty()) {
            return redirect()->route('hr.index', $redirectParameters)->with('error', 'No compensation setup data was submitted.');
        }

        $updatedUsers = 0;

        foreach ($userIds as $userId) {
            $updates = [];

            if ($availableUserColumns['daily_rate']) {
                $updates['daily_rate'] = (float) ($request->input("daily_rates.$userId", 0) ?? 0);
            }

            if ($availableUserColumns['monthly_salary']) {
                $updates['monthly_salary'] = (float) ($request->input("monthly_salaries.$userId", 0) ?? 0);
            }

            if ($availableUserColumns['sss_no']) {
                $updates['sss_no'] = $request->input("sss_nos.$userId");
            }

            if ($availableUserColumns['philhealth_no']) {
                $updates['philhealth_no'] = $request->input("philhealth_nos.$userId");
            }

            if ($availableUserColumns['tin_no']) {
                $updates['tin_no'] = $request->input("tin_nos.$userId");
            }

            if (!empty($updates)) {
                User::where('id', $userId)->update($updates);
                $updatedUsers++;
            }
        }

        if ($updatedUsers === 0) {
            return redirect()->route('hr.index', $redirectParameters)->with(
                'error',
                'No compensation values were saved. Please confirm the HR compensation columns already exist in the users table.'
            );
        }

        return redirect()->route('hr.index', $redirectParameters)
            ->with('success', 'Compensation setup updated successfully.');
    }

    private function buildPayrollPreview(
        User $employee,
        string $cutoffFrom,
        string $cutoffTo,
        float $allowance = 0,
        float $deduction = 0,
        array $selectedAttendanceDates = [],
        float $incentive = 0,
        float $pagibig = 0,
        float $cellphone = 0,
        float $gasulFund = 0,
        float $unreturnBudget = 0,
        float $cashBond = 0
    ): array
    {
        $attendanceRecords = Attendance::where('user_id', $employee->id)
            ->whereBetween('date', [$cutoffFrom, $cutoffTo])
            ->orderBy('date')
            ->get();

        $workedStatuses = ['Present', 'Late'];
        $defaultSelectedDates = $attendanceRecords
            ->filter(fn ($record) => in_array($record->status, $workedStatuses, true))
            ->map(fn ($record) => $record->date->format('Y-m-d'))
            ->values()
            ->all();

        $selectedDates = collect(!empty($selectedAttendanceDates) ? $selectedAttendanceDates : $defaultSelectedDates)
            ->map(fn ($date) => (string) $date)
            ->unique()
            ->values();

        $selectedAttendanceRecords = $attendanceRecords->filter(function ($record) use ($selectedDates, $workedStatuses) {
            return in_array($record->status, $workedStatuses, true)
                && $selectedDates->contains($record->date->format('Y-m-d'));
        });

        $totalDaysWorked = $selectedAttendanceRecords->count();
        $totalHoursWorked = (float) $selectedAttendanceRecords->sum('total_hours');

        $usesMonthlySalary = (float) ($employee->monthly_salary ?? 0) > 0;
        $monthlySalary = (float) ($employee->monthly_salary ?? 0);
        $dailyRate = (float) ($employee->daily_rate ?? 0);
        $compensationBasis = $usesMonthlySalary ? 'monthly_fixed' : 'daily_rate';
        $baseRate = $usesMonthlySalary ? $monthlySalary : $dailyRate;

        $grossSalary = $usesMonthlySalary
            ? round($monthlySalary / 2, 2)
            : ($totalDaysWorked * $dailyRate);

        $estimatedMonthlySalary = $usesMonthlySalary ? $monthlySalary : ($grossSalary * 2);
        $sssDeduction = $this->calculateSemiMonthlySss($estimatedMonthlySalary);
        $philhealthDeduction = $this->calculateSemiMonthlyPhilhealth($estimatedMonthlySalary);
        $taxableCompensation = max(0, $grossSalary - $sssDeduction - $philhealthDeduction);
        $taxDeduction = $this->calculateSemiMonthlyWithholdingTax($taxableCompensation);
        $totalDeduction = $sssDeduction + $philhealthDeduction + $taxDeduction + $pagibig + $cellphone + $gasulFund + $unreturnBudget + $cashBond + $deduction;
        $netSalary = $grossSalary + $allowance + $incentive - $totalDeduction;

        return [
            'employee'                => $employee,
            'cutoff_from'             => $cutoffFrom,
            'cutoff_to'               => $cutoffTo,
            'total_days_worked'       => $totalDaysWorked,
            'total_hours_worked'      => (float) $totalHoursWorked,
            'attendance_records'      => $attendanceRecords,
            'selected_attendance_dates' => $selectedDates->all(),
            'compensation_basis'      => $compensationBasis,
            'base_rate'               => $baseRate,
            'daily_rate'              => (float) ($employee->daily_rate ?? 0),
            'monthly_salary'          => $monthlySalary,
            'gross_salary'            => $grossSalary,
            'incentive_amount'        => $incentive,
            'total_allowance'         => $allowance,
            'sss_deduction'           => $sssDeduction,
            'pagibig_deduction'       => $pagibig,
            'philhealth_deduction'    => $philhealthDeduction,
            'tax_deduction'           => $taxDeduction,
            'cellphone_loan'          => $cellphone,
            'gasul_fund'              => $gasulFund,
            'unreturn_budget'         => $unreturnBudget,
            'cash_bond'               => $cashBond,
            'other_deduction'         => $deduction,
            'total_deduction'         => $totalDeduction,
            'net_salary'              => $netSalary,
        ];
    }

    private function buildPayslipData(Payroll $payroll, $attendanceRecords): array
    {
        $otherDeduction = (float) ($payroll->other_deduction ?? max(0,
            (float) $payroll->total_deduction
            - (float) $payroll->sss_deduction
            - (float) $payroll->pagibig_deduction
            - (float) $payroll->philhealth_deduction
            - (float) $payroll->tax_deduction
            - (float) $payroll->cellphone_loan
            - (float) $payroll->gasul_fund
            - (float) $payroll->unreturn_budget
            - (float) $payroll->cash_bond
        ));

        return [
            'payroll'            => $payroll,
            'employee'           => $payroll->user,
            'attendance_records' => $attendanceRecords,
            'earnings' => [
                ['label' => 'Basic',      'amount' => (float) $payroll->gross_salary],
                ['label' => 'Allowance',  'amount' => (float) $payroll->total_allowance],
                ['label' => 'Incentives', 'amount' => (float) ($payroll->incentive_amount ?? 0)],
            ],
            'deductions' => [
                ['label' => 'SSS Contribution/Loan',        'amount' => (float) $payroll->sss_deduction],
                ['label' => 'Pag-ibig Contribution/Loan',   'amount' => (float) ($payroll->pagibig_deduction ?? 0)],
                ['label' => 'PhilHealth Contribution/Loan', 'amount' => (float) $payroll->philhealth_deduction],
                ['label' => 'Cellphone Loan',               'amount' => (float) ($payroll->cellphone_loan ?? 0)],
                ['label' => 'Gasul Fund/Cash Advance',      'amount' => (float) ($payroll->gasul_fund ?? 0)],
                ['label' => 'Unreturn Budget for Delivery', 'amount' => (float) ($payroll->unreturn_budget ?? 0)],
                ['label' => 'Cash Bond',                    'amount' => (float) ($payroll->cash_bond ?? 0)],
                ['label' => 'Others',                       'amount' => $otherDeduction],
            ],
        ];
    }

    private function buildPayslipListQuery(?string $search, ?string $month, ?string $status, $employeeId = null)
    {
        $query = Payroll::with(['user', 'creator'])
            ->orderByDesc('cutoff_to')
            ->orderByDesc('created_at');

        if ($employeeId) {
            $query->where('user_id', $employeeId);
        } elseif ($search) {
            $query->where(function ($payrollQuery) use ($search) {
                $payrollQuery->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('fname', 'like', '%' . $search . '%')
                        ->orWhere('lname', 'like', '%' . $search . '%')
                        ->orWhere('employee_code', 'like', '%' . $search . '%');
                })->orWhere('payroll_no', 'like', '%' . $search . '%');
            });
        }

        if ($month) {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            $query->whereYear('cutoff_from', $monthDate->year)
                ->whereMonth('cutoff_from', $monthDate->month);
        }

        if ($status) {
            $query->where('payroll_status', $status);
        }

        return $query;
    }

    private function calculateSemiMonthlySss(float $monthlySalary): float
    {
        $monthlySalaryCredit = min(max($monthlySalary, 5000), 35000);
        $monthlyEmployeeShare = $monthlySalaryCredit * 0.05;

        return round($monthlyEmployeeShare / 2, 2);
    }

    private function calculateSemiMonthlyPhilhealth(float $monthlySalary): float
    {
        $salaryBasis = min(max($monthlySalary, 10000), 100000);
        $monthlyPremium = $salaryBasis * 0.05;
        $monthlyEmployeeShare = $monthlyPremium / 2;

        return round($monthlyEmployeeShare / 2, 2);
    }

    private function calculateSemiMonthlyWithholdingTax(float $taxableCompensation): float
    {
        if ($taxableCompensation <= 10417) {
            return 0;
        }

        if ($taxableCompensation <= 16666) {
            return round(($taxableCompensation - 10417) * 0.15, 2);
        }

        if ($taxableCompensation <= 33332) {
            return round(937.50 + (($taxableCompensation - 16667) * 0.20), 2);
        }

        if ($taxableCompensation <= 83332) {
            return round(4270.70 + (($taxableCompensation - 33333) * 0.25), 2);
        }

        if ($taxableCompensation <= 333332) {
            return round(16770.70 + (($taxableCompensation - 83333) * 0.30), 2);
        }

        return round(91770.70 + (($taxableCompensation - 333333) * 0.35), 2);
    }

    private function generatePayrollNumber(): string
    {
        $date = now()->format('Ymd');
        $lastPayroll = Payroll::where('payroll_no', 'like', "PR-{$date}%")
            ->orderBy('payroll_no', 'desc')
            ->first();

        if ($lastPayroll) {
            $lastNumber = (int) substr($lastPayroll->payroll_no, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "PR-{$date}-{$newNumber}";
    }

    private function getAvailableUserCompensationColumns(): array
    {
        return [
            'daily_rate' => Schema::hasColumn('users', 'daily_rate'),
            'monthly_salary' => Schema::hasColumn('users', 'monthly_salary'),
            'sss_no' => Schema::hasColumn('users', 'sss_no'),
            'philhealth_no' => Schema::hasColumn('users', 'philhealth_no'),
            'tin_no' => Schema::hasColumn('users', 'tin_no'),
        ];
    }
}
