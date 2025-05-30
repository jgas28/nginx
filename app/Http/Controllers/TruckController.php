<?php

namespace App\Http\Controllers;

use App\Models\Truck;
use Illuminate\Http\Request;

class TruckController extends Controller
{
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the trucks table
        $trucks = Truck::when($search, function ($query, $search) {
            return $query->where('truck_code', 'like', '%' . $search . '%')
                        ->orWhere('truck_name', 'like', '%' . $search . '%')
                        ->orWhere('plate_no', 'like', '%' . $search . '%')
                        ->orWhere('truck_type', 'like', '%' . $search . '%');
        })
        ->orderBy('truck_code')
        ->paginate(10);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('trucks.table', compact('trucks'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('trucks.index', compact('trucks', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('trucks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'truck_code' => 'required|unique:trucks,truck_code',
            'truck_name' => 'required',
            'plate_no' => 'required',
            'truck_type' => 'required',
        ]);

        // Create new employee
        $truck = new Truck([
            'truck_code' => $request->truck_code,
            'truck_name' => $request->truck_name,
            'plate_no' => $request->plate_no,
            'truck_type' => $request->truck_type,
        ]);

        $truck->save();

        return redirect()->route('trucks.index')->with('success', 'Truck created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Truck $truck)
    {
        return view('trucks.show', compact('truck'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Truck $truck)
    {
        return view('trucks.edit', compact('truck'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Truck $truck)
    {
        // Validate the request data
        $request->validate([
            'truck_code' => 'required|unique:trucks,truck_code,' . $truck->id,
            'truck_name' => 'required',
            'plate_no' => 'required',
            'truck_type' => 'required',
        ]);

        // Update the employee details
        $truck->truck_code = $request->truck_code;
        $truck->truck_name = $request->truck_name;
        $truck->plate_no = $request->plate_no;
        $truck->truck_type = $request->truck_type;

        $truck->save();

        return redirect()->route('trucks.index')->with('success', 'Truck updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Truck $truck)
    {
        $truck->delete();

        return redirect()->route('trucks.index')->with('success', 'Truck deleted successfully.');
    }
}
