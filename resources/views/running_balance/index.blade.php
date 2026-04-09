@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[30px] border border-slate-200/80 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.07)]">
        <div class="border-b border-slate-200/80 bg-gradient-to-r from-slate-50 via-white to-indigo-50/60 px-6 py-7 sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-white">
                            <i class="fas fa-scale-balanced text-sm"></i>
                        </span>
                        Running Balance
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">All Source Funds</h1>
                        <p class="max-w-3xl text-[15px] leading-7 text-slate-600 sm:text-base">
                            Review source balances, movement history, and filtered running totals with the same fast table experience used on the other queue pages.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    onclick="openModal()"
                    class="inline-flex items-center justify-center gap-3 rounded-2xl bg-indigo-600 px-5 py-3 text-[15px] font-semibold text-white shadow-[0_12px_24px_rgba(79,70,229,0.22)] transition hover:-translate-y-0.5 hover:bg-indigo-700"
                >
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/20">
                        <i class="fas fa-plus"></i>
                    </span>
                    New Transaction
                </button>
            </div>
        </div>

        <div class="px-6 py-6 sm:px-8">
            <form id="running-balance-filter-form" method="GET" action="{{ route('running_balance.index') }}" data-skip-inline-filter-enhancer class="rounded-[28px] border border-slate-200 bg-slate-50/80 p-5 shadow-inner shadow-slate-100">
                <div style="display:grid; grid-template-columns:repeat(6, minmax(0, 1fr)); gap:1rem; align-items:end;">
                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-calendar-day text-emerald-600"></i>
                            Start Date
                        </span>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </label>

                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-calendar-check text-amber-600"></i>
                            End Date
                        </span>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </label>

                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-building-columns text-blue-600"></i>
                            Source Funds
                        </span>
                        <select name="approver_id" class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">All Sources</option>
                            @foreach($approvers as $approver)
                                <option value="{{ $approver->id }}" {{ (string) request('approver_id') === (string) $approver->id ? 'selected' : '' }}>
                                    {{ $approver->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-right-left text-violet-600"></i>
                            Movement Type
                        </span>
                        <select name="adjustment_type" class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
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
                        <select name="sort" class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
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
                        <select name="direction" class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="desc" {{ request('direction', 'desc') === 'desc' ? 'selected' : '' }}>Descending</option>
                            <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>Ascending</option>
                        </select>
                    </label>
                </div>
            </form>

            <div id="running-balance-results" class="mt-6">
                @include('running_balance.partials.index-table', [
                    'balances' => $balances,
                    'approvers' => $approvers,
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

    <div id="transactionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/55 px-4 backdrop-blur-sm">
        <div class="relative w-full max-w-4xl overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_24px_60px_rgba(15,23,42,0.22)]">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
                <button onclick="closeModal()" class="absolute right-5 top-5 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-rose-50 hover:text-rose-600">
                    <i class="fas fa-times"></i>
                </button>
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-indigo-700">
                        <i class="fas fa-plus-circle"></i>
                        New Transaction
                    </div>
                    <h2 class="text-2xl font-black tracking-tight text-slate-900">Record Running Balance Movement</h2>
                    <p class="text-[15px] text-slate-500">Create a top-up, transfer, salary deduction, or manual adjustment.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('running_balance.store') }}" class="px-6 py-6">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-[15px] font-semibold text-slate-800">Transaction Type</span>
                        <select name="type" id="modal_type" class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                            <option value="">Select Type</option>
                            <option value="1">Top-up</option>
                            <option value="5">Salary Deduction</option>
                            <option value="10">Transfer</option>
                            <option value="11">Adjustment</option>
                            <option value="12">Adjustment for Uncollected</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-[15px] font-semibold text-slate-800">Amount</span>
                        <input type="number" step="0.01" name="amount" class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="mb-2 block text-[15px] font-semibold text-slate-800">Description</span>
                        <input type="text" name="description" class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-[15px] font-semibold text-slate-800">Employee</span>
                        <select name="employee_id" class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->fname }} {{ $emp->lname }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-[15px] font-semibold text-slate-800">To Source</span>
                        <select name="approver_id" class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                            <option value="">Select Approver</option>
                            @foreach($approvers as $ap)
                                <option value="{{ $ap->id }}">{{ $ap->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="hidden sm:col-span-2" id="modal_from_approver_wrapper">
                        <span class="mb-2 block text-[15px] font-semibold text-slate-800">From Source</span>
                        <select name="from_approver_id" id="modal_from_approver_id" class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">Select Source Approver</option>
                            @foreach($approvers as $ap)
                                <option value="{{ $ap->id }}">{{ $ap->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeModal()" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-[15px] font-semibold text-slate-700 transition hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 text-[15px] font-semibold text-white transition hover:bg-indigo-700">
                        <i class="fas fa-save"></i>
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openModal() {
        document.getElementById('transactionModal').classList.remove('hidden');
        document.getElementById('transactionModal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('transactionModal').classList.add('hidden');
        document.getElementById('transactionModal').classList.remove('flex');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('running-balance-filter-form');
        const results = document.getElementById('running-balance-results');
        const typeSelect = document.getElementById('modal_type');
        const fromApproverWrapper = document.getElementById('modal_from_approver_wrapper');
        const fromApproverSelect = document.getElementById('modal_from_approver_id');
        let debounceTimer;

        function toggleSourceField() {
            if (!typeSelect) {
                return;
            }

            if (typeSelect.value === '10') {
                fromApproverWrapper.classList.remove('hidden');
                fromApproverSelect.setAttribute('required', 'required');
            } else {
                fromApproverWrapper.classList.add('hidden');
                fromApproverSelect.removeAttribute('required');
            }
        }

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
                        Unable to load running balances right now. Please try again.
                    </div>
                `;
            } finally {
                results.classList.remove('opacity-70');
            }
        }

        form.querySelectorAll('input, select').forEach((field) => {
            const eventName = field.tagName === 'SELECT' || field.type === 'date' ? 'change' : 'input';
            field.addEventListener(eventName, () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => loadResults(true), 300);
            });
        });

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

        if (typeSelect) {
            typeSelect.addEventListener('change', toggleSourceField);
            toggleSourceField();
        }
    });
</script>
@endsection
