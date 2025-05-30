<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryRequestType;

class DeliveryRequestTypeController extends Controller
{
    //
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the regions table
        $deliveryRequestType = DeliveryRequestType::when($search, function ($query, $search) {
            return $query->where('code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
        })
        ->orderBy('code') // Sort by region_code in ascending order
        ->paginate(10);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('deliveryRequestType.table', compact('deliveryRequestType'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('deliveryRequestType.index', compact('deliveryRequestType', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('deliveryRequestType.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'code' => 'required',
            'description' => 'required',
        ]);

        // Create new region
        $region = new DeliveryRequestType([
            'code' => $request->code,
            'description' => $request->description,
        ]);

        $region->save();

        return redirect()->route('deliveryRequestType.index')->with('success', 'Expense Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DeliveryRequestType $deliveryRequestType)
    {
        return view('deliveryRequestType.show', compact('deliveryRequestType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeliveryRequestType $deliveryRequestType)
    {
        return view('deliveryRequestType.edit', compact('deliveryRequestType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeliveryRequestType $deliveryRequestType)
    {
        // Validate the request data
        $request->validate([
            'code' => 'required',
            'description' => 'required',
        ]);

        // Update the region details
        $deliveryRequestType->code = $request->code;
        $deliveryRequestType->description = $request->description;
        $deliveryRequestType->save();

        return redirect()->route('deliveryRequestType.index')->with('success', 'Expense Type updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeliveryRequestType $deliveryRequestType)
    {
        $deliveryRequestType->delete();

        return redirect()->route('deliveryRequestType.index')->with('success', 'Expense Type deleted successfully.');
    }
}
