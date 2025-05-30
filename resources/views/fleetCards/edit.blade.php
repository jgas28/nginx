@extends('layouts.app')

{{-- @section('content')
    <h1>Edit Fleet Card</h1>

    <form action="{{ route('fleetCards.update', $fleetCard) }}" method="POST">
@csrf
@method('PUT')
<div class="form-group">
    <label for="account">Account</label>
    <input type="text" name="account" id="account" class="form-control" value="{{ $fleetCard->account }}" required>
</div>
<div class="form-group">
    <label for="account_name">Account Name</label>
    <input type="text" name="account_name" id="account_name" class="form-control" value="{{ $fleetCard->account_name }}" required>
</div>
<div class="form-group">
    <label for="account_number">Account Number</label>
    <input type="text" name="account_number" id="account_number" class="form-control" value="{{ $fleetCard->account_number }}" required>
</div>
<button type="submit" class="btn btn-primary mt-4">Update Fleet Card</button>
</form>
@endsection --}}


@section('content')
<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <h1 class="text-3xl font-semibold text-gray-800 mb-8">Edit Fleet Card</h1>

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

    <form action="{{ route('fleetCards.update', $fleetCard) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="account" class="block text-sm font-medium text-gray-700">Account</label>
            <input type="text" name="account" id="account" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ $fleetCard->account }}" required>
        </div>

        <div>
            <label for="account_name" class="block text-sm font-medium text-gray-700">Account Name</label>
            <input type="text" name="account_name" id="account_name" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ $fleetCard->account_name }}" required>
        </div>

        <div>
            <label for="account_number" class="block text-sm font-medium text-gray-700">Account Number</label>
            <input type="text" name="account_number" id="account_number" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ $fleetCard->account_number }}" required>
        </div>

        <div>
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Update Fleet Card
            </button>
        </div>
    </form>
</div>
<br><br>
@endsection
