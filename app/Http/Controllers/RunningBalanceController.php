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
        // Base query with relationships
        $query = RunningBalance::with(['approver', 'employee', 'creator']);

        // Apply date filter if provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter by approver if provided
        if ($request->filled('approver_id')) {
            $query->where('approver_id', $request->approver_id);
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        $balances = $query->orderBy($sort, $direction)->get();

        $approvers = Approver::all();
        $employees = User::all();

        // ✅ Include transfer (type 10) in running balance
        $runningTotalsByApprover = RunningBalance::whereIn('type', [1, 2, 3, 5, 8, 10])
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        // ✅ Salary deductions only (type 5)
        $salaryDeductions = RunningBalance::where('type', 5)
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        // ✅ Uncollected + deductions (type 4 + 5) for display (absolute value)
        $uncollectedByApprover = RunningBalance::whereIn('type', [4, 5])
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id')
            ->map(fn($amount) => abs($amount));

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
            'type'             => 'required|in:1,2,3,4,5,6,8,10',
            'approver_id'      => 'required|exists:cvr_approver,id', // destination
            'amount'           => 'required|numeric|min:0.01',
            'description'      => 'nullable|string',
            'employee_id'      => 'nullable|exists:users,id',
            'from_approver_id' => 'required_if:type,10|nullable|exists:cvr_approver,id', // source (only for transfer)
        ]);

        $amount = $request->amount;

        // Handle Reimbursement, Uncollected, Release Approved Amount (negatives)
        if (in_array($request->type, [3, 4, 8])) {
            $amount *= -1;
        }

        // Handle Transfer (type 10)
        if ($request->type == 10) {
            // Deduct from source approver
            RunningBalance::create([
                'approver_id' => $request->from_approver_id,
                'type'        => 10,
                'amount'      => -$request->amount,
                'description' => 'Transfer to ' . optional(\App\Models\Approver::find($request->approver_id))->name . 
                                ($request->description ? ' - ' . $request->description : ''),
                'employee_id' => $request->employee_id,
                'created_by'  => $user->id,
            ]);

            // Credit to destination approver
            RunningBalance::create([
                'approver_id' => $request->approver_id,
                'type'        => 10,
                'amount'      => $request->amount,
                'description' => 'Transfer from ' . optional(\App\Models\Approver::find($request->from_approver_id))->name . 
                                ($request->description ? ' - ' . $request->description : ''),
                'employee_id' => $request->employee_id,
                'created_by'  => $user->id,
            ]);
        } else {
            // All other transaction types
            RunningBalance::create([
                'approver_id' => $request->approver_id,
                'type'        => $request->type,
                'amount'      => $amount,
                'description' => $request->description,
                'employee_id' => $request->employee_id,
                'created_by'  => $user->id,
            ]);
        }

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
