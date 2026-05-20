@extends('layouts.app')

@section('content')
<div class="mx-auto rounded-lg border border-gray-300 bg-white p-6">
    <form action="{{ route('liquidations.rejectUpdate', $liquidation->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <input type="hidden" name="cvr_id" value="{{ $liquidation->cashVoucher->id ?? '' }}">
        <input type="hidden" name="cvr_number" value="{{ $liquidation->cashVoucher->cvr_number ?? '' }}">

        <div class="rounded-lg bg-gray-50 p-4 shadow-sm">
            <h3 class="mb-3 border-b border-gray-300 pb-2 text-lg font-semibold">Expenses</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach ([
                    'allowance' => 'Allowance',
                    'lodging' => 'Lodging',
                    'manpower' => 'Manpower',
                    'hauling' => 'Hauling',
                    'freight' => 'Freight',
                    'right_of_way' => 'Right of Way',
                    'roro_expense' => 'RoRo Expense',
                ] as $field => $label)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                        <input
                            type="number"
                            step="0.01"
                            name="{{ $field }}"
                            value="{{ old($field, $liquidation->$field ?? 0) }}"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                @endforeach

                <div>
                    <label class="block text-sm font-medium text-gray-700">Cash Charge</label>
                    <input
                        type="hidden"
                        name="cash_charge"
                        value="{{ old('cash_charge', $liquidation->cash_charge ?? 0) }}"
                        readonly
                    />
                    <input
                        type="text"
                        value="PHP {{ number_format($liquidation->cash_charge ?? 0, 2) }}"
                        class="mt-1 w-full cursor-not-allowed rounded-md border border-gray-300 bg-gray-100 px-3 py-2 font-semibold text-indigo-600"
                        readonly
                    />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="rounded-lg bg-gray-50 p-4 shadow-sm">
                <h3 class="mb-3 border-b border-gray-300 pb-2 text-lg font-semibold text-gray-800">Gasoline</h3>
                <div id="gasoline-wrapper" class="space-y-3">
                    @php
                        $gasoline = is_array($liquidation->gasoline) ? $liquidation->gasoline : json_decode($liquidation->gasoline, true) ?? [];
                    @endphp
                    @foreach ($gasoline as $index => $item)
                        <div class="flex items-center gap-2" data-index="{{ $index }}">
                            <select name="gasoline[{{ $index }}][type]" class="w-28 rounded-md border border-gray-300 px-2 py-1 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Type</option>
                                <option value="cash" {{ ($item['type'] ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="card" {{ ($item['type'] ?? '') === 'card' ? 'selected' : '' }}>Card</option>
                            </select>
                            <input
                                type="number"
                                step="0.01"
                                name="gasoline[{{ $index }}][amount]"
                                value="{{ $item['amount'] ?? '' }}"
                                placeholder="Amount"
                                class="w-36 rounded-md border border-gray-300 px-2 py-1 focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <button type="button" onclick="this.closest('[data-index]').remove()" class="text-sm text-red-600 hover:text-red-800">&times;</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addGasolineField()" class="mt-3 rounded bg-indigo-600 px-3 py-1 text-sm text-white hover:bg-indigo-700">+ Add Gasoline</button>
            </div>

            <div class="rounded-lg bg-gray-50 p-4 shadow-sm">
                <h3 class="mb-3 border-b border-gray-300 pb-2 text-lg font-semibold text-gray-800">RFID</h3>
                <div id="rfid-wrapper" class="space-y-3">
                    @php
                        $rfids = is_array($liquidation->rfid) ? $liquidation->rfid : json_decode($liquidation->rfid, true) ?? [];
                    @endphp
                    @foreach ($rfids as $index => $item)
                        <div class="flex flex-wrap items-center gap-2" data-index="{{ $index }}">
                            <select name="rfid[{{ $index }}][tag]" class="w-28 rounded-md border border-gray-300 px-2 py-1 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Tag</option>
                                <option value="autosweep" {{ ($item['tag'] ?? '') === 'autosweep' ? 'selected' : '' }}>AutoSweep</option>
                                <option value="easytrip" {{ ($item['tag'] ?? '') === 'easytrip' ? 'selected' : '' }}>EasyTrip</option>
                            </select>
                            <select name="rfid[{{ $index }}][type]" class="w-24 rounded-md border border-gray-300 px-2 py-1 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Type</option>
                                <option value="cash" {{ ($item['type'] ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="card" {{ ($item['type'] ?? '') === 'card' ? 'selected' : '' }}>Card</option>
                            </select>
                            <input
                                type="number"
                                step="0.01"
                                name="rfid[{{ $index }}][amount]"
                                value="{{ $item['amount'] ?? '' }}"
                                placeholder="Amount"
                                class="w-32 rounded-md border border-gray-300 px-2 py-1 focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <button type="button" onclick="this.closest('[data-index]').remove()" class="text-sm text-red-600 hover:text-red-800">&times;</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addRFIDField()" class="mt-3 rounded bg-indigo-600 px-3 py-1 text-sm text-white hover:bg-indigo-700">+ Add RFID</button>
            </div>
        </div>

        <div class="mb-6 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-2xl font-semibold text-slate-800">Others</h3>
                <button
                    type="button"
                    onclick="addOther()"
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                >
                    + Add
                </button>
            </div>

            <div id="others-wrapper" class="space-y-4 px-6 py-6">
                @php
                    $others = is_array($liquidation->others) ? $liquidation->others : json_decode($liquidation->others, true) ?? [];
                @endphp
                @foreach ($others as $index => $item)
                    <div class="other-item flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input
                            type="text"
                            name="others[{{ $index }}][description]"
                            value="{{ $item['description'] ?? '' }}"
                            placeholder="Description"
                            class="min-w-0 rounded-[22px] border border-slate-300 bg-white px-6 py-4 text-base text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 sm:w-[60%] lg:w-[68%]"
                        />
                        <input
                            type="number"
                            step="0.01"
                            name="others[{{ $index }}][amount]"
                            value="{{ $item['amount'] ?? '' }}"
                            placeholder="Amount"
                            class="w-full rounded-[22px] border border-slate-300 bg-white px-6 py-4 text-base text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 sm:w-[28%] lg:w-[24%]"
                        />
                        <button
                            type="button"
                            onclick="this.parentElement.remove()"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-600 text-xl font-light leading-none text-white transition hover:bg-red-700 sm:h-11 sm:w-11"
                            aria-label="Remove other row"
                        >&times;</button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-lg bg-gray-50 p-4 shadow-sm">
            <div class="space-y-4">
                @foreach ([
                    'prepared_by' => ['label' => 'Prepared By', 'list' => $preparers],
                    'noted_by' => ['label' => 'Noted By', 'list' => $employees],
                ] as $field => $config)
                    <div>
                        <label class="mb-1 block font-medium text-gray-700">{{ $config['label'] }}</label>
                        <select name="{{ $field }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select {{ $config['label'] }}</option>
                            @foreach ($config['list'] as $person)
                                <option value="{{ $person->id }}" {{ $liquidation->$field == $person->id ? 'selected' : '' }}>
                                    {{ $person->fname }} {{ $person->lname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-right">
            <button type="submit" class="rounded bg-indigo-600 px-6 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500">
                Save Changes
            </button>
        </div>
    </form>
</div>

<script>
    let gasolineIndex = {{ count($gasoline) }};
    let rfidIndex = {{ count($rfids) }};

    function addGasolineField() {
        const wrapper = document.getElementById('gasoline-wrapper');
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="flex items-center gap-2" data-index="${gasolineIndex}">
                <select name="gasoline[${gasolineIndex}][type]" class="w-32 rounded-md border border-gray-300 px-2 py-1 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Type</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
                <input type="number" step="0.01" name="gasoline[${gasolineIndex}][amount]" placeholder="Amount" class="w-40 rounded-md border border-gray-300 px-2 py-1 focus:border-indigo-500 focus:ring-indigo-500" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="text-sm text-red-600 hover:text-red-800">&times;</button>
            </div>
        `);
        gasolineIndex++;
    }

    function addRFIDField() {
        const wrapper = document.getElementById('rfid-wrapper');
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="flex flex-wrap items-center gap-2" data-index="${rfidIndex}">
                <select name="rfid[${rfidIndex}][tag]" class="w-32 rounded-md border border-gray-300 px-2 py-1 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Tag</option>
                    <option value="autosweep">AutoSweep</option>
                    <option value="easytrip">EasyTrip</option>
                </select>
                <select name="rfid[${rfidIndex}][type]" class="w-28 rounded-md border border-gray-300 px-2 py-1 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Type</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
                <input type="number" step="0.01" name="rfid[${rfidIndex}][amount]" placeholder="Amount" class="w-36 rounded-md border border-gray-300 px-2 py-1 focus:border-indigo-500 focus:ring-indigo-500" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="text-sm text-red-600 hover:text-red-800">&times;</button>
            </div>
        `);
        rfidIndex++;
    }

    function addOther() {
        const wrapper = document.getElementById('others-wrapper');
        const index = wrapper.children.length;
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="other-item flex flex-col gap-3 sm:flex-row sm:items-center">
                <input type="text" name="others[${index}][description]" placeholder="Description" class="min-w-0 rounded-[22px] border border-slate-300 bg-white px-6 py-4 text-base text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 sm:w-[60%] lg:w-[68%]" />
                <input type="number" step="0.01" name="others[${index}][amount]" placeholder="Amount" class="w-full rounded-[22px] border border-slate-300 bg-white px-6 py-4 text-base text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 sm:w-[28%] lg:w-[24%]" />
                <button type="button" onclick="this.parentElement.remove()" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-600 text-xl font-light leading-none text-white transition hover:bg-red-700 sm:h-11 sm:w-11" aria-label="Remove other row">&times;</button>
            </div>
        `);
    }
</script>
@endsection
