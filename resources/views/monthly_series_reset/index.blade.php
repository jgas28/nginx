@extends('layouts.app')

@section('title', 'Reset Series Numbers')

@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    @php
        $maxChartValue = max(1, collect($chartSeries)->max('value') ?? 0);
    @endphp

    <style>
        #monthly-series-reset-page .dataTables_wrapper .dataTables_length,
        #monthly-series-reset-page .dataTables_wrapper .dataTables_filter {
            display: none;
        }

        #monthly-series-filter-bar {
            display: grid;
            gap: 0.75rem;
        }

        #monthly-series-filter-reset {
            background: #0f172a;
            color: #ffffff;
            border: 1px solid #0f172a;
        }

        @media (min-width: 1024px) {
            #monthly-series-filter-bar {
                grid-template-columns: repeat(4, minmax(0, 1fr));
                align-items: end;
            }
        }

        #monthly-series-reset-page .dataTables_wrapper .dataTables_info,
        #monthly-series-reset-page .dataTables_wrapper .dataTables_paginate {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 1rem;
        }

        #monthly-series-reset-page .dataTables_wrapper .monthly-series-table-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            width: 100%;
        }

        #monthly-series-reset-page .dataTables_wrapper .monthly-series-table-footer .dataTables_info {
            margin-top: 0;
            padding-top: 0;
            text-align: left;
        }

        #monthly-series-reset-page .dataTables_wrapper .monthly-series-table-footer .dataTables_paginate {
            margin-top: 0;
            padding-top: 0;
            margin-left: auto;
            text-align: right;
            white-space: nowrap;
        }

        #monthly-series-reset-page .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.9rem !important;
            border: 1px solid #cbd5e1 !important;
            background: #fff !important;
            color: #334155 !important;
            padding: 0.45rem 0.85rem !important;
            margin-left: 0.35rem !important;
        }

        #monthly-series-reset-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        #monthly-series-reset-page .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            border-color: #2563eb !important;
            background: #2563eb !important;
            color: #fff !important;
        }

        #monthly-series-reset-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            border-color: #93c5fd !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        #monthly-series-reset-page .dataTables_wrapper .dataTables_processing {
            border-radius: 1rem;
            border: 1px solid #dbeafe;
            background: rgba(255, 255, 255, 0.96);
            color: #1e3a8a;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
        }

        #monthly-series-reset-page .searchable-select-source {
            position: absolute;
            left: -9999px;
            opacity: 0;
            pointer-events: none;
        }

        #monthly-series-reset-page .searchable-select-panel::-webkit-scrollbar {
            width: 6px;
        }

        #monthly-series-reset-page .searchable-select-panel::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
    </style>

    <div id="monthly-series-reset-page" class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6">
        <section class="overflow-hidden rounded-[30px] border border-white/70 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_36%),linear-gradient(135deg,_#ffffff,_#eff6ff)] p-6 shadow-[0_20px_60px_rgba(15,23,42,0.12)] sm:p-8">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-600/10 px-4 py-2 text-sm font-semibold text-blue-700 ring-1 ring-blue-200">
                        <i class="fas fa-rotate-left text-xs"></i>
                        Monthly series control center
                    </div>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Reset company series with a clearer view</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        Review live running series, filter by company instantly, and reset the current cycle only when you are ready.
                    </p>
                </div>

                <div class="grid w-full gap-3 md:grid-cols-3 xl:max-w-2xl">
                    <div class="rounded-3xl border border-white/80 bg-white/85 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-500">Tracked companies</span>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-600">
                                <i class="fas fa-building"></i>
                            </span>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-slate-900">{{ number_format($series->count()) }}</p>
                        <p class="mt-1 text-sm text-slate-500">Visible for {{ $currentMonthLabel }}</p>
                    </div>
                    <div class="rounded-3xl border border-white/80 bg-white/85 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-500">Active series</span>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                <i class="fas fa-arrow-trend-up"></i>
                            </span>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-slate-900">{{ number_format($activeSeriesCount) }}</p>
                        <p class="mt-1 text-sm text-slate-500">Companies above zero</p>
                    </div>
                    <div class="rounded-3xl border border-white/80 bg-white/85 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-500">Highest series</span>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                                <i class="fas fa-chart-column"></i>
                            </span>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-slate-900">{{ number_format($highestSeries) }}</p>
                        <p class="mt-1 text-sm text-slate-500">Current top running value</p>
                    </div>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                        <i class="fas fa-circle-check"></i>
                    </span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,0.9fr)]">
            <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Series register</h2>
                        <p class="mt-1 text-sm text-slate-500">Fast server-side search, company filtering, and live pagination.</p>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600">
                        <i class="fas fa-calendar-days text-sky-600"></i>
                        {{ $currentMonthLabel }}
                    </div>
                </div>

                <div id="monthly-series-filter-bar" class="mt-6">
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Search</span>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i class="fas fa-magnifying-glass text-sm"></i>
                            </span>
                            <input id="monthly-series-search" type="text" placeholder="Search company, location, month, or series..." class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 py-2 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        </div>
                    </label>

                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Filter company</span>
                        <select id="monthly-series-company-filter" class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">All companies</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">
                                    {{ $company->company_code }} - {{ $company->company_name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Show</span>
                        <select id="monthly-series-length" class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="10">10 rows</option>
                            <option value="25">25 rows</option>
                            <option value="50">50 rows</option>
                            <option value="100">100 rows</option>
                        </select>
                    </label>

                    <div class="block w-full">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Reset</span>
                        <button id="monthly-series-reset-filters" type="button" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl px-3 text-sm font-semibold shadow-sm transition hover:opacity-95 focus:outline-none focus:ring-4 focus:ring-slate-200" style="background:#0f172a;color:#ffffff;border:1px solid #0f172a;min-width:100%;" data-role="filter-reset">
                            <i class="fas fa-rotate-right text-sm"></i>
                            Reset
                        </button>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-[26px] border border-slate-200">
                    <div class="overflow-x-auto">
                        <table id="monthlySeriesTable" class="min-w-full text-sm text-slate-700">
                            <thead>
                                <tr>
                                    <th>Company Code</th>
                                    <th>Company Name</th>
                                    <th>Location</th>
                                    <th>Month</th>
                                    <th>Running Series</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm text-sky-700">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-xl bg-white text-sky-600 shadow-sm">
                            <i class="fas fa-circle-info"></i>
                        </span>
                        <p>Search is instant across company code, name, location, month, and running series. The company filter also supports in-panel search.</p>
                    </div>
                </div>
            </section>

            <section class="space-y-6">
                <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Series snapshot</h2>
                            <p class="mt-1 text-sm text-slate-500">Top running company series this cycle.</p>
                        </div>
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                            <i class="fas fa-chart-bar"></i>
                        </span>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($chartSeries as $item)
                            <div>
                                <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-800">{{ $item['label'] }}</p>
                                        <p class="truncate text-slate-500">{{ $item['name'] }}</p>
                                    </div>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700">{{ number_format($item['value']) }}</span>
                                </div>
                                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-gradient-to-r from-sky-500 via-blue-500 to-indigo-500" style="width: {{ min(100, ($item['value'] / $maxChartValue) * 100) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                                No series data available yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[28px] border border-rose-200 bg-gradient-to-br from-rose-50 via-white to-orange-50 p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Reset all series</h2>
                            <p class="mt-1 text-sm text-slate-600">Use this only when you need to restart the running series for every company.</p>
                        </div>
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                            <i class="fas fa-triangle-exclamation"></i>
                        </span>
                    </div>

                    <div class="mt-5 rounded-2xl border border-rose-100 bg-white/80 px-4 py-4 text-sm text-slate-600">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                                <i class="fas fa-shield-halved"></i>
                            </span>
                            <div>
                                <p class="font-semibold text-slate-800">Safety note</p>
                                <p class="mt-1">This action sets every stored series number to `0` and should only be used for a full cycle reset.</p>
                            </div>
                        </div>
                    </div>

                    <form class="mt-6" action="{{ route('monthly-series.reset') }}" method="POST" onsubmit="return confirm('Are you sure you want to reset all series numbers to 0?')">
                        @csrf
                        <button type="submit" class="inline-flex h-16 w-full items-center justify-center gap-3 rounded-[22px] px-8 text-base font-semibold transition hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-red-200" style="background: linear-gradient(135deg, #dc2626 0%, #e11d48 55%, #b91c1c 100%); color: #ffffff; border: 1px solid #b91c1c; box-shadow: 0 18px 35px rgba(220, 38, 38, 0.28);">
                            <i class="fas fa-power-off text-base"></i>
                            Reset Series Numbers
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        (() => {
            function initializeSearchableSelect(select, config = {}) {
                if (!(select instanceof HTMLSelectElement) || select.dataset.searchableReady === 'true') {
                    return;
                }

                select.dataset.searchableReady = 'true';
                select.classList.add('searchable-select-source');

                const wrapper = document.createElement('div');
                wrapper.className = 'relative';
                wrapper.setAttribute('data-searchable-select-wrapper', 'true');

                const accentButton = config.buttonAccent || 'hover:border-blue-300 focus:ring-blue-500';
                const accentIcon = config.iconAccent || 'bg-blue-50 text-blue-600';
                const searchPlaceholder = config.searchPlaceholder || 'Search option...';
                const emptyText = config.emptyText || 'No matching options found.';

                wrapper.innerHTML = `
                    <button type="button" class="flex h-10 w-full items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 text-left text-sm text-slate-700 shadow-sm transition ${accentButton} focus:outline-none focus:ring-2">
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg ${accentIcon}">
                            <i class="fas fa-building text-xs"></i>
                        </span>
                        <span class="min-w-0 flex-1 truncate" data-searchable-select-label></span>
                        <span class="text-slate-400"><i class="fas fa-chevron-down text-xs"></i></span>
                    </button>
                    <div data-searchable-select-panel class="searchable-select-panel absolute left-0 right-0 z-30 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
                        <div class="border-b border-slate-200 p-3">
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <i class="fas fa-magnifying-glass text-xs"></i>
                                </span>
                                <input type="text" data-searchable-select-input placeholder="${searchPlaceholder}" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-blue-300 focus:bg-white focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                        <div data-searchable-select-list class="max-h-56 overflow-y-auto p-2"></div>
                        <div data-searchable-select-empty class="hidden px-4 py-3 text-sm text-slate-500">${emptyText}</div>
                    </div>
                `;

                select.insertAdjacentElement('afterend', wrapper);

                const button = wrapper.querySelector('button');
                const panel = wrapper.querySelector('[data-searchable-select-panel]');
                const searchInput = wrapper.querySelector('[data-searchable-select-input]');
                const list = wrapper.querySelector('[data-searchable-select-list]');
                const emptyState = wrapper.querySelector('[data-searchable-select-empty]');
                const label = wrapper.querySelector('[data-searchable-select-label]');

                const options = Array.from(select.options).map((option) => ({
                    value: option.value,
                    label: option.textContent.trim(),
                    selected: option.selected,
                }));

                const closeAll = () => {
                    document.querySelectorAll('[data-searchable-select-wrapper]').forEach((element) => {
                        if (!(element instanceof HTMLElement)) {
                            return;
                        }

                        element.dataset.open = 'false';
                        element.querySelector('[data-searchable-select-panel]')?.classList.add('hidden');
                    });
                };

                const syncLabel = () => {
                    const selectedOption = select.options[select.selectedIndex];
                    label.textContent = selectedOption ? selectedOption.textContent.trim() : 'Select option';
                };

                const renderOptions = (filter = '') => {
                    const normalizedFilter = filter.trim().toLowerCase();
                    list.innerHTML = '';

                    const filteredOptions = options.filter((option) => option.label.toLowerCase().includes(normalizedFilter));

                    filteredOptions.forEach((option) => {
                        const optionButton = document.createElement('button');
                        optionButton.type = 'button';
                        optionButton.className = 'flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm text-slate-700 transition hover:bg-blue-50 hover:text-blue-700';
                        optionButton.innerHTML = `
                            <span class="truncate">${option.label}</span>
                            <span class="${option.value === select.value ? '' : 'hidden'} text-blue-600" data-selected-indicator>
                                <i class="fas fa-check text-xs"></i>
                            </span>
                        `;

                        optionButton.addEventListener('click', () => {
                            select.value = option.value;
                            options.forEach((item) => {
                                item.selected = item.value === option.value;
                            });
                            syncLabel();
                            renderOptions(searchInput.value);
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                            closeAll();
                        });

                        list.appendChild(optionButton);
                    });

                    emptyState.classList.toggle('hidden', filteredOptions.length > 0);
                };

                syncLabel();
                renderOptions();

                button.addEventListener('click', () => {
                    const isOpen = wrapper.dataset.open === 'true';
                    closeAll();

                    if (!isOpen) {
                        wrapper.dataset.open = 'true';
                        panel.classList.remove('hidden');
                        searchInput.value = '';
                        renderOptions();
                        searchInput.focus();
                    }
                });

                searchInput.addEventListener('input', () => renderOptions(searchInput.value));

                document.addEventListener('click', (event) => {
                    if (!wrapper.contains(event.target)) {
                        wrapper.dataset.open = 'false';
                        panel.classList.add('hidden');
                    }
                });
            }

            $(document).ready(function () {
                const searchInput = document.getElementById('monthly-series-search');
                const companySelect = document.getElementById('monthly-series-company-filter');
                const lengthSelect = document.getElementById('monthly-series-length');
                const resetButton = document.getElementById('monthly-series-reset-filters');

                initializeSearchableSelect(companySelect, {
                    searchPlaceholder: 'Search company...',
                    emptyText: 'No matching companies found.'
                });

                const table = $('#monthlySeriesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: Number(lengthSelect?.value || 10),
                    autoWidth: false,
                    responsive: false,
                    ajax: {
                        url: '{{ route('monthly-series.reset.index') }}',
                        data: function (d) {
                            d.datatable = 1;
                            d.company_id = companySelect?.value || '';
                        }
                    },
                    columns: [
                        { data: 'company_code', name: 'company_code' },
                        { data: 'company_name', name: 'company_name' },
                        { data: 'company_location', name: 'company_location' },
                        {
                            data: 'month',
                            name: 'month',
                            render: function (data) {
                                return `<span class="inline-flex rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">${data}</span>`;
                            }
                        },
                        {
                            data: 'series_number',
                            name: 'series_number',
                            className: 'text-right',
                            render: function (data) {
                                const tone = Number(data) > 0
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : 'bg-slate-100 text-slate-600';

                                return `<div class="flex justify-end"><span class="inline-flex min-w-[72px] items-center justify-center rounded-full px-3 py-1 text-xs font-semibold ${tone}">${data}</span></div>`;
                            }
                        },
                        { data: 'updated_at', name: 'updated_at' }
                    ],
                    order: [[4, 'desc']],
                    language: {
                        processing: 'Loading live series data...',
                        emptyTable: 'No monthly series records found.',
                        zeroRecords: 'No matching series records found.',
                        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                        infoEmpty: 'Showing 0 to 0 of 0 entries',
                        paginate: {
                            previous: 'Prev',
                            next: 'Next'
                        }
                    },
                    dom: 'rt<"monthly-series-table-footer mt-4"ip>'
                });

                let debounceTimer;

                if (searchInput) {
                    searchInput.addEventListener('input', () => {
                        window.clearTimeout(debounceTimer);
                        debounceTimer = window.setTimeout(() => {
                            table.search(searchInput.value).draw();
                        }, 250);
                    });
                }

                if (companySelect) {
                    companySelect.addEventListener('change', () => {
                        table.ajax.reload();
                    });
                }

                if (lengthSelect) {
                    lengthSelect.addEventListener('change', () => {
                        table.page.len(Number(lengthSelect.value || 10)).draw();
                    });
                }

                if (resetButton) {
                    resetButton.addEventListener('click', () => {
                        if (searchInput) {
                            searchInput.value = '';
                        }

                        if (companySelect) {
                            companySelect.value = '';
                            companySelect.dispatchEvent(new Event('change', { bubbles: true }));
                        } else {
                            table.ajax.reload();
                        }

                        if (lengthSelect) {
                            lengthSelect.value = '10';
                            table.page.len(10);
                        }

                        table.search('').draw();
                    });
                }
            });
        })();
    </script>
@endsection
