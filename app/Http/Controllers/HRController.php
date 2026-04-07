<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HRController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $month = $request->input('month');

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

        $payrollRecords = $query->paginate(15);

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

        return view('hr.index', compact(
            'payrollRecords',
            'search',
            'status',
            'month',
            'totalPayrolls',
            'pendingPayrolls',
            'approvedPayrolls',
            'paidPayrolls',
            'totalGrossSalary',
            'totalNetSalary',
            'totalAllowances',
            'totalDeductions',
            'currentMonthPayrolls'
        ));
    }

    public function create()
    {
        $employees = User::where('status', '!=', 0)->orderBy('fname')->get();
        return view('hr.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payroll_no' => 'required|unique:payroll,payroll_no',
            'user_id' => 'required|exists:users,id',
            'cutoff_from' => 'required|date',
            'cutoff_to' => 'required|date',
            'total_days_worked' => 'required|numeric|min:0',
            'total_hours_worked' => 'required|numeric|min:0',
            'gross_salary' => 'required|numeric|min:0',
            'total_allowance' => 'required|numeric|min:0',
            'total_deduction' => 'required|numeric|min:0',
        ]);

        $netSalary = $request->gross_salary + $request->total_allowance - $request->total_deduction;

        Payroll::create([
            'payroll_no' => $request->payroll_no,
            'user_id' => $request->user_id,
            'cutoff_from' => $request->cutoff_from,
            'cutoff_to' => $request->cutoff_to,
            'total_days_worked' => $request->total_days_worked,
            'total_hours_worked' => $request->total_hours_worked,
            'total_late_minutes' => $request->total_late_minutes ?? 0,
            'total_undertime_minutes' => $request->total_undertime_minutes ?? 0,
            'total_overtime_hours' => $request->total_overtime_hours ?? 0,
            'gross_salary' => $request->gross_salary,
            'total_allowance' => $request->total_allowance,
            'total_deduction' => $request->total_deduction,
            'net_salary' => $netSalary,
            'payroll_status' => 'Pending',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('hr.index')
            ->with('success', 'Payroll record created successfully.');
    }
}
