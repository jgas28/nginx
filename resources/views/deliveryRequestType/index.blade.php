@extends('layouts.app')

@section('title', 'Delivery Request Types')

@section('content')
<div class="space-y-6">
    <div class="rounded-[28px] border border-white/70 bg-white/90 px-6 py-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 ring-1 ring-blue-100">
                    <i class="fas fa-file-lines"></i>
                    Delivery Request Types
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Delivery Request Types</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Search and manage delivery request types in a compact searchable table.</p>
            </div>
            <a href="{{ route('deliveryRequestType.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-plus"></i>
                Add Delivery Request Type
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-[28px] border border-white/70 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:p-6">
        <div id="delivery-request-type-table" data-fast-table data-endpoint="{{ route('deliveryRequestType.index') }}" data-search-selector="[data-delivery-request-type-search]" data-per-page-selector="[data-delivery-request-type-per-page]" data-pagination-selector="[data-delivery-request-type-pagination] a">
            @include('deliveryRequestType.table', ['deliveryRequestType' => $deliveryRequestType, 'search' => $search ?? '', 'perPage' => $perPage ?? 10])
        </div>
    </div>
</div>
@endsection
