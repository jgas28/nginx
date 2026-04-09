@extends('layouts.app')

@section('title', 'Add On Rates')

@section('content')
<div class="min-h-screen bg-slate-50 py-6">
    <div class="mx-auto max-w-7xl px-4">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add On Rates</h1>
                <p class="mt-1 text-sm text-slate-500">Manage add on rate records with a cleaner searchable and responsive table.</p>
            </div>
            <a href="{{ route('addOnRates.create') }}" class="inline-flex w-auto items-center justify-center gap-2 self-start rounded-full bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-blue-600/20 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-600/30 sm:self-auto"><span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/20"><i class="fas fa-plus text-xs"></i></span>Add Add On Rate</a>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div id="add-on-rates-table" data-fast-table data-endpoint="{{ route('addOnRates.index') }}" data-search-selector="#add-on-rates-search" data-per-page-selector="#add-on-rates-per-page" data-pagination-selector=".add-on-rates-pagination a">
                @include('addOnRates.table', ['addOnRates' => $addOnRates, 'search' => $search, 'perPage' => $perPage])
            </div>
        </div>
    </div>
</div>
@endsection
