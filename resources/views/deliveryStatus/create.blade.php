@extends('layouts.app')

@section('content')
{{-- <h1>Create Delivery Status</h1>

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

<form action="{{ route('deliveryStatus.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="status_code">Status Code</label>
        <input type="text" name="status_code" id="status_code" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="status_name">Status Name</label>
        <input type="text" name="status_name" id="status_name" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary mt-4">Create Delivery Status</button>
</form>
--}}

<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <h1 class="text-3xl font-semibold text-gray-800 mb-8">Create Delivery Status</h1>

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

    <form action="{{ route('deliveryStatus.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @csrf

        <div>
            <label for="status_code" class="block text-sm font-medium text-gray-700">Status Code</label>
            <input type="text" name="status_code" id="status_code" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="status_name" class="block text-sm font-medium text-gray-700">Status Name</label>
            <input type="text" name="status_name" id="status_name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="md:col-span-2 pt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Create Delivery Status
            </button>
        </div>
    </form>
</div>

<br><br>
@endsection
