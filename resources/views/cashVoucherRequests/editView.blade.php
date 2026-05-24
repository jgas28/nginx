@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
<style>
    .searchable-select-source {
        position: absolute;
        left: -9999px;
        opacity: 0;
        pointer-events: none;
    }
    .searchable-select-panel::-webkit-scrollbar {
        width: 6px;
    }
    .searchable-select-panel::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }
    #requestor + [data-searchable-select-wrapper] {
        width: 100%;
    }
    #requestor + [data-searchable-select-wrapper] [data-searchable-select-panel] {
        right: 0 !important;
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
    }
    #requestor + [data-searchable-select-wrapper] [data-searchable-select-panel] .relative > span {
        left: 0.95rem;
    }
    #requestor + [data-searchable-select-wrapper] [data-searchable-select-input] {
        padding-left: 3rem !important;
    }
    #requestor + [data-searchable-select-wrapper] [data-searchable-select-list] {
        max-height: 18rem;
        overflow-y: auto;
        overflow-x: hidden;
    }
    #requestor + [data-searchable-select-wrapper] [data-searchable-select-list]::-webkit-scrollbar {
        width: 10px;
    }
    #requestor + [data-searchable-select-wrapper] [data-searchable-select-list]::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 9999px;
        border: 2px solid #ffffff;
    }
    #requestor + [data-searchable-select-wrapper] [data-searchable-select-list] > button > span:first-child {
        width: auto !important;
        height: auto !important;
        border-radius: 0 !important;
        white-space: nowrap !important;
        display: block !important;
        flex: 1 1 auto !important;
    }
</style>
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-blue-600 text-white text-center py-4">
        <h4 class="text-xl font-semibold flex items-center justify-center gap-2">
            <!-- Heroicon: Document -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/>
            </svg>
            Cash Voucher Request
        </h4> 
    </div>
    <div class="p-6">
        <form action="{{ route('cashVoucherRequests.cvrUpdate', ['id' => $deliveryRequestId]) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- CVR Info -->
            <fieldset class="mb-6 p-4 border border-gray-200 rounded bg-gray-50">
                <legend class="text-blue-600 font-semibold text-sm mb-3 flex items-center gap-1">
                    <!-- Info Icon -->
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                    CVR Information
                </legend>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="cvr_number" class="block text-sm font-medium text-gray-700">CVR Number</label>
                        <input type="text" name="cvr_number" id="cvr_number" class="mt-1 block w-full rounded border-gray-300 shadow-sm bg-gray-100" value="{{ $cashVouchers->cvr_number }}" readonly>
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                        <input type="number" step="0.01" min="0" required name="amount" id="amount"
                               class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                               value="{{ old('amount', $cashVouchers->amount ?? '') }}">
                    </div>

                    <div>
                        <label for="request_type" class="block text-sm font-medium text-gray-700">Request Type</label>
                        <select name="request_type" id="request_type" required class="mt-1 block w-full rounded border-gray-300 shadow-sm">
                            <option value="" disabled {{ old('request_type', $cashVouchers->request_type_id ?? '') == '' ? 'selected' : '' }}>Select Type</option>
                            @foreach($requestType as $requestTypes)
                                <option value="{{ $requestTypes->id }}"
                                    {{ old('request_type', $cashVouchers->request_type ?? '') == $requestTypes->id ? 'selected' : '' }}>
                                    {{ $requestTypes->request_type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="requestor" class="block text-sm font-medium text-gray-700">Requestor</label>
                        <select name="requestor" id="requestor" required class="mt-1 block w-full rounded border-gray-300 shadow-sm">
                            <option value="" disabled {{ old('requestor', $cashVouchers->requestor ?? '') == '' ? 'selected' : '' }}>Select Requestor</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}"
                                    {{ old('requestor', $cashVouchers->requestor ?? '') == $employee->id ? 'selected' : '' }}>
                                    {{ trim(($employee->fname ?? '') . ' ' . ($employee->lname ?? '')) }}{{ !empty($employee->employee_code) ? ' - ' . $employee->employee_code : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </fieldset>

            <!-- Remarks -->
            <fieldset class="mb-6 p-4 border border-gray-200 rounded bg-gray-50">
                <legend class="text-blue-600 font-semibold text-sm mb-3 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2M7 8H5a2 2 0 00-2 2v6a2 2 0 002 2h2m10-4H7"/>
                    </svg>
                    Remarks Information
                </legend>
                <div id="remarks_fields" class="space-y-2">
                    @if(!empty($remarks))
                        @foreach($remarks as $remark)
                            <div class="flex gap-2">
                                <input type="text" name="remarks[]" class="flex-1 border rounded px-3 py-2" value="{{ $remark }}" placeholder="Enter Remarks value">
                                <button type="button" class="remove_remarks bg-red-500 text-white text-sm px-3 py-1 rounded">Remove</button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" id="add_remarks" class="mt-3 text-blue-600 text-sm hover:underline">
                    + Add Remarks
                </button>
            </fieldset>

            <!-- Submit -->
            <div class="text-center">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded shadow hover:bg-green-700 transition">
                    Update Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addRemarksBtn = document.getElementById('add_remarks');
        const remarksContainer = document.getElementById('remarks_fields');

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
            wrapper.className = 'relative mt-1';
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
                    <div data-searchable-select-list class="p-2"></div>
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

        if (addRemarksBtn && remarksContainer) {
            addRemarksBtn.addEventListener('click', function () {
                const div = document.createElement('div');
                div.className = 'flex gap-2 mt-2';
                div.innerHTML = `
                    <input type="text" name="remarks[]" class="flex-1 border rounded px-3 py-2" placeholder="Enter Remarks value">
                    <button type="button" class="remove_remarks bg-red-500 text-white text-sm px-3 py-1 rounded">Remove</button>
                `;
                remarksContainer.appendChild(div);
            });

            remarksContainer.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove_remarks')) {
                    e.target.parentElement.remove();
                }
            });
        }
    });
</script>
@endsection
