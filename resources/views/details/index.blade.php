@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

@php
    $summary = $deliveryRequests->reduce(function ($carry, $dr) {
        $accessorialRate = $dr->lineItems->sum(fn($item) => (float) $item->accessorial_rate);
        $requested = $dr->cashVouchers->sum('amount');
        $approved = $dr->cvrApprovals->sum('amount');
        $liquidatedCash = $dr->liquidations_totals['cash'] ?? 0;
        $liquidatedCard = $dr->liquidations_totals['card'] ?? 0;

        $carry['delivery_rate'] += (float) ($dr->delivery_rate ?? 0);
        $carry['accessorial_rate'] += $accessorialRate;
        $carry['requested'] += (float) $requested;
        $carry['approved'] += (float) $approved;
        $carry['liquidated_cash'] += (float) $liquidatedCash;
        $carry['liquidated_card'] += (float) $liquidatedCard;

        return $carry;
    }, [
        'delivery_rate' => 0,
        'accessorial_rate' => 0,
        'requested' => 0,
        'approved' => 0,
        'liquidated_cash' => 0,
        'liquidated_card' => 0,
    ]);

    $statCards = [
        ['label' => 'Delivery Requests', 'value' => number_format($deliveryRequests->count()), 'icon' => 'fa-truck-ramp-box', 'bg' => 'from-sky-100 to-blue-100', 'text' => 'text-sky-700'],
        ['label' => 'Delivery Rate', 'value' => 'PHP ' . number_format($summary['delivery_rate'], 2), 'icon' => 'fa-money-bill-wave', 'bg' => 'from-emerald-100 to-green-100', 'text' => 'text-emerald-700'],
        ['label' => 'Approved Total', 'value' => 'PHP ' . number_format($summary['approved'], 2), 'icon' => 'fa-circle-check', 'bg' => 'from-violet-100 to-fuchsia-100', 'text' => 'text-violet-700'],
        ['label' => 'Liquidated Total', 'value' => 'PHP ' . number_format($summary['liquidated_cash'] + $summary['liquidated_card'], 2), 'icon' => 'fa-file-invoice-dollar', 'bg' => 'from-amber-100 to-orange-100', 'text' => 'text-orange-700'],
    ];
@endphp

<style>
    #delivery-details-page .dataTables_wrapper .dataTables_length,
    #delivery-details-page .dataTables_wrapper .dataTables_filter {
        display: none;
    }

    #delivery-details-page .dataTables_wrapper .dataTables_info,
    #delivery-details-page .dataTables_wrapper .dataTables_paginate {
        font-size: 0.875rem;
        color: #64748b;
        margin-top: 1rem;
    }

    #delivery-details-page .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.9rem !important;
        border: 1px solid #cbd5e1 !important;
        background: #fff !important;
        color: #334155 !important;
        padding: 0.45rem 0.85rem !important;
        margin-left: 0.35rem !important;
    }

    #delivery-details-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    #delivery-details-page .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        border-color: #2563eb !important;
        background: #2563eb !important;
        color: #fff !important;
    }

    #delivery-details-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        border-color: #93c5fd !important;
        background: #eff6ff !important;
        color: #1d4ed8 !important;
    }

    #delivery-details-page .dataTables_wrapper .dataTables_processing {
        border-radius: 1rem;
        border: 1px solid #dbeafe;
        background: rgba(255, 255, 255, 0.96);
        color: #1e3a8a;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
    }

    #delivery-details-page table.dataTable thead th,
    #delivery-details-page table.dataTable thead td {
        border-bottom: 1px solid #e2e8f0;
    }

    #delivery-details-page table.dataTable.no-footer {
        border-bottom: 0;
    }
</style>

<div
    id="delivery-details-page"
    class="mx-auto max-w-7xl space-y-6 py-8"
    x-data="{
        open: false,
        selected: null,
        showDetails(item) {
            this.selected = item;
            this.open = true;
        }
    }"
