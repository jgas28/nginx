<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        $employees = User::with('roles')->when($search, function ($query, $search) {
            return $query->where('employee_code', 'like', '%' . $search . '%')
                        ->orWhere('fname', 'like', '%' . $search . '%')
                        ->orWhere('lname', 'like', '%' . $search . '%')
                        ->orWhere('position', 'like', '%' . $search . '%');
        })
        ->orderBy('fname')
        ->orderBy('lname')
        ->paginate($perPage)
        ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('employees.table', compact('employees', 'search', 'perPage'))->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $employees->total(),
            ]);
        }

        return view('employees.index', compact('employees', 'search', 'perPage'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $companies = Company::orderBy('company_name')->get();
        return view('employees.create', compact('roles', 'companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_code' => 'required|unique:users,employee_code',
            'first_name' => 'required',
            'last_name' => 'required',
            'position' => 'required',
            'roles' => 'nullable|array',
            'roles.*' => 'nullable|exists:roles,id',
            'password' => 'required|min:6|confirmed',
            'employment_status' => 'required',
            'daily_rate' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'sss_no' => 'nullable|string|max:50',
            'philhealth_no' => 'nullable|string|max:50',
            'tin_no' => 'nullable|string|max:50',
        ]);

        $employee = User::create([
            'employee_code' => $request->employee_code,
            'fname' => $request->first_name,
            'lname' => $request->last_name,
            'position' => $request->position,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 1,
            'employment_status' => $request->employment_status,
            'daily_rate' => $request->daily_rate ?? 0,
            'monthly_salary' => $request->monthly_salary ?? 0,
            'sss_no' => $request->sss_no,
            'philhealth_no' => $request->philhealth_no,
            'tin_no' => $request->tin_no,
            'company_id' => $request->company_id ?? null,
        ]);

        // Attach selected roles
        $employee->roles()->sync($request->input('roles', []));

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $employee)
    {
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $employee)
    {
        $roles = Role::all();
        $companies = Company::orderBy('company_name')->get();
        return view('employees.edit', compact('employee', 'roles', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $employee)
    {
        $request->validate([
            'employee_code' => 'required|unique:users,employee_code,' . $employee->id,
            'first_name' => 'required',
            'last_name' => 'required',
            'position' => 'required',
            'employment_status' => 'required',
            'roles' => 'nullable|array',
            'roles.*' => 'nullable|exists:roles,id',
            'password' => 'nullable|min:6|confirmed',
            'status' => 'required',
            'daily_rate' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'sss_no' => 'nullable|string|max:50',
            'philhealth_no' => 'nullable|string|max:50',
            'tin_no' => 'nullable|string|max:50',
        ]);

        $employee->employee_code = $request->employee_code;
        $employee->fname = $request->first_name;
        $employee->lname = $request->last_name;
        $employee->position = $request->position;
        $employee->status = $request->status;
        $employee->employment_status = $request->employment_status;
        $employee->daily_rate = $request->daily_rate ?? 0;
        $employee->monthly_salary = $request->monthly_salary ?? 0;
        $employee->sss_no = $request->sss_no;
        $employee->philhealth_no = $request->philhealth_no;
        $employee->tin_no = $request->tin_no;
        $employee->company_id = $request->company_id ?? null;

        if ($request->filled('password')) {
            $employee->password = Hash::make($request->password);
        }

        $employee->save();

        // Sync roles (replaces old ones with the new selection) 
        $employee->roles()->sync($request->input('roles', []));

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $employee)
    {
        $employee->status = 0; // Set the status to 0
        $employee->save();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}
