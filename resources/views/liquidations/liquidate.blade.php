@extends('layouts.app')

@section('content')
<div class="w-full mx-auto bg-white border border-gray-300 rounded-lg p-6 shadow-sm">
    <form action="{{ route('liquidations.storeSummary', ['id' => $liquidation->id]) }}" method="POST" class="space-y-8">
        @csrf
        <input type="hidden" name="cvr_id" value="{{ $liquidation->cashVoucher->id ?? '' }}">
        <input type="hidden" name="cvr_approval_id" value="{{ $liquidation->id ?? '' }}">
        <input type="hidden" name="cvr_number" value="{{ $liquidation->cashVoucher->cvr_number ?? '' }}">

        <!-- Expenses -->
        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Expenses</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 capitalize">
                            {{ $field === 'roro_expense' ? 'Freight' : str_replace('_', ' ', $field) }}
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="expenses[{{ $field }}]" 
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                @endforeach
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cash Charge</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        name="expenses[cash_charge]" 
                        value="{{ $liquidation->charge ?? '' }}" 
                        readonly 
                        class="w-full bg-gray-100 text-gray-500 cursor-not-allowed rounded-md border border-gray-300 px-3 py-2"
                    />
                </div>
            </div>
        </div>

        <!-- Gasoline -->
        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Gasoline</h2>
            <div id="gasoline-wrapper" class="space-y-3"></div>
            <button type="button" onclick="addGasolineField()" class="mt-2 bg-indigo-600 text-white text-sm px-4 py-2 rounded hover:bg-indigo-700">+ Add Gasoline</button>
        </div>

        <!-- RFID -->
        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">RFID</h2>
            <div id="rfid-wrapper" class="space-y-3"></div>
            <button type="button" onclick="addRFIDField()" class="mt-2 bg-indigo-600 text-white text-sm px-4 py-2 rounded hover:bg-indigo-700">+ Add RFID</button>
        </div>

        <!-- Others -->
        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Others</h2>
            <div id="others-wrapper" class="space-y-3">
                <div class="flex flex-col md:flex-row gap-3 other-item">
                    <input type="text" name="others[0][description]" placeholder="Description" class="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    <input type="number" step="0.01" name="others[0][amount]" placeholder="Amount" class="w-32 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    <button type="button" class="text-white bg-red-600 hover:bg-red-700 rounded px-3 py-2" onclick="this.parentElement.remove()">×</button>
                </div>
            </div>
            <button type="button" class="mt-3 text-indigo-600 hover:text-indigo-800 underline text-sm" onclick="addOther()">+ Add Another</button>
        </div>

        <!-- People Involved -->
        <div class="bg-gray-50 p-4 rounded-md border border-gray-200 space-y-4">
            @foreach ([
                'prepared_by' => ['label' => 'Prepared By', 'list' => $preparers],
                'noted_by' => ['label' => 'Noted By', 'list' => $employees]
            ] as $field => $config)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $config['label'] }}</label>
                    <select name="{{ $field }}" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select {{ $config['label'] }}</option>
                        @foreach ($config['list'] as $person)
                            <option value="{{ $person->id }}">{{ $person->fname }} {{ $person->lname }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>

        <!-- Submit -->
        <div class="text-right">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500">
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
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="flex flex-col md:flex-row gap-3 items-center" data-index="${gasolineIndex}">
                <select name="gasoline[${gasolineIndex}][type]" class="w-full md:w-32 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Type</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
                <input type="number" step="0.01" name="gasoline[${gasolineIndex}][amount]" placeholder="Amount" class="w-full md:w-40 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-600 hover:text-red-800 text-sm">✕</button>
            </div>
        `);
        gasolineIndex++;
    }

    function addRFIDField() {
        const wrapper = document.getElementById('rfid-wrapper');
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="flex flex-col md:flex-row gap-3 items-center" data-index="${rfidIndex}">
                <select name="rfid[${rfidIndex}][tag]" class="w-full md:w-32 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Tag</option>
                    <option value="autosweep">AutoSweep</option>
                    <option value="easytrip">EasyTrip</option>
                </select>
                <select name="rfid[${rfidIndex}][type]" class="w-full md:w-28 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Type</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
                <input type="number" step="0.01" name="rfid[${rfidIndex}][amount]" placeholder="Amount" class="w-full md:w-36 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-600 hover:text-red-800 text-sm">✕</button>
            </div>
        `);
        rfidIndex++;
    }

    function addOther() {
        const wrapper = document.getElementById('others-wrapper');
        const index = wrapper.children.length;
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="flex flex-col md:flex-row gap-3 other-item">
                <input type="text" name="others[${index}][description]" placeholder="Description" class="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                <input type="number" step="0.01" name="others[${index}][amount]" placeholder="Amount" class="w-32 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                <button type="button" class="text-white bg-red-600 hover:bg-red-700 rounded px-3 py-2" onclick="this.parentElement.remove()">×</button>
            </div>
        `);
    }
</script>
@endsection
