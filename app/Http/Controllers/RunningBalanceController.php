<?php

namespace App\Http\Controllers;

use App\Models\RunningBalance;
use App\Models\Company;
use App\Models\User;
use App\Models\Approver;
use App\Models\Supplier;
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

        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        $balances = $query->orderBy($sort, $direction)->get();

        $approvers = Approver::all();
        $employees = User::where('status', '!=', 0)->get();
        $suppliers = Supplier::all();

        $excludedApproverIds = [564,565,801,802,803,1532,1651,1652,1654,1655,1656,1660,1661,1662,1663,1664,1665,1666,1969,1972,1975];

        // ✅ Include transfer (type 10) in running balance
        $runningTotalsByApprover = RunningBalance::selectRaw('approver_id, SUM(amount) as total')
            // whereIn('type', [1, 2, 3, 5, 8, 9, 10, 11, 12])
            // ->whereNotIn('id', $excludedApproverIds)
            // ->
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        // ✅ Salary deductions only (type 5)
        $salaryDeductions = RunningBalance::where('type', 5)
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        // ✅ Uncollected + deductions (type 4 + 5) for display (absolute value)
        $uncollectedByApprover = RunningBalance::whereIn('type', [4, 5, 12]) // ✅ include 12 if applicable
        ->selectRaw('approver_id, SUM(amount) as total')
        ->groupBy('approver_id')
        ->pluck('total', 'approver_id');

        return view('running_balance.index', compact(
            'balances',
            'approvers',
            'employees',
            'runningTotalsByApprover',
            'salaryDeductions',
            'uncollectedByApprover',
            'suppliers'
        ));
    }

    // For Approver ID 2 (Laguna)
    public function adminFunds(Request $request)
    {
        $request->merge(['approver_id' => 2]);
        return $this->filteredFunds($request, 'adminFunds');
    }

    // For Approver ID 3 (Davao)
    public function davaoFunds(Request $request)
    {
        $request->merge(['approver_id' => 3]);
        return $this->filteredFunds($request, 'davaoFunds');
    }

    // Shared logic
    protected function filteredFunds(Request $request, $viewName)
    {
        // Determine the correct approver_id from the route logic
        $approverId = $request->approver_id;

        $query = RunningBalance::with(['approver', 'employee', 'creator'])
            ->where('approver_id', $approverId); // Always filter by fixed approver

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        $balances = $query->orderBy($sort, $direction)->get();

        $approvers = Approver::all();
        $employees = User::where('status', '!=', 0)->get();

        $runningTotalsByApprover = RunningBalance::where('approver_id', $approverId)
            // ->whereIn('type', [1, 2, 3, 5, 8, 9, 10, 11, 12])
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        $salaryDeductions = RunningBalance::where('approver_id', $approverId)
            ->where('type', 5)
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        $uncollectedByApprover = RunningBalance::where('approver_id', $approverId)
            ->whereIn('type', [4, 5, 12])
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        return view("running_balance.{$viewName}", compact(
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
            'type'             => 'required|in:1,2,3,4,5,6,8,9,10,11,12',
            'approver_id'      => 'required|exists:cvr_approver,id',
            'amount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $type = (int) $request->input('type');

                    if (in_array($type, [11, 12])) {
                        if ((float)$value === 0.0) {
                            $fail('The amount cannot be zero for adjustment types.');
                        }
                    } else {
                        if ((float)$value < 0.01) {
                            $fail('The amount must be at least 0.01.');
                        }
                    }
                },
            ],
            'description'      => 'nullable|string',
            'employee_id'      => 'nullable|exists:users,id',
            'from_approver_id' => 'required_if:type,10|nullable|exists:cvr_approver,id',
        ]);

        $amount = $request->amount;

        if (in_array($request->type, [3, 4, 8])) {
            $amount = -abs($amount); // Always negative
        } elseif (in_array($request->type, [11, 12])) {
            $amount = $amount; // Manual +/- allowed
        }

        // Determine adjustment type
        $adjustmentType = $amount > 0 ? 'In' : 'Out';

        // ✅ Handle Transfer separately
        if ($request->type == 10) {
            // Transfer out (deduction from source)
            RunningBalance::create([
                'approver_id' => $request->from_approver_id,
                'type'        => 10,
                'amount'      => -$request->amount,
                'description' => 'Transfer to ' . optional(Approver::find($request->approver_id))->name .
                                ($request->description ? ' - ' . $request->description : ''),
                'employee_id' => $request->employee_id,
                'created_by'  => $user->id,
                'adjustment_type' => 'Out',
            ]);

            // Transfer in (credit to destination)
            RunningBalance::create([
                'approver_id' => $request->approver_id,
                'type'        => 10,
                'amount'      => $request->amount,
                'description' => 'Transfer from ' . optional(Approver::find($request->from_approver_id))->name .
                                ($request->description ? ' - ' . $request->description : ''),
                'employee_id' => $request->employee_id,
                'created_by'  => $user->id,
                'adjustment_type' => 'In',
            ]);
        } else {
            $description = $request->description;

            if ($request->type == 9) {
                $description = 'Admin Adjustment: ' . $description;
            } elseif ($request->type == 11) {
                $description = 'Admin Adjustment: ' . $description;
            } elseif ($request->type == 12) {
                $description = 'Admin Adjustment for Uncollected: ' . $description;
            }

            // All other types
            RunningBalance::create([
                'approver_id' => $request->approver_id,
                'type'        => $request->type,
                'amount'      => $amount,
                'description' => $description,
                'employee_id' => $request->employee_id,
                'created_by'  => $user->id,
                'adjustment_type' => $adjustmentType,
            ]);
        }

        return redirect()->route('running_balance.index')->with('success', 'Transaction recorded successfully.');
    }


    public function storeReimbursement(Request $request)
    {
        $liquidation_id = $request->liquidation_id;
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

        // Check for duplicate based on a unique combination
        $existing = RunningBalance::where([
            'employee_id' => $validated['employee_id'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'approver_id' => $validated['approver_id'],
            'created_by' => $validated['created_by'],
            'cvr_number' => $validated['cvr_number'],
            'adjustment_type' => 'In',
            'type' => 3,
        ])->first(); 

        if ($existing) {     
            return redirect()->route('liquidations.review', $liquidation_id)
                ->with('info', 'Reimbursement already submitted.');
        }

        $reimbursement = RunningBalance::create($validated);

        return redirect()->route('liquidations.review', $liquidation_id);
    }

    public function storeReimbursementAdmin(Request $request)
    {
        $liquidation_id = $request->liquidation_id;
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

        // Check for duplicate based on a unique combination
        $existing = RunningBalance::where([
            'employee_id' => $validated['employee_id'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'approver_id' => $validated['approver_id'],
            'created_by' => $validated['created_by'],
            'cvr_number' => $validated['cvr_number'],
            'adjustment_type' => 'Out',
            'type' => 3,
        ])->first(); 

        if ($existing) {     
            return redirect()->route('liquidations.approval', $liquidation_id)
                ->with('info', 'Reimbursement already submitted.');
        }

        $reimbursement = RunningBalance::create($validated);

        return redirect()->route('liquidations.approval', $liquidation_id);
    }



    public function print($id)
    {
        $reimbursement = RunningBalance::with(['employee', 'approver'])->findOrFail($id);

        return view('reimbursements.print', compact('reimbursement'));
    }

    public function printRefund($id)
    {
        $reimbursement = RunningBalance::with(['employee', 'approver'])->findOrFail($id);

        return view('refunds.print', compact('reimbursement'));
    }

    public function printReturn($id)
    {
        $reimbursement = RunningBalance::with(['employee', 'approver'])->findOrFail($id);

        return view('returns.print', compact('reimbursement'));
    }

    public function storeCollected(Request $request)
    {
        $liquidation_id = $request->liquidation_id;
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
        $reimbursement=RunningBalance::create([
            'employee_id' => $validated['employee_id'],
            'amount' => abs($validated['amount_collected']), // positive value
            'description' => $validated['description'],
            'approver_id' => $validated['approver_id'],
            'created_by' => $validated['created_by'],
            'cvr_number' => $validated['cvr_number'],
            'adjustment_type' => 'In',
            'type' => 2,
        ]);

        RunningBalance::create([
            'employee_id' => $validated['employee_id'],
            'amount' => -abs($validated['amount_uncollected']), // negative value
            'description' => $validated['description1'],
            'approver_id' => $validated['approver_id'],
            'created_by' => $validated['created_by'],
            'cvr_number' => $validated['cvr_number'],
            'adjustment_type' => 'Float',
            'type' => 4,
        ]);
    }


    // public function storeCollected(Request $request)
    // {
    //     // dd($request->all());
    //     $liquidation_id = $request->liquidation_id;
    //     $user = Auth::user();
    //     // Validate the collected form data
    //     $validated = $request->validate([
    //         'amount_collected' => 'required|numeric|min:0', // Amount collected
    //         'description_collected' => 'required|string', // Description for collected
    //         'employee_id' => 'required|exists:users,id', // Employee ID for collected amount
    //         'approver_id_collected' => 'required|exists:cvr_approver,id', // Approver ID
    //         'cvr_number_collected' => 'required|string', // CVR Number
    //     ]);

    //     // Create a new reimbursement entry for the collected amount
    //     RunningBalance::create([
    //         'employee_id' => $validated['employee_id'],
    //         'amount' => abs($validated['amount_collected']), // Positive value for collected amount
    //         'description' => $validated['description_collected'],
    //         'approver_id' => $validated['approver_id_collected'],
    //         'created_by' => $user->id,
    //         'cvr_number' => $validated['cvr_number_collected'],
    //         'type' => 2,  // Assuming type '2' represents collected
    //         'adjustment_type' => 'In',  // Type of adjustment
    //     ]);

    //     return redirect()->route('liquidations.validated', $liquidation_id)
    //                     ->with('success', 'Collected amount saved successfully!');
    // }

    // public function storeUncollected(Request $request)
    // {
    //     // dd($request->all());
    //     $liquidation_id = $request->liquidation_id;
    //     $user = Auth::user();
    //     // Validate the uncollected form data
    //     $validated = $request->validate([
    //         'amount_uncollected' => 'required|numeric|min:0', // Amount uncollected (readonly in the form)
    //         'employee_id_uncollected' => 'required|array', // Employee IDs (array for multiple)
    //         'deduction_amount_uncollected' => 'required|array', // Deduction amounts (array for multiple)
    //         'employee_id_uncollected.*' => 'exists:users,id', // Ensure each employee exists
    //         'deduction_amount_uncollected.*' => 'numeric|min:0', // Ensure each deduction is a valid number
    //         'approver_id_uncollected' => 'required|exists:cvr_approver,id', // Approver ID
    //         'cvr_number_uncollected' => 'required|string', // CVR Number
    //         'description_uncollected' => 'required|string',
    //     ]);

    //     // Store uncollected amounts for each employee
    //     $employeeIds = $validated['employee_id_uncollected'];
    //     $deductionAmounts = $validated['deduction_amount_uncollected'];

    //     // Loop through each employee ID and its corresponding deduction amount
    //     foreach ($employeeIds as $index => $employeeId) {
    //         $deductionAmount = $deductionAmounts[$index];

    //         // Create a record in the RunningBalance table for each employee
    //         RunningBalance::create([
    //             'employee_id' => $employeeId,
    //             'amount' => -abs($deductionAmount), // Negative value for uncollected amount
    //             'description' => $validated['description_uncollected'], // Use description for uncollected
    //             'approver_id' => $validated['approver_id_uncollected'],
    //             'created_by' =>  $user->id,
    //             'cvr_number' => $validated['cvr_number_uncollected'],
    //             'type' => 4,  // Assuming type '4' represents uncollected
    //             'adjustment_type' => 'Float',  // Type of adjustment for uncollected
    //         ]);
    //     }

    //     // Redirect with success message
    //     return redirect()->route('liquidations.validated', $liquidation_id)
    //                     ->with('success', 'Uncollected amounts and deductions saved successfully!');
    // }

    public function storeCollectedAdmin(Request $request)
    {
        $liquidation_id = $request->liquidation_id;
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
        $reimbursement=RunningBalance::create([
            'employee_id' => $validated['employee_id'],
            'amount' => abs($validated['amount_collected']), // positive value
            'description' => $validated['description'],
            'approver_id' => $validated['approver_id'],
            'created_by' => $validated['created_by'],
            'cvr_number' => $validated['cvr_number'],
            'type' => 2,
            'adjustment_type' => 'In',
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
            'adjustment_type' => 'Float',
        ]);

         return redirect()->route('liquidations.approval', $liquidation_id);
    }

     // Edit Refund
    public function editRefund($id)
    {
        $refund = RunningBalance::findOrFail($id);
        return response()->json($refund);
    }

    // Update Refund
    public function updateRefund(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);

        $refund = RunningBalance::findOrFail($id);
        $refund->update($request->only('description', 'amount'));

        $liquidation_id = $request->input('liquidation_id');
        return redirect()->route('liquidations.approval', ['id' => $liquidation_id])
                 ->with('success', 'Refund updated successfully.');
    }

    // Edit Return
    public function editReturn($id)
    {
        $return = RunningBalance::findOrFail($id);
        return response()->json($return);
    }

    // Update Return
    public function updateReturn(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);

        $return = RunningBalance::findOrFail($id);
        $return->update($request->only('description', 'amount'));
       
        $liquidation_id = $request->input('liquidation_id');

        return redirect()->route('liquidations.approval', ['id' => $liquidation_id])
                 ->with('success', 'Return updated successfully.');
    }

    public function storeAdminAdjustment(Request $request)
    {
        $request->validate([
            'approver_id' => 'required|exists:cvr_approver,id',
            'employee_id' => 'nullable|exists:users,id',
            'amount' => 'required|numeric|not_in:0', // Allow both positive and negative
            'description' => 'required|string|max:255',
        ]);

        RunningBalance::create([
            'approver_id' => $request->approver_id,
            'employee_id' => $request->employee_id,
            'amount' => $request->amount,
            'description' => 'Admin Adjustment: ' . $request->description,
            'type' => 9, // Admin adjustment type
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('running_balance.index')->with('success', 'Admin adjustment recorded.');
    }

    public function storeUncollectedAdjustment(Request $request)
    {
        $request->validate([
            'approver_id' => 'required|exists:cvr_approver,id',
            'employee_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|not_in:0',
            'description' => 'required|string|max:255',
        ]);

        $adjustedAmount = $request->amount; // ✅ Keep raw amount (+ or -)

        RunningBalance::create([
            'approver_id' => $request->approver_id,
            'employee_id' => $request->employee_id,
            'amount' => $adjustedAmount,
            'description' => 'Uncollected Adjustment: ' . $request->description,
            'type' => 11,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('running_balance.index')->with('success', 'Uncollected adjustment recorded.');
    }

}
