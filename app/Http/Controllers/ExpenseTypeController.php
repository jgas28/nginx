<?php

namespace App\Http\Controllers;

use App\Models\Expense_Type;
use App\Models\DeliveryRequestType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{
    //
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the regions table
        $expenseTypes = Expense_Type::with('deliveryRequestType') // Eager load relationship
        ->when($search, function ($query, $search) {
            return $query->where('expense_code', 'like', '%' . $search . '%')
                         ->orWhere('expense_name', 'like', '%' . $search . '%');
        })
        ->orderBy('expense_code')
        ->paginate(10);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('expenseTypes.table', compact('expenseTypes'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('expenseTypes.index', compact('expenseTypes', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $deliveryRequestTypes = DeliveryRequestType::all();
        return view('expenseTypes.create', compact('deliveryRequestTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'expense_code' => 'required',
            'expense_name' => 'required',
            'type' => 'required',
        ]);

        // Create new region
        $region = new Expense_Type([
            'expense_code' => $request->expense_code,
            'expense_name' => $request->expense_name,
            'type' => $request->type,
        ]);

        $region->save();

        return redirect()->route('expenseTypes.index')->with('success', 'Expense Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense_Type $expenseType)
    {
        return view('expenseTypes.show', compact('expenseType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense_Type $expenseType)
    {
        $deliveryRequestTypes = DeliveryRequestType::all();
        return view('expenseTypes.edit', compact('expenseType', 'deliveryRequestTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense_Type $expenseType)
    {
        // Validate the request data
        $request->validate([
            'expense_code' => 'required',
            'expense_name' => 'required',
            'type' => 'required',
        ]);

        // Update the region details
        $expenseType->expense_code = $request->expense_code;
        $expenseType->expense_name = $request->expense_name;
        $expenseType->type = $request->type;
        $expenseType->save();

        return redirect()->route('expenseTypes.index')->with('success', 'Expense Type updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense_Type $expenseType)
    {
        $expenseType->delete();

        return redirect()->route('expenseTypes.index')->with('success', 'Expense Type deleted successfully.');
    }
}
 