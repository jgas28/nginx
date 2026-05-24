@extends('layouts.app')

@section('title', 'Edit CVR')

@section('content')
@php
    $primaryLineItem = $deliveryLineItems->first();
    $headerMtm = $cashVoucher->mtm ?: $primaryLineItem?->mtm ?: $deliveryRequest?->mtm;
    $headerCompany = $deliveryRequest?->company?->company_name ?: $cashVoucher->company?->company_name;
    $headerDeliveryType = $deliveryRequest?->delivery_type;
    $existingRemarks = old('remarks', json_decode($cashVoucher->remarks, true) ?? []);
@endphp

<style>
    .btn-check:checked + .btn-cvr {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }
</style>

<div class="bg-white shadow-lg rounded-lg">
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white text-center py-4 rounded-t-lg">
        <h3 class="text-lg font-bold">Delivery Information</h3>
    </div>

    <div class="p-6">
        <div class="flex flex-wrap justify-center mb-6 text-center">
            <div class="w-full md:w-1/3 mb-4">
                <p class="text-blue-600 uppercase font-semibold">MTM Number</p>
                <p class="text-gray-600">{{ $headerMtm ?: 'N/A' }}</p>
            </div>
            <div class="w-full md:w-1/3 mb-4">
                <p class="text-blue-600 uppercase font-semibold">Company</p>
                <p class="text-gray-600">{{ $headerCompany ?: 'N/A' }}</p>
            </div>
            <div class="w-full md:w-1/3">
                <p class="text-blue-600 uppercase font-semibold">Delivery Type</p>
                <p class="text-gray-600">{{ $headerDeliveryType ?: 'N/A' }}</p>
            </div>
        </div>

        @forelse($deliveryLineItems as $deliveryLineItem)
            <div class="bg-gray-50 border rounded-lg p-4 mb-4">
                <div class="flex flex-wrap justify-between text-center">
                    <div class="w-full md:w-1/3 mb-3">
                        <p class="text-blue-600 uppercase font-semibold">Site Name</p>
                        <p class="text-gray-800">
                            {{ is_array($deliveryLineItem->site_name) ? implode(', ', $deliveryLineItem->site_name) : str_replace(['"'], '', (string) $deliveryLineItem->site_name) }}
                        </p>
                    </div>
                    <div class="w-full md:w-1/3 mb-3">
                        <p class="text-blue-600 uppercase font-semibold">Delivery Number</p>
                        <p class="text-gray-800">
                            {{ is_array($deliveryLineItem->delivery_number) ? implode(', ', $deliveryLineItem->delivery_number) : str_replace(['"'], '', (string) $deliveryLineItem->delivery_number) }}
                        </p>
                    </div>
                    <div class="w-full md:w-1/3">
                        <p class="text-blue-600 uppercase font-semibold">Delivery Address</p>
                        <p class="text-gray-800">
                            {{ is_array($deliveryLineItem->delivery_address) ? implode(', ', $deliveryLineItem->delivery_address) : str_replace(['"'], '', (string) $deliveryLineItem->delivery_address) }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-center text-sm text-amber-800">
                No active delivery line items were found for this cash voucher. You can still update the CVR details below.
            </div>
        @endforelse
    </div>
</div>

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
                        <input
                            type="number"
                            name="tax_base_amount"
                            class="input w-full"
                            placeholder="Enter base amount"
                            value="{{ old('tax_base_amount', $cashVoucher->tax_based_amount) }}"
                            step="0.01"
                        >
                        <label class="block text-sm text-gray-600 mt-1">Tax Base Amount</label>
                    </div>
                </div>
            </fieldset>

            <fieldset class="bg-gray-100 p-4 rounded">
                <legend class="text-blue-700 font-semibold text-sm uppercase mb-3">CVR Information</legend>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <input type="text" name="cvr_number" value="{{ $cashVoucher->cvr_number }}" readonly class="input bg-gray-100 w-full">
                        <label class="block text-sm text-gray-600 mt-1">CVR Number</label>
                    </div>

                    <div>
                        <input type="number" name="amount" step="0.01" min="0" required class="input w-full" value="{{ old('amount', $cashVoucher->amount) }}">
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
                        <select name="requestor" id="requestor" class="input w-full" required>
                            <option value="">Select Requestor</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $cashVoucher->requestor == $emp->id ? 'selected' : '' }}>
                                    {{ trim(($emp->fname ?? '') . ' ' . ($emp->lname ?? '')) }}{{ !empty($emp->employee_code) ? ' - ' . $emp->employee_code : '' }}
                                </option>
                            @endforeach
                        </select>
                        <label class="block text-sm text-gray-600 mt-1">Requestor</label>
                    </div>
                </div>
            </fieldset>

            <fieldset class="bg-gray-100 p-4 rounded">
                <legend class="text-blue-700 font-semibold text-sm uppercase mb-3">Remarks</legend>
                <div id="remarks_fields" class="space-y-2">
                    @foreach($existingRemarks as $remark)
                        <div class="flex gap-2 items-center">
                            <input type="text" name="remarks[]" value="{{ $remark }}" class="form-input w-full rounded border-gray-300">
                            <button type="button" class="text-red-600 hover:text-red-800 font-bold remove_remarks">x</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add_remarks" class="text-blue-600 hover:underline text-sm mt-2">
                    + Add Remarks
                </button>
            </fieldset>

            <div class="text-center mt-6">
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg shadow-lg font-semibold">
                    <i class="bi bi-save"></i> Update CVR
                </button>
            </div>
        </form>
    </div>
