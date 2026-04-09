@extends('layouts.app')

@section('title', 'Create Region')

@section('content')
<div class="min-h-screen bg-[linear-gradient(180deg,#f8fafc_0%,#eefcf7_100%)] py-10">
    <div class="mx-auto max-w-4xl px-4">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                    <i class="fas fa-map-marked-alt"></i>
                    New Region
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Create Region</h1>
                <p class="mt-2 text-sm text-slate-500">Add a region record and assign it to the correct area.</p>
            </div>
            <a href="{{ route('regions.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <i class="fas fa-arrow-left"></i>
                Back to Regions
            </a>
        </div>

        <div class="rounded-3xl border border-white/70 bg-white p-8 shadow-[0_24px_70px_rgba(15,23,42,0.08)]">
            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-circle mt-0.5"></i>
                        <div>
                            <p class="text-sm font-semibold">Please review the region details below.</p>
                            <ul class="mt-2 list-disc pl-5 text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('regions.store') }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2" novalidate>
                @csrf

                <div>
                    <label for="region_code" class="mb-2 block text-sm font-semibold text-slate-700">Region Code</label>
                    <input type="text" name="region_code" id="region_code" value="{{ old('region_code') }}" required maxlength="255" autocomplete="off" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                </div>

                <div>
                    <label for="region_name" class="mb-2 block text-sm font-semibold text-slate-700">Region Name</label>
                    <input type="text" name="region_name" id="region_name" value="{{ old('region_name') }}" required maxlength="255" autocomplete="off" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                </div>

                <div>
                    <label for="province" class="mb-2 block text-sm font-semibold text-slate-700">Province</label>
                    <input type="text" name="province" id="province" value="{{ old('province') }}" required maxlength="255" autocomplete="off" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                </div>

                <div>
                    <label for="area_id" class="mb-2 block text-sm font-semibold text-slate-700">Area</label>
                    <select name="area_id" id="area_id" required class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                        <option value="">Select Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>{{ $area->area_code }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2 flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                    <a href="{{ route('regions.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 hover:shadow-emerald-600/30">
                        <i class="fas fa-save"></i>
                        Create Region
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
