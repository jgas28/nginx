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
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        $fleetCards = FleetCard::when($search, function ($query, $search) {
            return $query->where('account', 'like', '%' . $search . '%')
                        ->orWhere('account_name', 'like', '%' . $search . '%')
                        ->orWhere('account_number', 'like', '%' . $search . '%');
        })
        ->orderBy('account_name')
        ->paginate($perPage)
        ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('fleetCards.table', compact('fleetCards', 'search', 'perPage'))->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $fleetCards->total(),
            ]);
        }

        return view('fleetCards.index', compact('fleetCards', 'search', 'perPage'));
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
            'status' => $request->status,
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
            'status' => 'required',
        ]);

        // Update the employee details
        $fleetCard->account = $request->account;
        $fleetCard->account_name = $request->account_name;
        $fleetCard->account_number = $request->account_number;
        $fleetCard->status = $request->status;

        $fleetCard->save();

        return redirect()->route('fleetCards.index')->with('success', 'Fleet Card updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */ 
    public function destroy(FleetCard $fleetCard)
    {
        $fleetCard->update([
            'status' => 0,
        ]);

        return redirect()
            ->route('fleetCards.index')
            ->with('success', 'Fleet Card deactivated successfully.');
    }

}
