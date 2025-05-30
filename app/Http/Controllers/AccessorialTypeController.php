<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\AccessorialType;

class AccessorialTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the add_on_rates table
        $accessorialTypes = AccessorialType::when($search, function ($query, $search) {
            return $query->where('accessorial_types_code', 'like', '%' . $search . '%')
                         ->orWhere('accessorial_types_name', 'like', '%' . $search . '%');
        })
        ->paginate(5);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('accessorialTypes.table', compact('accessorialTypes'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('accessorialTypes.index', compact('accessorialTypes', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accessorialTypes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'accessorial_types_code' => 'required|unique:accessorial_types,accessorial_types_code',
            'accessorial_types_name' => 'required',
        ]);

        // Create new Accessorial Type
        $accessorialType = new AccessorialType([
            'accessorial_types_code' => $request->accessorial_types_code,
            'accessorial_types_name' => $request->accessorial_types_name,
        ]);

        $accessorialType->save();

        return redirect()->route('accessorialTypes.index')->with('success', 'Accessorial Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AccessorialType $accessorialType)
    {
        return view('accessorialTypes.show', compact('accessorialType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccessorialType $accessorialType)
    {
        return view('accessorialTypes.edit', compact('accessorialType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccessorialType $accessorialType)
    {
        Log::info('Updating Accessorial Type', [
            'accessorial_type_id' => $accessorialType->id,
            'old_accessorial_types_code' => $accessorialType->accessorial_types_code,
            'old_accessorial_types_name' => $accessorialType->accessorial_types_name
        ]);

        // Validate the request data
        $request->validate([
            'accessorial_types_code' => 'required|unique:accessorial_types,accessorial_types_code,' . $accessorialType->id,
            'accessorial_types_name' => 'required',
        ]);

        // Update the Accessorial Type details
        $accessorialType->accessorial_types_code = $request->accessorial_types_code;
        $accessorialType->accessorial_types_name = $request->accessorial_types_name;

        $accessorialType->save();

        // Log the successful update of the AccessorialType
        Log::info('Accessorial Type updated successfully', [
            'accessorial_type_id' => $accessorialType->id,
            'new_accessorial_types_code' => $accessorialType->accessorial_types_code,
            'new_accessorial_types_name' => $accessorialType->accessorial_types_name
        ]);

        return redirect()->route('accessorialTypes.index')->with('success', 'Accessorial Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccessorialType $accessorialType)
    {
        $accessorialType->delete();

        return redirect()->route('accessorialTypes.index')->with('success', 'Accessorial Type deleted successfully.');
    }
}
