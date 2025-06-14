@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
<style>
    .btn-check:checked + .btn-cvr {
        background-color: #0d6efd; /* Bootstrap primary */
        color: #fff;
        border-color: #0d6efd;
    }
</style>

<div class="bg-white shadow-lg rounded-lg">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white text-center py-4 rounded-t-lg">
        <h3 class="text-lg font-bold">Delivery Information</h3>
    </div>

    <!-- Card Body -->
    <div class="p-6">
        <!-- Header Section -->
        <div class="flex flex-wrap justify-center mb-6 text-center">
            <div class="w-full md:w-1/3 mb-4">
                <p class="text-blue-600 uppercase font-semibold">MTM Number</p>
                <p class="text-gray-600">{{ $deliveryLineItems->first()->mtm }}</p>
                <input type="hidden" name="mtm" value="{{ $deliveryLineItems->first()->mtm }}">
            </div>
            <div class="w-full md:w-1/3 mb-4">
                <p class="text-blue-600 uppercase font-semibold">Company</p>
                <p class="text-gray-600">{{ $deliveryLineItems->first()->company->company_name }}</p>
            </div>
            <div class="w-full md:w-1/3">
                <p class="text-blue-600 uppercase font-semibold">Delivery Type</p>
                <p class="text-gray-600">{{ $deliveryLineItems->first()->delivery_type }}</p>
            </div>
        </div>

        <!-- Line Items -->
        @foreach($deliveryLineItems as $deliveryLineItem)
        <div class="bg-gray-50 border rounded-lg p-4 mb-4">
            <div class="flex flex-wrap justify-between text-center">
                <div class="w-full md:w-1/3 mb-3">
                    <p class="text-blue-600 uppercase font-semibold">Site Name</p>
                    <p class="text-gray-800">{{ str_replace(['"'], '', $deliveryLineItem->site_name) }}</p>
                </div>
                <div class="w-full md:w-1/3 mb-3">
                    <p class="text-blue-600 uppercase font-semibold">Delivery Number</p>
                    <p class="text-gray-800">{{ str_replace(['"'], '', $deliveryLineItem->delivery_number) }}</p>
                </div>
                <div class="w-full md:w-1/3">
                    <p class="text-blue-600 uppercase font-semibold">Delivery Address</p>
                    <p class="text-gray-800">{{ str_replace(['"'], '', $deliveryLineItem->delivery_address) }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Cash Voucher Request Form -->
<div class="bg-white shadow-lg rounded-lg mt-6">
    <div class="bg-blue-600 text-white text-center py-3 rounded-t-lg">
        <h4 class="text-lg font-bold">Cash Voucher Request</h4>
    </div>

    <div class="p-6">
        <form action="{{ route('cashVoucherRequests.updateCVR', $cashVoucher->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="cvr_id" value="{{ $cashVoucher->id }}">
            <input type="hidden" name="dr_id" value="{{ $cashVoucher->dr_id }}">
            <input type="hidden" name="mtm" value="{{ $cashVoucher->mtm }}">
            <input type="hidden" name="cvr_type" value="{{ $cashVoucher->cvr_type }}">
            <input type="hidden" name="company_id" value="{{ $cashVoucher->company_id }}">

            <!-- CVR Type -->
            <fieldset class="bg-gray-100 p-4 rounded">
                <legend class="text-blue-700 font-semibold text-sm uppercase mb-3">CVR Type</legend>

                <div class="flex flex-col md:flex-row md:items-end md:space-x-4 space-y-4 md:space-y-0">
                    <div class="w-full md:w-1/3">
                        <select name="voucher_type" id="voucher_type_select" class="input w-full">
                            <option value="regular" {{ $cashVoucher->voucher_type == 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="with_tax" {{ $cashVoucher->voucher_type == 'with_tax' ? 'selected' : '' }}>w/TAX</option>
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">CVR Type</label>
                    </div>

                    <div id="withholding_tax_container" class="w-full md:w-1/3 {{ $cashVoucher->voucher_type != 'with_tax' ? 'hidden' : '' }}">
                        <select name="withholding_tax" class="input w-full">
                            <option value="">Select Withholding Tax</option>
                            @foreach ($taxes as $tax)
                                <option value="{{ $tax->id }}" {{ $cashVoucher->withholding_tax_id == $tax->id ? 'selected' : '' }}>
                                    {{ $tax->description }} ({{ $tax->percentage }}%)
                                </option>
                            @endforeach
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">Withholding Tax</label>
                    </div>

                    <div id="tax_base_container" class="w-full md:w-1/3 {{ $cashVoucher->voucher_type != 'with_tax' || !$cashVoucher->tax_based_amount ? 'hidden' : '' }}">
                        <input type="number" name="tax_base_amount" class="input w-full" placeholder="Enter base amount"
                            value="{{ old('tax_base_amount', $cashVoucher->tax_based_amount) }}" step="0.01">
                        <label class="block text-sm text-gray-600 mt-1">Tax Base Amount</label>
                    </div>
                </div>
            </fieldset>

            <!-- CVR Info -->
            <fieldset class="bg-gray-100 p-4 rounded">
                <legend class="text-blue-700 font-semibold text-sm uppercase mb-3">CVR Information</legend>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <input type="text" name="cvr_number" value="{{ $cashVoucher->cvr_number }}" readonly class="input bg-gray-100 w-full">
                        <label class="block text-sm text-gray-600 mt-1">CVR Number</label>
                    </div>

                    <div>
                        <input type="number" name="amount" step="0.01" min="0" required class="input w-full"
                            value="{{ old('amount', $cashVoucher->amount) }}">
                        <label class="block text-sm text-gray-600 mt-1">Amount</label>
                    </div>

                    <div>
                        <select name="request_type" class="input w-full" required>
                            <option value="">Select Type</option>
                            @foreach($requestType as $rt)
                                <option value="{{ $rt->id }}" {{ $cashVoucher->request_type == $rt->id ? 'selected' : '' }}>
                                    {{ $rt->request_type }}
                                </option>
                            @endforeach
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">Request Type</label>
                    </div>

                    <div>
                        <select name="requestor" class="input w-full" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $cashVoucher->requestor == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->fname }} {{ $emp->lname }}
                                </option>
                            @endforeach
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">Requestor</label>
                    </div>
                </div>
            </fieldset>

            <!-- Remarks -->
            <fieldset class="bg-gray-100 p-4 rounded">
                <legend class="text-blue-700 font-semibold text-sm uppercase mb-3">Remarks</legend>
                <div id="remarks_fields" class="space-y-2">
                    @php
                        $existingRemarks = json_decode($cashVoucher->remarks, true) ?? [];
                    @endphp

                    @foreach($existingRemarks as $remark)
                        <div class="flex gap-2 items-center">
                            <input type="text" name="remarks[]" value="{{ $remark }}" class="form-input w-full rounded border-gray-300">
                            <button type="button" class="text-red-600 hover:text-red-800 font-bold remove_remarks">×</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add_remarks" class="text-blue-600 hover:underline text-sm mt-2">
                    + Add Remarks
                </button>
            </fieldset>

            <!-- Submit -->
            <div class="text-center mt-6">
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg shadow-lg font-semibold">
                    <i class="bi bi-save"></i> Update CVR
                </button>
            </div>
        </form>

    </div>
</div>


<!-- Custom Styles -->
<style>
    .card {
        border-radius: 12px;
        border: none;
    }

    .card-header {
        font-size: 1.5rem;
        font-weight: bold;
    }

    fieldset {
        background-color: #f8f9fa;
        padding: 20px;
    }

    legend {
        font-size: 1.1rem;
        font-weight: bold;
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 3px;
        display: inline-block;
    }

    .form-floating label {
        font-size: 14px;
        color: #555;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        box-shadow: none;
    }

    .btn {
        border-radius: 6px;
    }

    .btn-success {
        background-color: #198754;
        border-color: #198754;
    }

    .btn-outline-primary {
        border-color: #0d6efd;
        color: #0d6efd;
    }

    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: #fff;
    }

    .shadow {
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
    }

