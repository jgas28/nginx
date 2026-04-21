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
                    <span>{{ number_format($summary['rows'] ?? 0) }} matching records</span>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[28px] border border-white/70 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
                <p class="text-sm font-medium text-slate-500">Requested Amount</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $formatCurrency($summary['requested'] ?? 0) }}</p>
            </div>
            <div class="rounded-[28px] border border-white/70 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
                <p class="text-sm font-medium text-slate-500">Approved Amount</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-blue-600">{{ $formatCurrency($summary['approved'] ?? 0) }}</p>
            </div>
            <div class="rounded-[28px] border border-white/70 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
                <p class="text-sm font-medium text-slate-500">Liquidated Cash</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-emerald-600">{{ $formatCurrency($summary['cash'] ?? 0) }}</p>
            </div>
            <div class="rounded-[28px] border border-white/70 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
                <p class="text-sm font-medium text-slate-500">Liquidated Card</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-violet-600">{{ $formatCurrency($summary['card'] ?? 0) }}</p>
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

                    <div class="flex flex-col gap-3 sm:ml-auto sm:flex-row">
                        <a
                            href="{{ route('liquidations.overall') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-900"
                        >
                            Reset Filters
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-[30px] border border-white/70 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.08)]">
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Status Overview</h2>
                        <p class="mt-1 text-sm text-slate-500">Paginated results help the page load faster while keeping your current filters intact.</p>
                    </div>
                    <div class="text-sm text-slate-500">
                        Total liquidated: <span class="font-semibold text-slate-800">{{ $formatCurrency($summary['liquidated'] ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-4 text-left sm:px-6">CVR No.</th>
                            <th class="px-4 py-4 text-left sm:px-6">Type</th>
                            <th class="px-4 py-4 text-left sm:px-6">Request Type</th>
                            <th class="px-4 py-4 text-right sm:px-6">Requested</th>
                            <th class="px-4 py-4 text-right sm:px-6">Approved</th>
                            <th class="px-4 py-4 text-right sm:px-6">Liquidated Cash</th>
                            <th class="px-4 py-4 text-right sm:px-6">Liquidated Card</th>
                            <th class="px-4 py-4 text-left sm:px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($cashVouchers as $voucher)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-4 font-medium text-slate-900 sm:px-6">
                                    @if($voucher->cvr_type === 'admin')
                                        {{ Str::before($voucher->cvr_number, '/') }}-{{ $voucher->company_code }}{{ $voucher->expense_code }}
                                    @else
                                        {{ Str::before($voucher->cvr_number, '/') }}-{{ $voucher->truck_name ?? 'N/A' }}-{{ $voucher->company_code }}{{ $voucher->expense_code }}
                                    @endif
                                </td>
                                <td class="px-4 py-4 capitalize sm:px-6">{{ $voucher->cvr_type }}</td>
                                <td class="px-4 py-4 sm:px-6">{{ $voucher->request_code ?: 'N/A' }}</td>
                                <td class="px-4 py-4 text-right tabular-nums sm:px-6">{{ number_format((float) $voucher->requested_amount, 2) }}</td>
                                <td class="px-4 py-4 text-right tabular-nums sm:px-6">{{ number_format((float) $voucher->approved_amount, 2) }}</td>
                                <td class="px-4 py-4 text-right tabular-nums sm:px-6">{{ number_format((float) ($voucher->liquidated_amount_cash ?? 0), 2) }}</td>
                                <td class="px-4 py-4 text-right tabular-nums sm:px-6">{{ number_format((float) ($voucher->liquidated_amount_card ?? 0), 2) }}</td>
                                <td class="px-4 py-4 sm:px-6">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadgeClasses[$voucher->overall_status] ?? 'bg-slate-100 text-slate-800 border border-slate-200' }}">
                                        {{ $voucher->overall_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-slate-500 sm:px-6">
                                    No records found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-4 py-4 sm:px-6">
                {{ $cashVouchers->links('pagination::tailwind') }}
            </div>
        </section>
    </div>
</div>
@endsection
