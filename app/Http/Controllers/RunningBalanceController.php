<?php

namespace App\Http\Controllers;

use App\Models\RunningBalance;
use App\Models\Company;
use App\Models\User;
use App\Models\Approver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RunningBalanceController extends Controller
{
    public function index(Request $request)
    {
        // Base query: Include all for listing, but filter as needed
        $query = RunningBalance::with(['approver', 'employee', 'creator']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('approver_id')) {
            $query->where('approver_id', $request->approver_id);
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        $balances = $query->orderBy($sort, $direction)->get();

        $approvers = Approver::all();
        $employees = User::all();

        // ✅ Compute running totals (Exclude type 4 — uncollected)
        $runningTotalsByApprover = RunningBalance::whereIn('type', [1, 2, 3, 5, 8])
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        // ✅ Get salary deductions only (type 5) – positive
        $salaryDeductions = RunningBalance::where('type', 5)
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        // ✅ Get uncollected (type 4) – negative values, shown separately (do NOT compute in running balance)
        $uncollectedByApprover = RunningBalance::whereIn('type', [4,5])
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id')
            ->map(function ($amount) {
                return abs($amount); // convert to positive for display
            });

        return view('running_balance.index', compact(
            'balances',
            'approvers',
            'employees',
            'runningTotalsByApprover',
            'salaryDeductions',
            'uncollectedByApprover'
        ));
    }

    
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'approver_id' => 'required|exists:cvr_approver,id',
            'type'        => 'required|in:1,2,3,4,5,6', // 1 = Top-up, 2 = Return, 3 = Refund, 4 = Uncollected, 5 = SD
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'employee_id' => 'nullable|exists:users,id',
        ]);

        $amount = $request->amount;

        // Reimbursement is a deduction
        if ($request->type == 3 || $request->type == 4 || $request->type == 8) {
            $amount *= -1;
        }

        RunningBalance::create([
            'approver_id' => $request->approver_id,
            'type'        => $request->type,
            'amount'      => $amount,
            'description' => $request->description,
            'employee_id' => $request->employee_id,
            'created_by'  => $user->id,
        ]);

        return redirect()->route('running_balance.index')->with('success', 'Transaction recorded successfully.');
    }

    public function storeReimbursement(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'approver_id' => 'required|exists:cvr_approver,id',
            'created_by' => 'required|exists:users,id',
            'cvr_number' => 'required|string',
        ]);
        
        $validated['type'] = 3;
        $validated['amount'] = -abs($validated['amount']);

        RunningBalance::create($validated);

        return redirect()->back()->with('success', 'Reimbursement recorded successfully.');
    }

    public function storeCollected(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'amount_collected' => 'required|numeric|min:0',
            'amount_uncollected' => 'required|numeric|min:0',
            'description' => 'required|string',
            'description1' => 'required|string',
            'approver_id' => 'required|exists:cvr_approver,id',
            'created_by' => 'required|exists:users,id',
            'cvr_number' => 'required|string',
        ]);

        // 1. Returned Cash Record
        RunningBalance::create([
            'employee_id' => $validated['employee_id'],
            'amount' => abs($validated['amount_collected']), // positive value
            'description' => $validated['description'],
            'approver_id' => $validated['approver_id'],
            'created_by' => $validated['created_by'],
            'cvr_number' => $validated['cvr_number'],
            'type' => 2,
        ]);

        // 2. Uncollected Cash Record
        RunningBalance::create([
            'employee_id' => $validated['employee_id'],
            'amount' => -abs($validated['amount_uncollected']), // negative value
            'description' => $validated['description1'],
            'approver_id' => $validated['approver_id'],
            'created_by' => $validated['created_by'],
            'cvr_number' => $validated['cvr_number'],
            'type' => 4,
        ]);

        return redirect()->back()->with('success', 'Returned and uncollected cash recorded successfully.');
    }

}
