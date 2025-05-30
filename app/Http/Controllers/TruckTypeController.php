<?php

namespace App\Http\Controllers;

use App\Models\TruckType;
use Illuminate\Http\Request;

class TruckTypeController extends Controller
{
    //
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the trucks table
        $trucks = TruckType::when($search, function ($query, $search) {
            return $query->where('truck_code', 'like', '%' . $search . '%')
                        ->orWhere('truck_type', 'like', '%' . $search . '%');
        })
        ->orderBy('truck_code')
        ->paginate(10);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('trucksTypes.table', compact('trucks'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('trucksTypes.index', compact('trucks', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('trucksTypes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'truck_code' => 'required|unique:trucks,truck_code',
            'truck_type' => 'required',
        ]);

        // Create new employee
        $truck = new TruckType([
            'truck_code' => $request->truck_code,
            'truck_type' => $request->truck_type,
        ]);

        $truck->save();

        return redirect()->route('trucksTypes.index')->with('success', 'Truck created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TruckType $truck)
    {
        return view('trucksTypes.show', compact('truck'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TruckType $trucksType)
    {
        return view('trucksTypes.edit', compact('trucksType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TruckType $trucksType)
    {
        // Validate the request data
        $request->validate([
            'truck_code' => 'required|unique:truck_types,truck_code,' . $trucksType->id,
            'truck_type' => 'required',
        ]);

        // Update the employee details
        $trucksType->truck_code = $request->truck_code;
        $trucksType->truck_type = $request->truck_type;

        $trucksType->save();

        return redirect()->route('trucksTypes.index')->with('success', 'Truck updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TruckType $trucksType)
    {
        $trucksType->delete();

        return redirect()->route('trucksTypes.index')->with('success', 'Truck deleted successfully.');
    }
}
