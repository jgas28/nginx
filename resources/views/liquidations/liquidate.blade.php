@extends('layouts.app')

@section('content')
<div class="mx-auto bg-white border border-gray-300 rounded-lg p-6">
    <form action="{{ route('liquidations.storeSummary', ['id' => $liquidation->id]) }}" method="POST" class="space-y-8">
        @csrf
        <input type="hidden" name="cvr_id" value="{{ $liquidation->cashVoucher->id ?? '' }}">
        <input type="hidden" name="cvr_approval_id" value="{{ $liquidation->id ?? '' }}">
        <input type="hidden" name="cvr_number" value="{{ $liquidation->cashVoucher->cvr_number ?? '' }}">

        <!-- Expenses -->
        <div class="space-y-4">
            <label class="font-semibold">Expenses</label>
            @foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 capitalize">{{ str_replace('_', ' ', $field) }}</label>
                    <input type="number" step="0.01" name="expenses[{{ $field }}]" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
            @endforeach
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cash Charge</label>
                <input type="number" step="0.01" name="expenses[cash_charge]" value="{{ $liquidation->charge ?? '' }}" readonly class="block w-full bg-gray-100 cursor-not-allowed rounded-md border-gray-300 shadow-sm" />
            </div>
        </div>

        <!-- Gasoline -->
        <div>
            <label class="block font-semibold mb-2">Gasoline</label>
            <div id="gasoline-wrapper" class="space-y-3"></div>
            <button type="button" onclick="addGasolineField()" class="bg-indigo-600 text-white text-sm px-3 py-1 rounded hover:bg-indigo-700">+ Add Gasoline</button>
        </div>

        <!-- RFID -->
        <div>
            <label class="block font-semibold mb-2">RFID</label>
            <div id="rfid-wrapper" class="space-y-3"></div>
            <button type="button" onclick="addRFIDField()" class="bg-indigo-600 text-white text-sm px-3 py-1 rounded hover:bg-indigo-700">+ Add RFID</button>
        </div>

        <!-- Others -->
        <div>
            <label class="block font-semibold mb-2">Others</label>
            <div id="others-wrapper" class="space-y-3">
                <div class="flex space-x-3 other-item">
                    <input type="text" name="others[0][description]" placeholder="Description" class="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    <input type="number" step="0.01" name="others[0][amount]" placeholder="Amount" class="w-24 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    <button type="button" class="text-white bg-red-600 hover:bg-red-700 rounded px-3" onclick="this.parentElement.remove()">×</button>
                </div>
            </div>
            <button type="button" class="mt-2 text-indigo-600 hover:text-indigo-800 underline" onclick="addOther()">Add Another</button>
        </div>

        <!-- People Involved -->
        <div class="space-y-4">
            @foreach (['prepared_by' => 'Prepared By', 'noted_by' => 'Noted By'] as $field => $label)
                <div>
                    <label class="block font-medium text-gray-700 mb-1">{{ $label }}</label>
                    <select name="{{ $field }}" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select {{ $label }}</option>
                        @foreach ($preparers as $preparer)
                            <option value="{{ $preparer->id }}">{{ $preparer->fname }} {{ $preparer->lname }}</option>
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
