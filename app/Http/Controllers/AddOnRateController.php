<?php

namespace App\Http\Controllers;

use App\Models\AddOnRate;
use Illuminate\Http\Request;

class AddOnRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the add_on_rates table
        $addOnRates = AddOnRate::when($search, function ($query, $search) {
            return $query->where('add_on_rate_type_code', 'like', '%' . $search . '%')
                         ->orWhere('add_on_rate_type_name', 'like', '%' . $search . '%')
                         ->orWhere('discount', 'like', '%' . $search . '%')
                         ->orWhere('rate', 'like', '%' . $search . '%')
                         ->orWhere('percent_rate', 'like', '%' . $search . '%')
                         ->orWhere('delivery_type', 'like', '%' . $search . '%');
        })
        ->paginate(5);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('addOnRates.table', compact('addOnRates'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('addOnRates.index', compact('addOnRates', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('addOnRates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'add_on_rate_type_code' => 'required|unique:add_on_rates,add_on_rate_type_code',
            'add_on_rate_type_name' => 'required',
            'discount' => 'nullable',
            'rate' => 'nullable',
            'percent_rate' => 'nullable',
            'delivery_type' => 'nullable',
        ]);

        // Create new add-on rate
        $addOnRate = new AddOnRate([
            'add_on_rate_type_code' => $request->add_on_rate_type_code,
            'add_on_rate_type_name' => $request->add_on_rate_type_name,
            'discount' => $request->discount,
            'rate' => $request->rate,
            'percent_rate' => $request->percent_rate,
            'delivery_type' => $request->delivery_type,
        ]);

        $addOnRate->save();

        return redirect()->route('addOnRates.index')->with('success', 'Add-On Rate created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AddOnRate $addOnRate)
    {
        return view('addOnRates.show', compact('addOnRate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AddOnRate $addOnRate)
    {
        return view('addOnRates.edit', compact('addOnRate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AddOnRate $addOnRate)
    {
        // Validate the request data
        $request->validate([
            'add_on_rate_type_code' => 'required|unique:add_on_rates,add_on_rate_type_code,' . $addOnRate->id,
            'add_on_rate_type_name' => 'required',
            'rate' => 'required',
            'percent_rate' => 'required',
            'delivery_type' => 'required',
        ]);

        // Update the add-on rate details
        $addOnRate->add_on_rate_type_code = $request->add_on_rate_type_code;
        $addOnRate->add_on_rate_type_name = $request->add_on_rate_type_name;
        $addOnRate->rate = $request->rate;
        $addOnRate->percent_rate = $request->percent_rate;
        $addOnRate->delivery_type = $request->delivery_type;

        $addOnRate->save();

        return redirect()->route('addOnRates.index')->with('success', 'Add-On Rate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AddOnRate $addOnRate)
    {
        $addOnRate->delete();

        return redirect()->route('addOnRates.index')->with('success', 'Add-On Rate deleted successfully.');
    }
}
