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
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        $suppliers = Supplier::when($search, function ($query, $search) {
            return $query->where('supplier_code', 'like', '%' . $search . '%')
                        ->orWhere('supplier_name', 'like', '%' . $search . '%');
        })
        ->orderBy('supplier_name')
        ->paginate($perPage)
        ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('suppliers.table', compact('suppliers', 'search', 'perPage'))->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $suppliers->total(),
            ]);
        }

        return view('suppliers.index', compact('suppliers', 'search', 'perPage'));
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

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
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

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
    
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