</div>

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

    .searchable-select-source {
        position: absolute;
        left: -9999px;
        opacity: 0;
        pointer-events: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const taxField = document.getElementById('withholding_tax_container');
        const taxBaseField = document.getElementById('tax_base_container');
        const taxSelect = document.querySelector('select[name="withholding_tax"]');
        const voucherTypeSelect = document.getElementById('voucher_type_select');

        function toggleTaxFields(value) {
            const showTax = value === 'with_tax';

            taxField.classList.toggle('hidden', !showTax);
            taxBaseField.classList.toggle('hidden', !showTax);

            if (!showTax) {
                taxSelect.value = '';
                const taxBaseInput = taxBaseField.querySelector('input[name="tax_base_amount"]');
                if (taxBaseInput) {
                    taxBaseInput.value = '';
                }
            }
        }

        voucherTypeSelect.addEventListener('change', function () {
            toggleTaxFields(this.value);
        });

        taxSelect.addEventListener('change', function () {
            const selected = this.value !== '';
            taxBaseField.classList.toggle('hidden', !selected);
        });

        toggleTaxFields(voucherTypeSelect.value);

        const remarksFields = document.getElementById('remarks_fields');

        document.getElementById('add_remarks').addEventListener('click', function () {
            const newRemarksField = document.createElement('div');
            newRemarksField.classList.add('flex', 'gap-2', 'items-center');

            newRemarksField.innerHTML = `
                <input type="text" name="remarks[]" class="form-input w-full rounded border-gray-300" placeholder="Enter Remarks">
                <button type="button" class="text-red-600 hover:text-red-800 font-bold remove_remarks">x</button>
            `;

            remarksFields.appendChild(newRemarksField);
        });

        remarksFields.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove_remarks')) {
                e.target.closest('div').remove();
            }
        });

        function closeAllSearchableSelects() {
            document.querySelectorAll('[data-searchable-select-wrapper]').forEach((wrapper) => {
                wrapper.dataset.open = 'false';
                wrapper.querySelector('[data-searchable-select-panel]')?.classList.add('hidden');
            });
        }

        function enhanceSearchableSelect(select, options = {}) {
            if (!select || select.dataset.searchableEnhanced === 'true') {
                return;
            }

            const placeholder = options.placeholder || select.options[0]?.text || 'Select option';
            select.dataset.searchableEnhanced = 'true';
            select.classList.add('searchable-select-source');

            const wrapper = document.createElement('div');
            wrapper.className = 'relative';
            wrapper.dataset.searchableSelectWrapper = 'true';
            wrapper.dataset.open = 'false';

            wrapper.innerHTML = `
                <button type="button" class="searchable-select-trigger flex w-full items-center gap-3 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-left text-sm text-slate-700 shadow-sm transition hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <span class="min-w-0 flex-1 truncate" data-searchable-select-label></span>
                    <span class="text-slate-400"><i class="fas fa-chevron-down text-xs"></i></span>
                </button>
                <div data-searchable-select-panel class="searchable-select-panel absolute left-0 right-0 z-30 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
                    <div class="border-b border-slate-200 p-3">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fas fa-magnifying-glass text-xs"></i>
                            </span>
                            <input type="text" data-searchable-select-input placeholder="Search requestor..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-blue-300 focus:bg-white focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div data-searchable-select-list class="max-h-56 overflow-y-auto p-2"></div>
                    <div data-searchable-select-empty class="hidden px-4 py-3 text-sm text-slate-500">No matching requestors found.</div>
                </div>
            `;

            select.insertAdjacentElement('afterend', wrapper);

            const trigger = wrapper.querySelector('button');
            const panel = wrapper.querySelector('[data-searchable-select-panel]');
            const searchInput = wrapper.querySelector('[data-searchable-select-input]');
            const list = wrapper.querySelector('[data-searchable-select-list]');
            const emptyState = wrapper.querySelector('[data-searchable-select-empty]');
            const label = wrapper.querySelector('[data-searchable-select-label]');

            const renderOptions = (filter = '') => {
                const selectedValue = select.value;
                const term = filter.trim().toLowerCase();
                const optionsMarkup = Array.from(select.options)
                    .filter((option) => option.value !== '')
                    .filter((option) => option.text.toLowerCase().includes(term))
                    .map((option) => `
                        <button type="button" class="searchable-select-option flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm transition hover:bg-blue-50 ${option.value === selectedValue ? 'bg-blue-50 text-blue-700' : 'text-slate-700'}" data-value="${option.value}">
                            <span class="truncate">${option.text}</span>
                            ${option.value === selectedValue ? '<i class="fas fa-check text-xs text-blue-600"></i>' : ''}
                        </button>
                    `)
                    .join('');

                list.innerHTML = optionsMarkup;
                emptyState.classList.toggle('hidden', optionsMarkup !== '');
            };

            const syncLabel = () => {
                const selectedOption = select.options[select.selectedIndex];
                label.textContent = selectedOption && selectedOption.value ? selectedOption.text : placeholder;
            };

            trigger.addEventListener('click', () => {
                const shouldOpen = wrapper.dataset.open !== 'true';
                closeAllSearchableSelects();
                wrapper.dataset.open = shouldOpen ? 'true' : 'false';
                panel.classList.toggle('hidden', !shouldOpen);
                if (shouldOpen) {
                    renderOptions(searchInput.value);
                    searchInput.focus();
                    searchInput.select();
                }
            });

            searchInput.addEventListener('input', () => renderOptions(searchInput.value));

            list.addEventListener('click', (event) => {
                const optionButton = event.target.closest('[data-value]');
                if (!optionButton) {
                    return;
                }

                select.value = optionButton.dataset.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                syncLabel();
                renderOptions(searchInput.value);
                wrapper.dataset.open = 'false';
                panel.classList.add('hidden');
            });

            select.addEventListener('change', () => {
                syncLabel();
                renderOptions(searchInput.value);
            });

            document.addEventListener('click', (event) => {
                if (!wrapper.contains(event.target)) {
                    wrapper.dataset.open = 'false';
                    panel.classList.add('hidden');
                }
            });

            syncLabel();
            renderOptions();
        }

        enhanceSearchableSelect(document.getElementById('requestor'), {
            placeholder: 'Select Requestor'
        });
    });
</script>
@endsection
