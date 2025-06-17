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
use Illuminate\Support\Facades\Log;

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
                            ->where('sequence', $cashVoucher->sequence) // <-- Added filter here
                            ->first();
            }

            // Attach to item so view can use it
            $item->allocation = $allocation;
        }

        return view('liquidations.index', compact('data'));
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
        $gasoline = $request->input('gasoline', []);
        $rfid = $request->input('rfid', []);
        $others = $request->input('others', []);

        // Calculate total liquidated amount (CASH ONLY for gasoline & RFID)
        $totalLiquidated = 0;
        $totalLiquidated += floatval($expenses['allowance'] ?? 0);
        $totalLiquidated += floatval($expenses['manpower'] ?? 0);
        $totalLiquidated += floatval($expenses['hauling'] ?? 0);
        $totalLiquidated += floatval($expenses['right_of_way'] ?? 0);
        $totalLiquidated += floatval($expenses['roro_expense'] ?? 0);
        $totalLiquidated += floatval($expenses['cash_charge'] ?? 0);

        foreach ($gasoline as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalLiquidated += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($rfid as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalLiquidated += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($others as $item) {
            $totalLiquidated += floatval($item['amount'] ?? 0);
        }

        // Get approved amount from CashVoucher
        $cashVoucher = CashVoucher::find($request->input('cvr_id'));
        $approvedAmount = floatval($cashVoucher->amount ?? 0);

        // Determine Liquidation status
        $status = 1; // default

        // Prepare data
        $data = [
            'cvr_id' => $request->input('cvr_id'),
            'cvr_number' => $request->input('cvr_number'),
            'cvr_approval_id' => $request->input('cvr_approval_id'),

            'allowance' => $expenses['allowance'] ?? null,
            'manpower' => $expenses['manpower'] ?? null,
            'hauling' => $expenses['hauling'] ?? null,
            'right_of_way' => $expenses['right_of_way'] ?? null,
            'roro_expense' => $expenses['roro_expense'] ?? null,
            'cash_charge' => $expenses['cash_charge'] ?? null,

            'gasoline' => array_values($gasoline),
            'rfid' => array_values($rfid),
            'others' => array_values($others),

            'status' => $status,
            'prepared_by' => $request->input('prepared_by'),
            'noted_by' => $request->input('noted_by'),
            'validated_by' => null,
            'collected_by' => null,
            'approved_by' => null,
        ];

        // Create the Liquidation record
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
        $liquidations = Liquidation::with(['preparedBy', 'notedBy', 'cashVoucher'])
            ->where('status', 1)
            ->paginate(10);

        foreach ($liquidations as $liquidation) {
            $cashVoucher = $liquidation->cashVoucher;

            if (!$cashVoucher) {
                continue; // Skip if no associated CashVoucher
            }

            $cvrType = $cashVoucher->cvr_type;
            $dr = $cashVoucher->deliveryRequest ?? null;

            // Only get allocation for these CVR types
            if (in_array($cvrType, ['delivery', 'others', 'rpm', 'freight', 'accessorial', 'pullout']) && $dr) {
                $allocation = Allocation::where('dr_id', $dr->id)
                    ->where('trip_type', $cvrType)
                    ->where('sequence', $cashVoucher->sequence)
                    ->first();

                $liquidation->allocation = $allocation;
                $liquidation->deliveryRequest = $dr;
            }
        }

        return view('liquidations.reviewList', compact('liquidations'));
    }

    public function review($id)
    {
        // Load liquidation with related data
        $liquidation = Liquidation::with('cashVoucher', 'cvrApproval', 'preparedBy', 'notedBy')->findOrFail($id);
        $employees = User::whereIn('id', [41])->get(); // You can adjust this condition as needed
        $staffs = User::all();
        $approvers = Approver::all();

        // Calculate total liquidated cash (cash only)
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

        // Calculate total non-cash (card) expenses
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

        // Approved amount from CVR
        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0) + floatval($liquidation->cvrApproval->charge ?? 0);

        // Raw difference (before adjustments)
        $difference = $totalCash - $approvedAmount;
        $totalLiquidated = $totalCash;
        // Load running balances
        $runningRefunds = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '3') // Refund
            ->get();

        $runningReturns = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '2') // Returned cash
            ->get();

        $runningUncollected = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '4') // Uncollected
            ->get();

        // Sum of existing refunds
        $refundTotal = $runningRefunds->sum(function ($item) {
            return isset($item->amount) ? abs($item->amount) : 0;
        });

        // Combine returns and uncollected
        $combinedReturns = collect();
        if ($runningReturns) {
            $combinedReturns = $combinedReturns->merge($runningReturns);
        }
        if ($runningUncollected) {
            $combinedReturns = $combinedReturns->merge($runningUncollected);
        }

        $returnedTotal = $combinedReturns->sum(function ($item) {
            return isset($item->amount) ? abs($item->amount) : 0;
        });

        // Adjusted difference
        $adjustedDifference = round($difference + $refundTotal + $returnedTotal, 2);

        // Decide next step
        $nextStatus = 4; // default to "For Approval"
        $refund = false;
        $return = false;

        if (
            ($approvedAmount == 0 && $totalLiquidated == 0) ||
            ($approvedAmount == $totalLiquidated)
        ) {
            // Perfect match or no transaction
            $nextStatus = 4;
        } elseif ($adjustedDifference > 0) {
            // Over-liquidated: user returned excess cash
            $refund = true;
            $nextStatus = 4;
        } elseif ($adjustedDifference < 0) {
            // Under-liquidated: user owes money
            $return = true;
            $nextStatus = 3;
        }

        // Return view
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
            'staffs',
            'runningRefunds',
            'runningReturns',
            'runningUncollected'
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


    public function validatedList()
    {
        $liquidations = Liquidation::with('preparedBy', 'notedBy', 'cashVoucher')
            ->where('status', 3)
            ->paginate(10);

        foreach ($liquidations as $liquidation) {
            $cashVoucher = $liquidation->cashVoucher;

            if (!$cashVoucher) {
                continue; // Skip if no associated CashVoucher
            }

            $cvrType = $cashVoucher->cvr_type;
            $dr = $cashVoucher->deliveryRequest ?? null;

            // Only get allocation for these CVR types
            if (in_array($cvrType, ['delivery', 'others', 'rpm', 'freight', 'accessorial', 'pullout']) && $dr) {
                $allocation = Allocation::where('dr_id', $dr->id)
                    ->where('trip_type', $cvrType)
                    ->where('sequence', $cashVoucher->sequence)
                    ->first();

                $liquidation->allocation = $allocation;
                $liquidation->deliveryRequest = $dr;
            }
        }

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

        $runningRefunds = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '3')
            ->get();

        $runningReturns = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '2')
            ->get();

        $runningUncollected = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '4')
            ->get();

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
            'staffs',
            'runningRefunds',
            'runningReturns',
            'runningUncollected'
        ));
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:1000',
            'validated_by' => 'required|exists:users,id',
        ]);

        $liquidation = Liquidation::findOrFail($id);
        $liquidation->status = 10; // Rejected
        $liquidation->validated_by = $request->validated_by;
        $liquidation->validated_at = now();
        $liquidation->remarks = $request->remarks;
        $liquidation->save();

        return redirect()->route('liquidations.index')->with('error', 'Liquidation has been rejected.');
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
        
        foreach ($liquidations as $liquidation) {
            $cashVoucher = $liquidation->cashVoucher;

            if (!$cashVoucher) {
                continue; // Skip if no associated CashVoucher
            }

            $cvrType = $cashVoucher->cvr_type;
            $dr = $cashVoucher->deliveryRequest ?? null;

            // Only get allocation for these CVR types
            if (in_array($cvrType, ['delivery', 'others', 'rpm', 'freight', 'accessorial', 'pullout']) && $dr) {
                $allocation = Allocation::where('dr_id', $dr->id)
                    ->where('trip_type', $cvrType)
                    ->where('sequence', $cashVoucher->sequence)
                    ->first();

                $liquidation->allocation = $allocation;
                $liquidation->deliveryRequest = $dr;
            }
        }

        return view('liquidations.approvalList', compact('liquidations'));
    }

    public function approval(Request $request, $id)
    {
        $liquidation = Liquidation::with(['cashVoucher', 'cvrApproval', 'preparedBy', 'notedBy', 'runningBalances'])
                        ->findOrFail($id);

        $employees = User::whereIn('id', [54])->get();
        $approvers = Approver::all();
        $staffs = User::all();

        // Calculate total liquidated cash
        $totalCash = 0;
        foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field) {
            $totalCash += floatval($liquidation->$field ?? 0);
        }
        $totalCash += floatval($liquidation->cash_charge ?? 0);

        // Decode JSON fields
        $gasoline = is_array($liquidation->gasoline) ? $liquidation->gasoline : json_decode($liquidation->gasoline, true) ?? [];
        $rfid = is_array($liquidation->rfid) ? $liquidation->rfid : json_decode($liquidation->rfid, true) ?? [];
        $others = is_array($liquidation->others) ? $liquidation->others : json_decode($liquidation->others, true) ?? [];

        // Add cash-based gasoline
        foreach ($gasoline as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        // Add cash-based RFID
        foreach ($rfid as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        // Add all "others" amounts
        foreach ($others as $item) {
            $totalCash += floatval($item['amount'] ?? 0);
        }

        // Total Card Expenses
        $totalCard = 0;
        foreach ($gasoline as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($rfid as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        // Properly calculate each running balance type
        $refundTotal = $liquidation->runningBalances
            ->where('type', '3') // Refund
            ->sum(fn($item) => abs($item->amount));

        $returnTotal = $liquidation->runningBalances
            ->where('type', '2') // Return
            ->sum(fn($item) => abs($item->amount));

        $uncollectedTotal = $liquidation->runningBalances
            ->where('type', '4') // Uncollected
            ->sum(fn($item) => abs($item->amount));

        // Calculate final liquidated (Cash spent + Returned + Uncollected)
        $finalLiquidated = $totalCash + $returnTotal + $uncollectedTotal;

        // Approved amount
        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0) + floatval($liquidation->cvrApproval->charge ?? 0);

        // Final difference
        $difference = $approvedAmount - $finalLiquidated;

        // Determine status
        $refund = $difference < 0;
        $return = $difference > 0;
        $nextStatus = $refund ? 3 : 4;

        // Separate running balances for display
        $runningRefunds = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '3')->get();

        $runningReturns = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '2')->get();

        $runningUncollected = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '4')->get();

        return view('liquidations.approval', compact(
            'liquidation',
            'employees',
            'approvers',
            'totalCash',
            'totalCard',
            'refundTotal',
            'returnTotal',
            'uncollectedTotal',
            'finalLiquidated',
            'approvedAmount',
            'difference',
            'refund',
            'return',
            'nextStatus',
            'runningRefunds',
            'runningReturns',
            'runningUncollected',
            'gasoline',
            'rfid',
            'others',
            'staffs'
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
            'gasoline' => array_values($request->gasoline ?? []),
            'rfid' => array_values($request->rfid ?? []),
            'others' => array_values($request->others ?? []),
        ]);

        // Redirect with success message
        return redirect()->route('liquidations.approval', $liquidation->id)
        ->with('success', 'Liquidation details updated!');
    }

    public function approvedCollection(Request $request, $id)
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
            'gasoline' => array_values($request->gasoline ?? []),
            'rfid' => array_values($request->rfid ?? []),
            'others' => array_values($request->others ?? []),
        ]);

        // Redirect with success message
        return redirect()->route('liquidations.approvalList') // Or wherever you want to redirect
            ->with('success', 'Liquidation updated and approved successfully!');
    }

    public function approvedValidation(Request $request, $id)
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
            'gasoline' => array_values($request->gasoline ?? []),
            'rfid' => array_values($request->rfid ?? []),
            'others' => array_values($request->others ?? []),
        ]);

        // Redirect with success message
        return redirect()->route('liquidations.approvalList') // Or wherever you want to redirect
            ->with('success', 'Liquidation updated and approved successfully!');
    }

    public function rejectedList()
    {
        // Fetch ALL liquidations with their immediate cashVoucher
        $liquidations = Liquidation::with('cashVoucher')
        ->where('status', 10)
        ->get();

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

          return view('liquidations.rejectList', compact('liquidations'));
    }

    public function rejectEdit($id)
    {
        $liquidation = Liquidation::with('cashVoucher')->findOrFail($id);

        $employees = User::whereIn('id', [1, 41, 15, 5])->get();
        $preparers = User::all();

        return view('liquidations.rejectEdit', compact('liquidation', 'preparers', 'employees'));
    }

    public function rejectUpdate(Request $request, $id)
    {
        // Log the raw incoming request data
        Log::info('Reject Update Request:', $request->all());

        $liquidation = Liquidation::findOrFail($id);

        $data = $request->validate([
            'expenses.allowance' => 'nullable|numeric',
            'expenses.manpower' => 'nullable|numeric',
            'expenses.hauling' => 'nullable|numeric',
            'expenses.right_of_way' => 'nullable|numeric',
            'expenses.roro_expense' => 'nullable|numeric',
            'expenses.cash_charge' => 'nullable|numeric',
            'gasoline' => 'nullable|array',
            'rfid' => 'nullable|array',
            'others' => 'nullable|array',
            'prepared_by' => 'required|exists:users,id',
            'noted_by' => 'nullable|exists:users,id',
        ]);

        $expenses = $data['expenses'];

        // Log extracted arrays individually
        Log::info('Parsed Arrays:', [
            'gasoline' => $request->gasoline,
            'rfid' => $request->rfid,
            'others' => $request->others,
        ]);

        $liquidation->update([
            'approved_by' => $request->approved_by ?? null,
            'allowance' => $expenses['allowance'] ?? 0,
            'manpower' => $expenses['manpower'] ?? 0,
            'hauling' => $expenses['hauling'] ?? 0,
            'right_of_way' => $expenses['right_of_way'] ?? 0,
            'roro_expense' => $expenses['roro_expense'] ?? 0,
            'cash_charge' => $expenses['cash_charge'] ?? 0,
            'gasoline' => array_values($request->gasoline ?? []),
            'rfid' => array_values($request->rfid ?? []),
            'others' => array_values($request->others ?? []),
            'prepared_by' => $request->prepared_by,
            'noted_by' => $request->noted_by,
            'status' => 1,
        ]);

        Log::info('Liquidation Updated:', $liquidation->toArray());

        return redirect()->route('liquidations.rejectedList')->with('success', 'Liquidation updated successfully.');
    }
}
