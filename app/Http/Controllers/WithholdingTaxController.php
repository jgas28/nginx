<?php

namespace App\Http\Controllers;

use App\Models\WithholdingTax;
use Illuminate\Http\Request;


class WithholdingTaxController extends Controller
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
        $taxes = WithholdingTax::when($search, function ($query, $search) {
            return $query->where('description', 'like', '%' . $search . '%')
                        ->orWhere('percentage', 'like', '%' . $search . '%');
        })
        ->paginate(5);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('taxes.table', compact('taxes'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('taxes.index', compact('taxes', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('taxes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'description' => 'required',
            'percentage' => 'required',
        ]);

        // Create new employee
        $taxes = new WithholdingTax([
            'description' => $request->description,
            'percentage' => $request->percentage,
        ]);

        $taxes->save();

        return redirect()->route('taxes.index')->with('success', 'Tax created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WithholdingTax $taxes)
    {
        return view('taxes.show', compact('taxes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WithholdingTax $tax)
    {
        return view('taxes.edit', compact('tax'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WithholdingTax $tax)
    {
        // Validate the request data
        $request->validate([
            'description' => 'required',
            'percentage' => 'required',
        ]);

        // Update the employee details
        $tax->description = $request->description;
        $tax->percentage = $request->percentage;

        $tax->save();

        return redirect()->route('taxes.index')->with('success', 'Tax updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WithholdingTax $tax)
    {
        $tax->delete();

        return redirect()->route('taxes.index')->with('success', 'taxes deleted successfully.');
    }

}
