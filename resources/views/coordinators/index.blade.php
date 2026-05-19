@extends('layouts.app')

@section('title', 'FCZCNYX')

@php
    $tabs = [
        'list' => ['label' => 'List', 'icon' => 'fas fa-list-ul', 'accent' => 'from-blue-500/15 to-sky-500/10 text-blue-700'],
        'status4' => ['label' => 'Pull Out', 'icon' => 'fas fa-truck-ramp-box', 'accent' => 'from-orange-500/15 to-amber-500/10 text-orange-700'],
        'status8' => ['label' => 'For Allocation', 'icon' => 'fas fa-diagram-project', 'accent' => 'from-violet-500/15 to-fuchsia-500/10 text-violet-700'],
        'status9' => ['label' => 'Allocated', 'icon' => 'fas fa-location-crosshairs', 'accent' => 'from-teal-500/15 to-cyan-500/10 text-teal-700'],
        'status10' => ['label' => 'Delivered', 'icon' => 'fas fa-circle-check', 'accent' => 'from-emerald-500/15 to-lime-500/10 text-emerald-700'],
        'status11' => ['label' => 'Hold / Cancel', 'icon' => 'fas fa-ban', 'accent' => 'from-rose-500/15 to-red-500/10 text-rose-700'],
        'staging' => ['label' => 'Staging', 'icon' => 'fas fa-warehouse', 'accent' => 'from-slate-500/15 to-zinc-500/10 text-slate-700'],
        'accessorial' => ['label' => 'Accessorial', 'icon' => 'fas fa-screwdriver-wrench', 'accent' => 'from-indigo-500/15 to-blue-500/10 text-indigo-700'],
    ];
    $activeTab = $activeTab ?? request('tab', 'list');
@endphp

@section('content')
<style>
    /* Hide scrollbar but keep scroll functionality */
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .scrollbar-hide::-webkit-scrollbar { display: none; }

    /* Active tab pill indicator */
    .tab-button.tab-active {
        border-color: #93c5fd;
        background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
        box-shadow: 0 8px 20px rgba(59,130,246,0.15), 0 0 0 1px rgba(147,197,253,0.4);
        transform: translateY(-2px);
    }
    .tab-button.tab-active span:last-child {
        color: #1d4ed8;
        font-weight: 700;
    }
