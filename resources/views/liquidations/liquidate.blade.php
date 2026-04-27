@extends('layouts.app')

@section('content')
<div class="mx-auto w-full rounded-lg border border-gray-300 bg-white p-6 shadow-sm">
    <form action="{{ route('liquidations.storeSummary', ['id' => $liquidation->id]) }}" method="POST" class="space-y-8">
        @csrf
        <input type="hidden" name="cvr_id" value="{{ $liquidation->cashVoucher->id ?? '' }}">
        <input type="hidden" name="cvr_approval_id" value="{{ $liquidation->id ?? '' }}">
        <input type="hidden" name="cvr_number" value="{{ $liquidation->cashVoucher->cvr_number ?? '' }}">

        <!-- Expenses -->
        <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
            <h2 class="mb-4 text-lg font-semibold text-gray-700">Expenses</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field)
                    <div>
                        <label class="mb-1 block text-sm font-medium capitalize text-gray-700">
                            {{ $field === 'roro_expense' ? 'Freight' : str_replace('_', ' ', $field) }}
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            name="expenses[{{ $field }}]"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                @endforeach
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Cash Charge</label>
                    <input
                        type="number"
                        step="0.01"
                        name="expenses[cash_charge]"
                        value="{{ $liquidation->charge ?? '' }}"
                        readonly
                        class="w-full cursor-not-allowed rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-gray-500"
                    />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Gasoline -->
            <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3">
                    <h2 class="text-lg font-semibold text-slate-700">Gasoline</h2>
                    <button type="button" onclick="addGasolineField()" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">+ Add</button>
                </div>
                <div id="gasoline-wrapper" class="space-y-3"></div>
            </div>

            <!-- RFID -->
            <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3">
                    <h2 class="text-lg font-semibold text-slate-700">RFID</h2>
                    <button type="button" onclick="addRFIDField()" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">+ Add</button>
                </div>
                <div id="rfid-wrapper" class="space-y-3"></div>
            </div>
        </div>

        <!-- Others -->
        <div class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3">
                <h2 class="text-lg font-semibold text-slate-700">Others</h2>
                <button type="button" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700" onclick="addOther()">+ Add</button>
            </div>
            <div id="others-wrapper" class="space-y-3">
                <div class="other-item flex w-full items-center gap-3">
                    <input type="text" name="others[0][description]" placeholder="Description" class="w-[60%] min-w-0 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500 sm:px-5 sm:text-base" />
                    <input type="number" step="0.01" name="others[0][amount]" placeholder="Amount" class="w-[35%] min-w-0 rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500 sm:px-4 sm:text-base" />
                    <button type="button" class="inline-flex h-10 w-[5%] min-w-[44px] items-center justify-center rounded-xl bg-red-600 text-2xl text-white transition hover:bg-red-700" onclick="this.parentElement.remove()">&times;</button>
                </div>
            </div>
        </div>

        <!-- People Involved -->
        <div class="space-y-4 rounded-md border border-gray-200 bg-gray-50 p-4">
            @foreach ([
                'prepared_by' => ['label' => 'Prepared By', 'list' => $preparers],
                'noted_by' => ['label' => 'Noted By', 'list' => $employees]
            ] as $field => $config)
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ $config['label'] }}</label>
                    <div class="relative searchable-select" data-placeholder="Select {{ $config['label'] }}">
                        <input type="hidden" name="{{ $field }}" id="{{ $field }}" />
                        <button
                            type="button"
                            class="searchable-select-toggle flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            aria-expanded="false"
                        >
                            <span class="searchable-select-label text-gray-400">Select {{ $config['label'] }}</span>
                            <span class="ml-3 text-sm text-gray-400">&#9662;</span>
                        </button>
                        <div class="searchable-select-panel absolute left-0 right-0 z-20 mt-2 hidden rounded-md border border-gray-200 bg-white shadow-lg">
                            <div class="border-b border-gray-100 p-2">
                                <input
                                    type="text"
                                    class="searchable-select-input w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Search {{ $config['label'] }}"
                                />
                            </div>
                            <div class="searchable-select-options max-h-56 overflow-y-auto py-1">
                                @foreach ($config['list'] as $person)
                                    <button
                                        type="button"
                                        class="searchable-select-option block w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700"
                                        data-value="{{ $person->id }}"
                                        data-label="{{ $person->fname }} {{ $person->lname }}"
                                    >
                                        {{ $person->fname }} {{ $person->lname }}
                                    </button>
                                @endforeach
                                <div class="searchable-select-empty hidden px-3 py-2 text-sm text-gray-400">No results found</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Submit -->
        <div class="text-right">
            <button type="submit" class="rounded bg-indigo-600 px-6 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500">
                Submit
            </button>
        </div>
    </form>
