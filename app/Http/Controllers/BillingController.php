<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\DeliveryRequest;
use App\Models\Company;
use App\Models\User;
use App\Models\WithholdingTax;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{

    public function index(Request $request)
    {
        $companyId = $request->input('company_id', 1); // Default to company_id = 1 if not provided

        // Filter delivery requests by company
        $query = DeliveryRequest::with('lineItems')
            ->where('delivery_status', 1)
            ->whereNull('billing_id')
            ->where('company_id', $companyId); // Filter by selected company

        $deliveryRequests = $query->get();

        // Get all companies except company_id = 4 (or any other condition you want)
        $companies = Company::where('id', '!=', 4)->get(); 

        return view('billing.index', compact('deliveryRequests', 'companies', 'companyId'));
    }

    public function indexAccessorial(Request $request)
    {
        $companyId = $request->input('company_id', 1); // Default to company_id = 1 if not provided

        // Filter delivery requests by company
        $query = DeliveryRequest::with('lineItems')
            ->where('delivery_status', 1)
            ->whereNull('billing_id')
            ->where('company_id', $companyId)
            ->where(function($query) {
                $query->where('delivery_type', 'multi-drop') // Filter for Multi-Drop
                    ->orWhere('delivery_type', 'multi-pickup'); // Filter for Multi Pick-Up
            });

        $deliveryRequests = $query->get();

        // Get all companies except company_id = 4 (or any other condition you want)
        $companies = Company::where('id', '!=', 4)->get(); 

        return view('billing.indexAccessorial', compact('deliveryRequests', 'companies', 'companyId'));
    }

    // Method to show the form for creating the SOA (after selecting delivery requests)
    public function createSOAForm(Request $request)
    {
        $selectedRequests = $request->input('delivery_requests');

        if (empty($selectedRequests) || !is_array($selectedRequests)) {
            return redirect()->back()->with('error', 'No valid delivery requests selected.');
        }

        // Fetch the selected delivery requests
        $deliveryRequests = DeliveryRequest::whereIn('id', $selectedRequests)->get();

        // Ensure all selected delivery requests belong to the same company
        $companyIds = $deliveryRequests->pluck('company_id')->unique();
        $withholdingTaxes = WithholdingTax::all();
        $users = User::where('status', '!=', 0)->get();

        if ($companyIds->count() > 1) {
            return redirect()->back()->with('error', 'Selected delivery requests must belong to the same company.');
        }

        // Pass the selected delivery requests to the form view
        return view('billing.create_soa_form', compact('deliveryRequests', 'users', 'withholdingTaxes'));
    }

    public function createSOAFormAccessorial(Request $request)
    {
        $selectedRequests = $request->input('delivery_requests');

        if (empty($selectedRequests) || !is_array($selectedRequests)) {
            return redirect()->back()->with('error', 'No valid delivery requests selected.');
        }

        // Fetch the selected delivery requests
        $deliveryRequests = DeliveryRequest::whereIn('id', $selectedRequests)->get();

        // Ensure all selected delivery requests belong to the same company
        $companyIds = $deliveryRequests->pluck('company_id')->unique();
        $withholdingTaxes = WithholdingTax::all();
        $users = User::where('status', '!=', 0)->get();

        if ($companyIds->count() > 1) {
            return redirect()->back()->with('error', 'Selected delivery requests must belong to the same company.');
        }

        // Pass the selected delivery requests to the form view
        return view('billing.create_soa_form_accessorial', compact('deliveryRequests', 'users', 'withholdingTaxes'));
    }


    // Method to create SOA after the form is submitted
    public function createSOA(Request $request)
    {
        $user = Auth::user();
        $employeeCode = $user->id;

        // Ensure delivery_requests is an array
        $selectedRequests = json_decode($request->input('delivery_requests'), true); // Decode the JSON array

        // Check if no delivery requests were selected
        if (empty($selectedRequests) || !is_array($selectedRequests)) {
            return redirect()->back()->with('error', 'No delivery requests selected.');
        }

        // Fetch the selected delivery requests
        $deliveryRequests = DeliveryRequest::whereIn('id', $selectedRequests)->get();

        // Ensure all selected delivery requests belong to the same company
        $companyIds = $deliveryRequests->pluck('company_id')->unique();

        if ($companyIds->count() > 1) {
            return redirect()->back()->with('error', 'Selected delivery requests must belong to the same company.');
        }

        $companyId = $companyIds->first();

        // Add validation for SOA fields
        $validatedData = $request->validate([
            'soa_number' => 'required|string|max:255|unique:billings,soa_number', // Ensure SOA number is unique
            'billed_to' => 'required|string|max:255',
            'billing_address' => 'required|string|max:255',
            'withholding_tax_id' => 'required|exists:withholding_taxes,id',
            'prepared_by' => 'required|exists:users,id',
            'received_by' => 'required|string|max:255',
        ]);

        // Get the total price passed from the form (from hidden input)
        $totalPrice = (float) $request->input('total_price'); // From the hidden input in the form

        // Create Billing record (SOA) with total_price
        $soa = Billing::create([
            'soa_number' => $validatedData['soa_number'],
            'company_id' => $companyId,
            'withholding_tax_id' => $validatedData['withholding_tax_id'],
            'billed_to' => $validatedData['billed_to'],
            'billing_address' => $validatedData['billing_address'],
            'billing_date' => Carbon::now(),
            'billing_type' => 1,
            'status' => 'Pending',
            'created_by' => $employeeCode,
            'total_price' => $totalPrice, // Include total_price here
        ]);

        // Update the delivery requests with the new SOA details 
        DeliveryRequest::whereIn('id', $selectedRequests)->update([
            'billing_id' => $soa->id,
            'soa_number' => $validatedData['soa_number'],
        ]);

        return redirect()->route('billing.index')->with('success', 'SOA created successfully.');
    }

    public function createAccessorialSOA(Request $request)
    {
        $user = Auth::user();
        $employeeCode = $user->id;

        // Ensure delivery_requests is an array
        $selectedRequests = json_decode($request->input('delivery_requests'), true); // Decode the JSON array

        // Check if no delivery requests were selected
        if (empty($selectedRequests) || !is_array($selectedRequests)) {
            return redirect()->back()->with('error', 'No delivery requests selected.');
        }

        // Fetch the selected delivery requests
        $deliveryRequests = DeliveryRequest::whereIn('id', $selectedRequests)->get();

        // Ensure all selected delivery requests belong to the same company
        $companyIds = $deliveryRequests->pluck('company_id')->unique();

        if ($companyIds->count() > 1) {
            return redirect()->back()->with('error', 'Selected delivery requests must belong to the same company.');
        }

        $companyId = $companyIds->first();

        // Add validation for SOA fields
        $validatedData = $request->validate([
            'soa_number' => 'required|string|max:255|unique:billings,soa_number', // Ensure SOA number is unique
            'billed_to' => 'required|string|max:255',
            'billing_address' => 'required|string|max:255',
            'withholding_tax_id' => 'required|exists:withholding_taxes,id',
            'prepared_by' => 'required|exists:users,id',
            'received_by' => 'required|string|max:255',
        ]);

        // Get the total price passed from the form (from hidden input)
        $totalPrice = (float) $request->input('total_price'); // From the hidden input in the form

        // Create Billing record (SOA) with total_price
        $soa = Billing::create([
            'soa_number' => $validatedData['soa_number'],
            'company_id' => $companyId,
            'withholding_tax_id' => $validatedData['withholding_tax_id'],
            'billed_to' => $validatedData['billed_to'],
            'billing_address' => $validatedData['billing_address'],
            'billing_date' => Carbon::now(),
            'billing_type' => 2,
            'status' => 'Pending',
            'created_by' => $employeeCode,
            'total_price' => $totalPrice, // Include total_price here
        ]);

        // Update the delivery requests with the new SOA details 
        DeliveryRequest::whereIn('id', $selectedRequests)->update([
            'billing_id' => $soa->id,
            'soa_number' => $validatedData['soa_number'],
        ]);

        return redirect()->route('billing.index')->with('success', 'SOA created successfully.');
    }

    public function showSoa()
    {
        $soas = Billing::all();
        return view('billing.showSoa', compact('soas'));
    }

    public function print($id) {
        // Eager load both deliveryRequests and lineitems
        $soa = Billing::with(['deliveryRequests.lineItems'])->findOrFail($id);

        // Pass the data to the view
        return view('billing.SoaPrint', compact('soa'));
    }
}
