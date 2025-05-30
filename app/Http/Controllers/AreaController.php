<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    //
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the regions table
        $regions = Area::when($search, function ($query, $search) {
            return $query->where('area_code', 'like', '%' . $search . '%')
                        ->orWhere('area_name', 'like', '%' . $search . '%');
        })
        ->orderBy('area_code') // Sort by region_code in ascending order
        ->paginate(10);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('areas.table', compact('regions'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('areas.index', compact('regions', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('areas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'area_code' => 'required',
            'area_name' => 'required',
        ]);

        // Create new region
        $region = new Area([
            'area_code' => $request->area_code,
            'area_name' => $request->area_name,
        ]);

        $region->save();

        return redirect()->route('areas.index')->with('success', 'Region created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $region)
    {
        return view('areas.show', compact('region'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area)
    {
        return view('areas.edit', compact('area'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        // Validate the request data
        $request->validate([
            'area_code' => 'required',
            'area_name' => 'required',
        ]);

        // Update the region details
        $area->area_code = $request->area_code;
        $area->area_name = $request->area_name;
        $area->save();

        return redirect()->route('areas.index')->with('success', 'Area updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
        $area->delete();

        return redirect()->route('areas.index')->with('success', 'Area deleted successfully.');
    }
}
