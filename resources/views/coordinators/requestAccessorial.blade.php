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
        <form action="{{ route('coordinators.storeAccessorial') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="dr_id" value="{{ $deliveryLineItems->first()->dr_id }}">
            <input type="hidden" name="mtm" value="{{ $deliveryLineItems->first()->mtm }}">
            <!-- <input type="hidden" name="cvr_type" value="accessorial"> -->
            <input type="hidden" name="company_id" value="{{ $deliveryLineItems->first()->company->id }}">

            <!-- CVR Type -->
            <fieldset class="bg-gray-100 p-4 rounded">
                <legend class="text-blue-700 font-semibold text-sm uppercase mb-3">CVR Type</legend>
                
                <div class="flex flex-col md:flex-row md:items-end md:space-x-4 space-y-4 md:space-y-0">

                    <!-- CVR Type Dropdown -->
                    <div class="w-full md:w-1/3">
                        <select name="voucher_type" id="voucher_type_select" class="input w-full">
                            <option value="regular" selected>Regular</option>
                            <option value="with_tax">w/TAX</option>
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">CVR Type</label>
                    </div>

                    <!-- Withholding Tax Select -->
                    <div id="withholding_tax_container" class="w-full md:w-1/3 hidden">
                        <select name="withholding_tax" class="input w-full">
                            <option value="">Select Withholding Tax</option>
                            @foreach ($taxes as $tax)
                                <option value="{{ $tax->id }}">{{ $tax->description }} ({{ $tax->percentage }}%)</option>
                            @endforeach
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">Withholding Tax</label>
                    </div>

                    <!-- Tax Base Amount -->
                    <div id="tax_base_container" class="w-full md:w-1/3 hidden">
                        <input type="number" name="tax_base_amount" class="input w-full" placeholder="Enter base amount">
                        <label class="block text-sm text-gray-600 mt-1">Tax Base Amount</label>
                    </div>

                </div>
            </fieldset>


            <!-- CVR Information -->
            <fieldset class="bg-gray-100 p-4 rounded">
                <legend class="text-blue-700 font-semibold text-sm uppercase mb-3">CVR Information</legend>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    
                    <!-- CVR Number -->
                    <div>
                        <input type="text" name="cvr_number" value="{{ $formattedCvrNumber }}" readonly class="input bg-gray-100 w-full">
                        <label class="block text-sm text-gray-600 mt-1">CVR Number</label>
                    </div>

                    <!-- Amount -->
                    <div>
                        <input type="number" name="amount" step="0.01" min="0" required class="input w-full" placeholder="Amount">
                        <label class="block text-sm text-gray-600 mt-1">Amount</label>
                    </div>

                    <!-- Request Type -->
                    <div>
                        <select name="request_type" class="input w-full" required>
                            <option value="">Select Type</option>
                            @foreach($requestType as $rt)
                                <option value="{{ $rt->id }}">{{ $rt->request_type }}</option>
                            @endforeach
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">Request Type</label>
                    </div>

                    <!-- Requestor -->
                    <div>
                        <select name="requestor" class="input w-full" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->fname }} {{ $emp->lname }}</option>
                            @endforeach
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">Requestor</label>
                    </div>

                     <!-- Truck -->
                    <div>
                        <select name="truck_id" class="input w-full" required>
                            <option value="">Select Truck</option>
                            @foreach($trucks as $truck)
                                <option value="{{ $truck->id }}">{{ $truck->truck_name }}</option>
                            @endforeach
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">Truck</label>
                    </div>

                     <!-- Driver -->
                    <div>
                         <select name="driver_id" class="input w-full" required>
                            <option value="">Select Driver</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->fname }} {{ $emp->lname }}</option>
                            @endforeach
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">Driver</label>
                    </div>

                    <!-- Fleet Card -->
                    <div>
                         <select name="fleet_card_id" class="input w-full" required>
                            <option value="">Select Fleet Card</option>
                            @foreach($fleetCards as $fleetCard)
                                <option value="{{ $fleetCard->id }}">{{ $fleetCard->account_name }} - {{ $fleetCard->account_number }}</option>
                            @endforeach
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">Fleet Card</label>
                    </div>

                    <div>
                        <select name="trip_type" class="input w-full">
                            <option value="accessorial" {{ old('trip_type', 'accessorial') === 'accessorial' ? 'selected' : '' }}>Accessorial</option>
                            <option value="freight" {{ old('trip_type') === 'freight' ? 'selected' : '' }}>Freight</option>
                            <option value="others" {{ old('trip_type') === 'others' ? 'selected' : '' }}>Others</option>
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">Trip Type</label>
                    </div>
                </div>
            </fieldset>
    
            <!-- Helpers -->
            <fieldset class="bg-gray-100 p-4 rounded">
                <legend class="text-blue-700 font-semibold text-sm uppercase mb-3">Helpers</legend>
                <div id="helpers_fields" class="space-y-2"></div>
                <button type="button" id="add_helpers" class="text-blue-600 hover:underline text-sm mt-2">
                    + Add Helpers
                </button>
            </fieldset>

            <!-- Remarks -->
            <fieldset class="bg-gray-100 p-4 rounded">
                <legend class="text-blue-700 font-semibold text-sm uppercase mb-3">Remarks</legend>
                <div id="remarks_fields" class="space-y-2"></div>
                <button type="button" id="add_remarks" class="text-blue-600 hover:underline text-sm mt-2">
                    + Add Remarks
                </button>
            </fieldset>

            <!-- Submit -->
            <div class="text-center mt-6">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg shadow-lg font-semibold">
                    <i class="bi bi-check-circle-fill"></i> Submit Request
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

        // Function to show/hide tax-related fields based on selection
        function toggleTaxFields(value) {
            const showTax = value === 'with_tax';
            taxField.classList.toggle('hidden', !showTax);
            taxBaseField.classList.toggle('hidden', !showTax && !taxSelect.value);
        }

        // Listener for CVR Type selection
        voucherTypeSelect.addEventListener('change', function () {
            toggleTaxFields(this.value);
        });

        // Listener for tax dropdown change to show base input if tax selected
        taxSelect.addEventListener('change', function () {
            const selected = this.value !== '';
            taxBaseField.classList.toggle('hidden', !selected);
        });

        // Initialize on page load
        toggleTaxFields(voucherTypeSelect.value);

        // ============================
        // Remarks Field Add/Remove
        // ============================
        document.getElementById('add_remarks').addEventListener('click', function () {
            const newRemarksField = document.createElement('div');
            newRemarksField.classList.add('flex', 'gap-2', 'items-center');

            newRemarksField.innerHTML = `
                <input type="text" name="remarks[]" class="form-input w-full rounded border-gray-300" placeholder="Enter Remarks">
                <button type="button" class="text-red-600 hover:text-red-800 font-bold remove_remarks">×</button>
            `;

            document.getElementById('remarks_fields').appendChild(newRemarksField);
        });

        document.getElementById('remarks_fields').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove_remarks')) {
                e.target.parentElement.remove();
            }
        });

        // ============================
        // Helpers Field Add/Remove
        // ============================
        document.getElementById('add_helpers').addEventListener('click', function () {
            const newRemarksField = document.createElement('div');
            newRemarksField.classList.add('flex', 'gap-2', 'items-center');

            newRemarksField.innerHTML = `
                <input type="text" name="helpers[]" class="form-input w-full rounded border-gray-300" placeholder="Enter Helpers">
                <button type="button" class="text-red-600 hover:text-red-800 font-bold remove_helpers">×</button>
            `;

            document.getElementById('helpers_fields').appendChild(newRemarksField);
        });

        document.getElementById('helpers_fields').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove_helpers')) {
                e.target.parentElement.remove();
            }
        });
    });
</script>

@endsection