</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const taxField = document.getElementById('withholding_tax_container');
        const taxBaseField = document.getElementById('tax_base_container');
        const taxSelect = document.querySelector('select[name="withholding_tax"]');
        const voucherTypeSelect = document.getElementById('voucher_type_select');

        // Toggle tax fields visibility based on voucher type
        function toggleTaxFields(value) {
            const showTax = value === 'with_tax';

            taxField.classList.toggle('hidden', !showTax);
            taxBaseField.classList.toggle('hidden', !showTax);

            if (!showTax) {
                // Clear tax fields if type is changed to regular
                taxSelect.value = '';
                const taxBaseInput = taxBaseField.querySelector('input[name="tax_base_amount"]');
                if (taxBaseInput) taxBaseInput.value = '';
            }
        }

        // Event listener for CVR type dropdown
        voucherTypeSelect.addEventListener('change', function () {
            toggleTaxFields(this.value);
        });

        // Show tax base field only if a tax is selected
        taxSelect.addEventListener('change', function () {
            const selected = this.value !== '';
            taxBaseField.classList.toggle('hidden', !selected);
        });

        // Initialize based on current state
        toggleTaxFields(voucherTypeSelect.value);

        // ====== Remarks Field Add/Remove ======
        const remarksFields = document.getElementById('remarks_fields');

        document.getElementById('add_remarks').addEventListener('click', function () {
            const newRemarksField = document.createElement('div');
            newRemarksField.classList.add('flex', 'gap-2', 'items-center');

            newRemarksField.innerHTML = `
                <input type="text" name="remarks[]" class="form-input w-full rounded border-gray-300" placeholder="Enter Remarks">
                <button type="button" class="text-red-600 hover:text-red-800 font-bold remove_remarks">×</button>
            `;

            remarksFields.appendChild(newRemarksField);
        });

        remarksFields.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove_remarks')) {
                e.target.closest('div').remove();
            }
        });
    });
</script>


@endsection
