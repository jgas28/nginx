@extends('layouts.app')

@section('title', 'Rejected Liquidations')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 py-8">

    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 ring-1 ring-rose-100">
                    <i class="fas fa-circle-xmark text-sm"></i>
                    Rejected Liquidations
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Rejected Queue</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Review and edit liquidations that were rejected, with searchable results and paginated rows.
                </p>
            </div>
        </div>
    </div>

    <div id="rejected-list-table" class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">

        {{-- Static toolbar --}}
        <div class="border-b border-slate-200 px-6 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="relative w-full lg:max-w-sm">
                    <div class="pointer-events-none absolute left-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-rose-50 text-rose-600 shadow-sm">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input
                        type="text"
                        id="rejected-list-search"
                        value="{{ $search ?? '' }}"
                        placeholder="Search CVR, company, type..."
                        autocomplete="off"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-14 pr-4 text-sm text-slate-900 outline-none transition focus:border-rose-500 focus:bg-white focus:ring-2 focus:ring-rose-100"
                    >
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span>Show</span>
                        <select id="rejected-list-per-page" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                            @foreach([5, 10, 25, 50] as $size)
                                <option value="{{ $size }}" {{ (int)($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span>entries</span>
                    </div>
                    <div id="rejected-list-count" class="text-sm text-slate-500">
                        {{ $liquidations->total() }} rejected liquidations found
                    </div>
                </div>
            </div>
        </div>

        {{-- Data area --}}
        <div class="relative">
            <div id="rejected-list-loading" class="pointer-events-none absolute inset-x-0 top-10 z-20 hidden justify-center">
                <div class="inline-flex items-center gap-2.5 rounded-full border border-slate-200 bg-white px-5 py-2.5 shadow-lg">
                    <svg class="h-4 w-4 animate-spin text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.568 3 7.291l3-2.291z"></path>
                    </svg>
                    <span class="text-sm font-medium text-slate-700">Loading...</span>
                </div>
            </div>
            <div id="rejected-list-data">
                @include('liquidations.partials.rejected-list-table', ['liquidations' => $liquidations, 'perPage' => $perPage])
            </div>
        </div>

    </div>
</div>

@section('scripts')
<script>
    (() => {
        const dataContainer = document.getElementById('rejected-list-data');
        const countEl       = document.getElementById('rejected-list-count');
        const loadingEl     = document.getElementById('rejected-list-loading');

        if (!dataContainer) return;

        const baseEndpoint = '{{ route('liquidations.rejectedList') }}';
        let state = {
            search:  '{{ addslashes($search ?? '') }}',
            perPage: {{ (int)($perPage ?? 10) }},
        };
        let searchTimer = null;

        async function loadTableData({ search, perPage, url } = {}) {
            let requestUrl;
            if (url) {
                requestUrl = new URL(url, window.location.origin);
            } else {
                if (search !== undefined) state.search = search;
                if (perPage !== undefined) state.perPage = perPage;
                requestUrl = new URL(baseEndpoint, window.location.origin);
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
                if (countEl && payload.total !== undefined) {
                    countEl.textContent = payload.total + ' rejected liquidations found';
                }
            } catch (error) {
                console.error('Error loading rejected liquidations:', error);
            } finally {
                dataContainer.classList.remove('opacity-60', 'pointer-events-none', 'transition-opacity');
                if (loadingEl) loadingEl.classList.replace('flex', 'hidden');
            }
        }

        // Search (debounced)
        document.addEventListener('input', (e) => {
            if (!e.target.matches('#rejected-list-search')) return;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => loadTableData({ search: e.target.value }), 300);
        });

        // Per-page
        document.addEventListener('change', (e) => {
            if (!e.target.matches('#rejected-list-per-page')) return;
            loadTableData({ perPage: Number(e.target.value) });
        });

        // Pagination
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link || !dataContainer.contains(e.target)) return;
            if (!link.closest('.rejected-list-pagination')) return;
            e.preventDefault();
            loadTableData({ url: link.href });
        });
    })();
</script>
@endsection
@endsection
