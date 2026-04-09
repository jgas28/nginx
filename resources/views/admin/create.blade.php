@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
<style>
    #admin-create-form .searchable-select-source {
        position: absolute;
        left: -9999px;
        opacity: 0;
        pointer-events: none;
    }

    #admin-create-form .searchable-select-panel::-webkit-scrollbar {
        width: 6px;
    }

    #admin-create-form .searchable-select-panel::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }

    #admin-create-form [data-searchable-select-wrapper] {
        z-index: 1;
    }

    #admin-create-form [data-searchable-select-wrapper][data-open="true"] {
        z-index: 90;
    }

    #admin-create-form input[type="radio"].peer:checked + span {
        border-color: #2563eb !important;
        background: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 10px 25px -15px rgba(37, 99, 235, 0.9);
    }

    #admin-create-form input[type="radio"].peer:checked + span i {
        color: #ffffff !important;
    }
</style>

<div class="mx-auto max-w-7xl space-y-6 py-8" id="admin-create-form">
    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-base font-semibold text-blue-700 ring-1 ring-blue-100">
                    <i class="fas fa-file-circle-plus text-sm"></i>
                    New Admin Voucher
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Create Admin Cash Voucher</h1>
                <p class="mt-2 max-w-3xl text-base leading-7 text-slate-500">
                    Create an internal or RPM cash voucher with cleaner sections, searchable lists, and balanced request details.
                </p>
            </div>
            <a href="{{ route('admin.index') }}"
               class="inline-flex h-12 items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-6 text-base font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:text-slate-900">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                    <i class="fas fa-arrow-left text-xs"></i>
                </span>
                Back to Admin Vouchers
            </a>
        </div>
    </div>

    <form action="{{ route('admin.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                            <i class="fas fa-sitemap"></i>
                        </span>
                        <div>
                            <div class="text-lg font-semibold text-slate-900">Voucher Source</div>
                            <div class="text-base text-slate-500">Choose whether this request is for Admin or RPM.</div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="cvr_type" value="admin" checked class="peer sr-only">
                            <span class="flex h-12 items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 text-[18px] font-semibold text-slate-700 transition peer-checked:border-blue-300 peer-checked:bg-blue-50 peer-checked:text-blue-700">
                                <i class="fas fa-building-shield text-base"></i>
                                Admin
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="cvr_type" value="rpm" class="peer sr-only">
                            <span class="flex h-12 items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 text-[18px] font-semibold text-slate-700 transition peer-checked:border-blue-300 peer-checked:bg-blue-50 peer-checked:text-blue-700">
                                <i class="fas fa-gas-pump text-base"></i>
                                RPM
                            </span>
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                            <i class="fas fa-file-signature"></i>
                        </span>
                        <div>
                            <div class="text-lg font-semibold text-slate-900">Voucher Computation</div>
                            <div class="text-base text-slate-500">Pick regular computation or enable withholding tax.</div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="voucher_type" value="regular" checked class="peer sr-only">
                            <span class="flex h-12 items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 text-[18px] font-semibold text-slate-700 transition peer-checked:border-violet-300 peer-checked:bg-violet-50 peer-checked:text-violet-700">
                                <i class="fas fa-receipt text-base"></i>
                                Regular
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="voucher_type" value="with_tax" class="peer sr-only">
                            <span class="flex h-12 items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 text-[18px] font-semibold text-slate-700 transition peer-checked:border-violet-300 peer-checked:bg-violet-50 peer-checked:text-violet-700">
                                <i class="fas fa-percent text-base"></i>
                                W/ Tax
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div id="tax-section" class="hidden rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-scale-balanced"></i>
                </span>
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Tax Details</h2>
                    <p class="text-base text-slate-500">Provide the tax base amount and select the withholding setup.</p>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="tax_base_amount" class="mb-1.5 block text-[18px] font-semibold text-slate-700">Tax Base Amount</label>
                    <input type="number" name="tax_base_amount" id="tax_base_amount" step="0.01"
                           class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-5 text-[20px] text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div>
                    <label for="withholding_tax" class="mb-1.5 block text-[18px] font-semibold text-slate-700">Withholding Tax</label>
                    <select name="withholding_tax" id="withholding_tax"
                            class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-5 text-[20px] text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Select Withholding Tax</option>
                        @foreach($taxes as $tax)
                            <option value="{{ $tax->id }}">{{ $tax->description }}% - {{ $tax->percentage }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                    <i class="fas fa-circle-info"></i>
                </span>
                <div>
                    <h2 class="text-[32px] font-semibold text-slate-900">Voucher Information</h2>
                    <p class="text-base text-slate-500">All text inputs and select pickers are aligned to one consistent size.</p>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="cvr_number" class="mb-1.5 block text-[18px] font-semibold text-slate-700">CVR Number</label>
                    <input type="text" name="cvr_number" id="cvr_number" readonly
                           class="h-[58px] w-full rounded-2xl border border-slate-300 bg-slate-100 px-5 text-[20px] text-slate-700 shadow-sm">
                </div>
                <div>
                    <label for="company_id" class="mb-1.5 block text-[18px] font-semibold text-slate-700">Company</label>
                    <select name="company_id" id="company_id"
                            class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-5 text-[20px] text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            required>
                        <option value="">Select Company</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->company_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="supplier_id" class="mb-1.5 block text-[18px] font-semibold text-slate-700">Supplier</label>
                    <select name="supplier_id" id="supplier_id"
                            class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-5 text-[20px] text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="expense_type_id" class="mb-1.5 block text-[18px] font-semibold text-slate-700">Expense Type</label>
                    <select name="expense_type_id" id="expense_type_id"
                            class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-5 text-[20px] text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            required>
                        <option value="">Select Expense Type</option>
                        @foreach($expenseTypes as $expenseType)
                            <option value="{{ $expenseType->id }}">{{ $expenseType->expense_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="request_type" class="mb-1.5 block text-[18px] font-semibold text-slate-700">Request Type</label>
                    <select name="request_type" id="request_type"
                            class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-5 text-[20px] text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            required>
                        <option value="">Select Request Type</option>
                        @foreach($cvrTypes as $cvrType)
                            <option value="{{ $cvrType->id }}">{{ $cvrType->request_type }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="truck-field-container" class="hidden">
                    <label for="truck_id" class="mb-1.5 block text-[18px] font-semibold text-slate-700">Truck</label>
                    <select name="truck_id" id="truck_id"
                            class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-5 text-[20px] text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Select Truck</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">{{ $truck->truck_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                            <i class="fas fa-list-ul"></i>
                        </span>
                        <div>
                            <h2 class="text-[30px] font-semibold text-slate-900">Request Lines</h2>
                            <p class="text-lg text-slate-500">Add one or more request descriptions and amounts.</p>
                        </div>
                    </div>
                    <button type="button" id="add_line"
                            class="inline-flex h-10 items-center gap-2 rounded-2xl bg-blue-600 px-4 text-base font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        <i class="fas fa-plus text-xs"></i>
                        Add Request Line
                    </button>
                </div>
                <div id="line_fields" class="space-y-3"></div>
            </div>

            <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                            <i class="fas fa-note-sticky"></i>
                        </span>
                        <div>
                            <h2 class="text-[30px] font-semibold text-slate-900">Remarks</h2>
                            <p class="text-lg text-slate-500">Add optional notes for reviewers and processors.</p>
                        </div>
                    </div>
                    <button type="button" id="add_remarks"
                            class="inline-flex h-10 items-center gap-2 rounded-2xl bg-blue-600 px-4 text-base font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        <i class="fas fa-plus text-xs"></i>
                        Add Remark
                    </button>
                </div>
                <div id="remarks_fields" class="space-y-3"></div>
            </div>
        </div>

        <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 border-t border-slate-200 pt-4 md:flex-row md:items-center md:justify-between">
                <p class="text-base text-slate-500">Review the voucher source, request type, and all line amounts before submitting.</p>
                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('admin.index') }}"
                       class="inline-flex h-12 min-w-[150px] items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 text-base font-semibold text-slate-600 shadow-sm transition hover:border-slate-400 hover:text-slate-900">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <i class="fas fa-xmark text-xs"></i>
                        </span>
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex h-12 min-w-[200px] items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 text-base font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                            <i class="fas fa-floppy-disk text-xs"></i>
                        </span>
                        Submit Request
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    (() => {
        const companySelect = document.getElementById('company_id');
        const cvrNumberInput = document.getElementById('cvr_number');
        const taxSection = document.getElementById('tax-section');
        const truckFieldContainer = document.getElementById('truck-field-container');
        const voucherTypeRadios = document.querySelectorAll('input[name="voucher_type"]');
        const cvrTypeRadios = document.querySelectorAll('input[name="cvr_type"]');

        const searchableConfigs = [
            { id: 'company_id', placeholder: 'Select Company', icon: 'fa-building' },
            { id: 'supplier_id', placeholder: 'Select Supplier', icon: 'fa-industry' },
            { id: 'expense_type_id', placeholder: 'Select Expense Type', icon: 'fa-receipt' },
            { id: 'request_type', placeholder: 'Select Request Type', icon: 'fa-layer-group' },
            { id: 'truck_id', placeholder: 'Select Truck', icon: 'fa-truck' },
            { id: 'withholding_tax', placeholder: 'Select Withholding Tax', icon: 'fa-percent' },
        ];

        function closeAllSearchableSelects() {
            document.querySelectorAll('[data-searchable-select-wrapper]').forEach((wrapper) => {
                wrapper.dataset.open = 'false';
                wrapper.querySelector('[data-searchable-select-panel]')?.classList.add('hidden');
            });
        }

        function mountSearchableSelect({ id, placeholder, icon }) {
            const select = document.getElementById(id);
            if (!select || select.dataset.searchableMounted === 'true') {
                return;
            }

            select.dataset.searchableMounted = 'true';
            select.classList.add('searchable-select-source');

            const wrapper = document.createElement('div');
            wrapper.dataset.searchableSelectWrapper = 'true';
            wrapper.dataset.open = 'false';
            wrapper.className = 'relative';
            wrapper.innerHTML = `
                <button type="button" class="flex h-[58px] w-full items-center gap-3 rounded-2xl border border-slate-300 bg-white px-5 text-left text-[20px] text-slate-700 shadow-sm transition hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <i class="fas ${icon} text-base"></i>
                    </span>
                    <span class="min-w-0 flex-1 truncate" data-searchable-select-label></span>
                    <span class="text-slate-400"><i class="fas fa-chevron-down text-xs"></i></span>
                </button>
                <div data-searchable-select-panel class="searchable-select-panel absolute left-0 right-0 z-[95] mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
                    <div class="border-b border-slate-200 p-3">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fas fa-magnifying-glass text-sm"></i>
                            </span>
                            <input type="text" data-searchable-select-input placeholder="Search option..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-base text-slate-700 outline-none transition focus:border-blue-300 focus:bg-white focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div data-searchable-select-list class="max-h-56 overflow-y-auto p-2"></div>
                    <div data-searchable-select-empty class="hidden px-4 py-3 text-base text-slate-500">No matching options found.</div>
                </div>
            `;

            select.insertAdjacentElement('afterend', wrapper);

            const trigger = wrapper.querySelector('button');
            const panel = wrapper.querySelector('[data-searchable-select-panel]');
            const searchInput = wrapper.querySelector('[data-searchable-select-input]');
            const list = wrapper.querySelector('[data-searchable-select-list]');
            const emptyState = wrapper.querySelector('[data-searchable-select-empty]');
            const label = wrapper.querySelector('[data-searchable-select-label]');

            function updateLabel() {
                const selectedOption = select.options[select.selectedIndex];
                label.textContent = selectedOption && selectedOption.value !== '' ? selectedOption.textContent.trim() : placeholder;
            }

            function renderOptions(term = '') {
                const normalizedTerm = term.trim().toLowerCase();
                list.innerHTML = '';
                let visibleCount = 0;

                Array.from(select.options).forEach((option) => {
                    if (!option.value && normalizedTerm) {
                        return;
                    }

                    if (normalizedTerm && !option.textContent.toLowerCase().includes(normalizedTerm)) {
                        return;
                    }

                    visibleCount += 1;
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = `flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-base transition ${option.selected ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100'}`;
                    button.innerHTML = `
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${option.selected ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500'}">
                            <i class="fas ${icon} text-sm"></i>
                        </span>
                        <span class="min-w-0 flex-1 truncate">${option.textContent.trim()}</span>
                        ${option.selected ? '<i class="fas fa-check text-xs text-blue-500"></i>' : ''}
                    `;

                    button.addEventListener('click', () => {
                        select.value = option.value;
                        updateLabel();
                        renderOptions(searchInput.value);
                        panel.classList.add('hidden');
                        wrapper.dataset.open = 'false';
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });

                    list.appendChild(button);
                });

                emptyState.classList.toggle('hidden', visibleCount !== 0);
            }

            trigger.addEventListener('click', () => {
                const shouldOpen = wrapper.dataset.open !== 'true';
                closeAllSearchableSelects();
                wrapper.dataset.open = shouldOpen ? 'true' : 'false';
                panel.classList.toggle('hidden', !shouldOpen);

                if (shouldOpen) {
                    searchInput.value = '';
                    renderOptions();
                    setTimeout(() => searchInput.focus(), 0);
                }
            });

            searchInput.addEventListener('input', () => renderOptions(searchInput.value));
            updateLabel();
            renderOptions();
        }

        function addLineRow() {
            const wrapper = document.createElement('div');
            wrapper.className = 'rounded-2xl border border-slate-200 bg-slate-50/70 p-4';
            wrapper.innerHTML = `
                <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_220px_120px] md:items-end">
                    <div>
                        <label class="mb-1.5 block text-[18px] font-semibold text-slate-700">Description</label>
                        <input type="text" name="description[]" class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-5 text-[20px] text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Enter description" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[18px] font-semibold text-slate-700">Amount</label>
                        <input type="number" name="amount_details[]" class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-5 text-[20px] text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="0.00" step="0.01" required>
                    </div>
                    <button type="button" class="remove_line inline-flex h-[58px] items-center justify-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 text-lg font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100">
                        <i class="fas fa-trash text-xs"></i>
                        Remove
                    </button>
                </div>
            `;
            document.getElementById('line_fields').appendChild(wrapper);
        }

        function addRemarkRow() {
            const wrapper = document.createElement('div');
            wrapper.className = 'rounded-2xl border border-slate-200 bg-slate-50/70 p-4';
            wrapper.innerHTML = `
                <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_120px] md:items-end">
                    <div>
                        <label class="mb-1.5 block text-[18px] font-semibold text-slate-700">Remark</label>
                        <input type="text" name="remarks[]" class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-5 text-[20px] text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Enter remark">
                    </div>
                    <button type="button" class="remove_remarks inline-flex h-[58px] items-center justify-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 text-lg font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100">
                        <i class="fas fa-trash text-xs"></i>
                        Remove
                    </button>
                </div>
            `;
            document.getElementById('remarks_fields').appendChild(wrapper);
        }

        function toggleTaxFields() {
            const selected = document.querySelector('input[name="voucher_type"]:checked')?.value;
            taxSection.classList.toggle('hidden', selected !== 'with_tax');
        }

        function toggleTruckField() {
            const selected = document.querySelector('input[name="cvr_type"]:checked')?.value;
            truckFieldContainer.classList.toggle('hidden', selected !== 'rpm');
            if (selected !== 'rpm') {
                document.getElementById('truck_id').value = '';
            }
        }

        function generateCvrNumber() {
            const companyId = companySelect.value;
            if (!companyId) {
                cvrNumberInput.value = '';
                return;
            }

            fetch('/admin/generate-cvr-number', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ company_id: companyId })
            })
            .then((res) => res.json())
            .then((data) => {
                cvrNumberInput.value = data.cvr_number || '';
            })
            .catch((err) => {
                console.error('Error generating CVR number:', err);
            });
        }

        searchableConfigs.forEach(mountSearchableSelect);
        addLineRow();
        addRemarkRow();
        toggleTaxFields();
        toggleTruckField();

        companySelect.addEventListener('change', generateCvrNumber);
        voucherTypeRadios.forEach((radio) => radio.addEventListener('change', toggleTaxFields));
        cvrTypeRadios.forEach((radio) => radio.addEventListener('change', toggleTruckField));

        document.getElementById('add_line').addEventListener('click', addLineRow);
        document.getElementById('add_remarks').addEventListener('click', addRemarkRow);

        document.getElementById('line_fields').addEventListener('click', (event) => {
            if (event.target.closest('.remove_line')) {
                event.target.closest('.rounded-2xl')?.remove();
            }
        });

        document.getElementById('remarks_fields').addEventListener('click', (event) => {
            if (event.target.closest('.remove_remarks')) {
                event.target.closest('.rounded-2xl')?.remove();
            }
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('[data-searchable-select-wrapper]')) {
                closeAllSearchableSelects();
            }
        });
    })();
</script>
@endsection
