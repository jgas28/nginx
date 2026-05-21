@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 py-8">
    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-100">
                    <i class="fas fa-file-invoice-dollar text-sm"></i>
                    Liquidation Requests
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Delivery Liquidations</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Review delivery-related liquidation requests in a faster server-side table with requestor filtering and quick search.
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('liquidations.index') }}" method="GET" id="liquidations-filter-form" class="flex flex-col items-stretch justify-start gap-4 md:flex-row md:items-end md:justify-between">
            <div class="min-w-0 w-full flex-1 md:max-w-[420px]">
                <label for="requestor" class="mb-1.5 block text-sm font-semibold text-slate-700">Requestor</label>
                <select
                    name="requestor"
                    id="requestor"
                    data-placeholder="All Requestors"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                >
                    <option value="">All Requestors</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('requestor') == $employee->id ? 'selected' : '' }}>
                            {{ trim(($employee->fname ?? '') . ' ' . ($employee->lname ?? '')) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex w-full shrink-0 flex-col items-stretch justify-start gap-3 sm:flex-row sm:items-end sm:justify-end md:w-auto">
                <button type="submit" id="liquidations-filter-submit" class="inline-flex h-[52px] w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-700 sm:min-w-[220px] sm:w-auto">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                            <i class="fas fa-magnifying-glass text-xs"></i>
                        </span>
                        Filter Results
                </button>
                <a href="{{ route('liquidations.index') }}" id="liquidations-filter-reset" class="inline-flex h-[52px] w-full items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-900 sm:min-w-[150px] sm:w-auto">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <i class="fas fa-rotate-left text-xs"></i>
                    </span>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div id="liquidations-table" class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">

        {{-- Toolbar: static — never replaced by AJAX so search stays focusable --}}
        <div class="border-b border-slate-200 px-6 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="relative w-full lg:max-w-sm">
                    <div class="pointer-events-none absolute left-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-blue-50 text-blue-600 shadow-sm">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input
                        type="text"
                        id="liquidations-search"
                        value="{{ $search ?? '' }}"
                        placeholder="Search CVR, company, requestor, expense..."
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-14 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        autocomplete="off"
                    >
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span>Show</span>
                        <select id="liquidations-per-page" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            @foreach([5, 10, 25, 50] as $size)
                                <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span>entries</span>
                    </div>
                    <div id="liquidations-count" class="text-sm text-slate-500">
                        {{ $data->total() }} liquidation requests found
                    </div>
                </div>
            </div>
        </div>

        {{-- Data area wrapper: relative so the loading overlay is scoped to the results --}}
        <div class="relative">
            <div id="liquidations-loading" class="pointer-events-none absolute inset-x-0 top-10 z-20 hidden justify-center">
                <div class="inline-flex items-center gap-2.5 rounded-full border border-slate-200 bg-white px-5 py-2.5 shadow-lg">
                    <svg class="h-4 w-4 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.568 3 7.291l3-2.291z"></path>
                    </svg>
                    <span class="text-sm font-medium text-slate-700">Loading...</span>
                </div>
            </div>
            <div id="liquidations-data">
                @include('liquidations.partials.index-table', ['data' => $data, 'perPage' => $perPage])
            </div>
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
        const form = document.getElementById('liquidations-filter-form');
        const select = document.getElementById('requestor');
        const dataContainer = document.getElementById('liquidations-data');
        const countEl = document.getElementById('liquidations-count');
        const loadingEl = document.getElementById('liquidations-loading');
        const resetLink = document.getElementById('liquidations-filter-reset');

        if (!form || !select || !dataContainer) {
            return;
        }

        const baseEndpoint = '{{ route('liquidations.index') }}';

        // Track current filter state so all controls stay in sync
        let state = {
            requestor: select.value,
            search: '{{ addslashes($search ?? '') }}',
            perPage: {{ (int)($perPage ?? 10) }},
        };

        let searchTimer = null;

        async function loadTableData({ requestor, search, perPage, url } = {}) {
            let requestUrl;

            if (url) {
                requestUrl = new URL(url, window.location.origin);
            } else {
                if (requestor !== undefined) state.requestor = requestor;
                if (search !== undefined) state.search = search;
                if (perPage !== undefined) state.perPage = perPage;

                requestUrl = new URL(baseEndpoint, window.location.origin);
                if (state.requestor) requestUrl.searchParams.set('requestor', state.requestor);
                if (state.search) requestUrl.searchParams.set('search', state.search);
                if (state.perPage && state.perPage !== 10) requestUrl.searchParams.set('per_page', state.perPage);
            }

            // Dim only the data area; toolbar stays fully interactive
            dataContainer.classList.add('opacity-60', 'pointer-events-none', 'transition-opacity');
            if (loadingEl) loadingEl.classList.replace('hidden', 'flex');

            try {
                const response = await fetch(requestUrl.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });
                const payload = await response.json();

                dataContainer.innerHTML = payload.html || '';
                window.history.replaceState({}, '', requestUrl.toString());

                if (countEl && payload.total !== undefined) {
                    countEl.textContent = payload.total + ' liquidation requests found';
                }
            } catch (error) {
                console.error('Error loading liquidations:', error);
            } finally {
                dataContainer.classList.remove('opacity-60', 'pointer-events-none', 'transition-opacity');
                if (loadingEl) loadingEl.classList.replace('flex', 'hidden');
            }
        }

        // --- Searchable requestor select ---

        function closePanel() {
            wrapper.dataset.open = 'false';
            panel.classList.add('hidden');
        }

        select.classList.add('searchable-select-source');

        const wrapper = document.createElement('div');
        wrapper.dataset.open = 'false';
        wrapper.className = 'relative mt-1 w-full shrink-0';
        wrapper.style.maxWidth = '420px';
        wrapper.innerHTML = `
            <button type="button" class="flex w-full items-center gap-3 rounded-2xl border border-slate-300 bg-white px-3 py-3 text-left text-sm text-slate-700 shadow-sm transition hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                    <i class="fas fa-user text-sm"></i>
                </span>
                <span class="min-w-0 flex-1 truncate" data-select-label></span>
                <span class="text-slate-400">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </button>
            <div class="searchable-select-panel absolute left-0 z-30 mt-2 hidden w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10" style="max-width:420px;">
                <div class="border-b border-slate-200 p-3">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fas fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" placeholder="Search requestor..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-2 focus:ring-indigo-100" data-select-search>
                    </div>
                </div>
                <div class="max-h-56 overflow-y-auto p-2" data-select-list></div>
                <div class="hidden px-4 py-3 text-sm text-slate-500" data-select-empty>No matching requestors found.</div>
            </div>
        `;

        select.insertAdjacentElement('afterend', wrapper);

        const panel = wrapper.querySelector('.searchable-select-panel');
        const trigger = wrapper.querySelector('button');
        const label = wrapper.querySelector('[data-select-label]');
        const supplierSearch = wrapper.querySelector('[data-select-search]');
        const list = wrapper.querySelector('[data-select-list]');
        const emptyState = wrapper.querySelector('[data-select-empty]');

        function updateLabel() {
            const selectedOption = select.options[select.selectedIndex];
            label.textContent = selectedOption && selectedOption.value !== '' ? selectedOption.textContent.trim() : 'All Requestors';
        }

        function renderOptions(term = '') {
            const normalizedTerm = term.trim().toLowerCase();
            list.innerHTML = '';
            let visibleCount = 0;

            Array.from(select.options).forEach((option) => {
                if (!option.value && normalizedTerm) return;
                if (normalizedTerm && !option.textContent.toLowerCase().includes(normalizedTerm)) return;

                visibleCount += 1;
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition ${option.selected ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-100'}`;
                button.innerHTML = `
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${option.selected ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500'}">
                        <i class="fas fa-user text-xs"></i>
                    </span>
                    <span class="min-w-0 flex-1 truncate">${option.textContent.trim()}</span>
                    ${option.selected ? '<i class="fas fa-check text-xs text-indigo-500"></i>' : ''}
                `;

                button.addEventListener('click', () => {
                    select.value = option.value;
                    updateLabel();
                    renderOptions(supplierSearch.value);
                    closePanel();
                    loadTableData({ requestor: select.value });
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
                supplierSearch.value = '';
                renderOptions();
                setTimeout(() => supplierSearch.focus(), 0);
            }
        });

        supplierSearch.addEventListener('input', () => renderOptions(supplierSearch.value));

        document.addEventListener('click', (event) => {
            if (!wrapper.contains(event.target)) closePanel();
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            loadTableData({ requestor: select.value });
        });

        if (resetLink) {
            resetLink.addEventListener('click', (event) => {
                event.preventDefault();
                select.value = '';
                updateLabel();
                renderOptions();
                loadTableData({ requestor: '', search: '', perPage: 10 });
            });
        }

        // --- Table search (debounced) ---
        document.addEventListener('input', (event) => {
            if (!event.target.matches('#liquidations-search')) return;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                loadTableData({ search: event.target.value });
            }, 300);
        });

        // --- Per-page selector ---
        document.addEventListener('change', (event) => {
            if (!event.target.matches('#liquidations-per-page')) return;
            loadTableData({ perPage: Number(event.target.value) });
        });

        // --- Pagination links ---
        document.addEventListener('click', (event) => {
            const link = event.target.closest('a');
            if (!link || !dataContainer.contains(event.target)) return;
            if (!link.closest('.liquidations-pagination')) return;
            event.preventDefault();
            loadTableData({ url: link.href });
        });

        updateLabel();
        renderOptions();
    })();
</script>
@endsection
