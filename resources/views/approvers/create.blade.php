@extends('layouts.app')

@section('title', 'Create Approver')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <section class="rounded-[28px] border border-white/70 bg-white/90 px-6 py-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Settings</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Create Approver</h1>
                <p class="mt-2 text-sm text-slate-600 sm:text-base">Add a new approver and assign the site they manage.</p>
            </div>

            <a
                href="{{ route('approvers.index') }}"
                class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900 sm:w-auto"
            >
                Back to Approvers
            </a>
        </div>
    </section>

    <section class="rounded-[30px] border border-white/70 bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:p-8">
        <form action="{{ route('approvers.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="name">Name</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        placeholder="Enter full name"
                        required
                    >
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="site">Site</label>
                    <input
                        type="text"
                        name="site"
                        id="site"
                        value="{{ old('site') }}"
                        placeholder="Enter site name"
                        required
                    >
                    @error('site')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
                <a
                    href="{{ route('approvers.index') }}"
                    class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-900 sm:w-auto"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 sm:w-auto"
                >
                    Create Approver
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
