@extends('layouts.app')

@section('content')
{{-- <h1>Create Employee</h1> --}}

{{-- <div class="max-w-xl mx-auto bg-white p-12 rounded-2xl shadow-md mt-10">
    <h2 class="text-2xl font-semibold mb-6 text-gray-800">Create New Employee</h2>
    <form action="{{ route('employees.store') }}" method="POST" class="space-y-5">
@csrf
<div>
    <label for="employee_code" class="block text-sm font-medium text-gray-700">Employee Code</label>
    <input type="text" name="employee_code" id="employee_code" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
</div>
<div>
    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
    <input type="text" name="first_name" id="first_name" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
</div>
<div>
    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
    <input type="text" name="last_name" id="last_name" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
</div>
<div>
    <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
    <input type="text" name="position" id="position" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
</div>
<div>
    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
    <input type="password" name="password" id="password" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
</div>
<div>
    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
    <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
</div>
<div class="pt-4">
    <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-xl hover:bg-blue-700 transition duration-300">
        Create Employee
    </button>
</div>
</form>
</div> --}}
<div class="max-w-6xl mx-auto bg-white p-12 rounded-2xl shadow-md mt-10">
    <h2 class="text-3xl font-semibold mb-10 text-gray-800">Create New Employee</h2>
    <form action="{{ route('employees.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @csrf

        <div>
            <label for="employee_code" class="block text-sm font-medium text-gray-700">Employee Code</label>
            <input type="text" name="employee_code" id="employee_code" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
            <input type="text" name="first_name" id="first_name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
            <input type="text" name="last_name" id="last_name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
            <input type="text" name="position" id="position" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="role_id" class="block text-sm font-medium text-gray-700">Position</label>
            <select name="role_id" id="role_id" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="" disabled selected>Select Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" id="password" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="md:col-span-2 pt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Create Employee
            </button>
        </div>
    </form>
</div>
<br><br>
@endsection
