@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <form action="{{ route('accessorialTypes.store') }}" method="POST" class="space-y-6">
        @csrf

        <div>
            <label for="accessorial_types_code" class="block text-sm font-medium text-gray-700">Accessorial Type Code</label>
            <input type="text" name="accessorial_types_code" id="accessorial_types_code" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div>
            <label for="accessorial_types_name" class="block text-sm font-medium text-gray-700">Accessorial Type Name</label>
            <input type="text" name="accessorial_types_name" id="accessorial_types_name" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div>
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Create Accessorial Type
            </button>
        </div>
    </form>
</div>
<br><br>
@endsection
