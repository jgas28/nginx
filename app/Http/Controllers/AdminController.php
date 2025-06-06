<?php

namespace App\Http\Controllers;

use App\Models\CashVoucher;
use App\Models\cvr_approval;
use App\Models\Company;
use App\Models\Expense_Type;
use App\Models\Supplier;
use App\Models\Truck;
use App\Models\User;
use App\Models\Approver;
use App\Models\cvr_request_type;
use App\Models\MonthlySeriesNumber;
use App\Models\WithholdingTax;
use App\Models\RunningBalance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use NumberToWords\NumberToWords;

class AdminController extends Controller
{
    //
    public function index(Request $request)
    {
        $search = $request->get('search');

        // Fetch related delivery line items by joining with the correct table name
        $cashVouchers = CashVoucher::with(['company', 'suppliers', 'expenseTypes','employee'])
        ->when($search, function ($query, $search) {
            return $query->where('cvr_type', 'like', '%' . $search . '%');
        })
        ->whereNotIn('cvr_type', ['delivery', 'pullout', 'accessorial'])
        ->where('status', '=', '1')
        ->paginate(10);

        return view('admin.index', compact('cashVouchers'));

    }

    public function create()
    {
        $companies=Company::all();
        $expenseTypes=Expense_Type::all();
        $suppliers=Supplier::all();
        $trucks=Truck::all();
        $taxes=WithholdingTax::all();

        return view('admin.create', compact('companies', 'expenseTypes', 'suppliers', 'trucks', 'taxes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $employeeCode = $user->id;
        $company_id = $request->company_id;
        $request->validate([
            'cvr_type' => 'required|string',
            'voucher_type' => 'required|string',
            'company_id' => 'required|exists:companies,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'expense_type_id' => 'required|exists:expense_types,id',
            'description' => 'required|array|min:1',
            'amount_details' => 'required|array|min:1',
            'description.*' => 'required|string',
            'amount_details.*' => 'required|numeric',
            'truck_id' => 'nullable|exists:trucks,id',
            'withholding_tax' => 'nullable|numeric',
            'tax_base_amount' => 'nullable|numeric',
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $employeeCode, $company_id) {
            // Calculate current (or next) month and year
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

            $nextCvrNumberFormatted = str_pad($nextCvrNumber, 3, '0', STR_PAD_LEFT);
            $formattedCvrNumber = "CVR-{$year}-{$month}-{$nextCvrNumberFormatted}/{$company_id}";

            // Save voucher
            $voucher = new CashVoucher();
            $voucher->cvr_type = $request->cvr_type;
            $voucher->voucher_type = $request->voucher_type;
            $voucher->cvr_number = $formattedCvrNumber;
            $voucher->company_id = $company_id;
            $voucher->supplier_id = $request->supplier_id;
            $voucher->expense_type_id = $request->expense_type_id;
            $voucher->withholding_tax_id = $request->voucher_type === 'with_tax' ? $request->withholding_tax : null;
            $voucher->tax_based_amount = $request->voucher_type === 'with_tax' ? $request->tax_base_amount : null;
            $voucher->description = json_encode($request->description);
            $voucher->amount_details = json_encode($request->amount_details);
            $voucher->remarks = $request->remarks ? json_encode($request->remarks) : null;
            $voucher->status = '1';
            $voucher->truck_id = $request->truck_id;
            $voucher->created_by = $employeeCode;
            $voucher->save();
        });

        return redirect()->route('admin.index')->with('success', 'Cash Voucher successfully created.');
    }



    public function generateCvrNumber(Request $request)
    {
        $company = Company::findOrFail($request->company_id);

        $now = now();

        $year = $now->format('Y');
        $month = $now->format('m');
        $yearMonth = $now->format('Y-m');
        $isFirstDay = $now->day === 1;

        $monthlySeries = MonthlySeriesNumber::where('company_id', $company->id)->first();

        if (!$monthlySeries || $monthlySeries->month !== $yearMonth || $isFirstDay) {
            $seriesNumber = 1;
        } else {
            $seriesNumber = $monthlySeries->series_number + 1;
        }

        $series = str_pad($seriesNumber, 3, '0', STR_PAD_LEFT);
        $cvrNumber = "CVR-{$year}-{$month}-{$series}";

        return response()->json(['cvr_number' => $cvrNumber]);
    }


    public function show($id)
    {
        $voucher = CashVoucher::findOrFail($id);
        return view('admin.show', compact('voucher'));
    }

    public function approvals()
    {
        $cashVouchers = CashVoucher::with(['company', 'suppliers', 'expenseTypes','employee'])
            ->whereIn('cvr_type', ['admin', 'rpm'])
            ->where('status', 1)
            ->paginate(10);

        return view('adminCV.approval', compact('cashVouchers'));
    }

    public function edit($id)
    {
        $voucher = CashVoucher::findOrFail($id);

        $companies = Company::all();
        $expenseTypes = Expense_Type::all();
        $suppliers = Supplier::all();
        $trucks = Truck::all();
        $taxes = WithholdingTax::all();

        return view('admin.edit', compact('voucher', 'companies', 'expenseTypes', 'suppliers', 'trucks', 'taxes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cvr_type' => 'required|in:admin,rpm',
            'voucher_type' => 'required|in:regular,with_tax',
            'tax_base_amount' => 'nullable|numeric',
            'withholding_tax' => 'nullable|string|max:255',
            'company_id' => 'required|integer|exists:companies,id',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'expense_type_id' => 'required|integer|exists:expense_types,id',
            'truck_id' => 'nullable|integer|exists:trucks,id',
            'description' => 'required|array',
            'description.*' => 'required|string',
            'amount_details' => 'required|array',
            'amount_details.*' => 'required|numeric',
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string',
        ]);

        $voucher = CashVoucher::findOrFail($id);

        // Clear tax fields if voucher_type is not "with_tax"
        $taxBaseAmount = $request->voucher_type === 'with_tax' ? $request->tax_base_amount : null;
        $withholdingTaxId = $request->voucher_type === 'with_tax' ? $request->withholding_tax : null;

        $voucher->update([
            'cvr_type' => $request->cvr_type,
            'voucher_type' => $request->voucher_type,
            'tax_based_amount' => $taxBaseAmount,
            'withholding_tax_id' => $withholdingTaxId,
            'supplier_id' => $request->supplier_id,
            'expense_type_id' => $request->expense_type_id,
            'truck_id' => $request->cvr_type === 'rpm' ? $request->truck_id : null,
            'description' => $request->description,
            'amount_details' => $request->amount_details,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('admin.index')->with('success', 'Voucher updated successfully.');
    }

    public function destroy($id)
    {
        $voucher = CashVoucher::findOrFail($id);
        $voucher->status = 0;
        $voucher->save();

        return redirect()->back()->with('success', 'Cash voucher has been archived successfully.');
    }

    public function viewPrint($id)
    {
        $voucher = CashVoucher::findOrFail($id);
        
        // return a dedicated view for printing, e.g., print.blade.php
        return view('admin.viewPrint', compact('voucher'));
    }

    public function editApproval($id)
    {
        dd($id);
    }

    public function approvalRequest($id)
    {
        $cashVouchers = CashVoucher::with(['company', 'suppliers', 'expenseTypes', 'employee'])
            ->findOrFail($id);
        
        $employees = User::all();
        $approves = Approver::all();
        $taxes = WithholdingTax::all();
        $requestType = cvr_request_type::all();

        return view('adminCV.approvalRequest', compact('cashVouchers', 'employees', 'approves', 'taxes', 'requestType'));
    }

    public function StoreApprovalRequest(Request $request)
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
            return redirect()->route('adminCV.approval')->with('success', 'Cash Voucher Approval Saved Successfully');
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
            return redirect()->route('adminCV.approval')
                ->with('error', 'DB Error: ' . $e->getMessage());
        }
    }

    public function rejectView()
    {
        $user = Auth::user();
        $employeeCode = $user->id;
        $cashVouchers = CashVoucher::where('status', 3)
            ->where('cvr_type', 'basic')
            ->where('created_by', $employeeCode)
            ->get();

        return view('adminCV.rejectView', compact('cashVouchers'));
    }

    public function printPreview($id)
    {
        $user = Auth::user();
        $fullname = $user->fname . ' ' . $user->lname;

        $vouchers = CashVoucher::findOrFail($id);
        // Compute the amount that will be shown on the Blade
        if ($vouchers->voucher_type === 'with_tax') {
            $taxAmount = $vouchers->tax_based_amount * 0.12;
            $withholdingAmount = $vouchers->tax_based_amount * $vouchers->withholdingTax->percentage;
            $finalAmount = $vouchers->tax_based_amount + $taxAmount - $withholdingAmount;
        } elseif ($vouchers->voucher_type === 'regular') {
            $finalAmount = $vouchers->amount;
        } else {
            $finalAmount = 0; // fallback
        }

        // Convert the calculated amount to words
        $amountInWords = $this->convertAmountToWordsPreview($finalAmount);
        return view('adminCV.printPreview', compact('vouchers', 'fullname', 'amountInWords')); 
    }

    public function printMultiple()
    {

    }

    public function printCVR($id, $cvr_number)
    {
        $user = Auth::user();
        $fullname = $user->fname . ' ' . $user->lname;

        $vouchers = cvr_approval::with('cashVoucher')->find($id);
        $fullname = $user->fname . ' ' . $user->lname;
        $approvers = DB::table('cvr_approvals')
                ->leftjoin('cvr_approver', 'cvr_approvals.source', '=', 'cvr_approver.id')
                ->where('cvr_approvals.id', $id)
                ->first();

        // Compute the amount that will be shown on the Blade
        if ($vouchers->cashVoucher->voucher_type === 'with_tax') {
            $taxAmount = $vouchers->cashVoucher->tax_based_amount * 0.12;
            $withholdingAmount = $vouchers->cashVoucher->tax_based_amount * $vouchers->cashVoucher->withholdingTax->percentage;
            $finalAmount = $vouchers->cashVoucher->tax_based_amount + $taxAmount - $withholdingAmount;
        } elseif ($vouchers->cashVoucher->voucher_type === 'regular') {
            $finalAmount = $vouchers->amount;
        } else {
            $finalAmount = 0; // fallback
        }

        // Convert the calculated amount to words
        $amountInWords = $this->convertAmountToWords($finalAmount);

        return view('AdminCV.print', compact('vouchers', 'fullname', 'approvers', 'amountInWords'));
    }

    public function cvrList(Request $request)
    { 
        // Get the search query from the request
        $search = $request->get('search');
    
        // Fetch related delivery line items by joining with the correct table name
        $cashVoucherRequests = cvr_approval::with('cashVoucher')
        ->whereHas('cashVoucher', function ($query) {
            $query->whereIn('cvr_type', ['admin', 'rpm'])
                ->where('status', 2)
                ->orderBy('print_status', 'asc');
        })
        ->paginate(10);

        // Check if the request expects an AJAX response
        if ($request->ajax()) {
            return view('adminCV.cvrList_table', compact('cashVoucherRequests'))->render();
        }
    
        // For the normal view
        return view('adminCV.cvrList', compact('cashVoucherRequests', 'search'));
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
}
