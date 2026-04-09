@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 py-8">
    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 ring-1 ring-blue-100">
                    <i class="fas fa-shield-halved text-sm"></i>
                    Admin Liquidation Queue
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Admin Liquidations</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Filter admin and RPM cash vouchers by supplier, then review them in a faster paginated queue.
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('liquidations.indexAdmin') }}" method="GET" id="liquidations-admin-filter-form" class="flex items-end justify-between gap-4">
            <div class="min-w-0 flex-1 max-w-[420px]">
                <label for="supplier_id" class="sr-only">Supplier</label>
                <select
                    name="supplier_id"
                    id="supplier_id"
                    data-placeholder="All Suppliers"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                >
                    <option value="">All Suppliers</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->supplier_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex shrink-0 items-end justify-end gap-3">
                <button type="submit" id="liquidations-admin-filter-submit" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                        <i class="fas fa-magnifying-glass text-xs"></i>
                    </span>
                    Filter Results
                </button>
                <a href="{{ route('liquidations.indexAdmin') }}" id="liquidations-admin-filter-reset" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-900">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <i class="fas fa-rotate-left text-xs"></i>
                    </span>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div id="liquidations-admin-table"
             data-fast-table
             data-endpoint="{{ route('liquidations.indexAdmin', ['supplier_id' => request('supplier_id')]) }}"
             data-base-endpoint="{{ route('liquidations.indexAdmin') }}"
             data-search-selector="#liquidations-admin-search"
             data-per-page-selector="#liquidations-admin-per-page"
             data-pagination-selector=".liquidations-admin-pagination a">
            @include('liquidations.partials.indexAdmin-table', ['data' => $data, 'search' => $search, 'perPage' => $perPage])
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
        const form = document.getElementById('liquidations-admin-filter-form');
        const select = document.getElementById('supplier_id');
        const table = document.getElementById('liquidations-admin-table');
        const resetLink = document.getElementById('liquidations-admin-filter-reset');

        if (!form || !select || !table) {
            return;
        }

        function closePanel() {
            wrapper.dataset.open = 'false';
            panel.classList.add('hidden');
        }

        select.classList.add('searchable-select-source');

        const wrapper = document.createElement('div');
        wrapper.dataset.open = 'false';
        wrapper.className = 'relative w-full max-w-[420px]';
        wrapper.innerHTML = `
            <button type="button" class="flex w-full items-center gap-3 rounded-2xl border border-slate-300 bg-white px-3 py-3 text-left text-sm text-slate-700 shadow-sm transition hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                    <i class="fas fa-industry text-sm"></i>
                </span>
                <span class="min-w-0 flex-1 truncate" data-select-label></span>
                <span class="text-slate-400">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </button>
            <div class="searchable-select-panel absolute left-0 right-0 z-30 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10" style="max-width:420px;">
                <div class="border-b border-slate-200 p-3">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fas fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" placeholder="Search supplier..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-2 focus:ring-indigo-100" data-select-search>
                    </div>
                </div>
                <div class="max-h-56 overflow-y-auto p-2" data-select-list></div>
                <div class="hidden px-4 py-3 text-sm text-slate-500" data-select-empty>No matching suppliers found.</div>
            </div>
        `;

        select.insertAdjacentElement('afterend', wrapper);

        const trigger = wrapper.querySelector('button');
        const panel = wrapper.querySelector('.searchable-select-panel');
        const label = wrapper.querySelector('[data-select-label]');
        const searchInput = wrapper.querySelector('[data-select-search]');
        const list = wrapper.querySelector('[data-select-list]');
        const emptyState = wrapper.querySelector('[data-select-empty]');
        const baseEndpoint = table.dataset.baseEndpoint || form.action;

        async function loadFilteredTable() {
            const url = new URL(baseEndpoint, window.location.origin);
            const supplierId = select.value;

            if (supplierId) {
                url.searchParams.set('supplier_id', supplierId);
            }

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
                console.error('Error loading admin liquidations:', error);
            } finally {
                table.classList.remove('opacity-60', 'pointer-events-none');
            }
        }

        function updateLabel() {
            const selectedOption = select.options[select.selectedIndex];
            label.textContent = selectedOption && selectedOption.value !== '' ? selectedOption.textContent.trim() : 'All Suppliers';
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
                        <i class="fas fa-industry text-xs"></i>
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

            emptyState.classList.toggle('hidden', visibleCount === 0);
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

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            loadFilteredTable();
        });

        if (resetLink) {
            resetLink.addEventListener('click', (event) => {
                event.preventDefault();
                select.value = '';
                updateLabel();
                renderOptions();
                loadFilteredTable();
            });
        }

        updateLabel();
        renderOptions();
    })();
</script>
@endsection
