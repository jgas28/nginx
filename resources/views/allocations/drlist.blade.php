@extends('layouts.app')

@section('title', 'Delivery Request List')

@section('content')
<div class="min-h-screen bg-slate-50 py-6">
    <div class="mx-auto max-w-7xl px-4">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Delivery Request List</h1>
                <p class="mt-1 text-sm text-slate-500">Search created delivery requests, filter them quickly, and review full details without leaving the list.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" id="open-allocation-filter" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-blue-600">
                        <i class="fas fa-sliders text-xs"></i>
                    </span>
                    Filter Options
                </button>
                <a href="{{ route('allocations.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-blue-600/20 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-600/30">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/20">
                        <i class="fas fa-route text-xs"></i>
                    </span>
                    Back to Allocation
                </a>
            </div>
        </div>

        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <form id="allocation-drlist-search-form" method="GET" action="{{ route('allocation.drlist') }}" class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="xl:col-span-2">
                        <label for="mtm" class="mb-2 block text-sm font-semibold text-slate-700">MTM</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute left-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-blue-50 text-blue-600 shadow-sm">
                                <i class="fas fa-search text-sm"></i>
                            </div>
                            <input type="text" name="mtm" id="mtm" value="{{ request('mtm') }}" placeholder="Search MTM..." class="w-full rounded-2xl border border-slate-300 bg-slate-50 py-3 pl-14 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        </div>
                    </div>
                    <div>
                        <label for="month" class="mb-2 block text-sm font-semibold text-slate-700">Month</label>
                        <select name="month" id="month" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="">All months</option>
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ (string) request('month') === (string) $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="per_page" class="mb-2 block text-sm font-semibold text-slate-700">Show</label>
                        <select name="per_page" id="per_page" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            @foreach([5, 10, 25, 50] as $size)
                                <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }} rows</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-md shadow-blue-600/20 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-600/30">
                        <i class="fas fa-search text-xs"></i>
                        Search
                    </button>
                    <a href="{{ route('allocation.drlist') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow">
                        <i class="fas fa-rotate-right text-xs text-slate-500"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div id="allocation-drlist-table">
                @include('allocations.drlist-table', ['drList' => $drList, 'perPage' => $perPage])
            </div>
        </div>
    </div>
</div>

