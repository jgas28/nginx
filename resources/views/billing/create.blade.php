@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h2 class="text-3xl font-semibold mb-6 text-gray-800">Create Billing</h2>

    <form action="{{ route('billing.store') }}" method="POST">
        @csrf

        <!-- SOA Number -->
        <div class="mb-6">
            <label for="soa_number" class="block text-sm font-medium text-gray-700">SOA Number</label>
            <input type="text" name="soa_number" id="soa_number" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('soa_number') }}" required>
        </div>

        <!-- Company Dropdown -->
        <div class="mb-6">
            <label for="company_id" class="block text-sm font-medium text-gray-700">Company</label>
            <select name="company_id" id="company_id" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                <option value="">Select Company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Withholding Tax Dropdown -->
        <div class="mb-6">
            <label for="withholding_tax_id" class="block text-sm font-medium text-gray-700">Withholding Tax</label>
            <select name="withholding_tax_id" id="withholding_tax_id" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                <option value="">Select Withholding Tax</option>
                @foreach($withholdingTaxes as $tax)
                    <option value="{{ $tax->id }}" {{ old('withholding_tax_id') == $tax->id ? 'selected' : '' }}>
                        {{ $tax->name }} ({{ $tax->rate }}%)
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Billed To -->
        <div class="mb-6">
            <label for="billed_to" class="block text-sm font-medium text-gray-700">Billed To</label>
            <input type="text" name="billed_to" id="billed_to" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('billed_to') }}" required>
        </div>

        <!-- Billing Address -->
        <div class="mb-6">
            <label for="billing_address" class="block text-sm font-medium text-gray-700">Billing Address</label>
            <textarea name="billing_address" id="billing_address" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>{{ old('billing_address') }}</textarea>
        </div>

        <!-- Billing Date -->
        <div class="mb-6">
            <label for="billing_date" class="block text-sm font-medium text-gray-700">Billing Date</label>
            <input type="date" name="billing_date" id="billing_date" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('billing_date') }}" required>
        </div>

        <!-- Delivery Requests Selection (MTMs) -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700">Select Delivery Requests (MTMs)</label>
            <div class="space-y-2 max-h-64 overflow-auto p-2 border rounded-lg border-gray-300">
                @foreach($deliveryRequests as $dr)
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="delivery_requests[]" value="{{ $dr->id }}" class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" {{ in_array($dr->id, old('delivery_requests', [])) ? 'checked' : '' }}>
                        <label class="text-sm text-gray-700">{{ $dr->mtm }} - {{ $dr->project_name }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Delivery Request Line Items Selection -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700">Select Line Items</label>
            <div class="space-y-2 max-h-64 overflow-auto p-2 border rounded-lg border-gray-300">
                @foreach($lineItems as $lineItem)
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="line_items[]" value="{{ $lineItem->id }}" class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" {{ in_array($lineItem->id, old('line_items', [])) ? 'checked' : '' }}>
                        <label class="text-sm text-gray-700">{{ $lineItem->delivery_number }} - {{ $lineItem->site_name }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mb-4">
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
                Create Billing
            </button>
        </div>
    </form>
</div>
@endsection
