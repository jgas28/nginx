@extends('layouts.app')

{{-- @section('content')
    <h1>Edit Accesorial Type</h1>

    <form action="{{ route('accessorialTypes.update', $accessorialType) }}" method="POST">
@csrf
@method('PUT')
<div class="form-group">
    <label for="accessorial_types_code">Accesorial Type Code</label>
    <input type="text" name="accessorial_types_code" id="accessorial_types_code" class="form-control" value="{{ $accessorialType->accessorial_types_code }}" required>
</div>
<div class="form-group">
    <label for="accessorial_types_name">Accesorial Type Name</label>
    <input type="text" name="accessorial_types_name" id="accessorial_types_name" class="form-control" value="{{ $accessorialType->accessorial_types_name }}" required>
</div>

<button type="submit" class="btn btn-primary mt-4">Update Accesorial Type</button>
</form>
@endsection --}}
@section('content')
<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <h1 class="text-3xl font-semibold text-gray-800 mb-8">Edit Accessorial Type</h1>

    <!-- Display validation errors if any -->
    @if ($errors->any())
    <div class="alert alert-danger mb-6">
        <ul>
            @foreach ($errors->all() as $error)
            <li class="text-red-600">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('accessorialTypes.update', $accessorialType) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="accessorial_types_code" class="block text-sm font-medium text-gray-700">Accessorial Type Code</label>
            <input type="text" name="accessorial_types_code" id="accessorial_types_code" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ $accessorialType->accessorial_types_code }}" required>
        </div>

        <div>
            <label for="accessorial_types_name" class="block text-sm font-medium text-gray-700">Accessorial Type Name</label>
            <input type="text" name="accessorial_types_name" id="accessorial_types_name" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ $accessorialType->accessorial_types_name }}" required>
        </div>

        <div>
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Update Accessorial Type
            </button>
        </div>
    </form>
</div>
@endsection
