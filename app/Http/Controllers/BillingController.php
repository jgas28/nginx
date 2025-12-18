<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\DeliveryRequest;
use App\Models\DeliveryRequestLineItem;
use App\Models\WithholdingTax;
use App\Models\Billing;

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

    // Step 3: store billing
    public function store(Request $request)
    {
        $validated = $request->validate([
            'soa_number' => 'required|string|max:255|unique:billings',
            'company_id' => 'required|exists:companies,id',
            'withholding_tax_id' => 'required|exists:withholding_taxes,id',
            'billed_to' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'billing_date' => 'required|date',
        ]);

        $billing = Billing::create($validated);

        // Attach selected items from session
        $deliveryRequests = session('selected_delivery_requests', []);
        $lineItems = session('selected_line_items', []);

        if($deliveryRequests){
            $billing->deliveryRequests()->attach($deliveryRequests);
        }

        if($lineItems){
            $billing->deliveryRequestLineItems()->attach($lineItems);
        }

        // Clear session after storing
        session()->forget(['selected_delivery_requests', 'selected_line_items']);

        return redirect()->route('billing.index')->with('status', 'Billing created successfully!');
    }
}
