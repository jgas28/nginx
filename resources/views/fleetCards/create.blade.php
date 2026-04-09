@extends('layouts.app')

@section('title', 'Create Fleet Card')

@section('content')
<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-4xl px-4">
        <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-xl shadow-slate-200/60">
            <div class="border-b border-slate-200 bg-gradient-to-r from-blue-600 via-indigo-600 to-cyan-600 px-6 py-6 text-white sm:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-blue-50">
                            <i class="fas fa-credit-card text-[11px]"></i>
                            Fleet Card Setup
                        </div>
                        <h1 class="mt-4 text-3xl font-bold tracking-tight">Create Fleet Card</h1>
                        <p class="mt-2 max-w-2xl text-sm text-blue-100">Add a new fleet card account with a clean and easy-to-review form.</p>
                    </div>
                    <a href="{{ route('fleetCards.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/25">
                        <i class="fas fa-arrow-left text-xs"></i>
                        Back to List
                    </a>
                </div>
            </div>

            <div class="px-6 py-8 sm:px-8">
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-700">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-rose-500 shadow-sm">
                                <i class="fas fa-circle-exclamation text-xs"></i>
                            </span>
                            <div>
                                <p class="font-semibold">Please review the form.</p>
                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('fleetCards.store') }}" method="POST" class="space-y-8">
                    @csrf

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <label for="account" class="mb-2 block text-sm font-semibold text-slate-700">Account</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute left-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                                    <i class="fas fa-layer-group text-xs"></i>
                                </div>
                                <input type="text" name="account" id="account" value="{{ old('account') }}" required autocomplete="off" class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-14 pr-4 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <label for="account_name" class="mb-2 block text-sm font-semibold text-slate-700">Account Name</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute left-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-cyan-50 text-cyan-600">
                                    <i class="fas fa-id-card text-xs"></i>
                                </div>
                                <input type="text" name="account_name" id="account_name" value="{{ old('account_name') }}" required autocomplete="off" class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-14 pr-4 text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100">
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 md:col-span-2">
                            <label for="account_number" class="mb-2 block text-sm font-semibold text-slate-700">Account Number</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute left-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                    <i class="fas fa-hashtag text-xs"></i>
                                </div>
                                <input type="text" name="account_number" id="account_number" value="{{ old('account_number') }}" required autocomplete="off" class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-14 pr-4 text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="status" value="1">

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-slate-500">New fleet cards are saved as active by default.</p>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('fleetCards.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow">
                                <i class="fas fa-xmark text-xs text-slate-500"></i>
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/25 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-600/30">
                                <i class="fas fa-floppy-disk text-xs"></i>
                                Create Fleet Card
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
