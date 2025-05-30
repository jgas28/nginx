@extends('layouts.app')

@section('content')
{{-- <h1>Create Add-On Rate</h1>

    <form action="{{ route('addOnRates.store') }}" method="POST">
@csrf
<div class="form-group">
    <label for="add_on_rate_type_code">Add-On Rate Type Code</label>
    <input type="text" name="add_on_rate_type_code" id="add_on_rate_type_code" class="form-control" required>
</div>
<div class="form-group">
    <label for="add_on_rate_type_name">Add-On Rate Type Name</label>
    <input type="text" name="add_on_rate_type_name" id="add_on_rate_type_name" class="form-control" required>
</div>
<div class="form-group">
    <label for="rate">Rate</label>
    <input type="text" name="rate" id="rate" class="form-control" required>
</div>
<div class="form-group">
    <label for="percent_rate">Percentage Rate</label>
    <input type="text" name="percent_rate" id="percent_rate" class="form-control" required>
</div>
<div class="form-group">
    <label for="delivery_type">Delivery Type</label>
    <select name="delivery_type" id="delivery_type" class="form-control" required>
        <option value="" disabled selected>Select a Delivery Type</option>
        <option value="Regular">Regular</option>
        <option value="Multi-Drop">Multi-Drop</option>
        <option value="Multi Pick-Up">Multi Pick-Up</option>
    </select>
</div>

<button type="submit" class="btn btn-primary mt-4">Create Add-On Rate</button>
</form> --}}

<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <h1 class="text-3xl font-semibold text-gray-800 mb-8">Create Add-On Rate</h1>

    <form action="{{ route('addOnRates.store') }}" method="POST" class="space-y-6">
        @csrf

        <div>
            <label for="add_on_rate_type_code" class="block text-sm font-medium text-gray-700">Add-On Rate Type Code</label>
            <input type="text" name="add_on_rate_type_code" id="add_on_rate_type_code" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div>
            <label for="add_on_rate_type_name" class="block text-sm font-medium text-gray-700">Add-On Rate Type Name</label>
            <input type="text" name="add_on_rate_type_name" id="add_on_rate_type_name" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div>
            <label for="rate" class="block text-sm font-medium text-gray-700">Rate</label>
            <input type="text" name="rate" id="rate" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div>
            <label for="percent_rate" class="block text-sm font-medium text-gray-700">Percentage Rate</label>
            <input type="text" name="percent_rate" id="percent_rate" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div>
            <label for="delivery_type" class="block text-sm font-medium text-gray-700">Delivery Type</label>
            <select name="delivery_type" id="delivery_type" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                <option value="" disabled selected>Select a Delivery Type</option>
                <option value="Regular">Regular</option>
                <option value="Multi-Drop">Multi-Drop</option>
                <option value="Multi Pick-Up">Multi Pick-Up</option>
            </select>
        </div>

        <div>
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Create Add-On Rate
            </button>
        </div>
    </form>
</div>
<br><br>
@endsection
