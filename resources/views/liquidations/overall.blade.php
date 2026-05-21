@extends('layouts.app')

@section('title', 'Cash Voucher Status')

@section('content')
@php
    use Illuminate\Support\Str;

    $filters = [
        'company_id' => request('company_id'),
        'request_code' => request('request_code'),
        'cvr_number' => request('cvr_number'),
        'status' => request('status'),
        'date_from' => request('date_from', now()->startOfMonth()->toDateString()),
        'date_to' => request('date_to', now()->endOfMonth()->toDateString()),
        'per_page' => request('per_page', $perPage ?? 25),
    ];

    $formatCurrency = fn ($value) => 'PHP ' . number_format((float) $value, 2);
    $statusBadgeClasses = [
        'Pending Cash Approval' => 'bg-amber-100 text-amber-800 border border-amber-200',
        'Rejected CVR' => 'bg-rose-100 text-rose-800 border border-rose-200',
        'Completed' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
        'Rejected Liquidation' => 'bg-red-100 text-red-800 border border-red-200',
        'For Validation' => 'bg-sky-100 text-sky-800 border border-sky-200',
        'For Collection' => 'bg-violet-100 text-violet-800 border border-violet-200',
        'For Approval' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
        'For Liquidation' => 'bg-cyan-100 text-cyan-800 border border-cyan-200',
        'Liquidation In Progress' => 'bg-slate-100 text-slate-800 border border-slate-200',
    ];
@endphp

