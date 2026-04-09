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
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        $taxes = WithholdingTax::when($search, function ($query, $search) {
            return $query->where('description', 'like', '%' . $search . '%')
                        ->orWhere('percentage', 'like', '%' . $search . '%');
        })
        ->orderBy('description')
        ->paginate($perPage)
        ->appends($request->query());

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json([
                'html' => view('taxes.table', compact('taxes', 'search', 'perPage'))->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $taxes->total(),
            ]);
        }

        return view('taxes.index', compact('taxes', 'search', 'perPage'));
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

        return redirect()->route('taxes.index')->with('success', 'Tax deleted successfully.');
    }

}
