@extends('layouts.app')

@section('title', 'Request Types')

@section('content')
<div class="min-h-screen bg-slate-50 py-6">
    <div class="mx-auto max-w-7xl px-4">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div><h1 class="text-2xl font-bold tracking-tight text-slate-900">Request Types</h1><p class="mt-1 text-sm text-slate-500">Manage cash voucher request type records with a cleaner searchable and responsive table.</p></div>
            <a href="{{ route('cvr_request_types.create') }}" class="inline-flex w-auto items-center justify-center gap-2 self-start rounded-full bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-blue-600/20 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-600/30 sm:self-auto"><span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/20"><i class="fas fa-plus text-xs"></i></span>Add Request Type</a>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm"><div id="cvr-request-types-table" data-fast-table data-endpoint="{{ route('cvr_request_types.index') }}" data-search-selector="#cvr-request-types-search" data-per-page-selector="#cvr-request-types-per-page" data-pagination-selector=".cvr-request-types-pagination a">@include('cvr_request_types.table', ['cvr_request_types' => $cvr_request_types, 'search' => $search, 'perPage' => $perPage])</div></div>
    </div>
</div>
@endsection
