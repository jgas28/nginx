<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the warehouses table
        $warehouses = Warehouse::when($search, function ($query, $search) {
            return $query->where('warehouse_code', 'like', '%' . $search . '%')
                        ->orWhere('warehouse_name', 'like', '%' . $search . '%')
                        ->orWhere('warehouse_location', 'like', '%' . $search . '%');
        })
        ->paginate(5);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('warehouses.table', compact('warehouses'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('warehouses.index', compact('warehouses', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('warehouses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'warehouse_code' => 'required|unique:warehouses,warehouse_code',
            'warehouse_name' => 'required',
            'warehouse_location' => 'required',
        ]);

        // Create new warehouse
        $warehouse = new Warehouse([
            'warehouse_code' => $request->warehouse_code,
            'warehouse_name' => $request->warehouse_name,
            'warehouse_location' => $request->warehouse_location,
        ]);

        $warehouse->save();

        return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        return view('warehouses.show', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        // Validate the request data
        $request->validate([
            'warehouse_code' => 'required|unique:warehouses,warehouse_code,' . $warehouse->id,
            'warehouse_name' => 'required',
            'warehouse_location' => 'required',
        ]);

        // Update the warehouse details
        $warehouse->warehouse_code = $request->warehouse_code;
        $warehouse->warehouse_name = $request->warehouse_name;
        $warehouse->warehouse_location = $request->warehouse_location;

        $warehouse->save();

        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted successfully.');
    }
}
