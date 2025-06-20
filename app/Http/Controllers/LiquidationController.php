<?php

namespace App\Http\Controllers;

use App\Models\Approver;
use App\Models\Liquidation;
use App\Models\cvr_approval;
use App\Models\CashVoucher;
use App\Models\RunningBalance;
use App\Models\DeliveryRequest;
use App\Models\User;
use Illuminate\Http\Request;

class LiquidationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $data = cvr_approval::with('cashVoucher')
        ->where('status', '1')
        ->whereHas('cashVoucher', function ($query) {
            $query->where('cvr_type', 'basic')
                ->where('status', '2');
        })
        ->get();

        return view('liquidations.index', compact('data'));
    }

    public function indexAdmin()
    {
        $data = cvr_approval::with('cashVoucher.deliveryRequest.allocations.truck.company')
        ->where('status', '1')
        ->whereHas('cashVoucher', function ($query) {
            $query->where('cvr_type', '!=', 'basic')
                ->where('status', '2');
        })
        ->get();

        return view('liquidations.indexAdmin', compact('data'));
    }

    public function storeSummary(Request $request, $id)
    {
        $validated = $request->validate([
            'expenses' => 'array',
            'gasoline' => 'array',
            'rfid' => 'array',
            'others' => 'array',
            'cvr_id' => 'required|integer',
            'cvr_number' => 'required|string',
            'cvr_approval_id' => 'nullable|integer',
            'prepared_by' => 'nullable|integer',
            'noted_by' => 'nullable|integer',
            'validated_by' => 'nullable|integer',
            'collected_by' => 'nullable|integer',
            'approved_by' => 'nullable|integer',
        ]);

        $expenses = $request->input('expenses', []);
        $data = [
            'cvr_id' => $request->input('cvr_id'),
            'cvr_number' => $request->input('cvr_number'),
            'cvr_approval_id' => $request->input('cvr_approval_id'),

            // Direct fields from expenses
            'allowance' => $expenses['allowance'] ?? null,
            'manpower' => $expenses['manpower'] ?? null,
            'hauling' => $expenses['hauling'] ?? null,
            'right_of_way' => $expenses['right_of_way'] ?? null,
            'roro_expense' => $expenses['roro_expense'] ?? null,
            'cash_charge' => $expenses['cash_charge'] ?? null,

            // JSON fields
            'gasoline' => array_values($request->input('gasoline', [])),
            'rfid' => array_values($request->input('rfid', [])),
            'others' => array_values($request->input('others', [])),

            // Status + people
            'status' => '1',
            'prepared_by' => $request->input('prepared_by'),
            'noted_by' => $request->input('noted_by'),
            'validated_by' => null,
            'collected_by' => null,
            'approved_by' => null,
        ];

        Liquidation::create($data);
        $cvrId = $request->input('cvr_id');
        CashVoucher::where('id', $cvrId)->update(['status' => 4]);

        return redirect()->route('liquidations.index')->with('success', 'Liquidation submitted successfully.');
    }



    public function liquidate($id)
    {
        $liquidation = cvr_approval::with('cashVoucher')->findOrFail($id);
        $employees = User::all();
        
        return view('liquidations.liquidate', compact('liquidation', 'employees'));
    }

    public function reviewList()
    {
        $liquidations = Liquidation::with('preparedBy', 'notedBy', 'cashVoucher')
            ->where('status', 1)
            ->paginate(10);

        return view('liquidations.reviewList', compact('liquidations'));
    }

    public function review($id)
    {
        $liquidation = Liquidation::with('cashVoucher', 'cvrApproval', 'preparedBy', 'notedBy')->findOrFail($id);
        $employees = User::all();
        $approvers = Approver::all();

        // Total Liquidated Cash (Only cash items)
        $totalCash = 0;

        foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field) {
            $totalCash += floatval($liquidation->$field ?? 0);
        }

        $totalCash += floatval($liquidation->cash_charge ?? 0);

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->others ?? [] as $item) {
            $totalCash += floatval($item['amount'] ?? 0);
        }

        // Total Card Expenses (non-cash)
        $totalCard = 0;

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0);
        $difference = $totalCash - $approvedAmount;

        // Logic for display and next step status
        $nextStatus = 4; // default: approval
        $refund = false;
        $return = false;

        if ($difference > 0) {
            $refund = true;
            $nextStatus = 4; // can still go to approval but shows refund button
        } elseif ($difference < 0) {
            $return = true;
            $nextStatus = 3; // needs collection
        }

        return view('liquidations.review', compact(
            'liquidation',
            'employees',
            'approvers',
            'totalCash',
            'totalCard',
            'approvedAmount',
            'difference',
            'refund',
            'return',
            'nextStatus'
        ));
    }


    public function validateLiquidation(Request $request, $id)
    {
        $request->validate([
            'validated_by' => 'required|exists:users,id',  // adjust table name accordingly
        ]);

        $liquidation = Liquidation::findOrFail($id);

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0);

        // Recalculate the total like in your `validated` method
        $totalCash = 0;

        foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field) {
            $totalCash += floatval($liquidation->$field ?? 0);
        }

        $totalCash += floatval($liquidation->cash_charge ?? 0);

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->others ?? [] as $item) {
            $totalCash += floatval($item['amount'] ?? 0);
        }


        $difference = $totalCash - $approvedAmount;

        // Assign status based on difference
        if ($difference > 0) {
            $liquidation->status = 4; // Refund, go to approval
        } else {
            $liquidation->status = 3; // Returned cash, go to collection
        }

        $liquidation->validated_by = $request->validated_by;
        $liquidation->validated_at = now();
        $liquidation->save();

        return redirect()->route('liquidations.reviewList')->with('success', 'Liquidation validated successfully.');
    }

    // wag muna to
    public function validatedList()
    {
        $liquidations = Liquidation::with('preparedBy', 'notedBy', 'cashVoucher')
            ->where('status', 3)
            ->paginate(10);

        return view('liquidations.validatedList', compact('liquidations'));
    }

    public function validate(Request $request, $id)
    {
        $liquidation = Liquidation::with('cashVoucher', 'cvrApproval', 'preparedBy', 'notedBy')->findOrFail($id);
        $employees = User::all();
        $approvers = Approver::all();

        // Total Liquidated Cash (Only cash items)
        $totalCash = 0;

        foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field) {
            $totalCash += floatval($liquidation->$field ?? 0);
        }

        $totalCash += floatval($liquidation->cash_charge ?? 0);

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->others ?? [] as $item) {
            $totalCash += floatval($item['amount'] ?? 0);
        }

        // Total Card Expenses (non-cash)
        $totalCard = 0;

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0);
        $difference = $totalCash - $approvedAmount;

        // Logic for display and next step status
        $nextStatus = 4; // default: approval
        $refund = false;
        $return = false;

        if ($difference > 0) {
            $refund = true;
            $nextStatus = 4; // can still go to approval but shows refund button
        } elseif ($difference < 0) {
            $return = true;
            $nextStatus = 3; // needs collection
        }

        return view('liquidations.validated', compact(
            'liquidation',
            'employees',
            'approvers',
            'totalCash',
            'totalCard',
            'approvedAmount',
            'difference',
            'refund',
            'return',
            'nextStatus'
        ));
    }

    public function collectedLiquidation(Request $request, $id)
    {
        $request->validate([
            'collected_by' => 'required|exists:users,id',  // adjust table name accordingly
        ]);

        $liquidation = Liquidation::findOrFail($id);

        $liquidation->collected_by = $request->validated_by;
        $liquidation->collected_at = now();
        $liquidation->status = 4;
        $liquidation->save();

        return redirect()->route('liquidations.reviewList')->with('success', 'Liquidation validated successfully.');
    }

    public function approvalList()
    {
        $liquidations = Liquidation::with('preparedBy', 'notedBy', 'cashVoucher')
            ->where('status', 4)
            ->paginate(10);

        return view('liquidations.approvalList', compact('liquidations'));
    }

    public function approval(Request $request, $id)
    {
        $liquidation = Liquidation::with(['cashVoucher', 'cvrApproval', 'preparedBy', 'notedBy', 'runningBalances'])
                        ->findOrFail($id);

        $employees = User::all();
        $approvers = Approver::all();

        // Calculate total liquidated cash
        $totalCash = 0;

        foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field) {
            $totalCash += floatval($liquidation->$field ?? 0);
        }

        $totalCash += floatval($liquidation->cash_charge ?? 0);

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->others ?? [] as $item) {
            $totalCash += floatval($item['amount'] ?? 0);
        }

        // Total Card Expenses (non-cash)
        $totalCard = 0;

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        // Add refund/return from running balances
        $refundReturnTotal = $liquidation->runningBalances->sum('amount');
        $finalLiquidated = $totalCash + $refundReturnTotal;

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0);
        $difference = $finalLiquidated - $approvedAmount;

        // Determine next status
        $nextStatus = 4; // default: approval
        $refund = false;
        $return = false;

        if ($difference > 0) {
            $refund = true;
            $nextStatus = 4;
        } elseif ($difference < 0) {
            $return = true;
            $nextStatus = 3;
        }

        $runningRefunds = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '3')
            ->get();

        $runningReturns = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '2')
            ->get();

        $runningUncollected = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '4')
            ->get();

        return view('liquidations.approval', compact(
            'liquidation',
            'employees',
            'approvers',
            'totalCash',
            'totalCard',
            'refundReturnTotal',
            'finalLiquidated',
            'approvedAmount',
            'difference',
            'refund',
            'return',
            'nextStatus',
            'runningRefunds',
            'runningReturns',
            'runningUncollected'
        ));
    }

    public function approvedLiquidation(Request $request, $id)
    {
        $request->validate([
            'approved_by' => 'required|exists:users,id',  // adjust table name accordingly
        ]);

        $liquidation = Liquidation::findOrFail($id);

        $liquidation->approved_by = $request->approved_by;
        $liquidation->approved_at = now();
        $liquidation->status = 5;
        $liquidation->save();

        return redirect()->route('liquidations.approvalList')->with('success', 'Liquidation validated successfully.');
    }

    public function approvalLiquidation(Request $request, $id)
    {
        $request->validate([
            'validated_by' => 'required|exists:users,id',  // adjust table name accordingly
        ]);

        $liquidation = Liquidation::findOrFail($id);

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0);

        // Recalculate the total like in your `validated` method
        $totalCash = 0;

        foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field) {
            $totalCash += floatval($liquidation->$field ?? 0);
        }

        $totalCash += floatval($liquidation->cash_charge ?? 0);

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->others ?? [] as $item) {
            $totalCash += floatval($item['amount'] ?? 0);
        }


        $difference = $totalCash - $approvedAmount;

        // Assign status based on difference
        if ($difference > 0) {
            $liquidation->status = 4; // Refund, go to approval
        } else {
            $liquidation->status = 3; // Returned cash, go to collection
        }

        $liquidation->validated_by = $request->validated_by;
        $liquidation->validated_at = now();
        $liquidation->save();

        return redirect()->route('liquidations.approvalList')->with('success', 'Liquidation validated successfully.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Liquidation $liquidation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Liquidation $liquidation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Liquidation $liquidation)
    {
        //
    }
}
