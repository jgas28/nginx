@extends('layouts.app')

@section('content')
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
            <label for="password" class="block text-sm font-medium text-gray-700">New Password (Optional)</label>
            <input type="password" name="password" id="password" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Roles</label>
            <div class="grid grid-cols-3 gap-4 max-h-48 overflow-y-auto border border-gray-300 rounded-xl p-4">
                @foreach($roles as $role)
                    <label class="inline-flex items-center space-x-2">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                            {{ in_array($role->id, old('roles', $employee->roles->pluck('id')->toArray())) ? 'checked' : '' }}
                            class="form-checkbox h-5 w-5 text-indigo-600 rounded">
                        <span class="text-gray-700">{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
            <p class="text-sm text-gray-500 mt-1">Select one or more roles for the employee.</p>
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
