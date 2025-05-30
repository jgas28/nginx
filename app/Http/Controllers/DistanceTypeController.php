<?php

namespace App\Http\Controllers;

use App\Models\DistanceType;
use Illuminate\Http\Request;

class DistanceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the Distance Type table
        $distanceType = DistanceType::when($search, function ($query, $search) {
            return $query->where('distance_type_code', 'like', '%' . $search . '%')
                        ->orWhere('distance_type_name', 'like', '%' . $search . '%');
        })
        ->paginate(5);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('distanceTypes.table', compact('distanceTypes'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('distanceTypes.index', compact('distanceType', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('distanceTypes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'distance_type_code' => 'required|unique:distance_types,distance_type_code',
            'distance_type_name' => 'required',
        ]);

        // Create new Distance Type
        $distanceType = new DistanceType([
            'distance_type_code' => $request->distance_type_code,
            'distance_type_name' => $request->distance_type_name,
        ]);

        $distanceType->save();

        return redirect()->route('distanceTypes.index')->with('success', 'Distance Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DistanceType $distanceType)
    {
        return view('distanceTypes.show', compact('distanceType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DistanceType $distanceType)
    {
        return view('distanceTypes.edit', compact('distanceType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DistanceType $distanceType)
    {
        // Validate the request data
        $request->validate([
            'distance_type_code' => 'required|unique:distance_types,distance_type_code,' . $distanceType->id,
            'distance_type_name' => 'required',
        ]);

        // Update the distance type_name details
        $distanceType->distance_type_code = $request->distance_type_code;
        $distanceType->distance_type_name = $request->distance_type_name;

        $distanceType->save();

        return redirect()->route('distanceTypes.index')->with('success', 'Distance Type updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DistanceType $distanceType)
    {
        $distanceType->delete();

        return redirect()->route('distanceTypes.index')->with('success', 'Distance Types deleted successfully.');
    }
}