<div id="allocation-filter-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 px-4 backdrop-blur-sm">
    <div class="absolute inset-0" data-close-allocation-filter></div>
    <div class="relative z-10 w-full max-w-5xl rounded-[28px] border border-white/60 bg-white p-6 shadow-2xl shadow-slate-900/20 sm:p-8">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-900">Advanced Filters</h2>
                <p class="mt-1 text-sm text-slate-500">Narrow the delivery request list by date, company, area, region, or creator.</p>
            </div>
            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700" data-close-allocation-filter>
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="allocation-drlist-filter-form" method="GET" action="{{ route('allocation.drlist') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                <label for="date_from" class="mb-2 block text-sm font-semibold text-slate-700">Date From</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
            </div>
            <div>
                <label for="date_to" class="mb-2 block text-sm font-semibold text-slate-700">Date To</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
            </div>
            <div>
                <label for="company_id" class="mb-2 block text-sm font-semibold text-slate-700">Company</label>
                <select name="company_id" id="company_id" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    <option value="">All companies</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ (string) request('company_id') === (string) $company->id ? 'selected' : '' }}>
                            {{ $company->company_code }} - {{ $company->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="area_id" class="mb-2 block text-sm font-semibold text-slate-700">Area</label>
                <select name="area_id" id="area_id" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    <option value="">All areas</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}" {{ (string) request('area_id') === (string) $area->id ? 'selected' : '' }}>
                            {{ $area->area_code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="region_id" class="mb-2 block text-sm font-semibold text-slate-700">Region</label>
                <select name="region_id" id="region_id" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    <option value="">All regions</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}" {{ (string) request('region_id') === (string) $region->id ? 'selected' : '' }}>
                            {{ $region->province }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="created_by" class="mb-2 block text-sm font-semibold text-slate-700">Created By</label>
                <select name="created_by" id="created_by" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    <option value="">All users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ (string) request('created_by') === (string) $user->id ? 'selected' : '' }}>
                            {{ trim($user->fname . ' ' . $user->lname) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 xl:col-span-4 flex flex-wrap items-center justify-between gap-3 pt-2">
                <a href="{{ route('allocation.drlist') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-slate-700">
                    <i class="fas fa-rotate-right text-xs"></i>
                    Reset all filters
                </a>
                <div class="flex flex-wrap gap-3">
                    <button type="button" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:shadow" data-close-allocation-filter>
                        Close
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-600/20 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-600/30">
                        <i class="fas fa-filter text-xs"></i>
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="dr-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 px-4 backdrop-blur-sm">
    <div class="absolute inset-0" data-close-dr-modal></div>
    <div id="dr-modal-content" class="relative z-10 max-h-[90vh] w-full max-w-7xl overflow-y-auto rounded-[28px] border border-white/60 bg-white p-6 shadow-2xl shadow-slate-900/20 sm:p-8">
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableWrapper = document.getElementById('allocation-drlist-table');
    const searchForm = document.getElementById('allocation-drlist-search-form');
    const filterForm = document.getElementById('allocation-drlist-filter-form');
    const filterModal = document.getElementById('allocation-filter-modal');
    const drModal = document.getElementById('dr-modal');
    const drModalContent = document.getElementById('dr-modal-content');
    let debounceTimer = null;

    function bindTableEvents() {
        const mtmInput = document.getElementById('mtm');
        const perPageSelect = document.getElementById('per_page');
        const monthSelect = document.getElementById('month');

        mtmInput?.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => submitSearchForm(), 250);
        });

        perPageSelect?.addEventListener('change', submitSearchForm);
        monthSelect?.addEventListener('change', submitSearchForm);

        tableWrapper.querySelectorAll('.allocation-drlist-pagination a').forEach((link) => {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                loadTableFromUrl(this.href);
            });
        });

        tableWrapper.querySelectorAll('[data-dr-view]').forEach((button) => {
            button.addEventListener('click', function () {
                fetchDRDetails(this.dataset.drView);
            });
        });
    }

    function submitSearchForm(event) {
        if (event) {
            event.preventDefault();
        }

        const url = new URL(searchForm.action);
        const params = new URLSearchParams(new FormData(searchForm));
        const filterParams = new URLSearchParams(new FormData(filterForm));

        filterParams.forEach((value, key) => {
            if (value !== '') {
                params.set(key, value);
            } else {
                params.delete(key);
            }
        });

        params.forEach((value, key) => {
            if (value !== '') {
                url.searchParams.set(key, value);
            }
        });

        loadTableFromUrl(url.toString());
    }

    function loadTableFromUrl(url) {
        tableWrapper.classList.add('opacity-60');

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then((response) => response.text())
            .then((html) => {
                tableWrapper.innerHTML = html;
                tableWrapper.classList.remove('opacity-60');
                window.history.replaceState({}, '', url);
                bindTableEvents();
            })
            .catch((error) => {
                tableWrapper.classList.remove('opacity-60');
                console.error('Error fetching delivery request list:', error);
            });
    }

    function fetchDRDetails(drId) {
        fetch('/allocation/show/' + drId)
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Failed to load delivery request details');
                }
                return response.text();
            })
            .then((html) => {
                drModalContent.innerHTML = html;
                drModal.classList.remove('hidden');
                drModal.classList.add('flex');
            })
            .catch((error) => {
                console.error(error);
                alert('Failed to load delivery request details. Please try again.');
            });
    }

    document.getElementById('open-allocation-filter')?.addEventListener('click', function () {
        filterModal.classList.remove('hidden');
        filterModal.classList.add('flex');
    });

    document.querySelectorAll('[data-close-allocation-filter]').forEach((element) => {
        element.addEventListener('click', function () {
            filterModal.classList.remove('flex');
            filterModal.classList.add('hidden');
        });
    });

    document.querySelectorAll('[data-close-dr-modal]').forEach((element) => {
        element.addEventListener('click', function () {
            drModal.classList.remove('flex');
            drModal.classList.add('hidden');
        });
    });

    searchForm.addEventListener('submit', submitSearchForm);
    filterForm.addEventListener('submit', function (event) {
        event.preventDefault();
        filterModal.classList.remove('flex');
        filterModal.classList.add('hidden');
        submitSearchForm();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            filterModal.classList.remove('flex');
            filterModal.classList.add('hidden');
            drModal.classList.remove('flex');
            drModal.classList.add('hidden');
        }
    });

    bindTableEvents();
});
</script>
@endsection
