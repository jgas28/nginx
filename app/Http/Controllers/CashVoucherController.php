<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\Approver;
use Illuminate\Http\Request;
use App\Models\cvr_approval;
use App\Models\DeliveryRequest;
use App\Models\DeliveryRequestLineItem;
use App\Models\User;
use App\Models\MonthlySeriesNumber;
use App\Models\cvr_request_type;
use App\Models\CashVoucher;
use App\Models\FleetCard;
use App\Models\Truck;
use App\Models\WithholdingTax;
use App\Models\RunningBalance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Empty_;
use NumberToWords\NumberToWords;
use DateTime;
use Illuminate\Support\Facades\Auth;

class CashVoucherController extends Controller
{
    //
    public function index(Request $request)
    {
        // Get the search query from the request
        $search = $request->get('search');

        // Fetch related delivery line items by joining with the correct table name
       $deliveryRequests = DeliveryRequest::with(['lineItems', 'region', 'company'])
        ->when($search, function ($query, $search) {
            return $query->where('mtm', 'like', '%' . $search . '%');
        })
        ->where('status', 1)
        ->whereHas('lineItems', function ($query) {
            $query->where('delivery_status', '"9"'); // or simply 9 if stored as number
        })
        ->paginate(10);

        // Get employees for the view (you can use it for dropdowns or other use cases)
        $employees = User::all();

        return view('cashVoucherRequests.index', compact('deliveryRequests', 'employees', 'search'));
    }

    public function approval(Request $request)
    { 
        // Get the search query from the request
        $search = $request->get('search');
    
        // Fetch related delivery line items by joining with the correct table name
        $deliveryRequests = CashVoucher::with([
            'deliveryRequest.deliveryAllocations' => function ($query) {
                $query->orderBy('sequence');
            },
            'deliveryRequest.pulloutAllocations',
            'deliveryRequest.accessorialAllocations',
            'deliveryRequest.othersAllocations',
            'deliveryRequest.freightAllocations',
            'cvrTypes'
        ])
        ->when($search, function ($query, $search) {
            return $query->whereHas('deliveryRequest', function ($q) use ($search) {
                $q->where('mtm', 'like', '%' . $search . '%');
            });
        })
        ->where('status', 1)
        ->whereNotIn('cvr_type', ['admin', 'rpm'])
        ->paginate(10);

        foreach ($deliveryRequests as $cashVoucher) {
            $allAllocations = collect([
                ...($cashVoucher->deliveryRequest->deliveryAllocations ?? []),
                ...($cashVoucher->deliveryRequest->pulloutAllocations ?? []),
                ...($cashVoucher->deliveryRequest->accessorialAllocations ?? []),
                ...($cashVoucher->deliveryRequest->othersAllocations ?? []),
                ...($cashVoucher->deliveryRequest->freightAllocations ?? []),
            ]);

            $matchedAllocation = $allAllocations->first(function ($allocation) use ($cashVoucher) {
                return $allocation->dr_id == $cashVoucher->dr_id &&
                    strtolower($allocation->trip_type) === strtolower($cashVoucher->cvr_type) &&
                    $allocation->sequence == $cashVoucher->sequence;
            });

            // Add this to the model temporarily so you can access in the view
            $cashVoucher->matched_allocation = $matchedAllocation;
        }
        // Check if the request expects an AJAX response
        if ($request->ajax()) {
            return view('cashVoucherRequests.approval', compact('deliveryRequests'))->render();
        }

        // For the normal view
        return view('cashVoucherRequests.approval', compact('deliveryRequests', 'search'));
    }    

    public function accessorial(Request $request) 
    {
        // Get the search query from the request
        $search = $request->get('search');

        // Fetch related delivery line items by joining with the correct table name
        $deliveryRequests = DeliveryRequest::with(['lineItems', 'region', 'company', 'lineItems.deliveryStatus'])
        ->when($search, function ($query, $search) {
            return $query->where('mtm', 'like', '%' . $search . '%');
        })
        ->whereHas('lineItems', function ($query) {
            $query->whereNotNull('accessorial_type'); 
        })
        ->where('status', '!=', 0)
        ->paginate(10);

        // Get employees for the view (you can use it for dropdowns or other use cases)
        $employees = User::all();

        return view('cashVoucherRequests.accessorial', compact('deliveryRequests', 'employees', 'search'));
    }

    public function edit(Request $request)
    {
        // Get the search query from the request
        $search = $request->get('search');

        // Fetch related delivery line items by joining with the correct table name
        $deliveryRequests = DeliveryRequest::with(['lineItems', 'region', 'company', 'lineItems.deliveryStatus'])
            ->when($search, function ($query, $search) {
                return $query->where('mtm', 'like', '%' . $search . '%');
            })
            ->paginate(10); // Pagination

        // Get employees for the view (you can use it for dropdowns or other use cases)
        $employees = User::all();

        return view('cashVoucherRequests.index', compact('deliveryRequests', 'employees', 'search'));
    }

    public function request($id)
    {
        $deliveryLineItems = DeliveryRequest::join('delivery_request_line_items', 'delivery_request.id', '=', 'delivery_request_line_items.dr_id')
            ->where('delivery_request.id', $id)
            ->where('delivery_request_line_items.status', '!=', 0)
            ->select('delivery_request.*', 'delivery_request_line_items.*', 'delivery_request.id as request_id')
            ->get();

        $allocation = Allocation::where('dr_id', $id)->first();

        if ($deliveryLineItems->isEmpty()) {
            abort(404, 'No delivery request line items found.');
        }

        $mtms = $deliveryLineItems->pluck('mtm')->unique();

        // Determine relevant trucks
        $truckIds = DeliveryRequestLineItem::whereIn('mtm', $mtms)->distinct()->pluck('truck_id');
        $trucks = Truck::all();

        $firstDeliveryLineItem = $deliveryLineItems->first();
        $company_id = $firstDeliveryLineItem->company_id ?? null;

        // Preview CVR Number (non-incremental, only for display)
        $monthlySeries = MonthlySeriesNumber::where('company_id', $company_id)->first();
        $nextCvrNumber = $monthlySeries ? $monthlySeries->series_number + 1 : 1;

        // Extract required codes for formatting
        $companyCode = DB::table('companies')->where('id', $company_id)->value('company_code') ?? '';
        $customerCode = DB::table('customers')->where('id', $firstDeliveryLineItem->customer_id)->value('name') ?? '';
        $expenseCode = DB::table('expense_types')->where('id', $firstDeliveryLineItem->expense_type_id)->value('expense_code') ?? '';

        $truckCode = Truck::where('id', $firstDeliveryLineItem->truck_id)->value('truck_code') ?? '';

        // Handle potential rollover
        $currentDate = new DateTime();
        $lastDayOfMonth = $currentDate->format('t');
        if ((int)$currentDate->format('j') === (int)$lastDayOfMonth) {
            $currentDate->modify('first day of next month');
        }

        $currentYear = $currentDate->format('Y');
        $currentMonth = $currentDate->format('m');
        $nextCvrNumberFormatted = sprintf('%03d', $nextCvrNumber);

        $formattedCvrNumber = "CVR-{$currentYear}-{$currentMonth}-{$nextCvrNumberFormatted}";

        // Fetch other required data
        $requestType = cvr_request_type::all();
        $employees = User::all();
        $fleetCards = FleetCard::all();
        $taxes = WithholdingTax::all();

        return view('cashVoucherRequests.request', compact(
            'deliveryLineItems',
            'nextCvrNumberFormatted',
            'formattedCvrNumber',
            'requestType',
            'employees',
            'fleetCards',
            'trucks',
            'taxes', 
            'allocation'
        ));
    }


