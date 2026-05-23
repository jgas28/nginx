<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    //
      /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        $customers = Customer::when($search, function ($query, $search) {
            return $query->where(function ($customerQuery) use ($search) {
                $customerQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('tin_no', 'like', '%' . $search . '%')
                    ->orWhere('customer_address', 'like', '%' . $search . '%');
            });
        })
        ->orderBy('name')
        ->paginate($perPage)
        ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('customers.table', compact('customers', 'search', 'perPage'))->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $customers->total(),
            ]);
        }

        return view('customers.index', compact('customers', 'search', 'perPage'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'tin_no' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string|max:1000',
        ]);

        // Create new customer
        $customer = new Customer([
            'name' => $request->name,
            'tin_no' => $request->tin_no,
            'customer_address' => $request->customer_address,
        ]);

        $customer->save();

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'tin_no' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string|max:1000',
        ]);

        // Update the customer details
        $customer->name = $request->name;
        $customer->tin_no = $request->tin_no;
        $customer->customer_address = $request->customer_address;

        $customer->save();

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
