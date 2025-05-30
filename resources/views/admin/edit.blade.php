@extends('layouts.app')

@section('title', 'Edit Cash Voucher Request')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-2">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-blue-600 text-white text-center py-4">
            <h4 class="text-xl font-semibold"><i class="bi bi-file-earmark-text"></i> Edit Cash Voucher Request</h4>
        </div>
      
        <div class="p-6">
            <form action="{{ route('admin.update', $voucher->id) }}" method="POST">
                @csrf
                @method('PUT')
                <!-- Voucher Type -->
                <fieldset class="mb-6 border border-gray-200 rounded-lg p-4">
                    <legend class="text-blue-600 font-semibold text-sm px-2">Voucher Type</legend>
                    <div class="flex space-x-4 mt-2">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="cvr_type" value="admin" class="sr-only peer"
                                {{ old('cvr_type', $voucher->cvr_type) == 'admin' ? 'checked' : '' }}
                            >
                            <div class="w-full text-center px-4 py-2 rounded-lg border border-gray-300
                                text-gray-700 font-medium
                                peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                transition-colors duration-200">
                                Admin
                            </div>
                        </label>

                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="cvr_type" value="rpm" class="sr-only peer"
                                {{ old('cvr_type', $voucher->cvr_type) == 'rpm' ? 'checked' : '' }}
                            >
                            <div class="w-full text-center px-4 py-2 rounded-lg border border-gray-300
                                text-gray-700 font-medium
                                peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                transition-colors duration-200">
                                RPM
                            </div>
                        </label>
                    </div>
                </fieldset>

                <!-- CVR Type -->
                <fieldset class="mb-6 border border-gray-300 rounded p-4">
                    <legend class="text-blue-600 font-bold text-sm px-2">CVR Type</legend>

                    <div class="flex space-x-4 mt-2">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="voucher_type" value="regular"
                                {{ old('voucher_type', $voucher->voucher_type) == 'regular' ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-full text-center px-4 py-2 rounded-lg border border-gray-300
                                        text-gray-700 font-medium
                                        peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                        transition-colors duration-200">
                                Regular
                            </div>
                        </label>

                        <label class="flex-1 cursor-pointer">
                           <input type="radio" name="voucher_type" value="with_tax"
                                {{ old('voucher_type', $voucher->voucher_type) == 'with_tax' ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-full text-center px-4 py-2 rounded-lg border border-gray-300
                                        text-gray-700 font-medium
                                        peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                        transition-colors duration-200">
                                w/TAX
                            </div>
                        </label>
                    </div>

                    <!-- Tax Fields -->
                    <div id="withholding_tax_container" class="mt-4 {{ $voucher->voucher_type == 'with_tax' ? '' : 'hidden' }}">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Tax Base Amount</label>
                            <input type="number" name="tax_base_amount" id="tax_base_amount"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                                value="{{ old('tax_base_amount', $voucher->tax_based_amount) }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Withholding Tax</label>
                            <select name="withholding_tax" id="withholding_tax"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <option value="" disabled>Select Withholding Tax</option>
                                @foreach($taxes as $tax)
                                    <option value="{{ $tax->id }}"
                                        {{ old('withholding_tax', $voucher->withholding_tax_id) == $tax->id ? 'selected' : '' }}>
                                        {{ $tax->description }}% - {{ $tax->percentage }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                <!-- CVR Info -->
                <fieldset class="mb-6 border border-gray-300 rounded p-4">
                    <legend class="text-blue-600 font-bold text-sm px-2">CVR Information</legend>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CVR Number</label>
                            <input type="text" name="cvr_number" id="cvr_number" readonly
                            class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm p-2"
                            value="{{ old('cvr_number', $voucher->cvr_number) }}">
                         </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company</label>
                            <select name="company_id_display" id="company_id_display" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-gray-100 cursor-not-allowed" disabled>
                                <option value="" disabled>Select Company</option>
                                @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ $voucher->company_id == $company->id ? 'selected' : '' }}>
                                    {{ $company->company_code }}
                                </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="company_id" value="{{ $voucher->company_id }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Supplier</label>
                            <select name="supplier_id" id="supplier_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                                <option value="" disabled>Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ $voucher->supplier_id == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->supplier_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Expense Type</label>
                            <select name="expense_type_id" id="expense_type_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                                <option value="" disabled>Select Expense Type</option>
                                @foreach($expenseTypes as $expenseType)
                                    <option value="{{ $expenseType->id }}" {{ $voucher->expense_type_id == $expenseType->id ? 'selected' : '' }}>
                                        {{ $expenseType->expense_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="truck_field_container" class="{{ $voucher->cvr_type === 'rpm' ? '' : 'hidden' }}">
                            <label class="block text-sm font-medium text-gray-700">Truck</label>
                            <select name="truck_id" id="truck_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <option value="" disabled>Select Truck</option>
                                @foreach($trucks as $truck)
                                    <option value="{{ $truck->id }}" {{ $voucher->truck_id == $truck->id ? 'selected' : '' }}>
                                        {{ $truck->truck_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                <!-- Line Items -->
                <fieldset class="mb-6 border border-gray-300 rounded p-4">
                    <legend class="text-blue-600 font-bold text-sm px-2">Request Lines</legend>
                    <div id="line_fields" class="space-y-2">
                        @foreach(json_decode($voucher->description, true) as $i => $desc)
                            <div class="row mb-2 align-items-center">
                                <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                    <div class="md:col-span-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <input type="text" name="description[]" class="block w-full border border-gray-300 rounded-md shadow-sm p-2"
                                               value="{{ $desc }}" required>
                                    </div>
                                    <div class="md:col-span-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                                        <input type="number" name="amount_details[]" step="0.01"
                                               class="block w-full border border-gray-300 rounded-md shadow-sm p-2"
                                               value="{{ json_decode($voucher->amount_details)[$i] }}" required>
                                    </div>
                                    <div class="md:col-span-2 flex justify-end">
                                        <button type="button" class="bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700 remove_line">Remove</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add_line" class="mt-2 px-4 py-1 bg-blue-500 text-white rounded shadow-sm">Add Request Line</button>
                </fieldset>

                <!-- Remarks -->
                <fieldset class="mb-6 border border-gray-300 rounded p-4">
                    <legend class="text-blue-600 font-bold text-sm px-2">Remarks</legend>
                    <div id="remarks_fields" class="space-y-2">
                        @if(!empty($voucher->remarks))
                            @foreach(json_decode($voucher->remarks, true) as $remark)
                                <div class="input-group mb-2">
                                    <div class="w-full">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                                        <div class="flex space-x-2 mb-2">
                                            <input type="text" name="remarks[]" class="flex-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                                                   value="{{ $remark }}">
                                            <button type="button" class="bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700 remove_remarks">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="button" id="add_remarks" class="mt-2 px-4 py-1 bg-blue-500 text-white rounded shadow-sm">Add Remark</button>
                </fieldset>

                <!-- Submit -->
                <div class="text-center mt-6">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded shadow hover:bg-green-700">
                        <i class="bi bi-check-circle-fill"></i> Update Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Add Line Item
    document.getElementById('add_line').addEventListener('click', function() {
        const newLineItem = document.createElement('div');
        newLineItem.classList.add('row', 'mb-2', 'align-items-center');
        newLineItem.innerHTML = `
            <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input type="text" name="description[]" class="block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <input type="number" name="amount_details[]" step="0.01" class="block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="button" class="bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700 remove_line">Remove</button>
                </div>
            </div>
        `;
        document.getElementById('line_fields').appendChild(newLineItem);
    });

    // Remove Line Item
    document.getElementById('line_fields').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove_line')) {
            e.target.closest('.row').remove();
        }
    });

    // Add Remark
    document.getElementById('add_remarks').addEventListener('click', function() {
        const newRemarksField = document.createElement('div');
        newRemarksField.classList.add('input-group', 'mb-2');
        newRemarksField.innerHTML = `
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                <div class="flex space-x-2 mb-2">
                    <input type="text" name="remarks[]" class="flex-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Enter remarks">
                    <button type="button" class="bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700 remove_remarks">Remove</button>
                </div>
            </div>
        `;
        document.getElementById('remarks_fields').appendChild(newRemarksField);
    });

    // Remove Remark
    document.getElementById('remarks_fields').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove_remarks')) {
            e.target.closest('.input-group').remove();
        }
    });

    // Toggle Withholding Tax
    function toggleTaxFields(value) {
        const taxContainer = document.getElementById('withholding_tax_container');
        const taxInput = document.getElementById('tax_base_amount');
        const withholdingSelect = document.getElementById('withholding_tax');

        if (value === 'with_tax') {
            taxContainer.classList.remove('hidden');
        } else {
            taxContainer.classList.add('hidden');
            // Clear values when switching to regular
            if (taxInput) taxInput.value = '';
            if (withholdingSelect) withholdingSelect.selectedIndex = 0; // Reset to "Select Withholding Tax"
        }
    }

    document.querySelectorAll('input[name="voucher_type"]').forEach(radio => {
        radio.addEventListener('change', () => {
            toggleTaxFields(radio.value);
        });
    });

    toggleTaxFields(document.querySelector('input[name="voucher_type"]:checked')?.value);

    // Toggle Truck Field
    function toggleTruckField() {
        const selectedType = document.querySelector('input[name="cvr_type"]:checked').value;
        const truckField = document.getElementById('truck_field_container');
        const truckSelect = document.getElementById('truck_id');
        if (selectedType === 'rpm') {
            truckField.classList.remove('hidden');
        } else {
            truckField.classList.add('hidden');
            truckSelect.value = "";
        }
    }

    document.querySelectorAll('input[name="cvr_type"]').forEach(radio => {
        radio.addEventListener('change', toggleTruckField);
    });

    toggleTruckField();
});
</script>
@endsection

