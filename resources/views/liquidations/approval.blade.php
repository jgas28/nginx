@extends('layouts.app')

@section('content')
<div class="mx-auto bg-white p-8 shadow-lg rounded-lg">

    <h2 class="text-2xl font-bold mb-6 border-b border-gray-200 pb-3">Validate Liquidation</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8 text-gray-700">
        <div>
            <span class="font-semibold text-gray-900">CVR Number:</span>
            <span class="ml-2">{{ $liquidation->cvr_number }}</span>
        </div>
        <div>
            <span class="font-semibold text-gray-900">Approved Amount:</span>
            <span class="ml-2 text-indigo-600 font-medium">₱{{ number_format($approvedAmount, 2) }}</span>
        </div>
        <div>
            <span class="font-semibold text-gray-900">Prepared By:</span>
            <span class="ml-2">{{ $liquidation->preparedBy->fname ?? '' }} {{ $liquidation->preparedBy->lname ?? '' }}</span>
        </div>
        <div>
            <span class="font-semibold text-gray-900">Noted By:</span>
            <span class="ml-2">{{ $liquidation->notedBy->fname ?? '' }} {{ $liquidation->notedBy->lname ?? '' }}</span>
        </div>
    </div>

    <form action="{{ route('liquidations.approvedEdit', $liquidation->id) }}" method="POST">
    @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
                <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2">Expenses</h3>
                @foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense', 'cash_charge'] as $field)
                    <div class="mb-3">
                        <label class="block font-medium capitalize">{{ str_replace('_', ' ', $field) }}</label>
                        <input type="number" step="0.01" name="{{ $field }}" value="{{ old($field, $liquidation->$field) }}" class="w-full px-3 py-2 border rounded" />
                    </div>
                @endforeach
            </div>

            {{-- Gasoline --}}
            <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
                <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2 flex justify-between items-center">
                    Gasoline
                    <button type="button" onclick="addGasolineField()" class="bg-indigo-600 text-white text-sm px-3 py-1 rounded hover:bg-indigo-700">+ Add Gasoline</button>
                </h3>
                <div id="gasolineList">
                   @if(is_array($liquidation->gasoline))
                        @forelse ($liquidation->gasoline as $index => $item)
                            <div class="flex gap-2 mb-2">
                                <input type="text" name="gasoline[{{ $index }}][type]" value="{{ $item['type'] ?? '' }}" placeholder="Type" class="w-1/2 px-2 py-1 border rounded" />
                                <input type="number" step="0.01" name="gasoline[{{ $index }}][amount]" value="{{ $item['amount'] ?? 0 }}" placeholder="Amount" class="w-1/2 px-2 py-1 border rounded" />
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">No gasoline entries.</p>
                        @endforelse
                    @else
                        <p class="text-sm text-gray-400">Gasoline data is not available.</p>
                    @endif
                </div>
            </div>

            {{-- RFID --}}
            <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
                <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2 flex justify-between items-center">
                    RFID
                    <button type="button" onclick="addRFIDField()" class="bg-indigo-600 text-white text-sm px-3 py-1 rounded hover:bg-indigo-700">+ Add RFID</button>
                </h3>
                <div id="rfidList">
                    @if(is_array($liquidation->rfid))
                        @forelse ($liquidation->rfid as $index => $item)
                            <div class="flex gap-2 mb-2">
                            <input type="text" name="rfid[{{ $index }}][tag]" value="{{ $item['tag'] ?? '' }}" placeholder="Tag" class="w-1/2 px-2 py-1 border rounded" />
                            <input type="text" name="rfid[{{ $index }}][type]" value="{{ $item['type'] ?? '' }}" placeholder="Type" class="w-1/4 px-2 py-1 border rounded" />
                            <input type="number" step="0.01" name="rfid[{{ $index }}][amount]" value="{{ $item['amount'] ?? 0 }}" placeholder="Amount" class="w-1/4 px-2 py-1 border rounded" />
                        </div>
                        @empty
                            <p class="text-sm text-gray-400">No gasoline entries.</p>
                        @endforelse
                    @else
                        <p class="text-sm text-gray-400">No RFID entries.</p>
                    @endif
                </div>
            </div>

            {{-- Others --}}
            <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
                <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2 flex justify-between items-center">
                    Others
                    <button type="button" onclick="addOther()" class="bg-indigo-600 text-white text-sm px-3 py-1 rounded hover:bg-indigo-700">+ Add Other</button>
                </h3>
                <div id="othersList">
                    @forelse ($liquidation->others ?? [] as $index => $item)
                        @if (!is_null($item['description']) && !is_null($item['amount']))
                            <div class="flex gap-2 mb-2">
                                <input type="text" name="others[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" placeholder="Description" class="w-2/3 px-2 py-1 border rounded" />
                                <input type="number" step="0.01" name="others[{{ $index }}][amount]" value="{{ $item['amount'] ?? 0 }}" placeholder="Amount" class="w-1/3 px-2 py-1 border rounded" />
                            </div>
                        @endif
                    @empty
                        <p class="text-sm text-gray-400">No other entries.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                Save Changes
            </button>
        </div>
    </form>

    {{-- Liquidation Summary Totals --}}
    <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6 font-medium text-lg">
        {{-- Approved Amount --}}
        <div class="bg-indigo-50 p-4 rounded shadow">
            <p class="text-gray-800">Approved Amount</p>
            <p class="text-indigo-700 text-right text-xl font-bold">₱{{ number_format($approvedAmount, 2) }}</p>
        </div>

        {{-- Total Expense --}}
        <div class="bg-green-50 p-4 rounded shadow">
            <p class="text-gray-800">Total Expense</p>
            <p class="text-green-700 text-right text-xl font-bold">₱{{ number_format($totalCash, 2) }}</p>
        </div>

        {{-- Start Here --}}
        @php
            // Calculate total amount from runningRefunds and make sure it's positive
            $refundTotal = 0;
            if (isset($runningRefunds)) {
                $refundTotal = $runningRefunds->sum(function ($item) {
                    return isset($item->amount) ? abs($item->amount) : 0;
                });
            }
        @endphp

        <div class="p-4 rounded-lg shadow-inner 
            {{ $difference > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-50 text-gray-700' }} 
            font-semibold text-lg flex flex-col">

            <div class="flex justify-between items-center mb-2">
                <span>Refund Request</span>
                <span>₱{{ number_format($refundTotal, 2) }}</span>
            </div>

            @if(isset($runningRefunds) && $runningRefunds->count() > 0)
                <ul class="text-sm text-red-800 space-y-1 max-h-32 overflow-auto border border-red-300 p-2 rounded bg-red-50">
                    @foreach ($runningRefunds as $refund)
                        <li class="flex justify-between">
                            <span>{{ $refund->description ?? 'No description' }}</span>
                            <span>₱{{ number_format(abs($refund->amount), 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-xs italic text-red-700 mt-1">No refund details available.</p>
            @endif
        </div>
        
        <div class="p-4 rounded-lg shadow-inner 
            {{ $difference < 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-50 text-gray-700' }} 
            font-semibold text-lg flex flex-col">

            @php
                // Combine returns and uncollected into one collection or array
                $combinedReturns = collect();

                if (isset($runningReturns)) {
                    $combinedReturns = $combinedReturns->merge($runningReturns);
                }
                if (isset($runningUncollected)) {
                    $combinedReturns = $combinedReturns->merge($runningUncollected);
                }

                // Calculate total amount from the combined list
                // Use abs() for uncollected amounts if negative
                $combinedTotal = $combinedReturns->sum(function ($item) {
                    return isset($item->amount) ? abs($item->amount) : 0;
                });
            @endphp

        <div class="flex justify-between items-center mb-2">
            <span>Returned Request</span>
            <span>₱{{ number_format($combinedTotal, 2) }}</span>
        </div>

            @php
                // Combine returns and uncollected into one collection or array
                $combinedReturns = collect();

                if(isset($runningReturns)) {
                    $combinedReturns = $combinedReturns->merge($runningReturns);
                }
                if(isset($runningUncollected)) {
                    $combinedReturns = $combinedReturns->merge($runningUncollected);
                }
            @endphp

            @if($combinedReturns->count() > 0)
                <ul class="text-sm text-yellow-800 space-y-1 max-h-32 overflow-auto border border-yellow-300 p-2 rounded bg-yellow-50">
                    @foreach ($combinedReturns as $item)
                        <li class="flex justify-between">
                            <span>{{ $item->description ?? 'No description' }}</span>
                            <span>₱{{ number_format($item->amount, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-xs italic text-yellow-700 mt-1">No returned cash details available.</p>
            @endif
        </div>
    </div>

    <div class="mt-8 pt-6 border-t border-gray-200">
        <form action="{{ route('liquidations.approved', $liquidation->id) }}" method="POST" class="bg-gray-50 p-4 rounded-lg shadow-sm">
            @csrf
            <label for="approved_by" class="block mb-2 font-medium text-gray-700">Approved By</label>
            <select id="approved_by" name="approved_by" required class="w-full border border-gray-300 rounded px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="" disabled selected>Select employee</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                @endforeach
            </select>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded transition duration-150">
                Approved
            </button>
        </form>
    </div>
</div>
<!-- Modal JavaScript -->
<script>
    let gasolineIndex = 0;
    let rfidIndex = 0;

    function addGasolineField() {
        const wrapper = document.getElementById('gasolineList'); // <-- corrected ID
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
        const wrapper = document.getElementById('rfidList'); // <-- corrected ID
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
        const wrapper = document.getElementById('othersList'); // <-- corrected ID
        const index = wrapper.children.length;
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="flex space-x-3 other-item">
                <input type="text" name="others[${index}][description]" placeholder="Description" class="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                <input type="number" step="0.01" name="others[${index}][amount]" placeholder="Amount" class="w-24 rounded-md border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                <button type="button" class="text-white bg-red-600 hover:bg-red-700 rounded px-3" onclick="this.parentElement.remove()">×</button>
            </div>
        `);
    }
    
    document.addEventListener('DOMContentLoaded', function () {
        const openBtn = document.getElementById('openModalBtn');
        const modal = document.getElementById('reimbursementModal');
        const closeBtn = document.getElementById('closeModalBtn');

        openBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });

        closeBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        // Close modal when clicking outside the panel
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });

        // Escape key closes modal
        document.addEventListener('keydown', function (e) {
            if (e.key === "Escape") {
                modal.classList.add('hidden');
            }
        });
    });
</script>
@endsection
