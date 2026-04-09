@extends('layouts.app')

@section('title', 'Expense Types')

@section('content')
<div class="min-h-screen bg-slate-50 py-6">
    <div class="mx-auto max-w-7xl px-4">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div><h1 class="text-2xl font-bold tracking-tight text-slate-900">Expense Types</h1><p class="mt-1 text-sm text-slate-500">Manage expense type records with a cleaner searchable and responsive table.</p></div>
            <a href="{{ route('expenseTypes.create') }}" class="inline-flex w-auto items-center justify-center gap-2 self-start rounded-full bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-blue-600/20 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-600/30 sm:self-auto"><span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/20"><i class="fas fa-plus text-xs"></i></span>Add Expense Type</a>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm"><div id="expense-types-table" data-fast-table data-endpoint="{{ route('expenseTypes.index') }}" data-search-selector="#expense-types-search" data-per-page-selector="#expense-types-per-page" data-pagination-selector=".expense-types-pagination a">@include('expenseTypes.table', ['expenseTypes' => $expenseTypes, 'search' => $search, 'perPage' => $perPage])</div></div>
    </div>
</div>
@endsection
