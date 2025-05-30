@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <form action="{{ route('trucksTypes.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @csrf

        <div>
            <label for="truck_code" class="block text-sm font-medium text-gray-700">Truck Code</label>
            <input type="text" name="truck_code" id="truck_code" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="truck_type" class="block text-sm font-medium text-gray-700">Truck Type</label>
            <input type="text" name="truck_type" id="truck_type" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="md:col-span-2 pt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Create Truck
            </button>
        </div>
    </form>
</div>



<br><br>
@endsection
