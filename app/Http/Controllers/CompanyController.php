<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\MonthlySeriesNumber;
use Carbon\Carbon; 
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the companies table
        $companies = Company::when($search, function ($query, $search) {
            return $query->where('company_code', 'like', '%' . $search . '%')
                        ->orWhere('company_name', 'like', '%' . $search . '%')
                        ->orWhere('company_location', 'like', '%' . $search . '%');
        })
        ->paginate(5);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('companies.table', compact('companies'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('companies.index', compact('companies', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'company_code' => 'required|unique:companies,company_code',
            'company_name' => 'required',
            'company_location' => 'required',
        ]);

        // Create new company
        $company = new Company([
            'company_code' => $request->company_code,
            'company_name' => $request->company_name,
            'company_location' => $request->company_location,
        ]);

        $company->save();

        MonthlySeriesNumber::create([
            'company_id'    => $company->id,
            'month'         => Carbon::now()->format('Y-m'), // format: YYYY-MM
            'series_number' => 0, // optional if default is set
        ]);

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        return view('companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        // Validate the request data
        $request->validate([
            'company_code' => 'required|unique:companies,company_code,' . $company->id,
            'company_name' => 'required',
            'company_location' => 'required',
        ]);

        // Update the company details
        $company->company_code = $request->company_code;
        $company->company_name = $request->company_name;
        $company->company_location = $request->company_location;

        $company->save();

        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        // Delete related series via relationship
        $company->monthlySeriesNumbers()->delete();
    
        // Delete the company itself
        $company->delete();
    
        return redirect()->route('companies.index')->with('success', 'Company deleted successfully.');
    }
}
