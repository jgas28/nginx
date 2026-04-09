@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[30px] border border-slate-200/80 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.07)]">
        <div class="border-b border-slate-200/80 bg-gradient-to-r from-slate-50 via-white to-emerald-50/70 px-6 py-7 sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white">
                            <i class="fas fa-location-dot text-sm"></i>
                        </span>
                        Running Balance - Laguna
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Laguna Funds Tracker</h1>
                        <p class="max-w-3xl text-[15px] leading-7 text-slate-600 sm:text-base">
                            Review Laguna running balance movements with faster filters, searchable records, and a lighter transaction table.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 py-6 sm:px-8">
            <form id="running-balance-filter-form" method="GET" action="{{ route('running_balance.adminFunds') }}" data-skip-inline-filter-enhancer class="rounded-[28px] border border-slate-200 bg-slate-50/80 p-5 shadow-inner shadow-slate-100">
                <input type="hidden" name="approver_id" value="2">

                <div style="display:grid; grid-template-columns:repeat(5, minmax(0, 1fr)); gap:1rem; align-items:end;">
                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-calendar-day text-emerald-600"></i>
                            Start Date
                        </span>
                        <input
                            type="date"
                            name="start_date"
                            value="{{ request('start_date') }}"
                            class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                    </label>

                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-calendar-check text-amber-600"></i>
                            End Date
                        </span>
                        <input
                            type="date"
                            name="end_date"
                            value="{{ request('end_date') }}"
                            class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                    </label>

                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-right-left text-violet-600"></i>
                            Movement Type
                        </span>
                        <select
                            name="adjustment_type"
                            class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="">All Movement Types</option>
                            <option value="In" {{ request('adjustment_type') === 'In' ? 'selected' : '' }}>In</option>
                            <option value="Out" {{ request('adjustment_type') === 'Out' ? 'selected' : '' }}>Out</option>
                            <option value="Float" {{ request('adjustment_type') === 'Float' ? 'selected' : '' }}>Float</option>
                        </select>
                    </label>

                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-arrow-down-wide-short text-cyan-600"></i>
                            Sort
                        </span>
                        <select
                            name="sort"
                            class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="created_at" {{ request('sort', 'created_at') === 'created_at' ? 'selected' : '' }}>Date</option>
                            <option value="amount" {{ request('sort') === 'amount' ? 'selected' : '' }}>Amount</option>
                            <option value="type" {{ request('sort') === 'type' ? 'selected' : '' }}>Type</option>
                            <option value="adjustment_type" {{ request('sort') === 'adjustment_type' ? 'selected' : '' }}>Movement</option>
                        </select>
                    </label>

                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-arrow-up-short-wide text-rose-600"></i>
                            Direction
                        </span>
                        <select
                            name="direction"
                            class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="desc" {{ request('direction', 'desc') === 'desc' ? 'selected' : '' }}>Descending</option>
                            <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>Ascending</option>
                        </select>
                    </label>
                </div>
            </form>

            <div id="running-balance-results" class="mt-6">
                @include('running_balance.partials.funds-table', [
                    'balances' => $balances,
                    'approverId' => $approverId,
                    'locationLabel' => $locationLabel,
                    'runningTotalsByApprover' => $runningTotalsByApprover,
                    'uncollectedByApprover' => $uncollectedByApprover,
                    'visibleCount' => $visibleCount,
                    'visibleAmount' => $visibleAmount,
                    'inCount' => $inCount,
                    'outCount' => $outCount,
                ])
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('running-balance-filter-form');
    const results = document.getElementById('running-balance-results');
    let debounceTimer;

    function buildQuery() {
        const formData = new FormData(form);
        return new URLSearchParams(formData).toString();
    }

    async function loadResults(pushState = true) {
        if (!results) {
            return;
        }

        results.classList.add('opacity-70');

        try {
            const response = await fetch(`${form.action}?${buildQuery()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const payload = await response.json();
            results.innerHTML = payload.html || '';

            if (pushState) {
                const nextUrl = new URL(window.location.href);
                nextUrl.search = buildQuery();
                window.history.replaceState({}, '', nextUrl);
            }

            document.dispatchEvent(new CustomEvent('fast-table:loaded'));
        } catch (error) {
            results.innerHTML = `
                <div class="rounded-[28px] border border-rose-200 bg-rose-50 px-5 py-4 text-[15px] text-rose-700">
                    Unable to load the Laguna running balance right now. Please try again.
                </div>
            `;
        } finally {
            results.classList.remove('opacity-70');
        }
    }

    function bindFilters() {
        form.querySelectorAll('input, select').forEach((field) => {
            const eventName = field.tagName === 'SELECT' || field.type === 'date' ? 'change' : 'input';
            field.addEventListener(eventName, () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => loadResults(true), 300);
            });
        });
    }

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('#running-balance-results .pagination a');
        if (!link) {
            return;
        }

        event.preventDefault();
        const url = new URL(link.href);
        const page = url.searchParams.get('page');
        if (page) {
            let pageInput = form.querySelector('input[name="page"]');
            if (!pageInput) {
                pageInput = document.createElement('input');
                pageInput.type = 'hidden';
                pageInput.name = 'page';
                form.appendChild(pageInput);
            }
            pageInput.value = page;
        }

        await loadResults(true);
    });

    bindFilters();
});
</script>
@endsection
