<?php

namespace App\Http\Controllers;

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
        $deliveryRequests = CashVoucher::join('delivery_request', 'cash_vouchers.dr_id', '=', 'delivery_request.id')
            ->join('cvr_request_type', 'cash_vouchers.request_type', '=', 'cvr_request_type.id')
            ->when($search, function ($query, $search) {
                return $query->where('delivery_request.mtm', 'like', '%' . $search . '%');
            })
            ->where('cash_vouchers.status', '=', 1)
            ->select('delivery_request.*', 'cash_vouchers.*', 'cvr_request_type.request_type as cvr_type')
            ->paginate(10);
    
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
            'taxes'
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
        // dd($deliveryLineItems);

        $cvr = MonthlySeriesNumber::all();
        $requestType = cvr_request_type::all();
        $employees = User::all();
        $fleetCards = FleetCard::All();

        return view('cashVoucherRequests.accessorialRequest', compact('deliveryLineItems', 'cvr', 'requestType', 'employees', 'fleetCards'));
    }

    public function store(Request $request)
    {
        Log::info('Request Data:', ['data' => $request->all()]);

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

        $company_id = $request->company_id;

        // Run everything in a DB transaction
        DB::transaction(function () use ($company_id, $request) {
            // Handle potential rollover
            $currentDate = new DateTime();
            $lastDayOfMonth = $currentDate->format('t');
            if ((int)$currentDate->format('j') === (int)$lastDayOfMonth) {
                $currentDate->modify('first day of next month');
            }

            $currentMonthString = $currentDate->format('Y-m');

            // Lock and fetch or create MonthlySeriesNumber
            $monthlySeries = MonthlySeriesNumber::where('company_id', $company_id)
                ->where('month', $currentMonthString)
                ->lockForUpdate()
                ->first();

            if (!$monthlySeries) {
                $monthlySeries = new MonthlySeriesNumber();
                $monthlySeries->company_id = $company_id;
                $monthlySeries->month = $currentMonthString;
                $monthlySeries->series_number = 1;
                $monthlySeries->save();
                $nextCvrNumber = 1;
                Log::info("Created new MonthlySeriesNumber with series_number = 1 for company_id: {$company_id}");
            } else {
                if ($monthlySeries->series_number <= 0) {
                    $monthlySeries->series_number = 1;
                } else {
                    $monthlySeries->increment('series_number');
                }
                $nextCvrNumber = $monthlySeries->series_number;
                Log::info("Updated MonthlySeriesNumber to {$nextCvrNumber} for company_id: {$company_id}");
            }

            // Construct the final CVR number
            $currentYear = $currentDate->format('Y');
            $currentMonth = $currentDate->format('m');
            $nextCvrNumberFormatted = sprintf('%03d', $nextCvrNumber);
            $formattedCvrNumber = "CVR-{$currentYear}-{$currentMonth}-{$nextCvrNumberFormatted}";
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
                'remarks' => $request->has('remarks') ? json_encode($request->remarks) : null,
                'created_by' => $employeeCode,
                'dr_id' => $request->dr_id,
            ]);

            Log::info('Saving Cash Voucher:', ['cash_voucher' => $cashVoucher->toArray()]);
            $cashVoucher->save();

            // Update DeliveryRequest status
            $deliveryRequest = DeliveryRequest::where('mtm', $request->mtm)->first();
            if ($deliveryRequest && $deliveryRequest->status != 0) {
                $deliveryRequest->status = '1';
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

        return redirect()->route('cashVoucherRequests.index')
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

        $employees = User::all();
        $approves = Approver::all(); 

        return view('cashVoucherRequests.approvalRequest', compact('deliveryRequestId', 'employees', 'approves', 'cashVouchers'));
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
        $cvr_id = $request->cvr_id;
        $cashVouchers = CashVoucher::where('id', $cvr_id)->first();

        if ($cashVouchers) {
            $cashVouchers->status = 2;
            $cashVouchers->save();
        }

        Log::info('Full Request Data', $request->all());

        // Set payment_name based on payment type
        $paymentName = '';
        $reference_number = '';
        $amount = '';
        $receiver = '';
        $fund_source = '';
        $charge = null;

        switch ($request->payment_type) {
            case 'cash':
                $paymentName = 'Cash';
                $reference_number = $request->reference_number;
                $amount = $request->cash_amount;
                $receiver = $request->cash_receiver;
                $fund_source = $request->cash_fund_source;
                $charge = null;
                break;
            case 'bank_transfer':
                $paymentName = $request->bank_name;
                $reference_number = $request->bank_reference_number;
                $amount = $request->bank_amount;
                $receiver = $request->bank_receiver;
                $fund_source = $request->bank_fund_source;
                $charge = $request->bank_charge;
                break;
            case 'outlet_transfer':
                $paymentName = $request->outlet_name;
                $reference_number = $request->outlet_reference_number;
                $amount = $request->outlet_amount;
                $receiver = $request->outlet_receiver;
                $fund_source = $request->outlet_fund_source;
                $charge = $request->outlet_charge;
                break;
        }

        // Wrap the saving logic in a try-catch block and use a transaction
        DB::beginTransaction();

        $user = Auth::user();
        $employeeCode = $user->id;

        try {
            // Save cvr_approval
            $cvrApproval = new cvr_approval();
            $cvrApproval->payment_type = $request->payment_type;
            $cvrApproval->payment_name = $paymentName;
            $cvrApproval->reference_number = $reference_number;
            $cvrApproval->amount = $amount;
            $cvrApproval->receiver = $receiver;
            $cvrApproval->source = $fund_source;
            $cvrApproval->charge = $charge ?? null;
            $cvrApproval->cvr_number = $request->cvr_number;
            $cvrApproval->status = 1; // Set initial status
            $cvrApproval->created_by = $employeeCode;
            $cvrApproval->cvr_id = $request->cvr_id;
            $cvrApproval->save();

            // Log success for cvr_approval
            Log::info('Cash Voucher Approval saved successfully', [
                'cvr_approval_id' => $cvrApproval->id,
                'amount' => $cvrApproval->amount,
                'receiver' => $cvrApproval->receiver,
            ]);

            // Update the related CashVoucher status to 2 (approved)
            $cashVoucher = CashVoucher::where('cvr_number', $request->cvr_number)->first(); // Assuming cvr_number is the identifier
            if ($cashVoucher) {
                $cashVoucher->status = 2; // Update status to 2 (approved)
                $cashVoucher->save(); // Save the status change

                Log::info('Cash Voucher status updated to 2', [
                    'cash_voucher_id' => $cashVoucher->id,
                    'new_status' => $cashVoucher->status,
                ]);

            $amount = floatval($cvrApproval->amount ?? 0);
            $charge = floatval($cvrApproval->charge ?? 0);
            $totalAmount = $amount + $charge;
            RunningBalance::create([
                'type' => 8, // CVR approval
                'amount' => -1 * floatval($totalAmount), // It's a deduction
                'description' => $cashVouchers->cvr_number,
                'employee_id' => $receiver, // Or set this if linked to a user
                'approver_id' => $fund_source,
                'created_by' => $employeeCode,
                'cvr_number' =>  $cashVouchers->cvr_number,
            ]);

            } else {
                // Log a warning if CashVoucher is not found
                Log::warning('Cash Voucher not found', [
                    'cvr_number' => $request->cvr_number,
                ]);

                // If CashVoucher isn't found, throw an exception to trigger rollback
                throw new \Exception('Cash Voucher not found.');
            }

            // Commit the transaction
            DB::commit();

            // Redirect to success page with success message
            return redirect()->route('cashVoucherRequests.approval')->with('success', 'Cash Voucher Approval Saved Successfully');
        } catch (\Exception $e) {
            // Rollback the transaction if any part of the process fails
            DB::rollBack();

            // Log error if the save or update fails
            Log::error('Failed to save Cash Voucher Approval or update Cash Voucher status', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'payment_type' => $request->payment_type,
                'reference_number' => $reference_number,
                'amount' => $amount,
                'receiver' => $receiver,
            ]);

            // Return an error message to the user
            return redirect()->route('cashVoucherRequests.approval')
                ->with('error', 'DB Error: ' . $e->getMessage());
        }
    }

    public function cvrList(Request $request)
    { 
        // Get the search query from the request
        $search = $request->get('search');
    
        // Fetch related delivery line items by joining with the correct table name
        $cashVoucherRequests = CashVoucher::join('delivery_request', 'cash_vouchers.dr_id', '=', 'delivery_request.id')
            ->join('cvr_request_type', 'cash_vouchers.request_type', '=', 'cvr_request_type.id')
            ->when($search, function ($query, $search) {
                return $query->where('delivery_request.mtm', 'like', '%' . $search . '%');
            })
            ->where('cash_vouchers.status', '=', 2)
            ->orderBy('cash_vouchers.print_status', 'asc')
            ->select('delivery_request.*', 'cash_vouchers.*', 'cvr_request_type.request_type as cvr_type', 'cash_vouchers.print_status' )
            ->paginate(10);
    
        // Check if the request expects an AJAX response
        if ($request->ajax()) {
            return view('cashVoucherRequests.cvrList_table', compact('cashVoucherRequests'))->render();
        }
    
        // For the normal view
        return view('cashVoucherRequests.cvrList', compact('cashVoucherRequests', 'search'));
    } 

    public function printMultiple(Request $request)
    {
        // Fetch the list of CVR numbers from the request (assumed to be passed as an array)
        $cvr_numbers = $request->input('cvr_numbers'); // This is an array of CVR numbers

        if (is_string($cvr_numbers)) {
            $cvr_numbers = explode(',', $cvr_numbers);  // Split by commas
        }

        if (!is_array($cvr_numbers)) {
            Log::error('Expected cvr_numbers to be an array, but it is not.', ['cvr_numbers' => $cvr_numbers]);
            return redirect()->back()->with('error', 'Invalid CVR numbers input.');
        }

        // Fetch all the cash vouchers based on the provided CVR numbers
        $cashVoucherRequests = DB::table('cash_vouchers')
            ->join('cvr_approvals', 'cash_vouchers.cvr_number', '=', 'cvr_approvals.cvr_number')
            ->join('delivery_request', 'cash_vouchers.mtm', '=', 'delivery_request.mtm')
            ->select(
                'cash_vouchers.*',
                'cash_vouchers.id as cash_vouchers_id',
                'cvr_approvals.*',
                'cvr_approvals.id as cvr_approvals_id',
                'cvr_approvals.amount as approved_amount',
                'delivery_request.*'
            )
            ->whereIn('cash_vouchers.cvr_number', $cvr_numbers)
            ->get();

        $allData = [];

        // Loop over the results to prepare the data for each voucher
        foreach ($cashVoucherRequests as $cashVoucherRequest) {
            if (is_string($cashVoucherRequest->remarks) && $this->isJson($cashVoucherRequest->remarks)) {
                $remarks = json_decode($cashVoucherRequest->remarks, true);
            } else {
                // If it's a string (but not JSON), explode it by commas into an array
                $remarks = is_string($cashVoucherRequest->remarks) ? explode(',', $cashVoucherRequest->remarks) : (array) $cashVoucherRequest->remarks;
            }

            $remarks = array_map('trim', $remarks);

            // Fetch additional related data for the voucher
            $deliveryLineItems = DB::table('delivery_request_line_items')
                ->select('delivery_request_line_items.*')
                ->where('delivery_request_line_items.mtm', $cashVoucherRequest->mtm)
                ->get();

            $deliveryRequest = DB::table('delivery_request')
                ->leftjoin('customers', 'delivery_request.customer_id', '=', 'customers.id')
                ->where('delivery_request.mtm', $cashVoucherRequest->mtm)
                ->first();

            $requestTypes = DB::table('cash_vouchers')
                ->join('cvr_request_type', 'cash_vouchers.request_type', '=', 'cvr_request_type.id')
                ->where('cash_vouchers.cvr_number', $cashVoucherRequest->cvr_number)
                ->first();

            $drivers = DB::table('cash_vouchers')
                ->join('employees', 'cash_vouchers.driver', '=', 'employees.id')
                ->where('cash_vouchers.cvr_number', $cashVoucherRequest->cvr_number)
                ->first();

            $fleets = DB::table('cash_vouchers')
                ->join('fleet_cards', 'cash_vouchers.fleet_card', '=', 'fleet_cards.id')
                ->where('cash_vouchers.cvr_number', $cashVoucherRequest->cvr_number)
                ->first();

            $employees = DB::table('cvr_approvals')
                ->join('employees', 'cvr_approvals.receiver', '=', 'employees.id')
                ->select('employees.*', 'cvr_approvals.*')
                ->where('cvr_approvals.cvr_number', $cashVoucherRequest->cvr_number)
                ->first();

            $approvers = DB::table('cvr_approvals')
                ->leftjoin('cvr_approver', 'cvr_approvals.source', '=', 'cvr_approver.id')
                ->where('cvr_approvals.cvr_number', $cashVoucherRequest->cvr_number)
                ->first();

            // Convert the amount to words
            $amountInWords = $cashVoucherRequest->approved_amount ? $this->convertAmountToWords($cashVoucherRequest->approved_amount) : 'N/A';

            // Add the data to the allData array for rendering in the view
            $allData[] = compact(
                'cashVoucherRequest', 'amountInWords', 'deliveryLineItems', 
                'employees', 'approvers', 'drivers', 'fleets', 'requestTypes', 
                'deliveryRequest', 'remarks'
            );
        }

        // Pass the allData array to the view
        return view('cashVoucherRequests.printMultiple', compact('allData'));
    }

    public function printCVR($id, $cvr_number, $mtm)
    {
        // Fetch the cash voucher request by its ID using raw DB queries
        $cashVoucherRequest = DB::table('cash_vouchers')
        ->join('cvr_approvals', 'cash_vouchers.cvr_number', '=', 'cvr_approvals.cvr_number')
        ->join('delivery_request', 'cash_vouchers.mtm', '=', 'delivery_request.mtm')
        // ->join('cvr_approver', 'cvr_approvals.source', '=', 'cvr_approver.id')
        // ->join('employees', 'cvr_approvals.receiver', '=', 'employees.id')
        ->select(
            'cash_vouchers.*',
            'cash_vouchers.id as cash_vouchers_id',
            'cvr_approvals.*',
            'cvr_approvals.id as cvr_approvals_id',
            'cvr_approvals.amount as approved_amount',
            'delivery_request.*'
            // 'cvr_approver.*',
            // 'employees.*',
        )
        ->where('cash_vouchers.id', $id)
        ->where('cash_vouchers.cvr_number', $cvr_number) 
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
        ->where('delivery_request_line_items.mtm', $mtm)
        ->get();

        $deliveryRequest = DB::table('delivery_request')
        ->leftjoin('customers', 'delivery_request.customer_id', '=', 'customers.id')
        ->where('delivery_request.mtm', $mtm) 
        ->first();

        $requestTypes = DB::table('cash_vouchers')
        ->join('cvr_request_type', 'cash_vouchers.request_type', '=', 'cvr_request_type.id')
        ->where('cash_vouchers.cvr_number', $cvr_number) 
        ->first();

        $drivers = DB::table('cash_vouchers')
        ->join('employees', 'cash_vouchers.driver', '=', 'employees.id')
        ->where('cash_vouchers.cvr_number', $cvr_number) 
        ->first();

        $fleets = DB::table('cash_vouchers')
        ->join('fleet_cards', 'cash_vouchers.fleet_card', '=', 'fleet_cards.id')
        ->where('cash_vouchers.cvr_number', $cvr_number) 
        ->first();

        $employees = DB::table('cvr_approvals')
        ->join('employees', 'cvr_approvals.receiver', '=', 'employees.id')
        ->select('employees.*', 'cvr_approvals.*') 
        ->where('cvr_approvals.cvr_number', $cvr_number) 
        ->first();

        $approvers = DB::table('cvr_approvals')
        ->leftjoin('cvr_approver', 'cvr_approvals.source', '=', 'cvr_approver.id')
        // ->select('cvr_approver.*', 'cvr_approvals.*') 
        ->where('cvr_approvals.cvr_number', $cvr_number) 
        ->first();



        // Check if the amount exists and convert it to words
        $amountInWords = $cashVoucherRequest->approved_amount ? $this->convertAmountToWords($cashVoucherRequest->approved_amount) : 'N/A';

        // Return the print view with the data
        return view(
            'cashVoucherRequests.print', compact('cashVoucherRequest', 'amountInWords', 
            'deliveryLineItems', 'employees', 'approvers', 'drivers', 'fleets', 'requestTypes', 'deliveryRequest', 'remarks'
        ));
    }

    public function updatePrintStatus(Request $request)
    {
        // validate & extract ids
        $cvrIds = $request->input('cvr_ids', []);
        $voucherIds = $request->input('voucher_ids', []);

        // update as needed
        cvr_approval::whereIn('id', $cvrIds)->update(['print_status' => '1']);
        CashVoucher::whereIn('id', $voucherIds)->update(['print_status' => '1']);

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
    
        
        $cashVouchers = CashVoucher::where('cvr_number', $id)->firstOrFail();
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


        public function showCustomCVR($id, $cvr_number)
        {
            $cashVoucherRequest = DB::table('cash_vouchers')
            ->join('delivery_request', 'cash_vouchers.dr_id', '=', 'delivery_request.id')
            // ->join('cvr_approver', 'cvr_approvals.source', '=', 'cvr_approver.id')
            // ->join('employees', 'cvr_approvals.receiver', '=', 'employees.id')
            ->select(
                'cash_vouchers.*',
                'cash_vouchers.id as cash_vouchers_id',
                'delivery_request.*'
                // 'cvr_approver.*',
                // 'employees.*',
            )
            ->where('cash_vouchers.id', $id)
            ->where('cash_vouchers.dr_id', $cvr_number) 
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
    
            $deliveryRequest = DB::table('delivery_request')
            ->leftjoin('customers', 'delivery_request.customer_id', '=', 'customers.id')
            ->where('delivery_request.mtm', $cashVoucherRequest->mtm) 
            ->first();
    
            $requestTypes = DB::table('cash_vouchers')
            ->join('cvr_request_type', 'cash_vouchers.request_type', '=', 'cvr_request_type.id')
            ->where('cash_vouchers.dr_id', $cvr_number) 
            ->first();
    
            $drivers = DB::table('allocations')
            ->join('users', 'allocations.driver_id', '=', 'users.id')
            ->where('allocations.dr_id', $cvr_number) 
            ->first();
    
            $fleets = DB::table('allocations')
            ->join('fleet_cards', 'allocations.fleet_card_id', '=', 'fleet_cards.id')
            ->where('allocations.dr_id', $cvr_number) 
            ->first();
    
            $employees = DB::table('cash_vouchers')
            ->join('users', 'cash_vouchers.requestor', '=', 'users.id')
            ->select('users.*', 'cash_vouchers.*') 
            ->where('cash_vouchers.dr_id', $cvr_number) 
            ->first();
    
            // Check if the amount exists and convert it to words
            $amountInWords = $cashVoucherRequest->amount ? $this->convertAmountToWordsPreview($cashVoucherRequest->amount) : 'N/A';
    
            // Return the print view with the data
            return view(
                'cashVoucherRequests.printPreview', compact('cashVoucherRequest', 'amountInWords', 
                'deliveryLineItems', 'employees', 'drivers', 'fleets', 'requestTypes', 'deliveryRequest', 'remarks'
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
            $cashVouchers = CashVoucher::where('status', 3)
            ->where('cvr_type', 'basic')
            ->where('created_by', $employeeCode)
            ->get();
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

}
