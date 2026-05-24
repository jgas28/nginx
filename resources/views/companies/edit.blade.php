@extends('layouts.app')

@section('title', 'Edit Company')

@section('content')
<div class="min-h-screen bg-[linear-gradient(180deg,#f8fafc_0%,#eef4ff_100%)] py-8">
    <div class="mx-auto max-w-5xl px-4">
        <div class="mb-6 rounded-[28px] border border-white/70 bg-white/80 px-6 py-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] backdrop-blur sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 ring-1 ring-amber-100">
                    <i class="fas fa-pen"></i>
                    Update Company
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Edit Company</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Review and update the selected company information carefully so your codes, names, and locations stay consistent across requests and reports.</p>
            </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('companies.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50">
                        <i class="fas fa-arrow-left text-xs"></i>
                        Back to Companies
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded-[30px] border border-white/70 bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:p-8">
            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-circle mt-0.5"></i>
                        <div>
                            <p class="text-sm font-semibold">Please review the company details below.</p>
                            <ul class="mt-2 list-disc pl-5 text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('companies.update', $company) }}" method="POST" class="space-y-8" novalidate>
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <label for="company_code" class="mb-2 block text-sm font-semibold text-slate-700">Company Code</label>
                        <input type="text" name="company_code" id="company_code" value="{{ old('company_code', $company->company_code) }}" required maxlength="255" autocomplete="off" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-100">
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <label for="company_name" class="mb-2 block text-sm font-semibold text-slate-700">Company Name</label>
                        <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $company->company_name) }}" required maxlength="255" autocomplete="off" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-100">
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <label for="tin_no" class="mb-2 block text-sm font-semibold text-slate-700">TIN No.</label>
                        <input type="text" name="tin_no" id="tin_no" value="{{ old('tin_no', $company->tin_no) }}" maxlength="50" autocomplete="off" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-100">
                    </div>

                    <div class="md:col-span-2 rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <label for="company_location" class="mb-2 block text-sm font-semibold text-slate-700">Company Location</label>
                        <input type="text" name="company_location" id="company_location" value="{{ old('company_location', $company->company_location) }}" required maxlength="255" autocomplete="off" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-100">
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">Keep company details aligned with your delivery request and billing records.</p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('companies.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" style="background-color:#1d4ed8;border-color:#1d4ed8;color:#ffffff;" class="inline-flex min-w-[220px] appearance-none items-center justify-center gap-2 whitespace-nowrap rounded-2xl border px-6 py-3 text-sm font-semibold shadow-none outline-none ring-0 transition hover:opacity-95 focus:outline-none focus:ring-0 focus-visible:outline-none">
                        <i class="fas fa-save"></i>
                        Update Company
                    </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
