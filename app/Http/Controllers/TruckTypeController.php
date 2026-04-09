<?php

namespace App\Http\Controllers;

use App\Models\TruckType;
use Illuminate\Http\Request;

class TruckTypeController extends Controller
{
    //
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        $trucks = TruckType::when($search, function ($query, $search) {
            return $query->where('truck_code', 'like', '%' . $search . '%')
                        ->orWhere('truck_type', 'like', '%' . $search . '%');
        })
        ->orderBy('truck_code')
        ->paginate($perPage)
        ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('trucksTypes.table', compact('trucks', 'search', 'perPage'))->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $trucks->total(),
            ]);
        }

        return view('trucksTypes.index', compact('trucks', 'search', 'perPage'));
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
        $request->validate([
            'truck_code' => 'required|unique:truck_types,truck_code',
            'truck_type' => 'required',
        ]);

        $truck = new TruckType([
            'truck_code' => $request->truck_code,
            'truck_type' => $request->truck_type,
        ]);

        $truck->save();

        return redirect()->route('trucksTypes.index')->with('success', 'Truck type created successfully.');
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
        $request->validate([
            'truck_code' => 'required|unique:truck_types,truck_code,' . $trucksType->id,
            'truck_type' => 'required',
        ]);

        $trucksType->truck_code = $request->truck_code;
        $trucksType->truck_type = $request->truck_type;

        $trucksType->save();

        return redirect()->route('trucksTypes.index')->with('success', 'Truck type updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TruckType $trucksType)
    {
        $trucksType->delete();

        return redirect()->route('trucksTypes.index')->with('success', 'Truck type deleted successfully.');
    }
}
