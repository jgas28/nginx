@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 py-8">
    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-700 ring-1 ring-violet-100">
                    <i class="fas fa-gas-pump text-sm"></i>
                    RPM Report
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">RPM Cash Voucher Report</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Track RPM vouchers with faster filters, searchable tables, and a cleaner report layout.
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('reports.rpm') }}" method="GET" id="rpm-report-filters" class="space-y-5">
            <div class="flex flex-wrap items-end gap-4 xl:flex-nowrap">
                <div class="min-w-[180px] flex-1">
                    <label for="start_date" class="mb-1.5 block text-sm font-semibold text-slate-700">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') ?: \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}" class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100">
                </div>
                <div class="min-w-[180px] flex-1">
                    <label for="end_date" class="mb-1.5 block text-sm font-semibold text-slate-700">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') ?: \Carbon\Carbon::now()->format('Y-m-d') }}" class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100">
                </div>
                <div class="min-w-[180px] flex-1">
                    <label for="status" class="mb-1.5 block text-sm font-semibold text-slate-700">Status</label>
                    <select id="status" name="status" class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100">
                        <option value="">All Statuses</option>
                        <option value="For Approval" {{ request('status') == 'For Approval' ? 'selected' : '' }}>For Approval</option>
                        <option value="For Liquidation" {{ request('status') == 'For Liquidation' ? 'selected' : '' }}>For Liquidation</option>
                        <option value="For Validation" {{ request('status') == 'For Validation' ? 'selected' : '' }}>For Validation</option>
                        <option value="For Collection" {{ request('status') == 'For Collection' ? 'selected' : '' }}>For Collection</option>
                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="min-w-[180px] flex-1">
                    <label for="cvr_type" class="mb-1.5 block text-sm font-semibold text-slate-700">CVR Type</label>
                    <select id="cvr_type" name="cvr_type" class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100">
                        <option value="">All Types</option>
                        @foreach ($cvrTypes as $cvrType)
                            <option value="{{ $cvrType->id }}" {{ request('cvr_type') == $cvrType->id ? 'selected' : '' }}>
                                {{ $cvrType->request_type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[220px] flex-[1.15]">
                    <label for="supplier" class="mb-1.5 block text-sm font-semibold text-slate-700">Supplier</label>
                    <select id="supplier" name="supplier" class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100">
                        <option value="">All Suppliers</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->supplier_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-4">
                <button type="submit"
                        class="inline-flex h-14 min-w-[170px] items-center justify-center gap-2 rounded-2xl px-5 text-sm font-semibold text-white shadow-sm transition"
                        style="background-color:#7c3aed;color:#ffffff;">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                            <i class="fas fa-magnifying-glass text-xs"></i>
                        </span>
                        Apply Filters
                </button>
                <a href="{{ route('reports.rpm.export', request()->query()) }}"
                   class="inline-flex h-14 min-w-[178px] items-center justify-center gap-2 rounded-2xl px-5 text-sm font-semibold text-white shadow-sm transition"
                   style="background-color:#059669;color:#ffffff;">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                            <i class="fas fa-file-excel text-xs"></i>
                        </span>
                        Download Excel
                </a>
                <a href="{{ route('reports.rpm') }}"
                   id="resetFilters"
                   class="inline-flex h-14 min-w-[140px] items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-400 hover:text-slate-900">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <i class="fas fa-rotate-left text-xs"></i>
                        </span>
                        Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div id="rpm-report-table"
             data-fast-table
             data-endpoint="{{ route('reports.rpm', request()->query()) }}"
             data-base-endpoint="{{ route('reports.rpm') }}"
             data-search-selector="#rpm-report-search"
             data-per-page-selector="#rpm-report-per-page"
             data-pagination-selector=".rpm-report-pagination a">
            @include('reports.partials.rpmCashVoucher-table', [
                'voucherStatuses' => $voucherStatuses,
                'search' => $search ?? '',
                'perPage' => $perPage ?? 10,
            ])
        </div>
    </div>
</div>

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
</style>

<script>
    (() => {
        const configs = [
            { id: 'status', placeholder: 'All Statuses', icon: 'fa-signal' },
            { id: 'cvr_type', placeholder: 'All Types', icon: 'fa-layer-group' },
            { id: 'supplier', placeholder: 'All Suppliers', icon: 'fa-industry' },
        ];

        function mountSearchableSelect({ id, placeholder, icon }) {
            const select = document.getElementById(id);
            if (!select || select.dataset.searchableMounted === 'true') {
                return;
            }

            select.dataset.searchableMounted = 'true';
            select.classList.add('searchable-select-source');

            const wrapper = document.createElement('div');
            wrapper.dataset.open = 'false';
            wrapper.className = 'relative';
            wrapper.innerHTML = `
                <button type="button" class="flex h-14 w-full items-center gap-3 rounded-2xl border border-slate-300 bg-white px-3 text-left text-sm text-slate-700 shadow-sm transition hover:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-500">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-50 text-violet-600">
                        <i class="fas ${icon} text-sm"></i>
                    </span>
                    <span class="min-w-0 flex-1 truncate" data-select-label></span>
                    <span class="text-slate-400"><i class="fas fa-chevron-down text-xs"></i></span>
                </button>
                <div class="searchable-select-panel absolute left-0 right-0 z-30 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
                    <div class="border-b border-slate-200 p-3">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fas fa-magnifying-glass text-xs"></i>
                            </span>
                            <input type="text" placeholder="Search..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-violet-300 focus:bg-white focus:ring-2 focus:ring-violet-100" data-select-search>
                        </div>
                    </div>
                    <div class="max-h-56 overflow-y-auto p-2" data-select-list></div>
                    <div class="hidden px-4 py-3 text-sm text-slate-500" data-select-empty>No matching options found.</div>
                </div>
            `;

            select.insertAdjacentElement('afterend', wrapper);

            const trigger = wrapper.querySelector('button');
            const panel = wrapper.querySelector('.searchable-select-panel');
            const label = wrapper.querySelector('[data-select-label]');
            const searchInput = wrapper.querySelector('[data-select-search]');
            const list = wrapper.querySelector('[data-select-list]');
            const emptyState = wrapper.querySelector('[data-select-empty]');

            function closePanel() {
                wrapper.dataset.open = 'false';
                panel.classList.add('hidden');
            }

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
                    button.className = `flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition ${option.selected ? 'bg-violet-50 text-violet-700' : 'text-slate-700 hover:bg-slate-100'}`;
                    button.innerHTML = `
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${option.selected ? 'bg-violet-100 text-violet-600' : 'bg-slate-100 text-slate-500'}">
                            <i class="fas ${icon} text-xs"></i>
                        </span>
                        <span class="min-w-0 flex-1 truncate">${option.textContent.trim()}</span>
                        ${option.selected ? '<i class="fas fa-check text-xs text-violet-500"></i>' : ''}
                    `;

                    button.addEventListener('click', () => {
                        select.value = option.value;
                        updateLabel();
                        renderOptions(searchInput.value);
                        closePanel();
                    });

                    list.appendChild(button);
                });

                emptyState.classList.toggle('hidden', visibleCount !== 0);
            }

            trigger.addEventListener('click', () => {
                const shouldOpen = wrapper.dataset.open !== 'true';
                wrapper.dataset.open = shouldOpen ? 'true' : 'false';
                panel.classList.toggle('hidden', !shouldOpen);

                if (shouldOpen) {
                    searchInput.value = '';
                    renderOptions();
                    setTimeout(() => searchInput.focus(), 0);
                }
            });

            searchInput.addEventListener('input', () => renderOptions(searchInput.value));

            document.addEventListener('click', (event) => {
                if (!wrapper.contains(event.target)) {
                    closePanel();
                }
            });

            updateLabel();
            renderOptions();
        }

        configs.forEach(mountSearchableSelect);
    })();
</script>
@endsection
