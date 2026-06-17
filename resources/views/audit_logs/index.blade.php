@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[30px] border border-slate-200/80 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.07)]">
        <div class="border-b border-slate-200/80 bg-gradient-to-r from-slate-50 via-white to-indigo-50/60 px-6 py-7 sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-700 text-white">
                            <i class="fas fa-clipboard-list text-sm"></i>
                        </span>
                        Audit Logs
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Activity History</h1>
                        <p class="max-w-3xl text-[15px] leading-7 text-slate-600 sm:text-base">
                            Every login, logout, and data change across the system, with before/after detail on each record.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a id="btn-export-excel" href="{{ route('audit-logs.exportExcel') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-[15px] font-semibold text-emerald-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-100 sm:w-auto">
                        <i class="fas fa-file-excel"></i>
                        Excel
                    </a>
                    <a id="btn-export-pdf" href="{{ route('audit-logs.exportPdf') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-rose-300 bg-rose-50 px-4 py-3 text-[15px] font-semibold text-rose-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-rose-100 sm:w-auto">
                        <i class="fas fa-file-pdf"></i>
                        PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="px-6 py-6 sm:px-8">
            <form id="audit-logs-filter-form" method="GET" action="{{ route('audit-logs.index') }}" data-skip-inline-filter-enhancer class="rounded-[28px] border border-slate-200 bg-slate-50/80 p-5 shadow-inner shadow-slate-100">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-calendar-day text-emerald-600"></i>
                            Start Date
                        </span>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </label>

                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-calendar-check text-amber-600"></i>
                            End Date
                        </span>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </label>

                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-user text-blue-600"></i>
                            User
                        </span>
                        <select name="user_id" class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>
                                    {{ trim($u->fname . ' ' . $u->lname) }} ({{ $u->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-bolt text-violet-600"></i>
                            Event
                        </span>
                        <select name="event" class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event }}" {{ request('event') === $event ? 'selected' : '' }}>{{ $event }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-database text-cyan-600"></i>
                            Model
                        </span>
                        <select name="auditable_type" class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">All Models</option>
                            @foreach($models as $model)
                                <option value="{{ $model }}" {{ request('auditable_type') === $model ? 'selected' : '' }}>{{ class_basename($model) }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block" style="min-width:0;">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-magnifying-glass text-rose-600"></i>
                            Search
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Description or actor..." class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </label>
                </div>
            </form>

            <div id="audit-logs-results" class="mt-6 overflow-hidden rounded-[28px] border border-slate-200">
                @include('audit_logs.partials.table', ['logs' => $logs])
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('audit-logs-filter-form');
        const results = document.getElementById('audit-logs-results');
        let debounceTimer;

        function buildQuery() {
            const formData = new FormData(form);
            return new URLSearchParams(formData).toString();
        }

        function updateExportLinks() {
            const params = buildQuery();
            document.getElementById('btn-export-excel').href = `{{ route('audit-logs.exportExcel') }}?${params}`;
            document.getElementById('btn-export-pdf').href = `{{ route('audit-logs.exportPdf') }}?${params}`;
        }

        async function loadResults(pushState = true) {
            if (!results) {
                return;
            }

            results.classList.add('opacity-70');

            try {
                const response = await fetch(`${form.action}?${buildQuery()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const payload = await response.json();
                results.innerHTML = payload.html || '';

                if (pushState) {
                    const nextUrl = new URL(window.location.href);
                    nextUrl.search = buildQuery();
                    window.history.replaceState({}, '', nextUrl);
                }
            } catch (error) {
                results.innerHTML = `
                    <div class="rounded-[28px] border border-rose-200 bg-rose-50 px-5 py-4 text-[15px] text-rose-700">
                        Unable to load audit logs right now. Please try again.
                    </div>
                `;
            } finally {
                results.classList.remove('opacity-70');
            }
        }

        updateExportLinks();

        form.querySelectorAll('input, select').forEach((field) => {
            const eventName = field.tagName === 'SELECT' || field.type === 'date' ? 'change' : 'input';
            field.addEventListener(eventName, () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => loadResults(true), 300);
                updateExportLinks();
            });
        });

        document.addEventListener('click', async (event) => {
            const link = event.target.closest('#audit-logs-results .app-pagination a');
            if (!link) {
                return;
            }

            event.preventDefault();
            const url = new URL(link.href);
            const page = url.searchParams.get('page');
            if (page) {
                let pageInput = form.querySelector('input[name="page"]');
                if (!pageInput) {
                    pageInput = document.createElement('input');
                    pageInput.type = 'hidden';
                    pageInput.name = 'page';
                    form.appendChild(pageInput);
                }
                pageInput.value = page;
            }

            await loadResults(true);
        });
    });
</script>
@endsection
