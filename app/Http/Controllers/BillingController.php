<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\DeliveryRequest;
use App\Models\DeliveryRequestLineItem;
use App\Models\WithholdingTax;
use App\Models\Billing;
use App\Models\Billing_Delivery_Request;
use App\Models\Billing_Delivery_Request_Line_Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;  

class BillingController extends Controller
{
    // Step 1: Select items
    public function selectItems()
    {
        $companies = Company::all();
        $deliveryRequests = DeliveryRequest::where('status', 1)->get();
        $lineItems = DeliveryRequestLineItem::whereHas('deliveryRequest', function ($query) {
            $query->where('status', 1);
        })->get();

        return view('billing.select-items', compact('companies', 'deliveryRequests', 'lineItems'));
    }

    public function getItemsByCompany(Request $request)
    {
        $companyId = $request->input('company_id');

        $deliveryRequests = DeliveryRequest::where('company_id', $companyId)
            ->where('status', 1)
            ->get();

        $lineItems = DeliveryRequestLineItem::whereHas('deliveryRequest', function ($query) use ($companyId) {
            $query->where('company_id', $companyId)
                ->where('status', 1);
        })->get();

        $lineItemsByDr = [];
        foreach ($lineItems as $item) {
            $lineItemsByDr[$item->deliveryRequest->id][] = $item;
        }

        return response()->json([
            'deliveryRequests' => $deliveryRequests,
            'lineItems' => $lineItemsByDr,
        ]);
    }

    public function getItemsByCompanyForBilling(Request $request)
    {
        $companyId = $request->company_id;

        return DeliveryRequest::where('company_id', $companyId)
            ->where('status', 1)
            ->with('lineItems')
            ->get()
            ->map(fn ($dr) => [
                'id' => $dr->id,
                'mtm' => $dr->mtm,
                'delivery_rate' => $dr->delivery_rate,
                'is_billed' => $dr->billing_id !== null,
                'line_items' => $dr->lineItems->map(fn ($li) => [
                    'id' => $li->id,
                    'delivery_number' => $li->delivery_number,
                    'accessorial_rate' => $li->accessorial_rate,
                    'is_billed' => $li->billing_id !== null,
                ]),
            ]);
    }

    // Step 1: storeSelection
    public function storeSelection(Request $request)
    {
        $deliveryRequests = json_decode($request->delivery_requests, true) ?? [];
        $lineItems = json_decode($request->line_items, true) ?? [];

        // Store selected IDs in session
        $request->session()->put('selected_delivery_requests', $deliveryRequests);
        $request->session()->put('selected_line_items', $lineItems);

        // Redirect to Step 2
        return redirect()->route('billing.create');
    }

    // Step 2: create billing
    public function create()
    {

        // Get IDs from session
        $deliveryRequestIds = session('selected_delivery_requests', []);
        $lineItemIds = session('selected_line_items', []);

        // Fetch the models using the IDs
        $deliveryRequests = DeliveryRequest::whereIn('id', $deliveryRequestIds)->get();
        $lineItems = DeliveryRequestLineItem::whereIn('id', $lineItemIds)->get();

        $company_id = $deliveryRequests->first()?->company_id ?? null;

        $companies = Company::all();
        $withholdingTaxes = WithholdingTax::all();

        return view('billing.create-billing', compact(
            'deliveryRequests', 'lineItems', 'companies', 'withholdingTaxes', 'company_id'
        ));
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'soa_number' => 'required|string|max:255|unique:billings',
            'company_id' => 'required|exists:companies,id',
            'withholding_tax_id' => 'required|exists:withholding_taxes,id',
            'billed_to' => 'required|string|max:255',
            'billing_address' => 'required|string|max:45',
            'billing_date' => 'required|date',
            'total_amount' => 'required|numeric',
            // Additional fields like total_price, etc.
        ]);

        // Start a transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // Create the billing record
            $billing = Billing::create(array_merge($validated));

            // Attach selected delivery requests from session (if any)
            $deliveryRequests = session('selected_delivery_requests', []);
            if ($deliveryRequests) {
                // Update `billing_id` in the delivery requests
                $updateDeliveryRequests = DeliveryRequest::whereIn('id', $deliveryRequests)
                    ->update(['billing_id' => $billing->id]);

                // If the update fails, throw an exception to trigger the rollback
                if ($updateDeliveryRequests === 0) {
                    throw new \Exception('Failed to update Delivery Requests.');
                }

                // Insert into the pivot table `billing_delivery_request`
                foreach ($deliveryRequests as $drId) {
                    Billing_Delivery_Request::create([
                        'billing_id' => $billing->id, // Use the billing ID
                        'delivery_request_id' => $drId,
                    ]);
                }
            }

            // Attach selected line items from session (if any)
            $lineItems = session('selected_line_items', []);
            if ($lineItems) {
                // Update `billing_id` in the delivery request line items
                $updateLineItems = DeliveryRequestLineItem::whereIn('id', $lineItems)
                    ->update(['billing_id' => $billing->id]);

                // If the update fails, throw an exception to trigger the rollback
                if ($updateLineItems === 0) {
                    throw new \Exception('Failed to update Delivery Request Line Items.');
                }

                // Insert into the pivot table `billing_delivery_request_line_item`
                foreach ($lineItems as $liId) {
                    Billing_Delivery_Request_Line_Item::create([
                        'billing_id' => $billing->id, // Use the billing ID
                        'delivery_request_line_item_id' => $liId,
                    ]);
                }
            }

            $billing->total_price = $request->input('total_amount'); // Store total amount
            $billing->status = 1;
            $billing->created_at = now();
            $billing->save();

            // Clear session after storing
            session()->forget(['selected_delivery_requests', 'selected_line_items']);

            // Commit the transaction
            DB::commit();

            // Redirect to the billing select page with success message
            return redirect()->route('billing.select')->with('status', 'Billing created successfully!');
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Log the error
            Log::error('Failed to create billing', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),  // Optionally log the request data
            ]);

            // Return error message
            return redirect()->route('billing.select')->with('error', 'Failed to create billing. Please try again.');
        }
    }

    public function index()
    {
        $billings = Billing::all();

        return view('billing.index', compact('billings'));
    }

    public function edit($id)
    {
        // Find the billing record by ID
        $billing = Billing::findOrFail($id);

        // Fetch associated data (e.g., companies, withholding taxes)
        $companies = Company::all();
        $withholdingTaxes = WithholdingTax::all();

        // Fetch the delivery requests and line items associated with this billing (or any other related data)
        $deliveryRequests = DeliveryRequest::all();
        $lineItems = DeliveryRequestLineItem::all();

        // Pass the data to the view
        return view('billing.edit', compact('billing', 'companies', 'withholdingTaxes', 'deliveryRequests', 'lineItems'));
    }


}
