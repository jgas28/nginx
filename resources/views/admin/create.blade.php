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

<div class="max-w-7xl mx-auto px-4 py-2">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-blue-600 text-white text-center py-4">
            <h4 class="text-xl font-semibold"><i class="bi bi-file-earmark-text"></i> Cash Voucher Request</h4>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.store') }}" method="POST">
                @csrf

                <!-- Voucher Type -->
                <fieldset class="mb-6 border border-gray-200 rounded-lg p-4">
                    <legend class="text-blue-600 font-semibold text-sm px-2">Voucher Type</legend>
                    <div class="flex space-x-4 mt-2">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="cvr_type" value="Admin" checked class="sr-only peer">
                            <div class="w-full text-center px-4 py-2 rounded-lg border border-gray-300
                                        text-gray-700 font-medium
                                        peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                        transition-colors duration-200">
                                Admin
                            </div>
                        </label>

                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="cvr_type" value="RPM" class="sr-only peer">
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
                            <input type="radio" name="voucher_type" value="regular" checked class="sr-only peer">
                            <div class="w-full text-center px-4 py-2 rounded-lg border border-gray-300
                                        text-gray-700 font-medium
                                        peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                        transition-colors duration-200">
                                Regular
                            </div>
                        </label>

                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="voucher_type" value="with_tax" class="sr-only peer">
                            <div class="w-full text-center px-4 py-2 rounded-lg border border-gray-300
                                        text-gray-700 font-medium
                                        peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                        transition-colors duration-200">
                                w/TAX
                            </div>
                        </label>
                    </div>

                    <!-- Tax Fields -->
                    <div id="withholding_tax_container" class="mt-4 hidden">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Tax Base Amount</label>
                            <input type="number" name="tax_base_amount" id="tax_base_amount"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Withholding Tax</label>
                            <select name="withholding_tax" id="withholding_tax"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <option value="" disabled selected>Select Withholding Tax</option>
                                @foreach($taxes as $tax)
                                    <option value="{{ $tax->id }}">{{ $tax->description }}% - {{ $tax->percentage }}</option>
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
                            class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm p-2">
                         </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company</label>
                            <select name="company_id" id="company_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                                <option value="" disabled selected>Select Company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->company_code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Supplier</label>
                            <select name="supplier_id" id="supplier_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                                <option value="" disabled selected>Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Expense Type</label>
                            <select name="expense_type_id" id="expense_type_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                                <option value="" disabled selected>Select Expense Type</option>
                                @foreach($expenseTypes as $expenseType)
                                    <option value="{{ $expenseType->id }}">{{ $expenseType->expense_code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="truck_field_container" class="hidden">
                            <label class="block text-sm font-medium text-gray-700">Truck</label>
                            <select name="truck_id" id="truck_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <option value="" disabled selected>Select Truck</option>
                                @foreach($trucks as $truck)
                                    <option value="{{ $truck->id }}">{{ $truck->truck_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                <!-- Line Items -->
                <fieldset class="mb-6 border border-gray-300 rounded p-4">
                    <legend class="text-blue-600 font-bold text-sm px-2">Request Lines</legend>
                    <div id="line_fields" class="space-y-2"></div>
                    <button type="button" id="add_line" class="mt-2 px-4 py-1 bg-blue-500 text-white rounded shadow-sm">Add Request Line</button>
                </fieldset>

                <!-- Remarks -->
                <fieldset class="mb-6 border border-gray-300 rounded p-4">
                    <legend class="text-blue-600 font-bold text-sm px-2">Remarks</legend>
                    <div id="remarks_fields" class="space-y-2"></div>
                    <button type="button" id="add_remarks" class="mt-2 px-4 py-1 bg-blue-500 text-white rounded shadow-sm">Add Remark</button>
                </fieldset>

                <div class="text-center mt-6">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded shadow hover:bg-green-700">
                        <i class="bi bi-check-circle-fill"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const companySelect = document.getElementById('company_id');
        const cvrNumberInput = document.getElementById('cvr_number');

        companySelect.addEventListener('change', function () {
            const companyId = this.value;
            if (!companyId) return;

            fetch(`/admin/generate-cvr-number`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ company_id: companyId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.cvr_number) {
                    cvrNumberInput.value = data.cvr_number;
                }
            })
            .catch(err => {
                console.error('Error generating CVR number:', err);
            });
        });
    });

    // Add dynamic line item (description and amount)
    document.getElementById('add_line').addEventListener('click', function() {
        const newLineItem = document.createElement('div');
        newLineItem.classList.add('row', 'mb-2', 'align-items-center');

        newLineItem.innerHTML = `
            <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input type="text" name="description[]" class="block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter description" required>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <input type="number" name="amount_details[]" class="block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter amount" step="0.01" required>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="button" class="bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700 remove_line">Remove</button>
                </div>
            </div>
        `;


        document.getElementById('line_fields').appendChild(newLineItem);
    });

    // Remove a line item
    document.getElementById('line_fields').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove_line')) {
            e.target.closest('.row').remove();
        }
    });

    // Add dynamic remarks field
    document.getElementById('add_remarks').addEventListener('click', function() {
        const newRemarksField = document.createElement('div');
        newRemarksField.classList.add('input-group', 'mb-2');

        newRemarksField.innerHTML = `
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                <div class="flex space-x-2 mb-2">
                    <input type="text" name="remarks[]" class="flex-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter remarks">
                    <button type="button" class="bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700 remove_remarks">Remove</button>
                </div>
            </div>
        `;


        document.getElementById('remarks_fields').appendChild(newRemarksField);
    });

    // Remove a remarks field
    document.getElementById('remarks_fields').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove_remarks')) {
            e.target.closest('.input-group').remove();
        }
    });

    // Toggle withholding tax fields
    document.addEventListener('DOMContentLoaded', function () {
        const taxField = document.getElementById('withholding_tax_container');
        const radios = document.querySelectorAll('input[name="voucher_type"]');

        function toggleTaxFields(value) {
            const showTax = value === 'with_tax';
            taxField.classList.toggle('hidden', !showTax);
        }

        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                toggleTaxFields(this.value);
            });
        });

        const selected = document.querySelector('input[name="voucher_type"]:checked');
        if (selected) {
            toggleTaxFields(selected.value);
        }
    });


    document.addEventListener('DOMContentLoaded', function () {
        const cvrTypeRadios = document.querySelectorAll('input[name="cvr_type"]');
        const truckField = document.getElementById('truck_field_container');
        const truckId = document.getElementById('truck_id');

        function toggleTruckField() {
            const selectedType = document.querySelector('input[name="cvr_type"]:checked').value;

            if (selectedType === 'RPM') {
                truckField.style.display = 'block';
            } else {
                truckField.style.display = 'none';
                truckId.value = ""; // Clear truck selection
            }
        }

        cvrTypeRadios.forEach(radio => {
            radio.addEventListener('change', toggleTruckField);
        });

        // Initialize on page load
        toggleTruckField();
    });
</script>

@endsection