>
    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-cyan-50 to-blue-50 px-4 py-2 text-sm font-semibold text-cyan-700 ring-1 ring-cyan-100 shadow-sm">
                    <i class="fas fa-boxes-stacked"></i>
                    Delivery Details
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Delivery Request Summary</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Review requested, approved, and liquidated delivery amounts in one interactive summary table with clearer actions and aligned icons.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($statCards as $card)
            <div class="rounded-3xl border border-white/70 bg-gradient-to-br from-white via-white to-slate-50 px-4 py-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $card['bg'] }} {{ $card['text'] }} ring-1 ring-white shadow-sm">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">{{ $card['label'] }}</p>
                        <p class="truncate text-lg font-bold leading-6 text-slate-900">{{ $card['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Delivery Detail Table</h2>
                    <p class="mt-1 text-sm text-slate-500">Track each MTM with rate, approval, and liquidation totals.</p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600 ring-1 ring-slate-200">
                    <i class="fas fa-table-list text-slate-400"></i>
                    <span id="delivery-details-total-count">{{ $deliveryRequests->count() }}</span> records
                </div>
            </div>
        </div>

        <div class="border-b border-slate-200 px-6 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="relative w-full lg:max-w-md">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fas fa-magnifying-glass text-sm"></i>
                    </span>
                    <input
                        type="text"
                        id="delivery-details-search"
                        placeholder="Search MTM, project, company, customer..."
                        class="w-full rounded-2xl border border-slate-300 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                    >
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <span>Show</span>
                    <select id="delivery-details-per-page" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @foreach ([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ $size === 10 ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>rows</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="delivery-details-table" class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                    <tr>
                        <th class="px-6 py-4">MTM / Project</th>
                        <th class="px-6 py-4">Rates</th>
                        <th class="px-6 py-4">Requested</th>
                        <th class="px-6 py-4">Approved</th>
                        <th class="px-6 py-4">Liquidated</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="delivery-details-table-body" class="divide-y divide-slate-200 bg-white">
                    @forelse ($deliveryRequests as $dr)
                        @php
                            $accessorialRate = $dr->lineItems->sum(fn($i) => (float) $i->accessorial_rate);
                            $requested = (float) $dr->cashVouchers->sum('amount');
                            $approved = (float) $dr->cvrApprovals->sum('amount');
                            $liquidatedCash = (float) ($dr->liquidations_totals['cash'] ?? 0);
                            $liquidatedCard = (float) ($dr->liquidations_totals['card'] ?? 0);
                            $modalPayload = [
                                'id' => $dr->id,
                                'mtm' => $dr->mtm,
                                'project_name' => $dr->project_name,
                                'company' => $dr->company->company_name ?? 'N/A',
                                'customer' => $dr->customer->name ?? 'N/A',
                                'booking_date' => optional($dr->booking_date)->format ? $dr->booking_date : $dr->booking_date,
                                'delivery_date' => $dr->delivery_date,
                                'delivery_rate' => (float) ($dr->delivery_rate ?? 0),
                                'accessorial_rate' => $accessorialRate,
                                'requested' => $requested,
                                'approved' => $approved,
                                'liquidated_cash' => $liquidatedCash,
                                'liquidated_card' => $liquidatedCard,
                                'line_items' => $dr->lineItems->count(),
                                'cash_vouchers' => $dr->cashVouchers->count(),
                                'approvals' => $dr->cvrApprovals->count(),
                                'liquidations' => $dr->liquidations->count(),
                            ];
                        @endphp
                        <tr
                            class="delivery-details-row transition hover:bg-slate-50/80"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-100 to-cyan-100 text-blue-700 ring-1 ring-blue-200 shadow-sm">
                                        <i class="fas fa-box text-sm"></i>
                                    </span>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $dr->mtm }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $dr->project_name ?: 'No project name' }}</p>
                                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">
                                                <i class="fas fa-building text-[10px] text-blue-500"></i>
                                                {{ $dr->company->company_code ?? 'N/A' }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">
                                                <i class="fas fa-user-group text-[10px] text-violet-500"></i>
                                                {{ $dr->customer->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    <div class="rounded-2xl bg-emerald-50/80 px-3 py-2 ring-1 ring-emerald-100">
                                        <p class="text-[11px] uppercase tracking-[0.14em] text-emerald-500">Delivery Rate</p>
                                        <p class="mt-1 font-semibold text-emerald-700">PHP {{ number_format((float) ($dr->delivery_rate ?? 0), 2) }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-amber-50/80 px-3 py-2 ring-1 ring-amber-100">
                                        <p class="text-[11px] uppercase tracking-[0.14em] text-amber-500">Accessorial</p>
                                        <p class="mt-1 font-semibold text-amber-700">PHP {{ number_format($accessorialRate, 2) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-sky-700">PHP {{ number_format($requested, 2) }}</td>
                            <td class="px-6 py-4 font-semibold text-violet-700">PHP {{ number_format($approved, 2) }}</td>
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    <div class="rounded-2xl bg-rose-50/70 px-3 py-2 ring-1 ring-rose-100">
                                        <p class="text-[11px] uppercase tracking-[0.14em] text-rose-500">Cash</p>
                                        <p class="mt-1 font-semibold text-rose-700">PHP {{ number_format($liquidatedCash, 2) }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-cyan-50/70 px-3 py-2 ring-1 ring-cyan-100">
                                        <p class="text-[11px] uppercase tracking-[0.14em] text-cyan-500">Card</p>
                                        <p class="mt-1 font-semibold text-cyan-700">PHP {{ number_format($liquidatedCard, 2) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end">
                                    <button
                                        type="button"
                                        @click='showDetails(@json($modalPayload))'
                                        class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                                    >
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                                            <i class="fas fa-eye text-xs"></i>
                                        </span>
                                        View Details
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 ring-1 ring-slate-200">
                                    <i class="fas fa-folder-open text-lg"></i>
                                </div>
                                <p class="mt-3 font-medium text-slate-700">No delivery detail records found</p>
                                <p class="mt-1 text-sm">Once delivery requests are available, they will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-6 py-4"></div>
    </div>

    <div
        x-show="open"
        x-transition.opacity
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4 backdrop-blur-sm"
        @click.self="open = false"
        @keydown.escape.window="open = false"
    >
        <div class="w-full max-w-3xl overflow-hidden rounded-[28px] border border-white/70 bg-white shadow-[0_35px_100px_rgba(15,23,42,0.20)]">
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-100 to-cyan-100 text-blue-700 ring-1 ring-blue-200 shadow-sm">
                            <i class="fas fa-circle-info"></i>
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Delivery Detail</p>
                            <h2 class="mt-1 text-xl font-bold text-slate-900" x-text="selected?.mtm || 'Delivery Detail'"></h2>
                            <p class="mt-1 text-sm text-slate-500" x-text="selected?.project_name || 'No project name'"></p>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700" @click="open = false">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div class="max-h-[70vh] overflow-y-auto px-6 py-5">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200">
                        <p class="text-[11px] uppercase tracking-[0.14em] text-slate-400">Company</p>
                        <p class="mt-2 font-semibold text-slate-900" x-text="selected?.company || 'N/A'"></p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200">
                        <p class="text-[11px] uppercase tracking-[0.14em] text-slate-400">Customer</p>
                        <p class="mt-2 font-semibold text-slate-900" x-text="selected?.customer || 'N/A'"></p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200">
                        <p class="text-[11px] uppercase tracking-[0.14em] text-slate-400">Booking Date</p>
                        <p class="mt-2 font-semibold text-slate-900" x-text="selected?.booking_date || 'N/A'"></p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200">
                        <p class="text-[11px] uppercase tracking-[0.14em] text-slate-400">Delivery Date</p>
                        <p class="mt-2 font-semibold text-slate-900" x-text="selected?.delivery_date || 'N/A'"></p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    <div class="rounded-3xl border border-emerald-100 bg-emerald-50/70 p-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <i class="fas fa-money-bill-wave"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-emerald-800">Rate Summary</p>
                                <p class="text-xs text-emerald-600">Delivery and accessorial values</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between text-emerald-800">
                                <span>Delivery Rate</span>
                                <span class="font-semibold" x-text="`PHP ${Number(selected?.delivery_rate || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></span>
                            </div>
                            <div class="flex items-center justify-between text-emerald-800">
                                <span>Accessorial Rate</span>
                                <span class="font-semibold" x-text="`PHP ${Number(selected?.accessorial_rate || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-violet-100 bg-violet-50/70 p-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
                                <i class="fas fa-sack-dollar"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-violet-800">Amount Flow</p>
                                <p class="text-xs text-violet-600">Requested, approved, and liquidated</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between text-violet-800">
                                <span>Requested</span>
                                <span class="font-semibold" x-text="`PHP ${Number(selected?.requested || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></span>
                            </div>
                            <div class="flex items-center justify-between text-violet-800">
                                <span>Approved</span>
                                <span class="font-semibold" x-text="`PHP ${Number(selected?.approved || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></span>
                            </div>
                            <div class="flex items-center justify-between text-violet-800">
                                <span>Liquidated Cash</span>
                                <span class="font-semibold" x-text="`PHP ${Number(selected?.liquidated_cash || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></span>
                            </div>
                            <div class="flex items-center justify-between text-violet-800">
                                <span>Liquidated Card</span>
                                <span class="font-semibold" x-text="`PHP ${Number(selected?.liquidated_card || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 rounded-3xl border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Record Counts</p>
                            <p class="text-xs text-slate-500">Linked records under this delivery request</p>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200">
                            <p class="text-[11px] uppercase tracking-[0.14em] text-slate-400">Line Items</p>
                            <p class="mt-2 text-lg font-bold text-slate-900" x-text="selected?.line_items ?? 0"></p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200">
                            <p class="text-[11px] uppercase tracking-[0.14em] text-slate-400">Cash Vouchers</p>
                            <p class="mt-2 text-lg font-bold text-slate-900" x-text="selected?.cash_vouchers ?? 0"></p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200">
                            <p class="text-[11px] uppercase tracking-[0.14em] text-slate-400">Approvals</p>
                            <p class="mt-2 text-lg font-bold text-slate-900" x-text="selected?.approvals ?? 0"></p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200">
                            <p class="text-[11px] uppercase tracking-[0.14em] text-slate-400">Liquidations</p>
                            <p class="mt-2 text-lg font-bold text-slate-900" x-text="selected?.liquidations ?? 0"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    (() => {
        const tableElement = document.getElementById('delivery-details-table');
        const searchInput = document.getElementById('delivery-details-search');
        const perPageSelect = document.getElementById('delivery-details-per-page');
        const totalCountLabel = document.getElementById('delivery-details-total-count');

        if (!tableElement || typeof window.jQuery === 'undefined' || !window.jQuery.fn.DataTable) {
            return;
        }

        const $table = window.jQuery(tableElement);
        const table = $table.DataTable({
            pageLength: parseInt(perPageSelect?.value || '10', 10),
            lengthChange: false,
            searching: true,
            ordering: false,
            info: true,
            paging: true,
            autoWidth: false,
            language: {
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'Showing 0 to 0 of 0 entries',
                emptyTable: 'No delivery detail records found',
                zeroRecords: 'No matching delivery detail records found',
                paginate: {
                    previous: 'Previous',
                    next: 'Next',
                },
            },
            drawCallback: function () {
                const info = table.page.info();
                if (totalCountLabel) {
                    totalCountLabel.textContent = String(info.recordsDisplay);
                }
            },
        });

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                table.search(searchInput.value).draw();
            });
        }

        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => {
                table.page.len(parseInt(perPageSelect.value || '10', 10)).draw();
            });
        }

        if (totalCountLabel) {
            totalCountLabel.textContent = String(table.page.info().recordsDisplay);
        }
    })();
</script>
@endsection