</div>

<!-- Scripts -->
<script>
    let gasolineIndex = 0;
    let rfidIndex = 0;

    function addGasolineField() {
        const wrapper = document.getElementById('gasoline-wrapper');
        wrapper.insertAdjacentHTML('afterbegin', `
            <div class="flex flex-nowrap items-center gap-3" data-index="${gasolineIndex}">
                <select name="gasoline[${gasolineIndex}][type]" class="w-36 shrink-0 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Type</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
                <input type="number" step="0.01" name="gasoline[${gasolineIndex}][amount]" placeholder="Amount" class="min-w-0 flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-600 text-2xl text-white transition hover:bg-red-700" aria-label="Remove gasoline row">&times;</button>
            </div>
        `);
        gasolineIndex++;
    }

    function addRFIDField() {
        const wrapper = document.getElementById('rfid-wrapper');
        wrapper.insertAdjacentHTML('afterbegin', `
            <div class="flex flex-nowrap items-center gap-3" data-index="${rfidIndex}">
                <select name="rfid[${rfidIndex}][tag]" class="w-40 shrink-0 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Tag</option>
                    <option value="autosweep">AutoSweep</option>
                    <option value="easytrip">EasyTrip</option>
                </select>
                <select name="rfid[${rfidIndex}][type]" class="w-32 shrink-0 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Type</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
                <input type="number" step="0.01" name="rfid[${rfidIndex}][amount]" placeholder="Amount" class="min-w-0 flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-600 text-2xl text-white transition hover:bg-red-700" aria-label="Remove RFID row">&times;</button>
            </div>
        `);
        rfidIndex++;
    }

    function addOther() {
        const wrapper = document.getElementById('others-wrapper');
        const index = wrapper.querySelectorAll('.other-item').length;
        wrapper.insertAdjacentHTML('afterbegin', `
            <div class="other-item flex w-full items-center gap-3">
                <input type="text" name="others[${index}][description]" placeholder="Description" class="w-[60%] min-w-0 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500 sm:px-5 sm:text-base" />
                <input type="number" step="0.01" name="others[${index}][amount]" placeholder="Amount" class="w-[35%] min-w-0 rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500 sm:px-4 sm:text-base" />
                <button type="button" class="inline-flex h-10 w-[5%] min-w-[44px] items-center justify-center rounded-xl bg-red-600 text-2xl text-white transition hover:bg-red-700" onclick="this.parentElement.remove()">&times;</button>
            </div>
        `);
    }

    document.querySelectorAll('.searchable-select').forEach((container) => {
        const hiddenInput = container.querySelector('input[type="hidden"]');
        const toggle = container.querySelector('.searchable-select-toggle');
        const label = container.querySelector('.searchable-select-label');
        const panel = container.querySelector('.searchable-select-panel');
        const searchInput = container.querySelector('.searchable-select-input');
        const options = Array.from(container.querySelectorAll('.searchable-select-option'));
        const emptyState = container.querySelector('.searchable-select-empty');
        const placeholder = container.dataset.placeholder || 'Select an option';

        const closeDropdown = () => {
            panel.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        };

        const openDropdown = () => {
            document.querySelectorAll('.searchable-select-panel').forEach((otherPanel) => {
                if (otherPanel !== panel) {
                    otherPanel.classList.add('hidden');
                }
            });

            document.querySelectorAll('.searchable-select-toggle').forEach((otherToggle) => {
                if (otherToggle !== toggle) {
                    otherToggle.setAttribute('aria-expanded', 'false');
                }
            });

            panel.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            searchInput.focus();
        };

        const filterOptions = () => {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;

            options.forEach((option) => {
                const matches = option.dataset.label.toLowerCase().includes(query);
                option.classList.toggle('hidden', !matches);

                if (matches) {
                    visibleCount++;
                }
            });

            emptyState.classList.toggle('hidden', visibleCount > 0);
        };

        toggle.addEventListener('click', () => {
            const isOpen = !panel.classList.contains('hidden');

            if (isOpen) {
                closeDropdown();
                return;
            }

            searchInput.value = '';
            filterOptions();
            openDropdown();
        });

        searchInput.addEventListener('input', filterOptions);

        options.forEach((option) => {
            option.addEventListener('click', () => {
                hiddenInput.value = option.dataset.value;
                label.textContent = option.dataset.label;
                label.classList.remove('text-gray-400');
                label.classList.add('text-gray-700');
                closeDropdown();
            });
        });

        document.addEventListener('click', (event) => {
            if (!container.contains(event.target)) {
                closeDropdown();
            }
        });
    });
</script>
@endsection
