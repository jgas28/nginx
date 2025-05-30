@extends('layouts.app')

@section('content')
{{-- <h1>Create Warehouse</h1>

    <form action="{{ route('warehouses.store') }}" method="POST">
@csrf
<div class="form-group">
    <label for="warehouse_code">Warehouse Code</label>
    <input type="text" name="warehouse_code" id="warehouse_code" class="form-control" required>
</div>
<div class="form-group">
    <label for="warehouse_name">Warehouse Name</label>
    <input type="text" name="warehouse_name" id="warehouse_name" class="form-control" required>
</div>
<div class="form-group">
    <label for="warehouse_location">Warehouse Location</label>
    <input type="text" name="warehouse_location" id="warehouse_location" class="form-control" required>
</div>

<button type="submit" class="btn btn-primary mt-4">Create Warehouse</button>
</form> --}}
<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <h1 class="text-3xl font-semibold text-gray-800 mb-8">Create Warehouse</h1>

    <form action="{{ route('warehouses.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @csrf

        <div>
            <label for="warehouse_code" class="block text-sm font-medium text-gray-700">Warehouse Code</label>
            <input type="text" name="warehouse_code" id="warehouse_code" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="warehouse_name" class="block text-sm font-medium text-gray-700">Warehouse Name</label>
            <input type="text" name="warehouse_name" id="warehouse_name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="md:col-span-2">
            <label for="warehouse_location" class="block text-sm font-medium text-gray-700">Warehouse Location</label>
            <input type="text" name="warehouse_location" id="warehouse_location" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="md:col-span-2 pt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Create Warehouse
            </button>
        </div>
    </form>
</div>

<br><br>
@endsection
