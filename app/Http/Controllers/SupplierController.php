<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    //
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the companies table
        $suppliers = Supplier::when($search, function ($query, $search) {
            return $query->where('supplier_code', 'like', '%' . $search . '%')
                        ->orWhere('supplier_name', 'like', '%' . $search . '%');
        })
        ->paginate(5);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('suppliers.table', compact('suppliers'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('suppliers.index', compact('suppliers', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'supplier_code' => 'required|unique:suppliers,supplier_code',
            'supplier_name' => 'required',
        ]);

        // Create new company
        $supplier = new Supplier([
            'supplier_code' => $request->supplier_code,
            'supplier_name' => $request->supplier_name,
        ]);

        $supplier->save();

        return redirect()->route('suppliers.index')->with('success', 'supplier created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        // Validate the request data
        $request->validate([
            'supplier_code' => 'required|unique:suppliers,supplier_code,' . $supplier->id,
            'supplier_name' => 'required',
        ]);

        // Update the company details
        $supplier->supplier_code = $request->supplier_code;
        $supplier->supplier_name = $request->supplier_name;

        $supplier->save();

        return redirect()->route('suppliers.index')->with('success', 'supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        // Delete the company itself
        $supplier->delete();
    
        return redirect()->route('suppliers.index')->with('success', 'suppliers deleted successfully.');
    }
}