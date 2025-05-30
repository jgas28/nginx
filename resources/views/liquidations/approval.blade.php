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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Expenses --}}
        <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
            <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2">Expenses</h3>
            <ul class="space-y-2">
                @foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field)
                    <li class="flex justify-between">
                        <span class="capitalize">{{ str_replace('_', ' ', $field) }}</span>
                        <span class="font-semibold">₱{{ number_format($liquidation->$field ?? 0, 2) }}</span>
                    </li>
                @endforeach
                <li class="flex justify-between">
                    <span>Cash Charge</span>
                    <span class="font-semibold text-indigo-600">₱{{ number_format($liquidation->cash_charge ?? 0, 2) }}</span>
                </li>
            </ul>
        </div>

        {{-- Gasoline --}}
        <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
            <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2">Gasoline</h3>
            <ul class="space-y-2">
                @forelse ($liquidation->gasoline ?? [] as $item)
                    <li class="flex justify-between">
                        <span>{{ ucfirst($item['type'] ?? '') }}</span>
                        <span class="font-semibold">₱{{ number_format($item['amount'] ?? 0, 2) }}</span>
                    </li>
                @empty
                    <li class="text-gray-400 italic">No gasoline entries.</li>
                @endforelse
            </ul>
        </div>

        {{-- RFID --}}
        <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
            <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2">RFID</h3>
            <ul class="space-y-2">
                @forelse ($liquidation->rfid ?? [] as $item)
                    <li class="flex justify-between">
                        <span>{{ ucfirst($item['tag'] ?? '') }} ({{ ucfirst($item['type'] ?? '') }})</span>
                        <span class="font-semibold">₱{{ number_format($item['amount'] ?? 0, 2) }}</span>
                    </li>
                @empty
                    <li class="text-gray-400 italic">No RFID entries.</li>
                @endforelse
            </ul>
        </div>

        {{-- Others --}}
        <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
            <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2">Others</h3>
            <ul class="space-y-2">
                @php
                    $hasOthers = false;
                @endphp

                @forelse ($liquidation->others ?? [] as $item)
                    @if (!empty($item['description']) || (isset($item['amount']) && floatval($item['amount']) > 0))
                        @php $hasOthers = true; @endphp
                        <li class="flex justify-between">
                            <span>{{ $item['description'] ?? '' }}</span>
                            <span class="font-semibold">₱{{ number_format($item['amount'] ?? 0, 2) }}</span>
                        </li>
                    @endif
                @empty
                    <li class="text-gray-400 italic">No other entries.</li>
                @endforelse

                @if (!$hasOthers)
                    <li class="text-gray-400 italic">No other entries.</li>
                @endif
            </ul>
        </div>
    </div>

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

        <!-- {{-- Refund Cash --}}
        <div class="p-4 rounded-lg shadow-inner 
            {{ $difference > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-50 text-gray-700' }} 
            font-semibold text-lg flex justify-between items-center">
            <span>Refund Cash</span>
            <span>₱{{ number_format($difference > 0 ? $difference : 0, 2) }}</span>
        </div>

        {{-- Returned Cash --}}
        <div class="p-4 rounded-lg shadow-inner 
            {{ $difference < 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-50 text-gray-700' }} 
            font-semibold text-lg flex justify-between items-center">
            <span>Returned Cash</span>
            <span>₱{{ number_format($difference < 0 ? abs($difference) : 0, 2) }}</span>
        </div> -->

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