</style>
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[30px] border border-slate-200/80 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.07)]">
        <div class="border-b border-slate-200/80 bg-gradient-to-r from-slate-50 via-white to-blue-50/60 px-6 py-7 sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white">
                            <i class="fas fa-user-tie text-sm"></i>
                        </span>
                        Coordinator Queue
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Coordinator Requests</h1>
                        <p class="max-w-3xl text-[15px] leading-7 text-slate-600 sm:text-base">
                            Monitor MTM requests with faster filters, lighter tab loading, and a cleaner table view per delivery stage.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('coordinators.create') }}"
                    class="inline-flex w-full items-center justify-center gap-3 rounded-2xl bg-blue-600 px-5 py-3 text-[15px] font-semibold text-white shadow-[0_12px_24px_rgba(37,99,235,0.22)] transition hover:-translate-y-0.5 hover:bg-blue-700 sm:w-auto"
                >
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/20">
                        <i class="fas fa-plus"></i>
                    </span>
                    Create Delivery Request
                </a>
            </div>
        </div>

        <div class="px-6 py-6 sm:px-8">
            <form id="filter-form" method="GET" class="rounded-[28px] border border-slate-200 bg-slate-50/80 p-5 shadow-inner shadow-slate-100">
                <input type="hidden" name="tab" id="active-tab" value="{{ $activeTab }}">

                <div class="grid gap-4 lg:grid-cols-3 lg:items-end">
                    <label class="block">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-hashtag text-violet-600"></i>
                            MTM
                        </span>
                        <input
                            type="text"
                            name="mtm"
                            value="{{ request('mtm') }}"
                            placeholder="Filter MTM"
                            class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                    </label>

                    <label class="block">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-calendar-day text-emerald-600"></i>
                            Date From
                        </span>
                        <input
                            type="date"
                            name="date_from"
                            value="{{ request()->filled('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('Y-m-d') : '' }}"
                            class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            autocomplete="off"
                        >
                    </label>

                    <label class="block">
                        <span class="mb-2 flex items-center gap-2 text-[15px] font-semibold text-slate-800">
                            <i class="fas fa-calendar-check text-amber-600"></i>
                            Date To
                        </span>
                        <input
                            type="date"
                            name="date_to"
                            value="{{ request()->filled('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('Y-m-d') : '' }}"
                            class="h-[56px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            autocomplete="off"
                        >
                    </label>
                </div>

                <div class="mt-4 flex flex-col gap-4 border-t border-slate-200 pt-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0 w-full flex-1 max-w-[720px]">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                                <i class="fas fa-search text-sm"></i>
                            </span>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search MTM, area, province, company..."
                                class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white pl-10 pr-4 text-[15px] text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >
                        </div>
                    </div>

                    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center sm:justify-end lg:w-auto">
                        <span class="whitespace-nowrap text-[15px] font-semibold text-slate-700">Show</span>
                        <select
                            name="per_page"
                            class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 sm:w-[160px]"
                        >
                            @foreach ([5, 10, 25, 50] as $size)
                                <option value="{{ $size }}" {{ (int) request('per_page', 10) === $size ? 'selected' : '' }}>{{ $size }} rows</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            <!-- Tab navigation — horizontally scrollable, works on all screen sizes -->
            <div class="relative mt-6">
                <!-- Fade edges hint at scrollability -->
                <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-5 bg-gradient-to-r from-slate-100/80 to-transparent"></div>
                <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-5 bg-gradient-to-l from-slate-100/80 to-transparent"></div>

                <div class="overflow-x-auto pb-2 scrollbar-hide" id="tabs-scroll">
                    <div class="flex gap-2.5 px-1" id="tabs" style="width: max-content;">
                        @foreach ($tabs as $tabKey => $tab)
                            <button
                                type="button"
                                class="tab-button group flex shrink-0 flex-col items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md active:scale-95 sm:flex-row sm:gap-3 sm:text-left"
                                data-tab="{{ $tabKey }}"
                            >
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br transition-transform duration-200 group-hover:scale-110 {{ $tab['accent'] }}">
                                    <i class="{{ $tab['icon'] }} text-sm"></i>
                                </span>
                                <span class="text-xs font-semibold leading-tight text-slate-600 group-hover:text-blue-700 sm:text-[13px] sm:whitespace-nowrap">{{ $tab['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_12px_28px_rgba(15,23,42,0.05)]">
                <div class="flex flex-col gap-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <i class="fas fa-table-columns text-lg"></i>
                        </span>
                        <div>
                            <h2 id="tab-heading" class="text-lg font-bold text-slate-900">{{ $tabs[$activeTab]['label'] ?? 'List' }}</h2>
                            <p class="text-sm text-slate-500">Only the active tab reloads to keep the queue fast.</p>
                        </div>
                    </div>
                    <div id="tab-loading-indicator" class="hidden items-center gap-2 self-start rounded-full bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 sm:self-auto">
                        <i class="fas fa-spinner fa-spin"></i>
                        Loading...
                    </div>
                </div>

                <div id="tab-container" class="min-h-[240px] bg-white">
                    @foreach (array_keys($tabs) as $tabKey)
                        <div id="tab-{{ $tabKey }}" class="tab-content {{ $tabKey === $activeTab ? '' : 'hidden' }}"></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = @json($tabs);
    const filterForm = document.getElementById('filter-form');
    const tabHiddenInput = document.getElementById('active-tab');
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    const loadingIndicator = document.getElementById('tab-loading-indicator');
    const tabHeading = document.getElementById('tab-heading');
    let debounceTimer;

    function setLoading(visible) {
        if (!loadingIndicator) {
            return;
        }

        loadingIndicator.classList.toggle('hidden', !visible);
        loadingIndicator.classList.toggle('flex', visible);
    }

    function setActiveTab(tabName) {
        tabHiddenInput.value = tabName;

        tabContents.forEach((content) => {
            content.classList.toggle('hidden', content.id !== `tab-${tabName}`);
        });

        tabButtons.forEach((button) => {
            const active = button.dataset.tab === tabName;
            button.classList.toggle('tab-active', active);
        });

        // Scroll active tab into view in the scroll container
        const activeBtn = document.querySelector(`.tab-button[data-tab="${tabName}"]`);
        if (activeBtn) {
            activeBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }

        if (tabs[tabName] && tabHeading) {
            tabHeading.textContent = tabs[tabName].label;
        }
    }

    function resetTabPagination() {
        filterForm
            .querySelectorAll('input[name$="_page"]')
            .forEach((input) => input.remove());
    }

    function syncPageInput(url, tabName) {
        resetTabPagination();

        const pageParam = `${tabName}_page`;
        const pageValue = url.searchParams.get(pageParam);
        if (!pageValue) {
            return;
        }

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = pageParam;
        input.value = pageValue;
        filterForm.appendChild(input);
    }

    function buildQuery(tabName) {
        const formData = new FormData(filterForm);
        formData.set('tab', tabName);
        return new URLSearchParams(formData).toString();
    }

    async function fetchTabData(tabName, pushState = true) {
        setActiveTab(tabName);
        setLoading(true);

        const url = `{{ route('coordinators.loadTabData') }}?${buildQuery(tabName)}`;

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const contentType = response.headers.get('content-type') || '';
            let html = '';

            if (contentType.includes('application/json')) {
                const payload = await response.json();
                html = payload.html || '';
            } else {
                html = await response.text();
            }

            const target = document.getElementById(`tab-${tabName}`);
            if (target) {
                target.innerHTML = html;
            }

            if (pushState) {
                const historyUrl = new URL(window.location.href);
                historyUrl.search = buildQuery(tabName);
                window.history.replaceState({}, '', historyUrl);
            }

            document.dispatchEvent(new CustomEvent('fast-table:loaded'));
        } catch (error) {
            const target = document.getElementById(`tab-${tabName}`);
            if (target) {
                target.innerHTML = `
                    <div class="p-6">
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-[15px] text-rose-700">
                            Unable to load this tab right now. Please try again.
                        </div>
                    </div>
                `;
            }
        } finally {
            setLoading(false);
        }
    }

    function handleFilterChange() {
        clearTimeout(debounceTimer);
        resetTabPagination();
        debounceTimer = setTimeout(() => {
            fetchTabData(tabHiddenInput.value || 'list');
        }, 350);
    }

    filterForm.querySelectorAll('input, select').forEach((field) => {
        const eventName = field.tagName === 'SELECT' || field.type === 'date' ? 'change' : 'input';
        field.addEventListener(eventName, handleFilterChange);
    });

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            resetTabPagination();
            fetchTabData(button.dataset.tab);
        });
    });

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('.pagination a');
        if (!link) {
            return;
        }

        event.preventDefault();

        const url = new URL(link.href);
        const tabName = url.searchParams.get('tab') || tabHiddenInput.value || 'list';
        syncPageInput(url, tabName);
        await fetchTabData(tabName, true);
    });

    const initialTab = new URLSearchParams(window.location.search).get('tab') || '{{ $activeTab }}';
    const initialUrl = new URL(window.location.href);
    syncPageInput(initialUrl, initialTab);
    fetchTabData(initialTab, false);
});
</script>
@endsection
