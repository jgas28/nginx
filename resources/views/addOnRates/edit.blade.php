@extends('layouts.app')

@section('title', 'Edit Add-On Rate')

@section('content')
<div class="min-h-screen bg-[linear-gradient(180deg,#f8fafc_0%,#eef4ff_100%)] py-8">
    <div class="mx-auto max-w-5xl px-4">
        <div class="mb-6 rounded-[28px] border border-white/70 bg-white/80 px-6 py-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 ring-1 ring-blue-100">
                        <i class="fas fa-pen-to-square"></i>
                        Update Add-On Rate
                    </div>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Edit Add-On Rate</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Review and update add-on rate values so pricing remains accurate across delivery scenarios.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('addOnRates.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50">
                        <i class="fas fa-arrow-left text-xs"></i>
                        Back to Add-On Rates
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded-[30px] border border-white/70 bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:p-8">
            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('addOnRates.update', $addOnRate) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <label for="add_on_rate_type_code" class="mb-2 block text-sm font-semibold text-slate-700">Add-On Rate Type Code</label>
                        <input type="text" name="add_on_rate_type_code" id="add_on_rate_type_code" value="{{ old('add_on_rate_type_code', $addOnRate->add_on_rate_type_code) }}" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <label for="add_on_rate_type_name" class="mb-2 block text-sm font-semibold text-slate-700">Add-On Rate Type Name</label>
                        <input type="text" name="add_on_rate_type_name" id="add_on_rate_type_name" value="{{ old('add_on_rate_type_name', $addOnRate->add_on_rate_type_name) }}" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <label for="rate" class="mb-2 block text-sm font-semibold text-slate-700">Rate</label>
                        <input type="number" name="rate" id="rate" value="{{ old('rate', $addOnRate->rate) }}" step="0.01" min="0" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <label for="percent_rate" class="mb-2 block text-sm font-semibold text-slate-700">Percentage Rate</label>
                        <input type="number" name="percent_rate" id="percent_rate" value="{{ old('percent_rate', $addOnRate->percent_rate) }}" step="0.01" min="0" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div class="md:col-span-2 rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <label for="delivery_type" class="mb-2 block text-sm font-semibold text-slate-700">Delivery Type</label>
                        <select name="delivery_type" id="delivery_type" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="" disabled>Select a Delivery Type</option>
                            <option value="Regular" {{ old('delivery_type', $addOnRate->delivery_type) == 'Regular' ? 'selected' : '' }}>Regular</option>
                            <option value="Multi-Drop" {{ old('delivery_type', $addOnRate->delivery_type) == 'Multi-Drop' ? 'selected' : '' }}>Multi-Drop</option>
                            <option value="Multi Pick-Up" {{ old('delivery_type', $addOnRate->delivery_type) == 'Multi Pick-Up' ? 'selected' : '' }}>Multi Pick-Up</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">Keep add-on pricing aligned with the delivery type it should apply to.</p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('addOnRates.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        <button type="submit" style="background-color:#1d4ed8;border-color:#1d4ed8;color:#ffffff;" class="inline-flex min-w-[220px] appearance-none items-center justify-center gap-2 whitespace-nowrap rounded-2xl border px-6 py-3 text-sm font-semibold shadow-none outline-none ring-0 transition hover:opacity-95 focus:outline-none focus:ring-0 focus-visible:outline-none">
                            <i class="fas fa-save"></i>
                            Update Add-On Rate
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
