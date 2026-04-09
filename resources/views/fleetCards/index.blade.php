@extends('layouts.app')

@section('title', 'Fleet Cards')

@section('content')
<div class="min-h-screen bg-slate-50 py-6">
    <div class="mx-auto max-w-7xl px-4">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Fleet Cards</h1>
                <p class="mt-1 text-sm text-slate-500">Manage fuel card accounts with a cleaner searchable table and quicker actions.</p>
            </div>
            <a href="{{ route('fleetCards.create') }}" class="inline-flex w-auto items-center justify-center gap-2 self-start rounded-full bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-blue-600/20 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-600/30 sm:self-auto">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/20">
                    <i class="fas fa-plus text-xs"></i>
                </span>
                Add Fleet Card
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div id="fleet-cards-table" data-fast-table data-endpoint="{{ route('fleetCards.index') }}" data-search-selector="#fleet-cards-search" data-per-page-selector="#fleet-cards-per-page" data-pagination-selector=".fleet-cards-pagination a">
                @include('fleetCards.table', ['fleetCards' => $fleetCards, 'search' => $search, 'perPage' => $perPage])
            </div>
        </div>
    </div>
</div>
@endsection
