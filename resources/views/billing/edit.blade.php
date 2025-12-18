@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Billing</h2>

    <form action="{{ route('billing.update', $billing->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- For the update request -->

        <!-- SOA Number -->
        <div class="form-group">
            <label for="soa_number">SOA Number</label>
            <input type="text" name="soa_number" id="soa_number" class="form-control" value="{{ old('soa_number', $billing->soa_number) }}" required>
        </div>

        <!-- Company Dropdown -->
        <div class="form-group">
            <label for="company_id">Company</label>
            <select name="company_id" id="company_id" class="form-control" required>
                <option value="">Select Company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ $billing->company_id == $company->id ? 'selected' : '' }}>
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
                    <option value="{{ $tax->id }}" {{ $billing->withholding_tax_id == $tax->id ? 'selected' : '' }}>
                        {{ $tax->name }} ({{ $tax->rate }}%)
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Delivery Request (MTM) Selection -->
        <div class="form-group">
            <label for="delivery_requests">Select Delivery Requests (MTMs)</label>
            <select name="delivery_requests[]" id="delivery_requests" class="form-control" multiple>
                @foreach($deliveryRequests as $dr)
                    <option value="{{ $dr->id }}" {{ in_array($dr->id, old('delivery_requests', $billing->deliveryRequests->pluck('id')->toArray())) ? 'selected' : '' }}>
                        {{ $dr->mtm }} - {{ $dr->project_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Delivery Request Line Item Selection -->
        <div class="form-group">
            <label for="line_items">Select Line Items</label>
            <select name="line_items[]" id="line_items" class="form-control" multiple>
                @foreach($lineItems as $lineItem)
                    <option value="{{ $lineItem->id }}" {{ in_array($lineItem->id, old('line_items', $billing->deliveryRequestLineItems->pluck('id')->toArray())) ? 'selected' : '' }}>
                        {{ $lineItem->delivery_number }} - {{ $lineItem->site_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Remove Delivery Requests -->
        <div class="form-group">
            <label for="remove_delivery_requests">Remove Delivery Requests</label>
            <select name="remove_delivery_requests[]" id="remove_delivery_requests" class="form-control" multiple>
                @foreach($billing->deliveryRequests as $dr)
                    <option value="{{ $dr->id }}">
                        {{ $dr->mtm }} - {{ $dr->project_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Remove Delivery Request Line Items -->
        <div class="form-group">
            <label for="remove_line_items">Remove Line Items</label>
            <select name="remove_line_items[]" id="remove_line_items" class="form-control" multiple>
                @foreach($billing->deliveryRequestLineItems as $lineItem)
                    <option value="{{ $lineItem->id }}">
                        {{ $lineItem->delivery_number }} - {{ $lineItem->site_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Billed To -->
        <div class="form-group">
            <label for="billed_to">Billed To</label>
            <input type="text" name="billed_to" id="billed_to" class="form-control" value="{{ old('billed_to', $billing->billed_to) }}" required>
        </div>

        <!-- Billing Address -->
        <div class="form-group">
            <label for="billing_address">Billing Address</label>
            <textarea name="billing_address" id="billing_address" class="form-control" required>{{ old('billing_address', $billing->billing_address) }}</textarea>
        </div>

        <!-- Billing Date -->
        <div class="form-group">
            <label for="billing_date">Billing Date</label>
            <input type="date" name="billing_date" id="billing_date" class="form-control" value="{{ old('billing_date', $billing->billing_date) }}" required>
        </div>

        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update Billing</button>
        </div>
    </form>
</div>
@endsection
