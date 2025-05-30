@extends('layouts.app')

@section('content')
{{-- <h1>Create Delivery Type</h1>

    <!-- Display validation errors if any -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<form action="{{ route('deliveryTypes.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="delivery_type_code">Delivery Type Code</label>
        <input type="text" name="delivery_type_code" id="delivery_type_code" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="delivery_type_name">Delivery Type Name</label>
        <input type="text" name="delivery_type_name" id="delivery_type_name" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary mt-4">Create Delivery Type</button>
</form> --}}

<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <h1 class="text-3xl font-semibold text-gray-800 mb-8">Create Delivery Type</h1>

    <!-- Display validation errors if any -->
    @if ($errors->any())
    <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('deliveryTypes.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @csrf

        <div>
            <label for="delivery_type_code" class="block text-sm font-medium text-gray-700">Delivery Type Code</label>
            <input type="text" name="delivery_type_code" id="delivery_type_code" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="delivery_type_name" class="block text-sm font-medium text-gray-700">Delivery Type Name</label>
            <input type="text" name="delivery_type_name" id="delivery_type_name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="md:col-span-2 pt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Create Delivery Type
            </button>
        </div>
    </form>
</div>
<br><br>
@endsection
