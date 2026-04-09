@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 py-8">
    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 ring-1 ring-blue-100">
                    <i class="fas fa-user-shield text-sm"></i>
                    Admin Vouchers
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Admin Cash Voucher Request</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Review internal and RPM cash vouchers with a faster table, clearer actions, and a cleaner request queue.
                </p>
            </div>
            <a href="{{ route('admin.create') }}"
               class="inline-flex h-14 items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                    <i class="fas fa-plus text-xs"></i>
                </span>
                Create Admin Cash Voucher
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div id="admin-vouchers-table"
             data-fast-table
             data-endpoint="{{ route('admin.index', request()->query()) }}"
             data-base-endpoint="{{ route('admin.index') }}"
             data-search-selector="#admin-vouchers-search"
             data-per-page-selector="#admin-vouchers-per-page"
             data-pagination-selector=".admin-vouchers-pagination a">
            @include('admin.table', [
                'cashVouchers' => $cashVouchers,
                'search' => $search ?? '',
                'perPage' => $perPage ?? 10,
            ])
        </div>
    </div>
</div>
@endsection
