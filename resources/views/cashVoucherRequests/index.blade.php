@extends('layouts.app')

@section('title', 'Cash Voucher Request')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 py-8">
    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 ring-1 ring-amber-100">
                    <i class="fas fa-wallet text-sm"></i>
                    Cash Voucher Requests
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Delivery Request Voucher Queue</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Review approved delivery requests that are ready for cash voucher creation with a faster searchable queue.
                </p>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div id="cash-voucher-request-table"
             data-fast-table
             data-endpoint="{{ route('cashVoucherRequests.index', ['search' => $search ?? '', 'per_page' => $perPage ?? 10]) }}"
             data-base-endpoint="{{ route('cashVoucherRequests.index') }}"
             data-search-selector="#cash-voucher-request-search"
             data-per-page-selector="#cash-voucher-request-per-page"
             data-pagination-selector=".cash-voucher-request-pagination a">
            @include('cashVoucherRequests.table', [
                'deliveryRequests' => $deliveryRequests,
                'search' => $search ?? '',
                'perPage' => $perPage ?? 10,
            ])
        </div>
    </div>
</div>
@endsection
