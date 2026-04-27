@extends('layouts.app')

@section('title', 'Delivery Request Report')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 py-8">
    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 ring-1 ring-indigo-100">
                    <i class="fas fa-truck-fast text-sm"></i>
                    Delivery Request Report
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Delivery Request Report</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Track delivery requests with faster filters, searchable results, and a cleaner report layout.
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <form action="{{ route('reports.dr') }}" method="GET" id="delivery-request-report-filters" class="space-y-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="mtm" class="mb-1.5 block text-[15px] font-semibold text-slate-700">MTM</label>
                    <input type="text" id="mtm" name="mtm" value="{{ request('mtm') }}" class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="date_from" class="mb-1.5 block text-[15px] font-semibold text-slate-700">Start Date</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from', $startOfMonth) }}" class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="date_to" class="mb-1.5 block text-[15px] font-semibold text-slate-700">End Date</label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to', $endOfMonth) }}" class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="delivery_date" class="mb-1.5 block text-[15px] font-semibold text-slate-700">Delivery Date</label>
                    <input type="date" id="delivery_date" name="delivery_date" value="{{ request('delivery_date') }}" class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="area" class="mb-1.5 block text-[15px] font-semibold text-slate-700">Area</label>
                    <select id="area" name="area" class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="">All Areas</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ (string) request('area') === (string) $area->id ? 'selected' : '' }}>
                                {{ $area->area_code }} - {{ $area->area_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="mb-1.5 block text-[15px] font-semibold text-slate-700">Status</label>
                    <select id="status" name="status" class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ (string) request('status') === (string) $status->id ? 'selected' : '' }}>
                                {{ $status->status_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="customer_id" class="mb-1.5 block text-[15px] font-semibold text-slate-700">Customer</label>
                    <select id="customer_id" name="customer_id" class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ (string) request('customer_id') === (string) $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="company_id" class="mb-1.5 block text-[15px] font-semibold text-slate-700">Company</label>
                    <select id="company_id" name="company_id" class="h-[58px] w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ (string) request('company_id') === (string) $company->id ? 'selected' : '' }}>
                                {{ $company->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                <button type="submit" class="inline-flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 sm:min-w-[170px] sm:w-auto">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                        <i class="fas fa-magnifying-glass text-xs"></i>
                    </span>
                    Apply Filters
                </button>
                <a href="{{ route('reports.export', array_filter(array_merge(request()->query(), ['search' => $search ?? '']))) }}" class="inline-flex h-14 w-full items-center justify-center gap-2 rounded-2xl px-5 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 sm:min-w-[178px] sm:w-auto" style="background-color:#059669;color:#ffffff;">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                        <i class="fas fa-file-excel text-xs"></i>
                    </span>
                    Download Excel
                </a>
                <a href="{{ route('reports.dr') }}" id="deliveryRequestResetFilters" class="inline-flex h-14 w-full items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-400 hover:text-slate-900 sm:min-w-[140px] sm:w-auto">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <i class="fas fa-rotate-left text-xs"></i>
                    </span>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div id="delivery-request-report-table"
             data-fast-table
             data-endpoint="{{ route('reports.dr', request()->query()) }}"
             data-base-endpoint="{{ route('reports.dr') }}"
             data-search-selector="#delivery-request-report-search"
             data-per-page-selector="#delivery-request-report-per-page"
             data-pagination-selector=".delivery-request-report-pagination a">
            @include('reports.partials.deliveryRequest-table', [
                'deliveryRequests' => $deliveryRequests,
                'search' => $search,
                'perPage' => $perPage,
                'overview' => $overview,
            ])
        </div>
    </div>
</div>

<style>
    #delivery-request-report-filters input,
    #delivery-request-report-filters select {
        min-width: 0;
    }

    #delivery-request-report-filters .relative {
        min-width: 0;
    }

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

    @media (max-width: 639px) {
        #delivery-request-report-filters {
            gap: 1rem;
        }

        #delivery-request-report-filters label {
            line-height: 1.3;
        }

        #delivery-request-report-filters .searchable-select-panel {
            left: 0;
            right: 0;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    (() => {
        const form = document.getElementById('delivery-request-report-filters');
        const table = document.getElementById('delivery-request-report-table');

        if (!form || !table) {
            return;
        }

        const configs = [
            { id: 'area', placeholder: 'All Areas', icon: 'fa-map-location-dot' },
            { id: 'status', placeholder: 'All Statuses', icon: 'fa-signal' },
            { id: 'customer_id', placeholder: 'All Customers', icon: 'fa-user-group' },
            { id: 'company_id', placeholder: 'All Companies', icon: 'fa-building' },
        ];

        function syncTableEndpoint() {
            const url = new URL(table.dataset.baseEndpoint || form.action, window.location.origin);
            new FormData(form).forEach((value, key) => {
                if (value !== null && String(value).trim() !== '') {
                    url.searchParams.set(key, value);
                }
            });

            table.dataset.endpoint = url.toString();
            return url;
        }

        async function loadFilteredTable() {
            const url = syncTableEndpoint();
            table.classList.add('opacity-60', 'pointer-events-none', 'transition-opacity');

            try {
                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const payload = await response.json();
                table.innerHTML = payload.html || '';
                window.history.replaceState({}, '', url.toString());
            } catch (error) {
                console.error('Error loading delivery request report:', error);
            } finally {
                table.classList.remove('opacity-60', 'pointer-events-none');
            }
        }

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
                <button type="button" class="flex h-14 w-full items-center gap-3 rounded-2xl border border-slate-300 bg-white px-3 text-left text-sm text-slate-700 shadow-sm transition hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
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
                            <input type="text" placeholder="Search..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-2 focus:ring-indigo-100" data-select-search>
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
                    button.className = `flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition ${option.selected ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-100'}`;
                    button.innerHTML = `
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${option.selected ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500'}">
                            <i class="fas ${icon} text-xs"></i>
                        </span>
                        <span class="min-w-0 flex-1 truncate">${option.textContent.trim()}</span>
                        ${option.selected ? '<i class="fas fa-check text-xs text-indigo-500"></i>' : ''}
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

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            loadFilteredTable();
        });

        configs.forEach(mountSearchableSelect);
        syncTableEndpoint();
    })();
</script>
@endsection
