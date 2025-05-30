@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto bg-white p-10 rounded-2xl shadow-md mt-10">
    <h2 class="text-3xl font-semibold mb-8 text-gray-800">Update Tax</h2>
    <form action="{{ route('taxes.update', $tax) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @csrf
        @method('PUT')

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <input type="text" name="description" id="description" required value="{{ $tax->description }}" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="percentage" class="block text-sm font-medium text-gray-700">Percentage</label>
            <input type="text" name="percentage" id="percentage" required value="{{ $tax->percentage }}" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="md:col-span-2 pt-4">
            <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300">
                Update Tax
            </button>
        </div>
    </form>
</div>
<br><br>

@endsection
