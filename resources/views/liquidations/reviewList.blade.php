@extends('layouts.app')

@section('title', 'Liquidation Review List')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 py-8">
    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-4 py-2 text-sm font-semibold text-cyan-700 ring-1 ring-cyan-100">
                    <i class="fas fa-clipboard-check text-sm"></i>
                    Liquidation Review
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Review Queue</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Validate submitted liquidations in a faster searchable queue with balanced controls, colorful status cards, and aligned actions.
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('liquidations.reviewList') }}" method="GET" id="liquidations-review-filter-form" class="flex items-end justify-between gap-4">
            <div class="min-w-0 flex-1 max-w-[420px]">
                <label for="cvr_type" class="sr-only">Voucher Type</label>
                <select
                    name="cvr_type"
                    id="cvr_type"
                    data-placeholder="All Voucher Types"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100"
                >
                    <option value="">All Voucher Types</option>
                    @foreach ($availableTypes as $type)
                        <option value="{{ $type }}" {{ $cvrType === $type ? 'selected' : '' }}>
                            {{ strtoupper($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex shrink-0 items-end justify-end gap-3 whitespace-nowrap">
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                        <i class="fas fa-filter text-xs"></i>
                    </span>
                    Apply Filters
                </button>
                <a href="{{ route('liquidations.reviewList') }}" id="liquidations-review-filter-reset" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-900">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <i class="fas fa-rotate-left text-xs"></i>
                    </span>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div
        id="liquidations-review-table"
        class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm"
        data-fast-table
        data-endpoint="{{ route('liquidations.reviewList', array_filter(['cvr_type' => $cvrType], fn ($value) => $value !== null && $value !== '')) }}"
        data-base-endpoint="{{ route('liquidations.reviewList') }}"
        data-search-selector="#liquidations-review-search"
        data-per-page-selector="#liquidations-review-per-page"
        data-pagination-selector=".liquidations-review-pagination a"
    >
        @include('liquidations.partials.review-list-table', [
            'liquidations' => $liquidations,
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
        const form = document.getElementById('liquidations-review-filter-form');
        const select = document.getElementById('cvr_type');
        const table = document.getElementById('liquidations-review-table');
        const resetLink = document.getElementById('liquidations-review-filter-reset');

        if (!form || !select || !table) {
            return;
        }

        select.classList.add('searchable-select-source');

        const wrapper = document.createElement('div');
        wrapper.dataset.open = 'false';
        wrapper.className = 'relative w-full max-w-[420px]';
        wrapper.innerHTML = `
            <button type="button" class="flex w-full items-center gap-3 rounded-2xl border border-slate-300 bg-white px-3 py-3 text-left text-sm text-slate-700 shadow-sm transition hover:border-cyan-300 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-cyan-50 text-cyan-600">
                    <i class="fas fa-layer-group text-sm"></i>
                </span>
                <span class="min-w-0 flex-1 truncate" data-select-label></span>
                <span class="text-slate-400"><i class="fas fa-chevron-down text-xs"></i></span>
            </button>
            <div class="searchable-select-panel absolute left-0 right-0 z-30 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10" style="max-width:420px;">
                <div class="border-b border-slate-200 p-3">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fas fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" placeholder="Search voucher type..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-cyan-300 focus:bg-white focus:ring-2 focus:ring-cyan-100" data-select-search>
                    </div>
                </div>
                <div class="max-h-56 overflow-y-auto p-2" data-select-list></div>
                <div class="hidden px-4 py-3 text-sm text-slate-500" data-select-empty>No matching voucher types found.</div>
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

        function closePanel() {
            wrapper.dataset.open = 'false';
            panel.classList.add('hidden');
        }

        function updateLabel() {
            const selectedOption = select.options[select.selectedIndex];
            label.textContent = selectedOption && selectedOption.value !== '' ? selectedOption.textContent.trim() : 'All Voucher Types';
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
                button.className = `flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition ${option.selected ? 'bg-cyan-50 text-cyan-700' : 'text-slate-700 hover:bg-slate-100'}`;
                button.innerHTML = `
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${option.selected ? 'bg-cyan-100 text-cyan-600' : 'bg-slate-100 text-slate-500'}">
                        <i class="fas fa-layer-group text-xs"></i>
                    </span>
                    <span class="min-w-0 flex-1 truncate">${option.textContent.trim()}</span>
                    ${option.selected ? '<i class="fas fa-check text-xs text-cyan-500"></i>' : ''}
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

        async function loadFilteredTable() {
            const url = new URL(baseEndpoint, window.location.origin);

            if (select.value) {
                url.searchParams.set('cvr_type', select.value);
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
                console.error('Error loading liquidation review list:', error);
            } finally {
                table.classList.remove('opacity-60', 'pointer-events-none');
            }
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
