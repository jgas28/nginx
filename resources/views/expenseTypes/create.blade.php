@extends('layouts.app')

@section('content')
{{-- <h1>Create Region</h1>

    <form action="{{ route('expenseTypes.store') }}" method="POST">
@csrf
<div class="form-group">
    <label for="expense_code">Expense Code</label>
    <input type="text" name="expense_code" id="expense_code" class="form-control" required>
</div>
<div class="form-group">
    <label for="expense_name">Expense Name</label>
    <input type="expense_name" name="expense_name" id="area_name" class="form-control" required>
</div>
<button type="submit" class="btn btn-primary mt-4">Create Expense Type</button>
</form> --}}

<div class="max-w-4xl mx-auto bg-white p-10 mt-10 rounded-2xl shadow-lg">
    <h1 class="text-3xl font-semibold text-gray-800 mb-8">Create Expense Type</h1>

    <form action="{{ route('expenseTypes.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @csrf

        <div>
            <label for="expense_code" class="block text-sm font-medium text-gray-700">Expense Code</label>
            <input type="text" name="expense_code" id="expense_code" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="expense_name" class="block text-sm font-medium text-gray-700">Expense Name</label>
            <input type="text" name="expense_name" id="expense_name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
    <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
    <select name="type" id="type" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        <option value="" disabled selected>Select Delivery Request Type</option>
        @foreach ($deliveryRequestTypes as $deliveryRequestType)
            <option value="{{ $deliveryRequestType->id }}" {{ old('type') == $deliveryRequestType->id ? 'selected' : '' }}>
                {{ $deliveryRequestType->description }}
            </option>
        @endforeach
    </select>
</div>

        <div class="md:col-span-2 pt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Create Expense Type
            </button>
        </div>
    </form>
</div>

<br><br>

@endsection
