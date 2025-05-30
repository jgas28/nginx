@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <form action="{{ route('companies.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @csrf

        <div>
            <label for="company_code" class="block text-sm font-medium text-gray-700">Company Code</label>
            <input type="text" name="company_code" id="company_code" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name</label>
            <input type="text" name="company_name" id="company_name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="md:col-span-2">
            <label for="company_location" class="block text-sm font-medium text-gray-700">Company Location</label>
            <input type="text" name="company_location" id="company_location" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="md:col-span-2 pt-4">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Create
            </button>
        </div>
    </form>
</div>
<br><br>
@endsection
