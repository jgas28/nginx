<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Area;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the regions table
        $regions = Region::with('area')
        ->when($search, function ($query, $search) {
            return $query->where('region_code', 'like', '%' . $search . '%')
                         ->orWhere('region_name', 'like', '%' . $search . '%')
                         ->orWhere('province', 'like', '%' . $search . '%');
        })
        ->orderBy('region_code')
        ->paginate(10);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('regions.table', compact('regions'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('regions.index', compact('regions', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $areas = Area::all();
        return view('regions.create', compact('areas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'region_code' => 'required',
            'region_name' => 'required',
            'province' => 'required',
            'area_id' => 'required',
        ]);

        // Create new region
        $region = new Region([
            'region_code' => $request->region_code,
            'region_name' => $request->region_name,
            'province' => $request->province,
            'area_id' => $request->area_id,
        ]);

        $region->save();

        return redirect()->route('regions.index')->with('success', 'Region created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Region $region)
    {
        return view('regions.show', compact('region'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Region $region)
    {
        $areas = Area::all();
        return view('regions.edit', compact('region', 'areas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Region $region)
    {
        // Validate the request data
        $request->validate([
            'region_code' => 'required',
            'region_name' => 'required',
            'province' => 'required',
            'area_id' => 'required',
        ]);

        // Update the region details
        $region->region_code = $request->region_code;
        $region->region_name = $request->region_name;
        $region->province = $request->province;
        $region->area_id = $request->area_id;
        $region->save();

        return redirect()->route('regions.index')->with('success', 'Region updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Region $region)
    {
        $region->delete();

        return redirect()->route('regions.index')->with('success', 'Region deleted successfully.');
    }

        public function getByArea($areaId)
    {
        $regions = Region::where('area_id', $areaId)->get();

        return response()->json($regions);
    }
}
