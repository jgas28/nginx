@extends('layouts.app')

@section('title', 'Create Warehouse')

@section('content')
<div class="min-h-screen bg-[linear-gradient(180deg,#f8fafc_0%,#eef4ff_100%)] py-8">
    <div class="mx-auto max-w-5xl px-4">
        <div class="mb-6 rounded-[28px] border border-white/70 bg-white/80 px-6 py-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 ring-1 ring-blue-100">
                        <i class="fas fa-warehouse"></i>
                        New Warehouse
                    </div>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Create Warehouse</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Add a warehouse profile with clear location details so storage references stay accurate across operations.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('warehouses.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50">
                        <i class="fas fa-arrow-left text-xs"></i>
                        Back to Warehouses
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded-[30px] border border-white/70 bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:p-8">
            <form action="{{ route('warehouses.store') }}" method="POST" class="space-y-8">
                @csrf
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <label for="warehouse_code" class="mb-2 block text-sm font-semibold text-slate-700">Warehouse Code</label>
                        <input type="text" name="warehouse_code" id="warehouse_code" value="{{ old('warehouse_code') }}" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <label for="warehouse_name" class="mb-2 block text-sm font-semibold text-slate-700">Warehouse Name</label>
                        <input type="text" name="warehouse_name" id="warehouse_name" value="{{ old('warehouse_name') }}" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div class="md:col-span-2 rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <label for="warehouse_location" class="mb-2 block text-sm font-semibold text-slate-700">Warehouse Location</label>
                        <input type="text" name="warehouse_location" id="warehouse_location" value="{{ old('warehouse_location') }}" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">Keep warehouse names and locations aligned with the actual physical site names your team uses.</p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('warehouses.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        <button type="submit" style="background-color:#1d4ed8;border-color:#1d4ed8;color:#ffffff;" class="inline-flex min-w-[220px] appearance-none items-center justify-center gap-2 whitespace-nowrap rounded-2xl border px-6 py-3 text-sm font-semibold shadow-none outline-none ring-0 transition hover:opacity-95 focus:outline-none focus:ring-0 focus-visible:outline-none">
                            <i class="fas fa-save"></i>
                            Create Warehouse
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
