@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto bg-white p-12 rounded-2xl shadow-md mt-10">
    <h2 class="text-3xl font-semibold mb-10 text-gray-800">Create New WithholdingTax</h2>
    <form action="{{ route('taxes.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @csrf

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <input type="text" name="description" id="description" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="percentage" class="block text-sm font-medium text-gray-700">Percentage</label>
            <input type="text" name="percentage" id="percentage" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="md:col-span-2 pt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Create Tax
            </button>
        </div>
    </form>
</div>
<br><br>
@endsection