<div class="min-h-screen bg-slate-50 py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
        <section class="rounded-[30px] border border-white/70 bg-white/90 px-6 py-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Operations</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Cash Voucher Status</h1>
                    <p class="mt-2 max-w-3xl text-sm text-slate-600 sm:text-base">
                        Review requested, approved, and liquidated cash vouchers with faster filtering, safer inputs, and paginated results.
                    </p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-2xl bg-slate-100 px-4 py-3 text-sm font-medium text-slate-600">
                    <i class="fas fa-database text-slate-500"></i>
                    <span id="overall-rows-count">{{ number_format($summary['rows'] ?? 0) }} matching records</span>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[28px] border border-white/70 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
                <p class="text-sm font-medium text-slate-500">Requested Amount</p>
                <p id="overall-summary-requested" class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $formatCurrency($summary['requested'] ?? 0) }}</p>
            </div>
            <div class="rounded-[28px] border border-white/70 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
                <p class="text-sm font-medium text-slate-500">Approved Amount</p>
                <p id="overall-summary-approved" class="mt-3 text-3xl font-semibold tracking-tight text-blue-600">{{ $formatCurrency($summary['approved'] ?? 0) }}</p>
            </div>
            <div class="rounded-[28px] border border-white/70 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
                <p class="text-sm font-medium text-slate-500">Liquidated Cash</p>
                <p id="overall-summary-cash" class="mt-3 text-3xl font-semibold tracking-tight text-emerald-600">{{ $formatCurrency($summary['cash'] ?? 0) }}</p>
            </div>
            <div class="rounded-[28px] border border-white/70 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
                <p class="text-sm font-medium text-slate-500">Liquidated Card</p>
                <p id="overall-summary-card" class="mt-3 text-3xl font-semibold tracking-tight text-violet-600">{{ $formatCurrency($summary['card'] ?? 0) }}</p>
            </div>
        </section>

        <section class="rounded-[30px] border border-white/70 bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:p-8">
            <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Filters</h2>
                    <p class="mt-1 text-sm text-slate-500">Use scoped filters to retrieve only the data you need.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('liquidations.overall') }}" class="space-y-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <label for="overall-company-id">Company</label>
                        <select id="overall-company-id" name="company_id">
                            <option value="">All Companies</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected((string) $filters['company_id'] === (string) $company->id)>
                                    {{ $company->company_code }} - {{ $company->company_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="overall-request-code">Request Type</label>
                        <select id="overall-request-code" name="request_code">
                            <option value="">All Request Types</option>
                            @foreach ($requestTypes as $requestType)
                                <option value="{{ $requestType }}" @selected($filters['request_code'] === $requestType)>
                                    {{ $requestType }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="overall-status">Status</label>
                        <select id="overall-status" name="status">
                            <option value="">All Statuses</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="overall-cvr-number">CVR Number</label>
                        <input id="overall-cvr-number" type="search" name="cvr_number" value="{{ $filters['cvr_number'] }}" placeholder="Search CVR number">
                    </div>

                    <div>
                        <label for="overall-date-from">From Date</label>
                        <input id="overall-date-from" type="date" name="date_from" value="{{ $filters['date_from'] }}">
                    </div>

                    <div>
                        <label for="overall-date-to">To Date</label>
                        <input id="overall-date-to" type="date" name="date_to" value="{{ $filters['date_to'] }}">
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:flex-wrap sm:items-center">
                    <div class="flex items-center gap-3">
                        <label for="overall-per-page" class="mb-0 text-sm font-medium text-slate-600">Per Page</label>
                        <select id="overall-per-page" name="per_page" class="max-w-[120px]">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int) $filters['per_page'] === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-3 sm:ml-auto sm:flex-row sm:flex-wrap sm:items-center">
                        <a
                            href="{{ route('liquidations.overall') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-900"
                        >
                            Reset Filters
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700"
                        >
                            <i class="fas fa-filter text-xs"></i>
                            Apply Filters
                        </button>

                        {{-- Export buttons — pass current filters via JS --}}
                        <button type="button" id="export-excel-btn"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-300 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 hover:border-emerald-400">
                            <i class="fas fa-file-excel text-sm"></i>
                            Export Excel
                        </button>
                        <button type="button" id="export-pdf-btn"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-300 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 hover:border-rose-400">
                            <i class="fas fa-file-pdf text-sm"></i>
                            Export PDF
                        </button>
                    </div>
                </div>
            </form>
        </section>

        {{-- Data area: replaced by AJAX on filter/pagination --}}
        <div class="relative">
            <div id="overall-loading" class="pointer-events-none absolute inset-x-0 top-10 z-20 hidden justify-center">
                <div class="inline-flex items-center gap-2.5 rounded-full border border-slate-200 bg-white px-5 py-2.5 shadow-lg">
                    <svg class="h-4 w-4 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.568 3 7.291l3-2.291z"></path>
                    </svg>
                    <span class="text-sm font-medium text-slate-700">Loading...</span>
                </div>
            </div>
            <div id="overall-data">
                @include('liquidations.partials.overall-table', ['cashVouchers' => $cashVouchers, 'summary' => $summary])
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
(() => {
    const form        = document.querySelector('form[action="{{ route('liquidations.overall') }}"]');
    const dataEl      = document.getElementById('overall-data');
    const loadingEl   = document.getElementById('overall-loading');
    const rowsCount   = document.getElementById('overall-rows-count');
    const summaryEls  = {
        requested: document.getElementById('overall-summary-requested'),
        approved:  document.getElementById('overall-summary-approved'),
        cash:      document.getElementById('overall-summary-cash'),
        card:      document.getElementById('overall-summary-card'),
    };

    if (!form || !dataEl) return;

    const formatCurrency = (v) => 'PHP ' + Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function buildUrl(base, extraParams = {}) {
        const url = new URL(base, window.location.origin);
        const data = new FormData(form);
        for (const [k, v] of data.entries()) {
            if (v) url.searchParams.set(k, v);
        }
        for (const [k, v] of Object.entries(extraParams)) {
            if (v) url.searchParams.set(k, v); else url.searchParams.delete(k);
        }
        return url;
    }

    async function loadData(url) {
        dataEl.classList.add('opacity-60', 'pointer-events-none', 'transition-opacity');
        if (loadingEl) loadingEl.classList.replace('hidden', 'flex');

        try {
            const res = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
            const payload = await res.json();

            dataEl.innerHTML = payload.html || '';
            window.history.replaceState({}, '', url.toString());

            if (payload.summary) {
                if (summaryEls.requested) summaryEls.requested.textContent = formatCurrency(payload.summary.requested ?? 0);
                if (summaryEls.approved)  summaryEls.approved.textContent  = formatCurrency(payload.summary.approved ?? 0);
                if (summaryEls.cash)      summaryEls.cash.textContent      = formatCurrency(payload.summary.cash ?? 0);
                if (summaryEls.card)      summaryEls.card.textContent      = formatCurrency(payload.summary.card ?? 0);
                if (rowsCount) rowsCount.textContent = Number(payload.summary.rows ?? 0).toLocaleString() + ' matching records';
            }
        } catch (err) {
            console.error('Error loading cash voucher status:', err);
        } finally {
            dataEl.classList.remove('opacity-60', 'pointer-events-none', 'transition-opacity');
            if (loadingEl) loadingEl.classList.replace('flex', 'hidden');
        }
    }

    // Filter form submit
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        loadData(buildUrl('{{ route('liquidations.overall') }}'));
    });

    // Reset filters link
    document.querySelector('a[href="{{ route('liquidations.overall') }}"]')?.addEventListener('click', (e) => {
        e.preventDefault();
        form.reset();
        loadData(new URL('{{ route('liquidations.overall') }}', window.location.origin));
    });

    // Pagination clicks inside data container
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link || !dataEl.contains(e.target)) return;
        if (!link.closest('.overall-pagination')) return;
        e.preventDefault();
        loadData(new URL(link.href, window.location.origin));
    });

    // Export buttons — read current form state
    function buildExportUrl(base) {
        const url = new URL(base, window.location.origin);
        const data = new FormData(form);
        for (const [k, v] of data.entries()) {
            if (v) url.searchParams.set(k, v);
        }
        return url.toString();
    }

    document.getElementById('export-excel-btn')?.addEventListener('click', () => {
        window.location.href = buildExportUrl('{{ route('liquidations.overall.exportExcel') }}');
    });
    document.getElementById('export-pdf-btn')?.addEventListener('click', () => {
        window.open(buildExportUrl('{{ route('liquidations.overall.exportPdf') }}'), '_blank');
    });
})();
</script>
@endsection
@endsection
