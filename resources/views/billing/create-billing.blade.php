@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Billing</h2>

    <form action="{{ route('billing.store') }}" method="POST">
        @csrf

        <!-- SOA Number -->
        <div class="form-group">
            <label for="soa_number">SOA Number</label>
            <input type="text" name="soa_number" id="soa_number" class="form-control" value="{{ old('soa_number') }}" required>
        </div>

        <!-- Company Dropdown -->
        <div class="form-group">
            <label for="company_id">Company</label>
            <select name="company_id" id="company_id" class="form-control" required>
                <option value="">Select Company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Withholding Tax Dropdown -->
        <div class="form-group">
            <label for="withholding_tax_id">Withholding Tax</label>
            <select name="withholding_tax_id" id="withholding_tax_id" class="form-control" required>
                <option value="">Select Withholding Tax</option>
                @foreach($withholdingTaxes as $tax)
                    <option value="{{ $tax->id }}" {{ old('withholding_tax_id') == $tax->id ? 'selected' : '' }}>
                        {{ $tax->name }} ({{ $tax->rate }}%)
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Billed To -->
        <div class="form-group">
            <label for="billed_to">Billed To</label>
            <input type="text" name="billed_to" id="billed_to" class="form-control" value="{{ old('billed_to') }}" required>
        </div>

        <!-- Billing Address -->
        <div class="form-group">
            <label for="billing_address">Billing Address</label>
            <textarea name="billing_address" id="billing_address" class="form-control" required>{{ old('billing_address') }}</textarea>
        </div>

        <!-- Billing Date -->
        <div class="form-group">
            <label for="billing_date">Billing Date</label>
            <input type="date" name="billing_date" id="billing_date" class="form-control" value="{{ old('billing_date') }}" required>
        </div>

        <!-- Display Selected Delivery Requests -->
        <div class="form-group">
            <label for="delivery_requests">Selected Delivery Requests (MTMs)</label>
            <ul>
                @foreach($deliveryRequests as $dr)
                    <li>{{ $dr->mtm }} - {{ $dr->project_name }}</li>
                @endforeach
            </ul>
        </div>

        <!-- Display Selected Line Items -->
        <div class="form-group">
            <label for="line_items">Selected Line Items</label>
            <ul>
                @foreach($lineItems as $lineItem)
                    <li>{{ $lineItem->delivery_number }} - {{ $lineItem->site_name }}</li>
                @endforeach
            </ul>
        </div>

        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Create Billing</button>
        </div>
    </form>
</div>
@endsection
