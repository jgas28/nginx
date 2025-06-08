@extends('layouts.app')

@section('content')
{{-- <h1>Edit Employee</h1>

    <form action="{{ route('employees.update', $employee) }}" method="POST">
@csrf
@method('PUT')
<div class="form-group">
    <label for="employee_code">Employee Code</label>
    <input type="text" name="employee_code" id="employee_code" class="form-control" value="{{ $employee->employee_code }}" required>
</div>
<div class="form-group">
    <label for="first_name">First Name</label>
    <input type="text" name="first_name" id="" class="form-control" value="{{ $employee->fname }}" required>
</div>
<div class="form-group">
    <label for="last_name">Last Name</label>
    <input type="text" name="last_name" id="last_name" class="form-control" value="{{ $employee->lname }}" required>
</div>
<div class="form-group">
    <label for="position">Position</label>
    <input type="text" name="position" id="position" class="form-control" value="{{ $employee->position }}" required>
</div>
<div class="form-group">
    <label for="password">New Password (Optional)</label>
    <input type="password" name="password" id="password" class="form-control">
</div>
<div class="form-group">
    <label for="password_confirmation">Confirm Password</label>
    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
</div>
<button type="submit" class="btn btn-primary mt-4">Update Employee</button>
</form> --}}

<div class="max-w-5xl mx-auto bg-white p-10 rounded-2xl shadow-md mt-10">
    <h2 class="text-3xl font-semibold mb-8 text-gray-800">Update Employee</h2>
    <form action="{{ route('employees.update', $employee) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @csrf
        @method('PUT')

        <div>
            <label for="employee_code" class="block text-sm font-medium text-gray-700">Employee Code</label>
            <input type="text" name="employee_code" id="employee_code" required value="{{ $employee->employee_code }}" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
            <input type="text" name="first_name" id="first_name" required value="{{ $employee->fname }}" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
            <input type="text" name="last_name" id="last_name" required value="{{ $employee->lname }}" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
            <input type="text" name="position" id="position" required value="{{ $employee->position }}" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="role_id" class="block text-sm font-medium text-gray-700">Position</label>
            <select name="role_id" id="role_id" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="" disabled {{ old('role_id', $employee->role_id ?? '') === '' ? 'selected' : '' }}>Select Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id', $employee->role_id ?? '') == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">New Password (Optional)</label>
            <input type="password" name="password" id="password" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="md:col-span-2 pt-4">
            <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300">
                Update Employee
            </button>
        </div>
    </form>
</div>
<br><br>

@endsection
