@extends('layouts.app')

@section('content')
{{-- <h1>Create Region</h1>

    <form action="{{ route('areas.store') }}" method="POST">
@csrf
<div class="form-group">
    <label for="area_code">Area Code</label>
    <input type="text" name="area_code" id="area_code" class="form-control" required>
</div>
<div class="form-group">
    <label for="area_name">Area Name</label>
    <input type="text" name="area_name" id="area_name" class="form-control" required>
</div>
<button type="submit" class="btn btn-primary mt-4">Create Area</button>
</form> --}}

<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <h1 class="text-3xl font-semibold text-gray-800 mb-8">Create Area</h1>

    <form action="{{ route('areas.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @csrf

        <div>
            <label for="area_code" class="block text-sm font-medium text-gray-700">Area Code</label>
            <input type="text" name="area_code" id="area_code" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="area_name" class="block text-sm font-medium text-gray-700">Area Name</label>
            <input type="text" name="area_name" id="area_name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="md:col-span-2 pt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Create Region
            </button>
        </div>
    </form>
</div>

<br><br>

@endsection
