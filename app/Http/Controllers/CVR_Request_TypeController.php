<?php

namespace App\Http\Controllers;

use App\Models\cvr_request_type;
use Illuminate\Http\Request;

class CVR_Request_TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the cvr_request_types table
        $cvr_request_types = cvr_request_type::when($search, function ($query, $search) {
            return $query->where('request_code', 'like', '%' . $search . '%')
                         ->orWhere('request_type', 'like', '%' . $search . '%')
                         ->orWhere('group_type', 'like', '%' . $search . '%');
        })
        ->paginate(5);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('cvr_request_types.table', compact('cvr_request_types'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('cvr_request_types.index', compact('cvr_request_types', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('cvr_request_types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'request_code' => 'required|unique:cvr_request_type,request_code',
            'request_type' => 'required',
            'group_type' => 'required',
        ]);

        // Create new Request Type
        $cvr_request_type = new cvr_request_type([
            'request_code' => $request->request_code,
            'request_type' => $request->request_type,
            'group_type' => $request->group_type,
        ]);

        $cvr_request_type->save();

        return redirect()->route('cvr_request_types.index')->with('success', 'Request Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(cvr_request_type $cvr_request_type)
    {
        return view('cvr_request_type.show', compact('cvr_request_type'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(cvr_request_type $cvr_request_type)
    {
        return view('cvr_request_types.edit', compact('cvr_request_type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, cvr_request_type $cvr_request_type)
    {
        // Validate the request data
        $request->validate([
            'request_code' => 'required|unique:cvr_request_type,request_code,' . $cvr_request_type->id,
            'request_type' => 'required',
            'group_type' => 'required',
        ]);

        // Update the Request Type details
        $cvr_request_type->request_code = $request->request_code;
        $cvr_request_type->request_type = $request->request_type;
        $cvr_request_type->group_type = $request->group_type;
        $cvr_request_type->save();

        return redirect()->route('cvr_request_types.index')->with('success', 'Request Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(cvr_request_type $cvr_request_type)
    {
        $cvr_request_type->delete();

        return redirect()->route('cvr_request_types.index')->with('success', 'Request Type deleted successfully.');
    }
}
