@extends('layouts.app')

@section('content')
<div class="mx-auto bg-white border border-gray-300 rounded-lg p-6">
    <form action="{{ route('liquidations.rejectUpdate', $liquidation->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <input type="hidden" name="cvr_id" value="{{ $liquidation->cashVoucher->id ?? '' }}">
        <input type="hidden" name="cvr_number" value="{{ $liquidation->cashVoucher->cvr_number ?? '' }}">

        <!-- Editable Expenses (except Cash Charge) -->
        <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
            <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2">Expenses</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ([
                    'allowance' => 'Allowance',
                    'manpower' => 'Manpower',
                    'hauling' => 'Hauling',
                    'right_of_way' => 'Right of Way',
                    'roro_expense' => 'Freight'
                ] as $field => $label)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                        <input type="number" step="0.01" name="{{ $field }}"
                            value="{{ old($field, $liquidation->$field ?? 0) }}"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>
                @endforeach

                <!-- Cash Charge (read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cash Charge</label>
                    <input type="text" value="₱{{ number_format($liquidation->cash_charge ?? 0, 2) }}"
                        class="mt-1 w-full bg-gray-100 text-indigo-600 font-semibold rounded-md border border-gray-300 px-3 py-2 cursor-not-allowed" readonly />
                </div>
            </div>
        </div>

        <!-- Gasoline & RFID as Separate Sections -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Gasoline Section -->
            <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
                <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2 text-gray-800">Gasoline</h3>
                <div id="gasoline-wrapper" class="space-y-3">
                    @php
                        $gasoline = is_array($liquidation->gasoline) ? $liquidation->gasoline : json_decode($liquidation->gasoline, true) ?? [];
                    @endphp
                    @foreach ($gasoline as $index => $item)
                        <div class="flex gap-2 items-center" data-index="{{ $index }}">
                            <select name="gasoline[{{ $index }}][type]" class="w-28 rounded-md border border-gray-300 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Type</option>
                                <option value="cash" {{ $item['type'] == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="card" {{ $item['type'] == 'card' ? 'selected' : '' }}>Card</option>
                            </select>
                            <input type="number" step="0.01" name="gasoline[{{ $index }}][amount]" value="{{ $item['amount'] ?? '' }}" placeholder="Amount" class="w-36 rounded-md border border-gray-300 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500" />
                            <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-600 hover:text-red-800 text-sm">✕</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addGasolineField()" class="mt-3 bg-indigo-600 text-white text-sm px-3 py-1 rounded hover:bg-indigo-700">+ Add Gasoline</button>
            </div>

            <!-- RFID Section -->
            <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
                <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2 text-gray-800">RFID</h3>
                <div id="rfid-wrapper" class="space-y-3">
                    @php
                        $rfids = is_array($liquidation->rfid) ? $liquidation->rfid : json_decode($liquidation->rfid, true) ?? [];
                    @endphp
                    @foreach ($rfids as $index => $item)
                        <div class="flex flex-wrap gap-2 items-center" data-index="{{ $index }}">
                            <select name="rfid[{{ $index }}][tag]" class="w-28 rounded-md border border-gray-300 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Tag</option>
                                <option value="autosweep" {{ $item['tag'] == 'autosweep' ? 'selected' : '' }}>AutoSweep</option>
                                <option value="easytrip" {{ $item['tag'] == 'easytrip' ? 'selected' : '' }}>EasyTrip</option>
                            </select>
                            <select name="rfid[{{ $index }}][type]" class="w-24 rounded-md border border-gray-300 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Type</option>
                                <option value="cash" {{ $item['type'] == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="card" {{ $item['type'] == 'card' ? 'selected' : '' }}>Card</option>
                            </select>
                            <input type="number" step="0.01" name="rfid[{{ $index }}][amount]" value="{{ $item['amount'] ?? '' }}" placeholder="Amount" class="w-32 rounded-md border border-gray-300 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500" />
                            <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-600 hover:text-red-800 text-sm">✕</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addRFIDField()" class="mt-3 bg-indigo-600 text-white text-sm px-3 py-1 rounded hover:bg-indigo-700">+ Add RFID</button>
            </div>

        </div>

        <!-- Others -->
        <div class="p-4 rounded-lg bg-gray-50 shadow-sm mb-6">
            <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2 text-gray-800">Others</h3>
            <div id="others-wrapper" class="space-y-3">
                @php
                    $others = is_array($liquidation->others) ? $liquidation->others : json_decode($liquidation->others, true) ?? [];
                @endphp
                @foreach ($others as $index => $item)
                    <div class="flex space-x-3 other-item">
                        <input type="text" name="others[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" placeholder="Description" class="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                        <input type="number" step="0.01" name="others[{{ $index }}][amount]" value="{{ $item['amount'] ?? '' }}" placeholder="Amount" class="w-24 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                        <button type="button" class="text-white bg-red-600 hover:bg-red-700 rounded px-3" onclick="this.parentElement.remove()">×</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="mt-3 text-indigo-600 hover:text-indigo-800 underline" onclick="addOther()">+ Add Another</button>
        </div>

        <!-- People Involved -->
        <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
            <div class="space-y-4">
                @foreach ([
                    'prepared_by' => ['label' => 'Prepared By', 'list' => $preparers],
                    'noted_by' => ['label' => 'Noted By', 'list' => $employees]
                ] as $field => $config)
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">{{ $config['label'] }}</label>
                        <select name="{{ $field }}" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
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


        <!-- Submit -->
        <div class="text-right">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500">
                Update
            </button>
        </div>
    </form>
</div>

<!-- Scripts -->
<script>
    let gasolineIndex = {{ count($gasoline) }};
    let rfidIndex = {{ count($rfids) }};

    function addGasolineField() {
        const wrapper = document.getElementById('gasoline-wrapper');
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="flex gap-2 items-center" data-index="${gasolineIndex}">
                <select name="gasoline[${gasolineIndex}][type]" class="w-32 rounded-md border border-gray-300 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Type</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
                <input type="number" step="0.01" name="gasoline[${gasolineIndex}][amount]" placeholder="Amount" class="w-40 rounded-md border border-gray-300 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-600 hover:text-red-800 text-sm">✕</button>
            </div>
        `);
        gasolineIndex++;
    }

    function addRFIDField() {
        const wrapper = document.getElementById('rfid-wrapper');
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="flex flex-wrap gap-2 items-center" data-index="${rfidIndex}">
                <select name="rfid[${rfidIndex}][tag]" class="w-32 rounded-md border border-gray-300 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Tag</option>
                    <option value="autosweep">AutoSweep</option>
                    <option value="easytrip">EasyTrip</option>
                </select>
                <select name="rfid[${rfidIndex}][type]" class="w-28 rounded-md border border-gray-300 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Type</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
                <input type="number" step="0.01" name="rfid[${rfidIndex}][amount]" placeholder="Amount" class="w-36 rounded-md border border-gray-300 px-2 py-1 focus:ring-indigo-500 focus:border-indigo-500" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-600 hover:text-red-800 text-sm">✕</button>
            </div>
        `);
        rfidIndex++;
    }

    function addOther() {
        const wrapper = document.getElementById('others-wrapper');
        const index = wrapper.children.length;
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="flex space-x-3 other-item">
                <input type="text" name="others[${index}][description]" placeholder="Description" class="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                <input type="number" step="0.01" name="others[${index}][amount]" placeholder="Amount" class="w-24 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                <button type="button" class="text-white bg-red-600 hover:bg-red-700 rounded px-3" onclick="this.parentElement.remove()">×</button>
            </div>
        `);
    }
</script>
@endsection
