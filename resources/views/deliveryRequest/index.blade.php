@extends('layouts.app')

@section('title', 'Delivery Request')

@section('content')
<div class="mx-auto max-w-7xl space-y-4 py-6">
    <div class="rounded-[28px] border border-slate-200 bg-white px-5 py-5 shadow-sm sm:px-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-50 to-cyan-50 px-3.5 py-1.5 text-xs font-semibold text-blue-700 ring-1 ring-blue-100 shadow-sm">
                    <i class="fas fa-truck-ramp-box"></i>
                    Delivery Requests
                </div>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Delivery Request Queue</h1>
                <p class="mt-1.5 max-w-3xl text-sm leading-6 text-slate-500">
                    Manage delivery requests in a faster JSON-powered table with cleaner filters, searchable controls, and aligned actions.
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-4 shadow-sm">
        <form action="{{ route('deliveryRequest.index') }}" method="GET" id="delivery-request-filter-form" class="grid gap-3 lg:grid-cols-2 xl:grid-cols-[minmax(0,1fr),minmax(0,1fr),auto,auto]">
            <div class="min-w-0">
                <label for="company_id" class="mb-1 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Company</label>
                <select
                    name="company_id"
                    id="company_id"
                    data-placeholder="All Companies"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                >
                    <option value="">All Companies</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ (string) $companyId === (string) $company->id ? 'selected' : '' }}>
                            {{ $company->company_code }} - {{ $company->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-0">
                <label for="delivery_status" class="mb-1 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Delivery Status</label>
                <select
                    name="delivery_status"
                    id="delivery_status"
                    data-placeholder="All Statuses"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                >
                    <option value="">All Statuses</option>
                    @foreach ($deliveryStatuses as $status)
                        <option value="{{ $status->id }}" {{ (string) $deliveryStatusId === (string) $status->id ? 'selected' : '' }}>
                            {{ $status->status_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap items-end gap-2 lg:col-span-1 xl:col-span-1">
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15 shrink-0">
                        <i class="fas fa-filter text-xs"></i>
                    </span>
                    Apply Filters
                </button>
                <a href="{{ route('deliveryRequest.index') }}" id="delivery-request-filter-reset" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-900">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 shrink-0">
                        <i class="fas fa-rotate-left text-xs"></i>
                    </span>
                    Reset
                </a>
            </div>

            <div class="flex items-end lg:justify-start xl:justify-end">
                <a href="{{ route('deliveryRequest.create') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 hover:shadow-blue-600/30 sm:w-auto">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15 shrink-0">
                        <i class="fas fa-plus text-xs"></i>
                    </span>
                    Create Delivery Request
                </a>
            </div>
        </form>
    </div>

    <div
        id="delivery-request-table"
        class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm"
        data-fast-table
        data-endpoint="{{ route('deliveryRequest.index', array_filter(['company_id' => $companyId, 'delivery_status' => $deliveryStatusId], fn ($value) => $value !== null && $value !== '')) }}"
        data-base-endpoint="{{ route('deliveryRequest.index') }}"
        data-search-selector="#delivery-request-search"
        data-per-page-selector="#delivery-request-per-page"
        data-pagination-selector=".delivery-request-pagination a"
    >
        @include('deliveryRequest.partials.index-table', [
            'deliveryRequests' => $deliveryRequests,
            'search' => $search,
            'perPage' => $perPage,
            'overview' => $overview,
        ])
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
@endsection

@section('scripts')
<script>
    (() => {
        const form = document.getElementById('delivery-request-filter-form');
        const table = document.getElementById('delivery-request-table');
        const resetLink = document.getElementById('delivery-request-filter-reset');
        const selectIds = ['company_id', 'delivery_status'];

        if (!form || !table) {
            return;
        }

        const baseEndpoint = table.dataset.baseEndpoint || form.action;

        async function loadFilteredTable() {
            const url = new URL(baseEndpoint, window.location.origin);

            selectIds.forEach((id) => {
                const select = document.getElementById(id);
                if (select && select.value) {
                    url.searchParams.set(id, select.value);
                }
            });

            table.dataset.endpoint = url.toString();
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
                console.error('Error loading delivery requests:', error);
            } finally {
                table.classList.remove('opacity-60', 'pointer-events-none');
            }
        }

        function initSearchableSelect(selectId, iconClass, emptyText) {
            const select = document.getElementById(selectId);

            if (!select || select.dataset.searchableReady === 'true') {
                return;
            }

            select.dataset.searchableReady = 'true';
            select.classList.add('searchable-select-source');

            const wrapper = document.createElement('div');
            wrapper.dataset.open = 'false';
            wrapper.className = 'relative mt-1';
            wrapper.innerHTML = `
                <button type="button" class="flex w-full items-center gap-3 rounded-2xl border border-slate-300 bg-white px-3 py-3 text-left text-sm text-slate-700 shadow-sm transition hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                        <i class="fas ${iconClass} text-sm"></i>
                    </span>
                    <span class="min-w-0 flex-1 truncate" data-select-label></span>
                    <span class="text-slate-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </span>
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
                    <div class="hidden px-4 py-3 text-sm text-slate-500" data-select-empty>${emptyText}</div>
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
                label.textContent = selectedOption && selectedOption.value !== '' ? selectedOption.textContent.trim() : (select.dataset.placeholder || 'Select');
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
                            <i class="fas ${iconClass} text-xs"></i>
                        </span>
                        <span class="min-w-0 flex-1 truncate">${option.textContent.trim()}</span>
                        ${option.selected ? '<i class="fas fa-check text-xs text-indigo-500"></i>' : ''}
                    `;

                    button.addEventListener('click', () => {
                        select.value = option.value;
                        updateLabel();
                        renderOptions(searchInput.value);
                        closePanel();
                        loadFilteredTable();
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

        initSearchableSelect('company_id', 'fa-building', 'No matching companies found.');
        initSearchableSelect('delivery_status', 'fa-signal', 'No matching statuses found.');

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            loadFilteredTable();
        });

        if (resetLink) {
            resetLink.addEventListener('click', (event) => {
                event.preventDefault();
                window.location.href = baseEndpoint;
            });
        }
    })();
</script>
@endsection
