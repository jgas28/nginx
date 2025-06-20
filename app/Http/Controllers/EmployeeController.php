<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the employees table
        $employees = User::when($search, function ($query, $search) {
            return $query->where('employee_code', 'like', '%' . $search . '%')
                        ->orWhere('fname', 'like', '%' . $search . '%')
                        ->orWhere('lname', 'like', '%' . $search . '%')
                        ->orWhere('position', 'like', '%' . $search . '%');
        })
        ->paginate(5);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('employees.table', compact('employees'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('employees.index', compact('employees', 'search'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles=Role::all();
        return view('employees.create', compact('roles'));
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
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'password' => 'required|min:6|confirmed',
        ]);

        $employee = User::create([
            'employee_code' => $request->employee_code,
            'fname' => $request->first_name,
            'lname' => $request->last_name,
            'position' => $request->position,
            'email' => $request->email, // if you use email
            'password' => Hash::make($request->password),
        ]);

        // Attach selected roles
        $employee->roles()->attach($request->roles);

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
        $roles=Role::all();
        return view('employees.edit', compact('employee','roles'));
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
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $employee->employee_code = $request->employee_code;
        $employee->fname = $request->first_name;
        $employee->lname = $request->last_name;
        $employee->position = $request->position;

        if ($request->filled('password')) {
            $employee->password = Hash::make($request->password);
        }

        $employee->save();

        // Sync roles (replaces old ones with the new selection) 
        $employee->roles()->sync($request->roles);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}
