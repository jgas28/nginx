<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\WithholdingTax;
use App\Models\DeliveryRequest;
use App\Models\DeliveryRequestLineItem;
use App\Models\Billing;

class BillingController extends Controller
{
    public function selectItems()
    {
        // Fetch all companies
        $companies = Company::all();

        // Fetch all unbilled DeliveryRequests with status = 1 for the selected company
        $deliveryRequests = DeliveryRequest::where('status', 1)
                                            ->whereNull('billing_id') // Ensure billing_id is null
                                            ->get();

        // Fetch all unbilled DeliveryRequestLineItems for the selected company
        $lineItems = DeliveryRequestLineItem::whereNull('billing_id')
                                            ->whereHas('deliveryRequest', function ($query) {
                                                $query->where('status', 1); // Ensure related DeliveryRequest is delivered
                                            })
                                            ->get();

        return view('billing.select-items', compact('companies', 'deliveryRequests', 'lineItems'));
    }


    public function getItemsByCompany(Request $request)
    {
        $companyId = $request->input('company_id');

        // Fetch all unbilled DeliveryRequests with status = 1 for the selected company
        $deliveryRequests = DeliveryRequest::where('company_id', $companyId)
                                            ->where('status', 1)
                                            ->whereNull('billing_id') // Ensure billing_id is null
                                            ->get();

        // Fetch all unbilled DeliveryRequestLineItems for the selected company
        $lineItems = DeliveryRequestLineItem::whereNull('billing_id')
                                            ->whereHas('deliveryRequest', function ($query) use ($companyId) {
                                                $query->where('company_id', $companyId)
                                                    ->where('status', 1); // Ensure related DeliveryRequest is delivered
                                            })
                                            ->get();

        // Organize line items by delivery request id
        $lineItemsByDr = [];
        foreach ($lineItems as $item) {
            $lineItemsByDr[$item->deliveryRequest->id][] = $item;
        }

        // Include total amount for each delivery request and its line items
        $deliveryRequestsWithTotal = $deliveryRequests->map(function($dr) use ($lineItemsByDr) {
            $totalAmount = 0;
            $lineItemsForDr = $lineItemsByDr[$dr->id] ?? [];

            // Add the `delivery_rate` from the DeliveryRequest itself
            $totalAmount += $dr->delivery_rate;

            // Add up the `accessorial_rate` from the associated line items
            foreach ($lineItemsForDr as $lineItem) {
                $totalAmount += $lineItem->accessorial_rate;
            }

            $dr->totalAmount = $totalAmount;
            return $dr;
        });

        // Return the results as JSON
        return response()->json([
            'deliveryRequests' => $deliveryRequestsWithTotal,
            'lineItems' => $lineItemsByDr, // grouped by deliveryRequest id
        ]);
    }



    public function storeSelection(Request $request)
    {
        // Store the selected MTMs and line items in session or pass to the next step
        $request->session()->put('selected_delivery_requests', $request->delivery_requests);
        $request->session()->put('selected_line_items', $request->line_items);

        return redirect()->route('billing.create');
    }

    public function create()
    {
        // Retrieve selected items from the session
        $deliveryRequests = session('selected_delivery_requests', []);
        $lineItems = session('selected_line_items', []);

        // Fetch additional data for the second step (company, withholding tax, etc.)
        $companies = Company::all();
        $withholdingTaxes = WithholdingTax::all();

        return view('billing.create-billing', compact('deliveryRequests', 'lineItems', 'companies', 'withholdingTaxes'));
    }

    public function store(Request $request)
    {
        // Handle the storing of the full billing data

        // Validate and create the billing record
        $validated = $request->validate([
            'soa_number' => 'required|string|max:255|unique:billings',
            'company_id' => 'required|exists:companies,id',
            'withholding_tax_id' => 'required|exists:withholding_taxes,id',
            'billed_to' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'billing_date' => 'required|date',
        ]);

        $billing = Billing::create($validated);

        // Attach selected delivery requests and line items
        if ($deliveryRequests = session('selected_delivery_requests')) {
            $billing->deliveryRequests()->attach($deliveryRequests);
        }

        if ($lineItems = session('selected_line_items')) {
            $billing->deliveryRequestLineItems()->attach($lineItems);
        }

        // Clear session after creating the billing
        session()->forget(['selected_delivery_requests', 'selected_line_items']);

        return redirect()->route('billing.index')->with('status', 'Billing created successfully!');
    }

}
