@extends('layouts.app')

@section('content')
{{-- <h1>Create Region</h1>

    <form action="{{ route('regions.store') }}" method="POST">
@csrf
<div class="form-group">
    <label for="region_code">Region Code</label>
    <input type="text" name="region_code" id="region_code" class="form-control" required>
</div>
<div class="form-group">
    <label for="region_name">Region Name</label>
    <input type="text" name="region_name" id="region_name" class="form-control" required>
</div>
<div class="form-group">
    <label for="province">Province</label>
    <input type="text" name="province" id="province" class="form-control" required>
</div>
<div class="form-group">
    <label for="area_id">Delivery Status</label>
    <select name="area_id" id="area_id" class="form-control">
        @foreach($areas as $area)
            <option value="{{ $area->id }}">{{ $area->area_code }}</option>
        @endforeach
    </select>
    @error('area_id')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
</div>
<button type="submit" class="btn btn-primary mt-4">Create Region</button>
</form> --}}

<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <h1 class="text-3xl font-semibold text-gray-800 mb-8">Create Region</h1>

    <form action="{{ route('regions.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @csrf

        <div>
            <label for="region_code" class="block text-sm font-medium text-gray-700">Region Code</label>
            <input type="text" name="region_code" id="region_code" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="region_name" class="block text-sm font-medium text-gray-700">Region Name</label>
            <input type="text" name="region_name" id="region_name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="form-group">
            <label for="province" class="block text-sm font-medium text-gray-700">Province</label>
            <input type="text" name="province" id="province" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="form-group">
            <label for="area_id">Area</label>
            <select name="area_id" id="area_id" class="form-control">
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->area_code }}</option>
                @endforeach
            </select>
            @error('area_id')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
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
