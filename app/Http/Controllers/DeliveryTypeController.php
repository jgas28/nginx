<?php

namespace App\Http\Controllers;

use App\Models\DeliveryType;
use Illuminate\Http\Request;

class DeliveryTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the DeliveryType table
        $deliveryTypes = DeliveryType::when($search, function ($query, $search) {
            return $query->where('delivery_type_code', 'like', '%' . $search . '%')
                        ->orWhere('delivery_type_name', 'like', '%' . $search . '%');
        })
        ->paginate(5);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('deliveryTypes.table', compact('deliveryTypes'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('deliveryTypes.index', compact('deliveryTypes', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('deliveryTypes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'delivery_type_code' => 'required|unique:delivery_types,delivery_type_code',
            'delivery_type_name' => 'required',
        ]);

        // Create new delivery type
        $deliveryTypes = new DeliveryType([
            'delivery_type_code' => $request->delivery_type_code,
            'delivery_type_name' => $request->delivery_type_name,
        ]);

        $deliveryTypes->save();

        return redirect()->route('deliveryTypes.index')->with('success', 'Delivery Types created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DeliveryType $deliveryTypes)
    {
        return view('deliveryTypes.show', compact('deliveryTypes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeliveryType $deliveryType)
    {
        return view('deliveryTypes.edit', compact('deliveryType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeliveryType $deliveryType)
    {
        // Validate the request data
        $request->validate([
            'delivery_type_code' => 'required|unique:delivery_types,delivery_type_code,' . $deliveryType->id,
            'delivery_type_name' => 'required',
        ]);

        // Update the delivery type_name details
        $deliveryType->delivery_type_code = $request->delivery_type_code;
        $deliveryType->delivery_type_name = $request->delivery_type_name;

        $deliveryType->save();

        return redirect()->route('deliveryTypes.index')->with('success', 'Delivery Types updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeliveryType $deliveryType)
    {
        $deliveryType->delete();

        return redirect()->route('deliveryTypes.index')->with('success', 'Delivery Type deleted successfully.');
    }
}