    public function accessorialRequest($id)
    {
        $deliveryLineItems = DeliveryRequest::join('delivery_request_line_items', 'delivery_request.id', '=', 'delivery_request_line_items.dr_id')
        ->leftJoin('accessorial_types', DB::raw('REPLACE(delivery_request_line_items.accessorial_type, \'"\', \'\')'), '=', 'accessorial_types.id')
        ->where('delivery_request.id', $id)
        ->where('delivery_request_line_items.status', '!=', 0)
        ->whereNotNull('delivery_request_line_items.accessorial_type')
        ->select(
            'delivery_request.*',
            'delivery_request_line_items.*',
            'delivery_request.id as request_id',
            'accessorial_types.accessorial_types_name as accessorial_type_name'
        )
        ->get();


        $cvr = MonthlySeriesNumber::all();
        $requestType = cvr_request_type::all();
        $employees = User::all();
        $fleetCards = FleetCard::All();

        return view('cashVoucherRequests.accessorialRequest', compact('deliveryLineItems', 'cvr', 'requestType', 'employees', 'fleetCards'));
    }

    public function store(Request $request)
    {
        Log::info('Request Data:', ['data' => $request->all()]);
        $company_id = $request->company_id;
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric',
                'request_type' => 'required',
                'requestor' => 'required',
                'mtm' => 'required',
                'remarks' => 'nullable|array',
                'remarks.*' => 'nullable|string',
                'voucher_type' => 'required|in:regular,with_tax',
                'withholding_tax' => 'nullable|exists:withholding_taxes,id',
                'tax_base_amount' => 'nullable|numeric|min:0',
            ]);
            Log::info('Validation Passed:', ['validated_data' => $validated]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Errors:', ['errors' => $e->errors()]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        $sequence = CashVoucher::where('dr_id', $request->dr_id)
        ->where('cvr_type', $request->cvr_type)
        ->count() + 1;

        // Run everything in a DB transaction
        DB::transaction(function () use ($company_id, $request, $sequence) {
            // Handle potential rollover
            $currentDate = new DateTime(); // Always current date
            $yearMonth = $currentDate->format('Y-m');
            $year = $currentDate->format('Y');
            $month = $currentDate->format('m');
            $isFirstDayOfMonth = $currentDate->format('j') === '1';

            // Lock and fetch the row for this company
            $monthlySeries = MonthlySeriesNumber::where('company_id', $company_id)
                ->lockForUpdate()
                ->first();

            if (!$monthlySeries) {
                // Create if not exists
                $monthlySeries = MonthlySeriesNumber::create([
                    'company_id' => $company_id,
                    'month' => $yearMonth,
                    'series_number' => 1,
                ]);
                $nextCvrNumber = 1;
                Log::info("Created MonthlySeriesNumber: company_id = $company_id, month = $yearMonth, series = 1");
            } else {
                if ($isFirstDayOfMonth || $monthlySeries->month !== $yearMonth) {
                    // Reset series if it's first day OR stored month is outdated
                    $monthlySeries->update([
                        'month' => $yearMonth,
                        'series_number' => 1,
                    ]);
                    $nextCvrNumber = 1;
                    Log::info("Reset MonthlySeriesNumber: company_id = $company_id, new month = $yearMonth, series = 1");
                } else {
                    // Normal increment
                    $monthlySeries->increment('series_number');
                    $nextCvrNumber = $monthlySeries->series_number;
                    Log::info("Incremented MonthlySeriesNumber: company_id = $company_id, series = $nextCvrNumber");
                }
            }

            // Construct the final CVR number
            $currentYear = $currentDate->format('Y');
            $currentMonth = $currentDate->format('m');
            $nextCvrNumberFormatted = sprintf('%03d', $nextCvrNumber);
            $formattedCvrNumber = "CVR-{$currentYear}-{$currentMonth}-{$nextCvrNumberFormatted}/{$company_id}";
            $user = Auth::user();
            $employeeCode = $user->id;

            // Save the actual cash voucher
            $cashVoucher = new CashVoucher([
                'cvr_number' => $formattedCvrNumber,
                'cvr_type' => $request->cvr_type,
                'amount' => $request->amount,
                'request_type' => $request->request_type,
                'requestor' => $request->requestor,
                'mtm' => $request->mtm,    
                'status' => '1',
                'voucher_type' => $request->voucher_type,
                'withholding_tax_id' => $request->voucher_type === 'with_tax' ? $request->withholding_tax : null,
                'tax_based_amount' => $request->voucher_type === 'with_tax' ? $request->tax_base_amount : null,
                'remarks' => $request->has('remarks') ? $request->remarks : null,
                'created_by' => $employeeCode,
                'dr_id' => $request->dr_id,
                'sequence' => $sequence,
            ]);

            Log::info('Saving Cash Voucher:', ['cash_voucher' => $cashVoucher->toArray()]);
            $cashVoucher->save();

            // Update DeliveryRequest status
            $deliveryRequest = DeliveryRequest::where('id', $request->dr_id)->first();
            if ($deliveryRequest && $deliveryRequest->status != 0) {
                $deliveryRequest->status = '1';
                $deliveryRequest->delivery_status = 2;
                $deliveryRequest->save();
                Log::info('Updated DeliveryRequest status to 1.');
            }

            // Update Line Items
            $lineItems = DeliveryRequestLineItem::where('mtm', $request->mtm)
                ->where('status', '!=', 0)
                ->get();

            foreach ($lineItems as $lineItem) {
                $lineItem->status = '1';
                $lineItem->save();
            }

            Log::info('Updated DeliveryRequestLineItems status to 1.');
        });

        return redirect()->route('coordinators.index')
            ->with('success', 'Cash Voucher created successfully and statuses updated.');
    }

    public function store_accessorial(Request $request)
    {
        // Log the request for debugging
        Log::info('Request Data:', $request->all());

        // Log before validation
        Log::info('Validating Request Data...');

        // Validate the incoming data
        try {
            $validated = $request->validate([
                'cvr_number' => 'required|unique:cash_vouchers,cvr_number',
                'amount' => 'required|numeric',
                'request_type' => 'required',
                'requestor' => 'required',
                'mtm' => 'required',
                'helper' => 'nullable|array',
                'helper.*' => 'nullable|string',
                'remarks' => 'nullable|array',
                'remarks.*' => 'nullable|string',
                'driver' => 'required',
                'fleet_card' => 'required',
            ]);
            // Log if validation passed
            Log::info('Validation Passed:', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors if validation fails
            Log::error('Validation Errors:', $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        // Create a new CashVoucher
        $cashVoucher = new CashVoucher([
            'cvr_number' => $request->cvr_number,
            'cvr_type' => $request->cvr_type,  // Hardcoded as per your example
            'amount' => $request->amount,
            'request_type' => $request->request_type,
            'requestor' => $request->requestor,
            'mtm' => $request->mtm,
            'driver' => $request->driver,
            'fleet_card' => $request->fleet_card,
        ]);

        // Handle the helpers (if any) and store them as a JSON array
        if ($request->has('helper') && !empty($request->helper)) {
            $cashVoucher->helpers = json_encode($request->helper);  // Store the helper values as a JSON array
        }

        if ($request->has('remarks') && !empty($request->remarks)) {
            $cashVoucher->remarks = json_encode($request->remarks);  // Store the helper values as a JSON array
        }


        // Log before saving
        Log::info('Saving Cash Voucher:', $cashVoucher->toArray());

        // Save the new cash voucher record
        $cashVoucher->save();

        // Log after saving
        Log::info('Cash Voucher Saved Successfully.');

        // Update DeliveryRequest and DeliveryRequestLineItem statuses
        try {
            // Update DeliveryRequest status only if the status is not 0
            $deliveryRequest = DeliveryRequest::where('mtm', $request->mtm)->first();
            if ($deliveryRequest) {
                if ($deliveryRequest->status != 0) {
                    $deliveryRequest->status = '1'; // Example: Change status
                    $deliveryRequest->save();
                    Log::info('DeliveryRequest status updated successfully.');
                }
            }

            // Update DeliveryRequestLineItem status where status != 0
            $lineItems = DeliveryRequestLineItem::where('mtm', $request->mtm)
                ->where('status', '!=', 0)  // Only update line items where the status is not 0
                ->get();

            foreach ($lineItems as $lineItem) {
                $lineItem->status = '1'; // Example: Change status
                $lineItem->save();
            }
            Log::info('DeliveryRequestLineItems status updated successfully.');
        } catch (\Exception $e) {
            // Log any errors that occurred during the update process
            Log::error('Error updating statuses:', ['message' => $e->getMessage()]);
        }

        // Redirect with success message
        return redirect()->route('cashVoucherRequests.accessorial')
                        ->with('success', 'Cash Voucher created successfully and statuses updated.');
    }
    
    public function approvalRequest($id)
    {
        Log::info("Approval request ID: $id");  // Log the ID to see if it's reaching here
        $deliveryRequestId = $id;

        $cashVouchers = CashVoucher::where('id', $id)->first();
        $deliveryRequests = DeliveryRequest::where('id', $cashVouchers->dr_id)->first();
        $allocations = Allocation::where('dr_id', $cashVouchers->dr_id)
            ->where('trip_type', $cashVouchers->cvr_type)
            ->where('sequence', $cashVouchers->sequence)
            ->first();

        $employees = User::all();
        $approves = Approver::all(); 

        return view('cashVoucherRequests.approvalRequest', compact('deliveryRequestId', 'employees', 'approves', 'cashVouchers', 'allocations', 'deliveryRequests'));
    }

    public function reject(Request $request)
    {
        $request->validate([
            'cvr_id' => 'required|integer',
            'reject_remarks' => 'nullable|string',
        ]);

        $cashVoucher = CashVoucher::find($request->cvr_id);

        if ($cashVoucher) {
            $cashVoucher->status = 3;

            // Decode existing remarks and append new one
            $existingRemarks = json_decode($cashVoucher->reject_remarks, true) ?? [];
            $existingRemarks[] = $request->reject_remarks;

            $cashVoucher->reject_remarks = json_encode($existingRemarks);
            $cashVoucher->save();

             return redirect()->route('cashVoucherRequests.approval')->with('success', 'Cash Voucher Approval Rejected Successfully');
        }

        return redirect()->back()->with('error', 'Cash Voucher not found.');
    }

    public function approvalRequestStore(Request $request)
    {
        // Step 1: Validate request
        $validated = $request->validate([
            'cvr_id' => 'required|exists:cash_vouchers,id',
            'cvr_number' => 'required|string',
            'payment_type' => 'required|in:cash,bank_transfer,outlet_transfer',

            // Cash fields
            'cash_amount' => 'sometimes|required_if:payment_type,cash|numeric',
            'cash_receiver' => 'sometimes|required_if:payment_type,cash|string',
            'cash_fund_source' => 'sometimes|required_if:payment_type,cash|string',
            'reference_number' => 'nullable|string',

            // Bank fields
            'bank_name' => 'sometimes|required_if:payment_type,bank_transfer|string',
            'bank_reference_number' => 'sometimes|required_if:payment_type,bank_transfer|string',
            'bank_amount' => 'sometimes|required_if:payment_type,bank_transfer|numeric',
            'bank_receiver' => 'sometimes|required_if:payment_type,bank_transfer|string',
            'bank_fund_source' => 'sometimes|required_if:payment_type,bank_transfer|string',
            'bank_charge' => 'nullable|numeric',

            // Outlet fields
            'outlet_name' => 'sometimes|required_if:payment_type,outlet_transfer|string',
            'outlet_reference_number' => 'sometimes|required_if:payment_type,outlet_transfer|string',
            'outlet_amount' => 'sometimes|required_if:payment_type,outlet_transfer|numeric',
            'outlet_receiver' => 'sometimes|required_if:payment_type,outlet_transfer|string',
            'outlet_fund_source' => 'sometimes|required_if:payment_type,outlet_transfer|string',
            'outlet_charge' => 'nullable|numeric',
        ]);


        Log::info('Full Request Data', $request->all());

        // Step 2: Extract payment fields
        $paymentName = '';
        $reference_number = '';
        $amount = 0;
        $receiver = '';
        $fund_source = '';
        $charge = 0;

        switch ($request->payment_type) {
            case 'cash':
                $paymentName = 'Cash';
                $reference_number = $request->reference_number;
                $amount = $request->cash_amount;
                $receiver = $request->cash_receiver;
                $fund_source = $request->cash_fund_source;
                $charge = 0;
                break;

            case 'bank_transfer':
                $paymentName = $request->bank_name;
                $reference_number = $request->bank_reference_number;
                $amount = $request->bank_amount;
                $receiver = $request->bank_receiver;
                $fund_source = $request->bank_fund_source;
                $charge = $request->bank_charge ?? 0;
                break;

            case 'outlet_transfer':
                $paymentName = $request->outlet_name;
                $reference_number = $request->outlet_reference_number;
                $amount = $request->outlet_amount;
                $receiver = $request->outlet_receiver;
                $fund_source = $request->outlet_fund_source;
                $charge = $request->outlet_charge ?? 0;
                break;
        }

        $user = Auth::user();
        $employeeCode = $user->id;

        // Step 3: Start transaction
        DB::beginTransaction();

        try {
            // Step 4: Save cvr_approval
            $cvrApproval = new cvr_approval();
            $cvrApproval->fill([
                'payment_type' => $request->payment_type,
                'payment_name' => $paymentName,
                'reference_number' => $reference_number,
                'amount' => $amount,
                'receiver' => $receiver,
                'source' => $fund_source,
                'charge' => $charge,
                'cvr_number' => $request->cvr_number,
                'status' => 1,
                'created_by' => $employeeCode,
                'cvr_id' => $request->cvr_id,
            ]);
            $cvrApproval->saveOrFail();

            Log::info('Cash Voucher Approval saved successfully', [
                'cvr_approval_id' => $cvrApproval->id,
                'amount' => $amount,
                'receiver' => $receiver,
            ]);

            // Step 5: Update CashVoucher status
            $cashVoucher = CashVoucher::where('cvr_number', $request->cvr_number)->firstOrFail();
            $cashVoucher->status = 2;
            $cashVoucher->saveOrFail();

            Log::info('Cash Voucher status updated to 2', [
                'cash_voucher_id' => $cashVoucher->id,
                'new_status' => $cashVoucher->status,
            ]);

            // Step 6: Save running balance
            $totalAmount = floatval($amount) + floatval($charge);
            RunningBalance::create([
                'type' => 8,
                'amount' => -1 * $totalAmount,
                'description' => $cashVoucher->cvr_number,
                'employee_id' => $receiver,
                'approver_id' => $fund_source,
                'created_by' => $employeeCode,
                'cvr_number' => $cashVoucher->cvr_number,
            ]);

            // Step 7: Commit transaction
            DB::commit();

            return redirect()->route('cashVoucherRequests.approval')
                ->with('success', 'Cash Voucher Approval Saved Successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to process Cash Voucher Approval', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('cashVoucherRequests.approval')
                ->with('error', 'DB Error: ' . $e->getMessage());
        }
    }


    public function cvrList(Request $request)
    {
        $search = $request->get('search');

        $cashVoucherRequests = CashVoucher::with([
            'deliveryRequest.deliveryAllocations',
            'deliveryRequest.pulloutAllocations',
            'deliveryRequest.accessorialAllocations',
            'deliveryRequest.othersAllocations',
            'deliveryRequest.freightAllocations',
            'cvrTypes',
            'cvrApprovals'  
        ])
        ->when($search, function ($query, $search) {
            return $query->whereHas('deliveryRequest', function ($q) use ($search) {
                $q->where('mtm', 'like', '%' . $search . '%');
            });
        })
        ->where('status', 2)
        ->whereNotIn('cvr_type', ['admin', 'rpm'])
        ->orderBy('print_status', 'asc')
        ->orderBy('cvr_number')
        ->paginate(10);

        // Add matched_allocation to each cash voucher
        foreach ($cashVoucherRequests as $cashVoucher) {
            $allAllocations = collect([
                ...($cashVoucher->deliveryRequest->deliveryAllocations ?? []),
                ...($cashVoucher->deliveryRequest->pulloutAllocations ?? []),
                ...($cashVoucher->deliveryRequest->accessorialAllocations ?? []),
                ...($cashVoucher->deliveryRequest->othersAllocations ?? []),
                ...($cashVoucher->deliveryRequest->freightAllocations ?? []),
            ]);

            $matchedAllocation = $allAllocations->first(function ($allocation) use ($cashVoucher) {
                return $allocation->dr_id == $cashVoucher->dr_id &&
                    strtolower($allocation->trip_type) === strtolower($cashVoucher->cvr_type) &&
                    $allocation->sequence == $cashVoucher->sequence;
            });

            // Temporarily attach the matched allocation to the model
            $cashVoucher->matched_allocation = $matchedAllocation;
        }

        if ($request->ajax()) {
            return view('cashVoucherRequests.cvrList_table', compact('cashVoucherRequests'))->render();
        }

        return view('cashVoucherRequests.cvrList', compact('cashVoucherRequests', 'search'));
    }

    public function printMultiple(Request $request)
    {
        $ids = $request->input('cvr_ids', []);
        $types = $request->input('cvr_types', []); // e.g., ['1' => 'delivery', '2' => 'pullout']

        if (!is_array($ids) || empty($ids)) {
            return redirect()->back()->with('error', 'No CVRs selected for printing.');
        }

        $allData = [];

        foreach ($ids as $id) {
            $mtm = $types[$id] ?? null;

            // Fetch CVR with relationships
            $cashVoucherRequest = CashVoucher::with([
                'deliveryRequest',
                'withholdingTax:id,description,percentage'
            ])->find($id);

            if (!$cashVoucherRequest || !$mtm) {
                continue;
            }

            // Decode or explode remarks
            if (is_string($cashVoucherRequest->remarks) && $this->isJson($cashVoucherRequest->remarks)) {
                $remarks = json_decode($cashVoucherRequest->remarks, true);
            } else {
                $remarks = is_string($cashVoucherRequest->remarks)
                    ? explode(',', $cashVoucherRequest->remarks)
                    : (array) $cashVoucherRequest->remarks;
            }
            $remarks = array_map('trim', $remarks);

            // Delivery line items
            $deliveryLineItems = DB::table('delivery_request_line_items')
                ->where('dr_id', $cashVoucherRequest->dr_id)
                ->get();

            // Delivery request with company & customer
            $deliveryRequest = DeliveryRequest::with('company', 'customer')
                ->find($cashVoucherRequest->dr_id);

            // Request type
            $requestTypes = DB::table('cash_vouchers')
                ->join('cvr_request_type', 'cash_vouchers.request_type', '=', 'cvr_request_type.id')
                ->where('cash_vouchers.id', $id)
                ->first();

            // Driver info
            $drivers = DB::table('allocations')
                ->join('users', 'allocations.driver_id', '=', 'users.id')
                ->where('allocations.dr_id', $cashVoucherRequest->dr_id)
                ->where('allocations.trip_type', $mtm)
                ->first();
                
            // Allocation + Truck
            $allocations = Allocation::with('truck')
                ->where('dr_id', $cashVoucherRequest->dr_id)
                ->where('trip_type', $mtm)
                ->where('sequence', $cashVoucherRequest->sequence)
                ->first();

            // Fleet card info
            $fleets = DB::table('allocations')
                ->join('fleet_cards', 'allocations.fleet_card_id', '=', 'fleet_cards.id')
                ->where('allocations.dr_id', $cashVoucherRequest->dr_id)
                ->where('trip_type', $mtm)
                ->first();

            // Employee info (requestor)
            $employees = DB::table('cash_vouchers')
                ->join('users', 'cash_vouchers.requestor', '=', 'users.id')
                ->select('users.*', 'cash_vouchers.*')
                ->where('cash_vouchers.id', $id)
                ->first();

            // Approval info
            $cvrApprovals = cvr_approval::where('cvr_id', $id)->first();

            $approvers = DB::table('cvr_approvals')
                ->leftJoin('cvr_approver', 'cvr_approvals.source', '=', 'cvr_approver.id')
                ->where('cvr_approvals.cvr_number', $cashVoucherRequest->cvr_number)
                ->first();

            // Amount calculations
            if ($cashVoucherRequest->voucher_type === 'with_tax') {
                $baseAmount = $cashVoucherRequest->tax_based_amount ?? 0;
                $vatAmount = $baseAmount * 0.12;
                $taxPercentage = $cashVoucherRequest->withholdingTax->percentage ?? 0;
                $taxDeduction = $baseAmount * $taxPercentage;
                $finalAmount = $baseAmount + $vatAmount - $taxDeduction;
            } elseif ($cashVoucherRequest->voucher_type === 'regular') {
                $baseAmount = $cvrApprovals->amount ?? 0;
                $vatAmount = 0;
                $taxDeduction = 0;
                $finalAmount = $baseAmount;
            } else {
                $finalAmount = 0;
            }

            $amountInWords = $finalAmount > 0 ? $this->convertAmountToWords($finalAmount) : 'N/A';

            $allData[] = compact(
                'cashVoucherRequest', 'amountInWords', 'deliveryLineItems', 'employees',
                'approvers', 'drivers', 'fleets', 'requestTypes', 'deliveryRequest',
                'remarks', 'allocations', 'cvrApprovals'
            );
        }

        return view('cashVoucherRequests.printMultiple', compact('allData'));
    }


    public function printCVR($id, $cvr_number, $mtm)
    {
         $cashVoucherRequest = CashVoucher::with([
                'deliveryRequest',
                'withholdingTax:id,description,percentage'
            ])
            ->where('id', $id)
            ->where('dr_id', $cvr_number)
            ->first();

            // Check if the remarks column contains a JSON string
            if (is_string($cashVoucherRequest->remarks) && $this->isJson($cashVoucherRequest->remarks)) {
                // Decode JSON if it is in JSON format
                $remarks = json_decode($cashVoucherRequest->remarks, true);
            } else {
                // Otherwise, just explode the string (assuming it is comma-separated)
                $remarks = explode(',', $cashVoucherRequest->remarks);
            }
    
            $remarks = array_map('trim', $remarks);
    
            $deliveryLineItems = DB::table('delivery_request_line_items')
            ->select(
                'delivery_request_line_items.*'
            )
            ->where('delivery_request_line_items.dr_id', $cvr_number)
            ->get();

    
            $deliveryRequest = DeliveryRequest::with('company','customer')
            ->where('id', $cashVoucherRequest->dr_id)
            ->first();
    
            $requestTypes = DB::table('cash_vouchers')
            ->join('cvr_request_type', 'cash_vouchers.request_type', '=', 'cvr_request_type.id')
            ->where('cash_vouchers.dr_id', $cvr_number) 
            ->where('cash_vouchers.cvr_type', $mtm) 
            ->first();
    
            $drivers = DB::table('allocations')
            ->join('users', 'allocations.driver_id', '=', 'users.id')
            ->where('allocations.dr_id', $cvr_number) 
             ->where('allocations.trip_type', $mtm) 
            ->first();

            $allocations = Allocation::with('truck')
            ->where('dr_id', $cashVoucherRequest->dr_id)
            ->where('allocations.trip_type', $mtm) 
            ->where('sequence', $cashVoucherRequest->sequence)
            ->first();
    
            $fleets = DB::table('allocations')
            ->join('fleet_cards', 'allocations.fleet_card_id', '=', 'fleet_cards.id')
            ->where('allocations.dr_id', $cvr_number) 
            ->where('allocations.trip_type', $mtm) 
            ->first();
    
            $employees = DB::table('cash_vouchers')
            ->join('users', 'cash_vouchers.requestor', '=', 'users.id')
            ->select('users.*', 'cash_vouchers.*') 
            ->where('cash_vouchers.dr_id', $cvr_number) 
             ->where('cash_vouchers.cvr_type', $mtm) 
            ->first();

            $cvrApprovals = cvr_approval::where('cvr_id', $id)
            ->first();

            $approvers = DB::table('cvr_approvals')
                ->leftjoin('cvr_approver', 'cvr_approvals.source', '=', 'cvr_approver.id')
                ->where('cvr_approvals.cvr_number', $cashVoucherRequest->cvr_number)
                ->first();

            if ($cashVoucherRequest->voucher_type === 'with_tax') {
                $baseAmount = $cashVoucherRequest->tax_based_amount ?? 0;
                $vatAmount = $baseAmount * 0.12;
                $taxPercentage = $cashVoucherRequest->withholdingTax->percentage ?? 0;
                $taxDeduction = $baseAmount * $taxPercentage;
                $finalAmount = $baseAmount + $vatAmount - $taxDeduction;
            } elseif ($cashVoucherRequest->voucher_type === 'regular') {
                $baseAmount = $cvrApprovals->amount ?? 0;
                $vatAmount = 0;
                $taxDeduction = 0;
                $finalAmount = $cvrApprovals->amount ?? 0;
            } else {
                $baseAmount = 0;
                $vatAmount = 0;
                $taxDeduction = 0;
                $finalAmount = 0;
            }

            $amountInWords = $finalAmount > 0 ? $this->convertAmountToWords($finalAmount) : 'N/A';
   
        // Return the print view with the data
        return view(
            'cashVoucherRequests.print', compact('cashVoucherRequest', 'amountInWords', 
            'deliveryLineItems', 'employees', 'approvers', 'drivers', 'fleets', 'requestTypes', 'deliveryRequest', 'remarks', 'allocations', 'cvrApprovals'
        ));
    }

    public function updatePrintStatus(Request $request)
    {
        // validate & extract ids
        $cvrIds = $request->input('cvr_ids', []);
        $voucherIds = $request->input('voucher_ids', []);
 
        // update as needed
        CashVoucher::whereIn('id', $cvrIds)->update(['print_status' => '1']);
        cvr_approval::whereIn('id', $voucherIds)->update(['print_status' => '1']);

        return response()->json(['message' => 'Print status updated']);
    }

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function editView($id)
    {
        Log::info("Approval request ID: $id");
    
        
        $cashVouchers = CashVoucher::where('id', $id)->firstOrFail();
        $deliveryRequestId = $cashVouchers->id;
        $employees = User::all();
        $approves = Approver::all();
        $requestType = cvr_request_type::all();
        $fleetCards = FleetCard::All();
        $trucks = Truck::All();

        $remarks = json_decode($cashVouchers->remarks ?? '[]', true);
        $helpers = json_decode($cashVouchers->helpers ?? '[]', true);
    
        return view('cashVoucherRequests.editView', compact(
            'deliveryRequestId',
            'employees',
            'approves',
            'cashVouchers',
            'requestType',
            'fleetCards',
            'trucks',
            'remarks',
            'helpers'
        ));
    }

    public function cvrUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'request_type' => 'required|exists:cvr_request_type,id',
            'requestor' => 'required|exists:users,id',
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string|max:255',
        ]);

        $cashVoucher = CashVoucher::findOrFail($id);

        $cashVoucher->amount = $validated['amount'];
        $cashVoucher->request_type = $validated['request_type'];
        $cashVoucher->requestor = $validated['requestor'];
        $cashVoucher->remarks = json_encode($request->input('remarks', [])); // Optional: cast to array

        // Save the changes
        $cashVoucher->save();

        return redirect()
            ->route('cashVoucherRequests.approval') // Or another route
            ->with('success', 'Cash Voucher updated successfully.');
        }


        public function showCustomCVR($id, $cvr_number, $cvr_type)
        {   

            $cashVoucherRequest = CashVoucher::with([
                'deliveryRequest',
                'withholdingTax:id,description,percentage'
            ])
            ->where('id', $id)
            ->where('dr_id', $cvr_number)
            ->first();
                
            // Check if the remarks column contains a JSON string
            if (is_string($cashVoucherRequest->remarks) && $this->isJson($cashVoucherRequest->remarks)) {
                // Decode JSON if it is in JSON format
                $remarks = json_decode($cashVoucherRequest->remarks, true);
            } else {
                // Otherwise, just explode the string (assuming it is comma-separated)
                $remarks = explode(',', $cashVoucherRequest->remarks);
            }
    
            $remarks = array_map('trim', $remarks);
    
            $deliveryLineItems = DB::table('delivery_request_line_items')
            ->select(
                'delivery_request_line_items.*'
            )
            ->where('delivery_request_line_items.mtm', $cashVoucherRequest->mtm)
            ->get();
    
            // $_POST = DB::table('delivery_request')
            // ->leftjoin('customers', 'delivery_request.customer_id', '=', 'customers.id')
            // ->where('delivery_request.id', $cashVoucherRequest->dr_id) 
            // ->first();

            $deliveryRequest = DeliveryRequest::with('company','customer')
            ->where('id', $cashVoucherRequest->dr_id)
            ->first();
    
            $requestTypes = DB::table('cash_vouchers')
            ->join('cvr_request_type', 'cash_vouchers.request_type', '=', 'cvr_request_type.id')
            ->where('cash_vouchers.dr_id', $cvr_number) 
            ->where('cash_vouchers.id', $id) 
            ->where('cvr_type', $cvr_type)
            ->first();
    
            $drivers = DB::table('allocations')
            ->join('users', 'allocations.driver_id', '=', 'users.id')
            ->where('allocations.dr_id', $cvr_number) 
            ->where('allocations.trip_type', $cvr_type)
            ->where('allocations.sequence', $cashVoucherRequest->sequence)
            ->first();

            $allocations = Allocation::with('truck')
            ->where('dr_id', $cashVoucherRequest->dr_id)
            ->where('trip_type', $cvr_type)
            ->where('sequence', $cashVoucherRequest->sequence)
            ->first();
    
            $fleets = DB::table('allocations')
            ->join('fleet_cards', 'allocations.fleet_card_id', '=', 'fleet_cards.id')
            ->where('allocations.dr_id', $cvr_number) 
            ->where('trip_type', $cvr_type)
            ->where('sequence', $cashVoucherRequest->sequence)
            ->first();
    
            $employees = DB::table('allocations')
            ->join('users', 'allocations.requestor_id', '=', 'users.id')
            ->select('users.*', 'allocations.*') 
            ->where('allocations.dr_id', $cvr_number) 
            ->where('trip_type', $cvr_type)
            ->where('sequence', $cashVoucherRequest->sequence)
            ->first();


            $cvrApprovals = cvr_approval::where('cvr_id', $id)
            ->first();
    
            // Recalculate the amount to match the view logic
            if ($cashVoucherRequest->voucher_type === 'with_tax') {
                $baseAmount = $cashVoucherRequest->tax_based_amount ?? 0;
                $vatAmount = $baseAmount * 0.12;
                $taxPercentage = $cashVoucherRequest->withholdingTax->percentage ?? 0;
                $taxDeduction = $baseAmount * $taxPercentage;
                $finalAmount = $baseAmount + $vatAmount - $taxDeduction;
            } elseif ($cashVoucherRequest->voucher_type === 'regular') {
                $baseAmount = $cashVoucherRequest->amount ?? 0;
                $vatAmount = 0;
                $taxDeduction = 0;
                $finalAmount = $cashVoucherRequest->amount ?? 0;
            } else {
                $baseAmount = 0;
                $vatAmount = 0;
                $taxDeduction = 0;
                $finalAmount = 0;
            }

            $amountInWords = $finalAmount > 0 ? $this->convertAmountToWordsPreview($finalAmount) : 'N/A';
    
            // Return the print view with the data
            return view(
                'cashVoucherRequests.printPreview', compact('cashVoucherRequest', 'amountInWords', 
                'deliveryLineItems', 'employees', 'drivers', 'fleets', 'requestTypes', 'deliveryRequest', 'remarks', 'allocations', 'cvrApprovals'
            ));
        }

        public function convertAmountToWords($amount)
        {
            // Handle edge cases like 0, null or non-numeric values
            if (is_null($amount) || !is_numeric($amount) || $amount <= 0) {
                return 'Zero or Invalid Amount';
            }

            // Initialize the NumberToWords class
            $numberToWords = new NumberToWords();

            // Get the number to words transformer (not currency transformer)
            $numberTransformer = $numberToWords->getNumberTransformer('en');
            
            // Convert the amount (integer part) into words
            $amountInWords = $numberTransformer->toWords(floor($amount)); // Get the integer part

            // Handle fractional part (cents)
            $fractionalPart = round(($amount - floor($amount)) * 100); // Get the cents (if any)

            $currency = 'pesos'; // Default currency
            $fractionalCurrency = 'centavos'; // Default fractional currency
            
            // Check for singular/plural currency
            if ($amount == 1) {
                $currency = 'peso';
            }

            // If there is a fractional part, format it as a fraction (e.g., 45/100)
            if ($fractionalPart > 0) {
                return ucfirst($amountInWords) . ' ' . $currency . ' & ' . $fractionalPart . '/100 ';
            }

            // Return the amount in words with currency (e.g., "five hundred pesos")
            return ucfirst($amountInWords) . ' ' . $currency;
        }

        public function convertAmountToWordsPreview($amount)
        {
            // Handle edge cases like 0, null or non-numeric values
            if (is_null($amount) || !is_numeric($amount) || $amount <= 0) {
                return 'Zero or Invalid Amount';
            }

            // Initialize the NumberToWords class
            $numberToWords = new NumberToWords();

            // Get the number to words transformer (not currency transformer)
            $numberTransformer = $numberToWords->getNumberTransformer('en');
            
            // Convert the amount (integer part) into words
            $amountInWords = $numberTransformer->toWords(floor($amount)); // Get the integer part

            // Handle fractional part (cents)
            $fractionalPart = round(($amount - floor($amount)) * 100); // Get the cents (if any)

            $currency = 'pesos'; // Default currency
            $fractionalCurrency = 'centavos'; // Default fractional currency
            
            // Check for singular/plural currency
            if ($amount == 1) {
                $currency = 'peso';
            }

            // If there is a fractional part, format it as a fraction (e.g., 45/100)
            if ($fractionalPart > 0) {
                return ucfirst($amountInWords) . ' ' . $currency . ' & ' . $fractionalPart . '/100 ';
            }

            // Return the amount in words with currency (e.g., "five hundred pesos")
            return ucfirst($amountInWords) . ' ' . $currency;
        }

        public function rejectView()
        {
            $user = Auth::user();
            $employeeCode = $user->id;

            $cashVouchers = CashVoucher::with([
                'deliveryRequest.deliveryAllocations',
                'deliveryRequest.pulloutAllocations',
                'deliveryRequest.accessorialAllocations',
                'deliveryRequest.freightAllocations',
                'deliveryRequest.othersAllocations',
                'deliveryRequest.company',
                'deliveryRequest.expenseType',
            ])
            ->where('status', 3)
            ->whereIn('cvr_type', ['delivery', 'pullout', 'accessorial', 'freight', 'others'])
            // ->where('created_by', $employeeCode)
            ->get();

            // Attach matched allocation dynamically
            foreach ($cashVouchers as $cashVoucher) {
                $allAllocations = collect([
                    ...($cashVoucher->deliveryRequest->deliveryAllocations ?? []),
                    ...($cashVoucher->deliveryRequest->pulloutAllocations ?? []),
                    ...($cashVoucher->deliveryRequest->accessorialAllocations ?? []),
                    ...($cashVoucher->deliveryRequest->othersAllocations ?? []),
                    ...($cashVoucher->deliveryRequest->freightAllocations ?? []),
                ]);

                $matchedAllocation = $allAllocations->first(function ($allocation) use ($cashVoucher) {
                    return $allocation->dr_id == $cashVoucher->dr_id &&
                        strtolower($allocation->trip_type) === strtolower($cashVoucher->cvr_type) &&
                        $allocation->sequence == $cashVoucher->sequence;
                });

                $cashVoucher->matched_allocation = $matchedAllocation;
            }

            return view('cashVoucherRequests.rejectView', compact('cashVouchers'));
        }

        public function editCVR($id)
        {
            $cashVoucher = CashVoucher::where('id', $id)->first();

            $deliveryLineItems = DeliveryRequest::with(['lineItems', 'company'])
            ->where('id', $cashVoucher->dr_id)
            ->get();

            $employees = User::all();
            $approves = Approver::all();
            $taxes = WithholdingTax::all();
            $requestType = cvr_request_type::all();

            return view('cashVoucherRequests.editCVR', compact('cashVoucher', 'deliveryLineItems', 'employees', 'taxes', 'requestType'));
        }

        public function updateCVR(Request $request)
        {
            $request->validate([
                'cvr_id' => 'required|integer|exists:cash_vouchers,id',
                'dr_id' => 'required|integer',
                'mtm' => 'required|string',
                'cvr_type' => 'required|string',
                'company_id' => 'nullable|integer',
                'voucher_type' => 'required|string',
                'withholding_tax' => 'nullable|integer',
                'tax_base_amount' => 'nullable|numeric',
                'cvr_number' => 'required|string',
                'amount' => 'required|numeric',
                'request_type' => 'required|integer',
                'requestor' => 'required|integer',
                'remarks' => 'nullable|array',
                'remarks.*' => 'nullable|string',
            ]);

            $cashVoucher = CashVoucher::find($request->cvr_id);

            if (!$cashVoucher) {
                return redirect()->back()->with('error', 'Cash Voucher not found.');
            }

            // Update the fields
            $cashVoucher->dr_id = $request->dr_id;
            $cashVoucher->mtm = $request->mtm;
            $cashVoucher->cvr_type = $request->cvr_type;
            $cashVoucher->company_id = $request->company_id;
            $cashVoucher->voucher_type = $request->voucher_type;
            $cashVoucher->withholding_tax_id = $request->withholding_tax;
            $cashVoucher->tax_based_amount = $request->tax_base_amount;
            $cashVoucher->cvr_number = $request->cvr_number;
            $cashVoucher->amount = $request->amount;
            $cashVoucher->request_type = $request->request_type;
            $cashVoucher->requestor = $request->requestor;
            $cashVoucher->status = 1;
            $cashVoucher->remarks = $request->remarks; // Store remarks as JSON array

            $cashVoucher->save();

            return redirect()->route('cashVoucherRequests.rejectView')->with('success', 'Cash Voucher updated successfully.');
        }

    public function rejectPrintView($id, $cvr_number, $cvr_type)
    {
        $cashVoucherRequest = CashVoucher::with([
            'deliveryRequest',
            'withholdingTax:id,description,percentage'
        ])
        ->where('id', $id)
        ->where('dr_id', $cvr_number)
        ->first();
                
        // Check if the remarks column contains a JSON string
        if (is_string($cashVoucherRequest->remarks) && $this->isJson($cashVoucherRequest->remarks)) {
            // Decode JSON if it is in JSON format
            $remarks = json_decode($cashVoucherRequest->remarks, true);
        } else {
            // Otherwise, just explode the string (assuming it is comma-separated)
            $remarks = explode(',', $cashVoucherRequest->remarks);
        }
    
        $remarks = array_map('trim', $remarks);
    
        $deliveryLineItems = DB::table('delivery_request_line_items')
        ->select(
            'delivery_request_line_items.*'
        )
        ->where('delivery_request_line_items.mtm', $cashVoucherRequest->mtm)
        ->get();

        $deliveryRequest = DeliveryRequest::with('company','customer')
        ->where('id', $cashVoucherRequest->dr_id)
        ->first();
    
        $requestTypes = DB::table('cash_vouchers')
        ->join('cvr_request_type', 'cash_vouchers.request_type', '=', 'cvr_request_type.id')
        ->where('cash_vouchers.dr_id', $cvr_number) 
        ->where('cash_vouchers.id', $id) 
        ->where('cvr_type', $cvr_type)
        ->first();
    
        $drivers = DB::table('allocations')
        ->join('users', 'allocations.driver_id', '=', 'users.id')
        ->where('allocations.dr_id', $cvr_number) 
        ->where('allocations.trip_type', $cvr_type)
        ->where('allocations.sequence', $cashVoucherRequest->sequence)
        ->first();

        $allocations = Allocation::with('truck')
        ->where('dr_id', $cashVoucherRequest->dr_id)
        ->where('trip_type', $cvr_type)
        ->where('sequence', $cashVoucherRequest->sequence)
        ->first();
    
        $fleets = DB::table('allocations')
        ->join('fleet_cards', 'allocations.fleet_card_id', '=', 'fleet_cards.id')
        ->where('allocations.dr_id', $cvr_number) 
        ->where('trip_type', $cvr_type)
        ->where('sequence', $cashVoucherRequest->sequence)
        ->first();
    
        $employees = DB::table('allocations')
        ->join('users', 'allocations.requestor_id', '=', 'users.id')
        ->select('users.*', 'allocations.*') 
        ->where('allocations.dr_id', $cvr_number) 
        ->where('trip_type', $cvr_type)
        ->where('sequence', $cashVoucherRequest->sequence)
        ->first();


        $cvrApprovals = cvr_approval::where('cvr_id', $id)
        ->first();
    
        // Recalculate the amount to match the view logic
        if ($cashVoucherRequest->voucher_type === 'with_tax') {
            $baseAmount = $cashVoucherRequest->tax_based_amount ?? 0;
            $vatAmount = $baseAmount * 0.12;
            $taxPercentage = $cashVoucherRequest->withholdingTax->percentage ?? 0;
            $taxDeduction = $baseAmount * $taxPercentage;
            $finalAmount = $baseAmount + $vatAmount - $taxDeduction;
        } elseif ($cashVoucherRequest->voucher_type === 'regular') {
            $baseAmount = $cashVoucherRequest->amount ?? 0;
            $vatAmount = 0;
            $taxDeduction = 0;
            $finalAmount = $cashVoucherRequest->amount ?? 0;
        } else {
            $baseAmount = 0;
            $vatAmount = 0;
            $taxDeduction = 0;
            $finalAmount = 0;
        }

        $amountInWords = $finalAmount > 0 ? $this->convertAmountToWordsPreview($finalAmount) : 'N/A';

        $rejectRemarks = [];

        if (!empty($cashVoucherRequest->reject_remarks) && $this->isJson($cashVoucherRequest->reject_remarks)) {
            $rejectRemarks = json_decode($cashVoucherRequest->reject_remarks, true);
        }
    
        // Return the print view with the data
        return view(
            'cashVoucherRequests.reject_print', compact('cashVoucherRequest', 'amountInWords', 
            'deliveryLineItems', 'employees', 'drivers', 'fleets', 'requestTypes', 'deliveryRequest', 'remarks', 'allocations', 'cvrApprovals','rejectRemarks'
        ));       
    }

    public function rejectPrintViewMultiple(Request $request)
    {
        $ids = $request->input('ids'); // already an array from the form

        if (empty($ids) || !is_array($ids)) {
            abort(400, 'No voucher IDs provided.');
        }

        $vouchers = [];

        foreach ($ids as $id) {
            $cashVoucherRequest = CashVoucher::with([
                'deliveryRequest',
                'withholdingTax:id,description,percentage'
            ])->find($id);

            if (!$cashVoucherRequest) continue;

            $remarks = [];
            if (is_string($cashVoucherRequest->remarks) && $this->isJson($cashVoucherRequest->remarks)) {
                $remarks = json_decode($cashVoucherRequest->remarks, true);
            } else {
                $remarks = is_string($cashVoucherRequest->remarks) 
                    ? explode(',', $cashVoucherRequest->remarks) 
                    : [];
            }
            $remarks = array_map('trim', $remarks);

            $deliveryRequest = DeliveryRequest::with('company', 'customer')->find($cashVoucherRequest->dr_id);

            $requestTypes = DB::table('cash_vouchers')
                ->join('cvr_request_type', 'cash_vouchers.request_type', '=', 'cvr_request_type.id')
                ->where('cash_vouchers.id', $id)
                ->first();

            $drivers = DB::table('allocations')
                ->join('users', 'allocations.driver_id', '=', 'users.id')
                ->where('allocations.dr_id', $cashVoucherRequest->dr_id)
                ->where('allocations.trip_type', $cashVoucherRequest->cvr_type)
                ->where('allocations.sequence', $cashVoucherRequest->sequence)
                ->first();

            $allocations = Allocation::with('truck')
                ->where('dr_id', $cashVoucherRequest->dr_id)
                ->where('trip_type', $cashVoucherRequest->cvr_type)
                ->where('sequence', $cashVoucherRequest->sequence)
                ->first();

            $fleets = DB::table('allocations')
                ->join('fleet_cards', 'allocations.fleet_card_id', '=', 'fleet_cards.id')
                ->where('allocations.dr_id', $cashVoucherRequest->dr_id)
                ->where('trip_type', $cashVoucherRequest->cvr_type)
                ->where('sequence', $cashVoucherRequest->sequence)
                ->first();

            $employees = DB::table('allocations')
                ->join('users', 'allocations.requestor_id', '=', 'users.id')
                ->select('users.*', 'allocations.*')
                ->where('allocations.dr_id', $cashVoucherRequest->dr_id)
                ->where('trip_type', $cashVoucherRequest->cvr_type)
                ->where('sequence', $cashVoucherRequest->sequence)
                ->first();

            $deliveryLineItems = DB::table('delivery_request_line_items')
                ->where('mtm', $cashVoucherRequest->mtm)
                ->get();

            $cvrApprovals = cvr_approval::where('cvr_id', $id)->first();

            // Recalculate the amount
            if ($cashVoucherRequest->voucher_type === 'with_tax') {
                $baseAmount = $cashVoucherRequest->tax_based_amount ?? 0;
                $vatAmount = $baseAmount * 0.12;
                $taxPercentage = $cashVoucherRequest->withholdingTax->percentage ?? 0;
                $taxDeduction = $baseAmount * $taxPercentage;
                $finalAmount = $baseAmount + $vatAmount - $taxDeduction;
            } else {
                $finalAmount = $cashVoucherRequest->amount ?? 0;
            }

            $amountInWords = $finalAmount > 0 ? $this->convertAmountToWordsPreview($finalAmount) : 'N/A';

            $rejectRemarks = [];
            if (!empty($cashVoucherRequest->reject_remarks) && $this->isJson($cashVoucherRequest->reject_remarks)) {
                $rejectRemarks = json_decode($cashVoucherRequest->reject_remarks, true);
            }

            $vouchers[] = compact(
                'cashVoucherRequest', 'amountInWords', 'deliveryLineItems', 'employees',
                'drivers', 'fleets', 'requestTypes', 'deliveryRequest', 'remarks', 'allocations',
                'cvrApprovals', 'rejectRemarks'
            );
        }

        return view('cashVoucherRequests.reject_print_multiple', compact('vouchers'));
    }


}
