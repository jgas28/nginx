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
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        $companies = Company::when($search, function ($query, $search) {
            return $query->where('company_code', 'like', '%' . $search . '%')
                        ->orWhere('company_name', 'like', '%' . $search . '%')
                        ->orWhere('company_location', 'like', '%' . $search . '%')
                        ->orWhere('tin_no', 'like', '%' . $search . '%');
        })
        ->orderBy('company_name')
        ->paginate($perPage)
        ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('companies.table', compact('companies', 'search', 'perPage'))->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $companies->total(),
            ]);
        }

        return view('companies.index', compact('companies', 'search', 'perPage'));
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
            'tin_no' => 'nullable|string|max:50',
        ]);

        // Create new company
        $company = new Company([
            'company_code' => $request->company_code,
            'company_name' => $request->company_name,
            'company_location' => $request->company_location,
            'tin_no' => $request->tin_no,
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
            'tin_no' => 'nullable|string|max:50',
        ]);

        // Update the company details
        $company->company_code = $request->company_code;
        $company->company_name = $request->company_name;
        $company->company_location = $request->company_location;
        $company->tin_no = $request->tin_no;

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
