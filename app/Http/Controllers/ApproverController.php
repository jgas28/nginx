<?php

namespace App\Http\Controllers;

use App\Models\Approver;
use Illuminate\Http\Request;

class ApproverController extends Controller
{
    //
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the employees table
        $approvers = Approver::when($search, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('site', 'like', '%' . $search . '%');
        })
        ->paginate(5);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('approvers.table', compact('approvers'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('approvers.index', compact('approvers', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('approvers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required',
            'site' => 'required',
        ]);

        // Create new employee
        $approver = new Approver([
            'name' => $request->name,
            'site' => $request->site,
        ]);

        $approver->save();

        return redirect()->route('approvers.index')->with('success', 'Approver created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Approver $approver)
    {
        return view('approvers.show', compact('approver'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Approver $approver)
    {
        return view('approvers.edit', compact('approver'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Approver $approver)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required',
            'site' => 'required',
        ]);

        // Update the employee details
        $approver->name = $request->name;
        $approver->site = $request->site;

        $approver->save();

        return redirect()->route('approvers.index')->with('success', 'Approver updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Approver $approver)
    {
        $approver->delete();

        return redirect()->route('approvers.index')->with('success', 'Approver deleted successfully.');
    }
}
