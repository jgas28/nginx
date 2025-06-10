@extends('layouts.app')

@section('title', 'Edit CVR')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-2">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white text-center py-4">
                <h4 class="text-xl font-semibold"><i class="bi bi-file-earmark-text"></i> Edit Cash Voucher Request</h4>
            </div>

            <div class="p-6">
                <form action="{{ route('adminCV.updateCVR', $cashVoucher->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Voucher Type -->
                    <fieldset class="mb-6 border border-gray-200 rounded-lg p-4">
                        <legend class="text-blue-600 font-semibold text-sm px-2">Voucher Type</legend>
                        <div class="flex space-x-4 mt-2">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="cvr_type" value="admin" {{ $cashVoucher->cvr_type == 'admin' ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-full text-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-colors duration-200">
                                    Admin
                                </div>
                            </label>

                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="cvr_type" value="RPM" {{ $cashVoucher->cvr_type == 'RPM' ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-full text-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-colors duration-200">
                                    RPM
                                </div>
                            </label>
                        </div>
                    </fieldset>

                    <!-- CVR Type -->
                    <fieldset class="mb-6 border border-gray-300 rounded p-4">
                        <legend class="text-blue-600 font-bold text-sm px-2">CVR Information</legend>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">CVR Number</label>
                                <input type="text" name="cvr_number" value="{{ old('cvr_number', $cashVoucher->cvr_number) }}" class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm p-2" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Company</label>
                                <select name="company_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                                    <option value="" disabled>Select Company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ $company->id == $cashVoucher->company_id ? 'selected' : '' }}>{{ $company->company_code }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Supplier</label>
                                <select name="supplier_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                                    <option value="" disabled>Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ $supplier->id == $cashVoucher->supplier_id ? 'selected' : '' }}>{{ $supplier->supplier_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Expense Type</label>
                                <select name="expense_type_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                                    <option value="" disabled>Select Expense Type</option>
                                    @foreach($expenseTypes as $expenseType)
                                        <option value="{{ $expenseType->id }}" {{ $expenseType->id == $cashVoucher->expense_type_id ? 'selected' : '' }}>{{ $expenseType->expense_code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Line Items -->
                    <fieldset class="mb-6 border border-gray-300 rounded p-4">
                        <legend class="text-blue-600 font-bold text-sm px-2">Request Lines</legend>
                        <div id="line_fields" class="space-y-2">
                            @if(is_array($cashVoucher->description) && is_array($cashVoucher->amount_details))
                                @foreach($cashVoucher->description as $index => $desc)
                                    <div class="row mb-2 align-items-center">
                                        <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                                <input type="text" name="description[]" value="{{ old('description.' . $index, $desc) }}"
                                                    class="block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" required>
                                            </div>

                                            <div class="md:col-span-4">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                                                <input type="number" name="amount_details[]" value="{{ old('amount_details.' . $index, $cashVoucher->amount_details[$index] ?? '') }}"
                                                    class="block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
                                                    step="0.01" required>
                                            </div>

                                            <div class="md:col-span-2 flex justify-end">
                                                <button type="button" class="bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700 remove_line">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" id="add_line" class="mt-2 px-4 py-1 bg-blue-500 text-white rounded shadow-sm">Add Request Line</button>
                    </fieldset>

                    <!-- Remarks -->
                    <fieldset class="mb-6 border border-gray-300 rounded p-4">
                        <legend class="text-blue-600 font-bold text-sm px-2">Remarks</legend>
                        <div id="remarks_fields" class="space-y-2">
                            @foreach($cashVoucher->remarks as $remark)
                                <div class="input-group mb-2">
                                    <div class="w-full">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                                        <div class="flex space-x-2 mb-2">
                                            
                                            <input type="text" name="remarks[]" value="{{ old('remarks[]', $remark) }}" class="flex-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter remarks">
                                            <button type="button" class="bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700 remove_remarks">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add_remarks" class="mt-2 px-4 py-1 bg-blue-500 text-white rounded shadow-sm">Add Remark</button>
                    </fieldset>

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
    // Line Items – Add new
    document.getElementById('add_line').addEventListener('click', function() {
        const newLineItem = document.createElement('div');
        newLineItem.classList.add('row', 'mb-2', 'align-items-center');

        newLineItem.innerHTML = `
            <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input type="text" name="description[]" class="block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Enter description" required>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <input type="number" name="amount_details[]" class="block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Enter amount" step="0.01" required>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="button" class="bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700 remove_line">Remove</button>
                </div>
            </div>
        `;

        document.getElementById('line_fields').appendChild(newLineItem);
    });

    // Line Items – Remove
    document.getElementById('line_fields').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove_line')) {
            e.target.closest('.row').remove();
        }
    });

    // Remarks – Add
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

    // Remarks – Remove
    document.getElementById('remarks_fields').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove_remarks')) {
            e.target.closest('.input-group').remove();
        }
    });

    // Show/hide withholding tax fields based on voucher_type selection
    const taxField = document.getElementById('withholding_tax_container');
    const voucherTypeRadios = document.querySelectorAll('input[name="voucher_type"]');

    function toggleTaxFields(value) {
        taxField.classList.toggle('hidden', value !== 'with_tax');
    }

    voucherTypeRadios.forEach(radio => {
        radio.addEventListener('change', () => toggleTaxFields(radio.value));
    });

    const selectedVoucherType = document.querySelector('input[name="voucher_type"]:checked');
    if (selectedVoucherType) toggleTaxFields(selectedVoucherType.value);

    // Show/hide truck field based on CVR type (Admin vs RPM)
    const truckField = document.getElementById('truck_field_container');
    const truckId = document.getElementById('truck_id');
    const cvrTypeRadios = document.querySelectorAll('input[name="cvr_type"]');

    function toggleTruckField() {
        const selectedType = document.querySelector('input[name="cvr_type"]:checked').value;
        if (selectedType === 'RPM') {
            truckField.classList.remove('hidden');
        } else {
            truckField.classList.add('hidden');
            truckId.value = '';
        }
    }

    cvrTypeRadios.forEach(radio => {
        radio.addEventListener('change', toggleTruckField);
    });

    toggleTruckField(); // Run on page load
});
</script>

@endsection
