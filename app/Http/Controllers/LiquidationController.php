<?php

namespace App\Http\Controllers;

use App\Models\Approver;
use App\Models\Liquidation;
use App\Models\cvr_approval;
use App\Models\CashVoucher;
use App\Models\Allocation;
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
            $query->whereIn('cvr_type', ['delivery', 'pullout', 'accessorial', 'freight', 'others'])
                ->where('status', '2');
        })
        ->get();

        foreach ($data as $item) {
            $cashVoucher = $item->cashVoucher;
            $drId = $cashVoucher->deliveryRequest->id ?? null;
            $cvrType = $cashVoucher->cvr_type ?? null;

            $allocation = null;

            if ($drId && $cvrType) {
                $allocation = Allocation::where('dr_id', $drId)
                            ->where('trip_type', $cvrType)
                            ->first();
            }

            // Attach to item so view can use it
            $item->allocation = $allocation;
        }

        return view('liquidations.index', compact('data', 'allocation'));
    }

    public function indexAdmin()
    {
         $data = cvr_approval::with('cashVoucher')
        ->where('status', '1')
        ->whereHas('cashVoucher', function ($query) {
            $query->whereIn('cvr_type', ['admin', 'rpm'])
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
            'prepared_by' => 'required|integer',
            'noted_by' => 'required|integer',
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
        $employees = User::whereIn('id', [1, 41, 15, 5])->get();
        $preparers = User::all();
        
        return view('liquidations.liquidate', compact('liquidation', 'employees', 'preparers'));
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
        $employees = User::whereIn('id', [41])->get();
        $staffs = User::all();
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

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0) + floatval($liquidation->cvrApproval->charge ?? 0);
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
            'nextStatus',
            'staffs'
        ));
    }


    public function validateLiquidation(Request $request, $id)
    {
        $request->validate([
            'validated_by' => 'required|exists:users,id',  // adjust table name accordingly
        ]);

        $liquidation = Liquidation::findOrFail($id);

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0) + floatval($liquidation->cvrApproval->charge ?? 0);

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
        $employees = User::whereIn('id', [54, 15, 35])->get();
        $staffs = User::all();
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

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0) + floatval($liquidation->cvrApproval->charge ?? 0);
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
            'nextStatus',
            'staffs'
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

       $employees = User::whereIn('id', [54])->get();
        $approvers = Approver::all();

        // Calculate total liquidated cash
        $totalCash = 0;

        foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field) {
            $totalCash += floatval($liquidation->$field ?? 0);
        }

        $totalCash += floatval($liquidation->cash_charge ?? 0);

        // Decode and iterate over gasoline, if it's a string (JSON-encoded)
        $gasoline = is_array($liquidation->gasoline) ? $liquidation->gasoline : json_decode($liquidation->gasoline, true) ?? [];
        foreach ($gasoline as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        // Decode and iterate over RFID
        $rfid = is_array($liquidation->rfid) ? $liquidation->rfid : json_decode($liquidation->rfid, true) ?? [];
        foreach ($rfid as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        // Decode and iterate over others
        $others = is_array($liquidation->others) ? $liquidation->others : json_decode($liquidation->others, true) ?? [];
        foreach ($others as $item) {
            $totalCash += floatval($item['amount'] ?? 0);
        }

        // Total Card Expenses (non-cash)
        $totalCard = 0;

        // Decode and iterate over gasoline for card type
        foreach ($gasoline as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        // Decode and iterate over RFID for card type
        foreach ($rfid as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        // Add refund/return from running balances
        $refundReturnTotal = $liquidation->runningBalances->sum('amount');
        $finalLiquidated = $totalCash + $refundReturnTotal;

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0) + floatval($liquidation->cvrApproval->charge ?? 0);
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

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0) + floatval($liquidation->cvrApproval->charge ?? 0);
 
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

    public function liquidationList()
    {
        // Fetch ALL liquidations with their immediate cashVoucher
        $liquidations = Liquidation::with('cashVoucher')->get();

        $liquidations->each(function ($liquidation) {
            $cashVoucher = $liquidation->cashVoucher;

            // Initialize total expenses
            $totalExpenses = 0;

            if ($cashVoucher) {
                // Load nested relationships
                $cashVoucher->load([
                    'deliveryRequest.company',
                    'deliveryRequest.expenseType',
                    'withholdingTax',
                ]);

                $deliveryRequest = $cashVoucher->deliveryRequest;

                // Load allocation relation dynamically
                $allocationRelation = match ($cashVoucher->cvr_type) {
                    'delivery'     => 'deliveryAllocations',
                    'pullout'      => 'pulloutAllocations',
                    'accessorial'  => 'accessorialAllocations',
                    'freight'      => 'freightAllocations',
                    'others', 'admin', 'rpm' => 'othersAllocations',
                    default        => null,
                };

                if ($deliveryRequest && $allocationRelation && method_exists($deliveryRequest, $allocationRelation)) {
                    $liquidation->allocations = $deliveryRequest->$allocationRelation()->with('truck')->get();
                } else {
                    $liquidation->allocations = collect();
                }

                // Direct expense fields (numeric)
                $totalExpenses += (float) $liquidation->allowance;
                $totalExpenses += (float) $liquidation->manpower;
                $totalExpenses += (float) $liquidation->hauling;
                $totalExpenses += (float) $liquidation->right_of_way;
                $totalExpenses += (float) $liquidation->roro_expense;
                $totalExpenses += (float) $liquidation->cash_charge;

                // Parse 'others' JSON
                $others = $liquidation->others;
                if (is_string($others)) {
                    $others = json_decode($others, true);
                }
                if (is_array($others)) {
                    foreach ($others as $item) {
                        $totalExpenses += isset($item['amount']) ? (float) $item['amount'] : 0;
                    }
                }

                // Parse 'gasoline' JSON - only type == 'cash'
                $gasoline = $liquidation->gasoline;
                if (is_string($gasoline)) {
                    $gasoline = json_decode($gasoline, true);
                }
                if (is_array($gasoline)) {
                    foreach ($gasoline as $item) {
                        if (($item['type'] ?? '') === 'cash') {
                            $totalExpenses += isset($item['amount']) ? (float) $item['amount'] : 0;
                        }
                    }
                }

                // Parse 'rf_id' JSON - only type == 'cash'
                $rf_id = $liquidation->rf_id;
                if (is_string($rf_id)) {
                    $rf_id = json_decode($rf_id, true);
                }
                if (is_array($rf_id)) {
                    foreach ($rf_id as $item) {
                        if (($item['type'] ?? '') === 'cash') {
                            $totalExpenses += isset($item['amount']) ? (float) $item['amount'] : 0;
                        }
                    }
                }
            } else {
                $liquidation->allocations = collect(); // fallback
            }

            // Attach total expense to the liquidation instance
            $liquidation->total_expense = $totalExpenses;
        });

        return view('liquidations.liquidationList', compact('liquidations'));
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

     public function approvedLiqUpdate(Request $request, $id)
    {
        // Find the liquidation by ID
        $liquidation = Liquidation::findOrFail($id);

        // Validate the request data (e.g., approved_by and expenses)
        $request->validate([
            'allowance' => 'nullable|numeric',
            'manpower' => 'nullable|numeric',
            'hauling' => 'nullable|numeric',
            'right_of_way' => 'nullable|numeric',
            'roro_expense' => 'nullable|numeric',
            'cash_charge' => 'nullable|numeric',
            'gasoline' => 'nullable|array',
            'gasoline.*.type' => 'nullable|string',
            'gasoline.*.amount' => 'nullable|numeric',
            'rfid' => 'nullable|array',
            'rfid.*.tag' => 'nullable|string',
            'rfid.*.type' => 'nullable|string',
            'rfid.*.amount' => 'nullable|numeric',
            'others' => 'nullable|array',
            'others.*.description' => 'nullable|string',
            'others.*.amount' => 'nullable|numeric',
        ]);

        // Update fields with the form data
        $liquidation->update([
            'approved_by' => $request->approved_by,
            'allowance' => $request->allowance ?? 0,
            'manpower' => $request->manpower ?? 0,
            'hauling' => $request->hauling ?? 0,
            'right_of_way' => $request->right_of_way ?? 0,
            'roro_expense' => $request->roro_expense ?? 0,
            'cash_charge' => $request->cash_charge ?? 0,
            'gasoline' => $request->gasoline ?? [], // Encode array as JSON
            'rfid' => $request->rfid ?? [], // Encode array as JSON
            'others' => $request->others ?? [], // Encode array as JSON
        ]);

        // Redirect with success message
        return redirect()->route('liquidations.approvalList') // Or wherever you want to redirect
            ->with('success', 'Liquidation updated and approved successfully!');
    }
}
