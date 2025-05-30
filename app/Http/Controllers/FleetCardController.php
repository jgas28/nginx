<?php

namespace App\Http\Controllers;

use App\Models\FleetCard;
use Illuminate\Http\Request;

class FleetCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the employees table
        $fleetCards = FleetCard::when($search, function ($query, $search) {
            return $query->where('account', 'like', '%' . $search . '%')
                        ->orWhere('account_name', 'like', '%' . $search . '%')
                        ->orWhere('account_number', 'like', '%' . $search . '%');
        })
        ->paginate(5);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('fleetCards.table', compact('fleetCards'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('fleetCards.index', compact('fleetCards', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fleetCards.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'account' => 'required',
            'account_name' => 'required',
            'account_number' => 'required',
        ]);

        // Create new employee
        $fleetCard = new FleetCard([
            'account' => $request->account,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
        ]);

        $fleetCard->save();

        return redirect()->route('fleetCards.index')->with('success', 'Fleet Card created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FleetCard $fleetCard)
    {
        return view('fleetCards.show', compact('fleetCard'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FleetCard $fleetCard)
    {
        return view('fleetCards.edit', compact('fleetCard'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FleetCard $fleetCard)
    {
        // Validate the request data
        $request->validate([
            'account' => 'required',
            'account_name' => 'required',
            'account_number' => 'required',
        ]);

        // Update the employee details
        $fleetCard->account = $request->account;
        $fleetCard->account_name = $request->account_name;
        $fleetCard->account_number = $request->account_number;

        $fleetCard->save();

        return redirect()->route('fleetCards.index')->with('success', 'Fleet Card updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */ 
    public function destroy(FleetCard $fleetCard)
    {
        $fleetCard->delete();

        return redirect()->route('fleetCards.index')->with('success', 'Fleet Card deleted successfully.');
    }
}
