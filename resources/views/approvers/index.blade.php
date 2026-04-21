@extends('layouts.app')

@section('title', 'Approvers')

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        <section class="rounded-[28px] border border-white/70 bg-white/90 px-6 py-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Settings</p>
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Approvers</h1>
                    <p class="max-w-2xl text-sm text-slate-600 sm:text-base">
                        Review approver assignments, search by name or site, and manage records from one place.
                    </p>
                </div>

                <a
                    href="{{ route('approvers.create') }}"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 sm:w-auto"
                >
                    Create New Approver
                </a>
            </div>
        </section>

        <section class="rounded-[28px] border border-white/70 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:p-6">
            <form method="GET" action="{{ route('approvers.index') }}" class="mb-6">
                <label for="search-input" class="sr-only">Search approvers</label>
                <div class="relative max-w-2xl">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fas fa-search text-sm"></i>
                    </span>
                    <input
                        type="search"
                        id="search-input"
                        name="search"
                        value="{{ $search ?? '' }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100"
                        placeholder="Search approvers by name or site"
                        autocomplete="off"
                    >
                </div>
            </form>

            <div id="approvers-table" class="overflow-hidden rounded-3xl border border-slate-200 bg-white">
                @include('approvers.table', ['approvers' => $approvers])
            </div>
        </section>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('search-input');
        const tableContainer = document.getElementById('approvers-table');

        if (!searchInput || !tableContainer) {
            return;
        }

        let debounceTimer;
        let currentRequest;

        const loadApprovers = async (query) => {
            if (currentRequest) {
                currentRequest.abort();
            }

            currentRequest = new AbortController();

            const url = new URL(`{{ route('approvers.index') }}`, window.location.origin);
            if (query.trim() !== '') {
                url.searchParams.set('search', query.trim());
            }

            tableContainer.classList.add('opacity-60', 'pointer-events-none');

            try {
                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: currentRequest.signal,
                });

                tableContainer.innerHTML = await response.text();
                window.history.replaceState({}, '', url.toString());
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Error fetching approvers:', error);
                }
            } finally {
                tableContainer.classList.remove('opacity-60', 'pointer-events-none');
            }
        };

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadApprovers(searchInput.value), 250);
        });

        tableContainer.addEventListener('click', async (event) => {
            const link = event.target.closest('.approvers-pagination a');

            if (!link) {
                return;
            }

            event.preventDefault();
            tableContainer.classList.add('opacity-60', 'pointer-events-none');

            try {
                const response = await fetch(link.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                tableContainer.innerHTML = await response.text();
                window.history.replaceState({}, '', link.href);
            } catch (error) {
                console.error('Error loading pagination:', error);
            } finally {
                tableContainer.classList.remove('opacity-60', 'pointer-events-none');
            }
        });
    });
</script>
@endsection
