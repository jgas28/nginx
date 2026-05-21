@extends('layouts.app')

@section('title', 'Liquidation Approval Queue')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 py-8">
    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-700 ring-1 ring-violet-100">
                    <i class="fas fa-stamp text-sm"></i>
                    Liquidation Approval
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Approval Queue</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Review liquidations ready for approval with faster search, searchable filters, and a more balanced queue layout.
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('liquidations.approvalList') }}" method="GET" id="liquidations-approval-filter-form" class="flex flex-col items-stretch justify-start gap-4 md:flex-row md:items-end md:justify-between">
            <div class="min-w-0 w-full flex-1 md:max-w-[420px]">
                <label for="cvr_type" class="sr-only">Voucher Type</label>
                <select
                    name="cvr_type"
                    id="cvr_type"
                    data-placeholder="All Voucher Types"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100"
                >
                    <option value="">All Voucher Types</option>
                    @foreach ($availableTypes as $type)
                        <option value="{{ $type }}" {{ $cvrType === $type ? 'selected' : '' }}>
                            {{ strtoupper($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex w-full shrink-0 flex-col items-stretch justify-start gap-3 whitespace-nowrap sm:flex-row sm:items-end sm:justify-end md:w-auto">
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-violet-700 sm:w-auto">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                        <i class="fas fa-filter text-xs"></i>
                    </span>
                    Filter Results
                </button>
                <a href="{{ route('liquidations.approvalList') }}" id="liquidations-approval-filter-reset" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-900 sm:w-auto">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <i class="fas fa-rotate-left text-xs"></i>
                    </span>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div id="liquidations-approval-table" class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">

        {{-- Static toolbar --}}
        <div class="border-b border-slate-200 px-6 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="relative w-full lg:max-w-sm">
                    <div class="pointer-events-none absolute left-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-violet-50 text-violet-600 shadow-sm">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" id="liquidations-approval-search" value="{{ $search ?? '' }}"
                        placeholder="Search CVR, company, supplier, requestor, expense..."
                        autocomplete="off"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-14 pr-4 text-sm text-slate-900 outline-none transition focus:border-violet-500 focus:bg-white focus:ring-2 focus:ring-violet-100">
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span>Show</span>
                        <select id="liquidations-approval-per-page" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-700 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100">
                            @foreach([5, 10, 25, 50] as $size)
                                <option value="{{ $size }}" {{ (int)($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span>entries</span>
                    </div>
                    <div id="liquidations-approval-count" class="text-sm text-slate-500">
                        {{ $liquidations->total() }} liquidation requests found
                    </div>
                </div>
            </div>
        </div>

        {{-- Data area --}}
        <div class="relative">
            <div id="liquidations-approval-loading" class="pointer-events-none absolute inset-x-0 top-10 z-20 hidden justify-center">
                <div class="inline-flex items-center gap-2.5 rounded-full border border-slate-200 bg-white px-5 py-2.5 shadow-lg">
                    <svg class="h-4 w-4 animate-spin text-violet-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.568 3 7.291l3-2.291z"></path>
                    </svg>
                    <span class="text-sm font-medium text-slate-700">Loading...</span>
                </div>
            </div>
            <div id="liquidations-approval-data">
                @include('liquidations.partials.approval-list-table', ['liquidations' => $liquidations, 'perPage' => $perPage, 'overview' => $overview])
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
@endsection

@section('scripts')
<script>
    (() => {
        const form = document.getElementById('liquidations-approval-filter-form');
        const select = document.getElementById('cvr_type');
        const dataContainer = document.getElementById('liquidations-approval-data');
        const countEl = document.getElementById('liquidations-approval-count');
        const loadingEl = document.getElementById('liquidations-approval-loading');
        const resetLink = document.getElementById('liquidations-approval-filter-reset');

        if (!form || !select || !dataContainer) return;

        const baseEndpoint = '{{ route('liquidations.approvalList') }}';
        let state = {
            cvrType: select.value,
            search: '{{ addslashes($search ?? '') }}',
            perPage: {{ (int)($perPage ?? 10) }},
        };
        let searchTimer = null;

        async function loadTableData({ cvrType, search, perPage, url } = {}) {
            let requestUrl;
            if (url) {
                requestUrl = new URL(url, window.location.origin);
            } else {
                if (cvrType !== undefined) state.cvrType = cvrType;
                if (search !== undefined) state.search = search;
                if (perPage !== undefined) state.perPage = perPage;
                requestUrl = new URL(baseEndpoint, window.location.origin);
                if (state.cvrType) requestUrl.searchParams.set('cvr_type', state.cvrType);
                if (state.search) requestUrl.searchParams.set('search', state.search);
                if (state.perPage && state.perPage !== 10) requestUrl.searchParams.set('per_page', state.perPage);
            }
            dataContainer.classList.add('opacity-60', 'pointer-events-none', 'transition-opacity');
            if (loadingEl) loadingEl.classList.replace('hidden', 'flex');
            try {
                const response = await fetch(requestUrl.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                });
                const payload = await response.json();
                dataContainer.innerHTML = payload.html || '';
                window.history.replaceState({}, '', requestUrl.toString());
                if (countEl && payload.total !== undefined) countEl.textContent = payload.total + ' liquidation requests found';
            } catch (error) {
                console.error('Error loading liquidation approval list:', error);
            } finally {
                dataContainer.classList.remove('opacity-60', 'pointer-events-none', 'transition-opacity');
                if (loadingEl) loadingEl.classList.replace('flex', 'hidden');
            }
        }

        function closePanel() { wrapper.dataset.open = 'false'; panel.classList.add('hidden'); }

        select.classList.add('searchable-select-source');
        const wrapper = document.createElement('div');
        wrapper.dataset.open = 'false';
        wrapper.className = 'relative w-full max-w-[420px]';
        wrapper.innerHTML = `
            <button type="button" class="flex w-full items-center gap-3 rounded-2xl border border-slate-300 bg-white px-3 py-3 text-left text-sm text-slate-700 shadow-sm transition hover:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-500">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-50 text-violet-600"><i class="fas fa-layer-group text-sm"></i></span>
                <span class="min-w-0 flex-1 truncate" data-select-label></span>
                <span class="text-slate-400"><i class="fas fa-chevron-down text-xs"></i></span>
            </button>
            <div class="searchable-select-panel absolute left-0 right-0 z-30 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10" style="max-width:420px;">
                <div class="border-b border-slate-200 p-3"><div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-magnifying-glass text-xs"></i></span>
                    <input type="text" placeholder="Search voucher type..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-violet-300 focus:bg-white focus:ring-2 focus:ring-violet-100" data-select-search>
                </div></div>
                <div class="max-h-56 overflow-y-auto p-2" data-select-list></div>
                <div class="hidden px-4 py-3 text-sm text-slate-500" data-select-empty>No matching voucher types found.</div>
            </div>`;
        select.insertAdjacentElement('afterend', wrapper);

        const trigger = wrapper.querySelector('button');
        const panel = wrapper.querySelector('.searchable-select-panel');
        const label = wrapper.querySelector('[data-select-label]');
        const typeSearch = wrapper.querySelector('[data-select-search]');
        const list = wrapper.querySelector('[data-select-list]');
        const emptyState = wrapper.querySelector('[data-select-empty]');

        function updateLabel() {
            const opt = select.options[select.selectedIndex];
            label.textContent = opt && opt.value !== '' ? opt.textContent.trim() : 'All Voucher Types';
        }
        function renderOptions(term = '') {
            const t = term.trim().toLowerCase();
            list.innerHTML = ''; let n = 0;
            Array.from(select.options).forEach((option) => {
                if (!option.value && t) return;
                if (t && !option.textContent.toLowerCase().includes(t)) return;
                n++;
                const btn = document.createElement('button'); btn.type = 'button';
                btn.className = `flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition ${option.selected ? 'bg-violet-50 text-violet-700' : 'text-slate-700 hover:bg-slate-100'}`;
                btn.innerHTML = `<span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${option.selected ? 'bg-violet-100 text-violet-600' : 'bg-slate-100 text-slate-500'}"><i class="fas fa-layer-group text-xs"></i></span><span class="min-w-0 flex-1 truncate">${option.textContent.trim()}</span>${option.selected ? '<i class="fas fa-check text-xs text-violet-500"></i>' : ''}`;
                btn.addEventListener('click', () => { select.value = option.value; updateLabel(); renderOptions(typeSearch.value); closePanel(); loadTableData({ cvrType: select.value }); });
                list.appendChild(btn);
            });
            emptyState.classList.toggle('hidden', n !== 0);
        }

        trigger.addEventListener('click', () => {
            const open = wrapper.dataset.open !== 'true';
            wrapper.dataset.open = open ? 'true' : 'false';
            panel.classList.toggle('hidden', !open);
            if (open) { typeSearch.value = ''; renderOptions(); setTimeout(() => typeSearch.focus(), 0); }
        });
        typeSearch.addEventListener('input', () => renderOptions(typeSearch.value));
        document.addEventListener('click', (e) => { if (!wrapper.contains(e.target)) closePanel(); });
        form.addEventListener('submit', (e) => { e.preventDefault(); loadTableData({ cvrType: select.value }); });
        if (resetLink) resetLink.addEventListener('click', (e) => { e.preventDefault(); select.value = ''; updateLabel(); renderOptions(); loadTableData({ cvrType: '', search: '', perPage: 10 }); });

        document.addEventListener('input', (e) => {
            if (!e.target.matches('#liquidations-approval-search')) return;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => loadTableData({ search: e.target.value }), 300);
        });
        document.addEventListener('change', (e) => {
            if (!e.target.matches('#liquidations-approval-per-page')) return;
            loadTableData({ perPage: Number(e.target.value) });
        });
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link || !dataContainer.contains(e.target)) return;
            if (!link.closest('.liquidations-approval-pagination')) return;
            e.preventDefault(); loadTableData({ url: link.href });
        });

        updateLabel(); renderOptions();
    })();
</script>
@endsection
