<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    //
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        $areas = Area::when($search, function ($query, $search) {
            return $query->where('area_code', 'like', '%' . $search . '%')
                        ->orWhere('area_name', 'like', '%' . $search . '%');
        })
        ->orderBy('area_name')
        ->paginate($perPage)
        ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('areas.table', compact('areas', 'search', 'perPage'))->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $areas->total(),
            ]);
        }

        return view('areas.index', compact('areas', 'search', 'perPage'));
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
        $request->validate([
            'area_code' => 'required',
            'area_name' => 'required',
        ]);

        $area = new Area([
            'area_code' => $request->area_code,
            'area_name' => $request->area_name,
        ]);

        $area->save();

        return redirect()->route('areas.index')->with('success', 'Area created successfully.');
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
        $request->validate([
            'area_code' => 'required',
            'area_name' => 'required',
        ]);

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
