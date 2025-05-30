@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <h1 class="text-3xl font-semibold text-gray-800 mb-8">Edit Suppliers</h1>

    <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @csrf
        @method('PUT')

        <div>
            <label for="supplier_code" class="block text-sm font-medium text-gray-700">Supplier Code</label>
            <input type="text" name="supplier_code" id="supplier_code" value="{{ $supplier->supplier_code }}" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="supplier_name" class="block text-sm font-medium text-gray-700">Supplier Name</label>
            <input type="text" name="supplier_name" id="supplier_name" value="{{ $supplier->supplier_name }}" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="md:col-span-2 pt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Update Supplier
            </button>
        </div>
    </form>
</div>
<br><br>
@endsection
