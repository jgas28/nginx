@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Str;

    // Calculate totals based on the filtered cashVouchers
    $totals = collect($cashVouchers)->reduce(function ($carry, $item) {
        $carry['approved'] += $item->approved_amount;
        $carry['cash'] += $item->liquidated_amount_cash ?? 0;
        $carry['card'] += $item->liquidated_amount_card ?? 0;
        $carry['liquidated'] = $carry['cash'] + $carry['card'];
        return $carry;
    }, ['approved'=>0,'cash'=>0,'card'=>0,'liquidated'=>0]);
@endphp

<!-- Filter Modal -->
<div id="filterModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg">
        <h2 class="text-xl font-semibold mb-4">Filter Liquidations</h2>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('liquidations.overall') }}" class="grid grid-cols-1 gap-4">
            <!-- Company -->
            <div>
                <label class="block text-sm text-gray-600">Company</label>
                <select name="company_id" class="w-full border rounded px-2 py-1">
                    <option value="">All Companies</option>
                    @foreach (collect($cashVouchers)->pluck('company_code','company_id')->unique() as $cid => $code)
                        <option value="{{ $cid }}" @if(request('company_id')==$cid) selected @endif>
                            {{ $code }}
                        </option> 
                    @endforeach
                </select>
            </div>

            <!-- Request Type -->
            <div>
                <label class="block text-sm text-gray-600">Request Type</label>
                <select name="request_code" class="w-full border rounded px-2 py-1">
                    <option value="">All Types</option>
                    @foreach (collect($cashVouchers)->pluck('request_code')->unique()->sort() as $code)
                        <option value="{{ $code }}" @if(request('request_code') == $code) selected @endif>
                            {{ $code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- CVR Number -->
            <div>
                <label class="block text-sm text-gray-600">CVR Number</label>
                <input type="text" name="cvr_number" value="{{ request('cvr_number') }}" class="w-full border rounded px-2 py-1">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm text-gray-600">Status</label>
                <select name="status" class="w-full border rounded px-2 py-1">
                    <option value="">All Statuses</option>
                    <option value="1" @if(request('status') == '1') selected @endif>Pending Cash Approval</option>
                    <option value="3" @if(request('status') == '3') selected @endif>Rejected CVR</option>
                    <option value="5" @if(request('status') == '5') selected @endif>Completed</option>
                    <option value="10" @if(request('status') == '10') selected @endif>Rejected Liquidation</option>
                    <option value="for_validation" @if(request('status') == 'for_validation') selected @endif>For Validation</option>
                    <option value="for_collection" @if(request('status') == 'for_collection') selected @endif>For Collection</option>
                    <option value="for_approval" @if(request('status') == 'for_approval') selected @endif>For Approval</option>
                    <option value="liquidation_in_progress" @if(request('status') == 'liquidation_in_progress') selected @endif>Liquidation In Progress</option>
                </select> 
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm text-gray-600">From</label>
                <input type="date" name="date_from" value="{{ request('date_from', now()->startOfMonth()->toDateString()) }}" class="w-full border rounded px-2 py-1">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm text-gray-600">To</label>
                <input type="date" name="date_to" value="{{ request('date_to', now()->endOfMonth()->toDateString()) }}" class="w-full border rounded px-2 py-1">
            </div>

            <!-- Filter Button -->
            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Apply Filters</button>
                <button type="button" id="closeModalBtn" class="ml-2 bg-gray-400 text-white px-4 py-2 rounded">Close</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-4 shadow rounded border">
        <h4 class="text-gray-500 text-sm">Total Approved</h4>
        <div class="text-xl font-bold text-blue-600">₱{{ number_format($totals['approved'], 2) }}</div>
    </div>
    <div class="bg-white p-4 shadow rounded border">
        <h4 class="text-gray-500 text-sm mb-1">Total Liquidated</h4>
        <div class="text-xl font-bold text-green-600 mb-1">₱{{ number_format($totals['liquidated'], 2) }}</div>
        <div class="text-sm text-gray-700">
            Cash: ₱{{ number_format($totals['cash'], 2) }}<br>
            Card: ₱{{ number_format($totals['card'], 2) }}
        </div>
    </div>
</div>
<!-- Filter Button to open Modal -->
<div class="flex justify-end mb-4">
    <button id="filterBtn" class="bg-blue-600 text-white px-4 py-2 rounded">
        Filter
    </button>
</div>
<!-- Table -->
<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200 rounded shadow-sm mb-8">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2">CVR No.</th>
                <th class="px-4 py-2">Type</th>
                <th class="px-4 py-2 text-right">Request type</th>
                <th class="px-4 py-2 text-right">Requested</th>
                <th class="px-4 py-2 text-right">Approved</th>
                <th class="px-4 py-2 text-right">Liqui (Cash)</th>
                <th class="px-4 py-2 text-right">Liqui (Card)</th>
                <th class="px-4 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse (collect($cashVouchers)->sortBy('cvr_number') as $v)
                <tr class="even:bg-gray-50">
                    <td class="px-4 py-2">
                        @if($v->cvr_type === 'admin')
                            {{ Str::before($v->cvr_number,'/') }}-{{ $v->company_code }}{{ $v->expense_code }}
                        @else
                            {{ Str::before($v->cvr_number,'/') }}-{{ $v->truck_name }}-{{ $v->company_code }}{{ $v->expense_code }}
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ $v->cvr_type }}</td> 
                    <td class="px-4 py-2 text-right">{{ $v->request_code }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($v->requested_amount, 2) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($v->approved_amount, 2) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($v->liquidated_amount_cash ?? 0, 2) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($v->liquidated_amount_card ?? 0, 2) }}</td>
                    <td class="px-4 py-2">{{ ucfirst($v->overall_status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center px-4 py-4 text-gray-500">No records found for selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<script>
    // Get modal element
    const modal = document.getElementById('filterModal');
    const filterBtn = document.getElementById('filterBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');

    // Open modal when filter button is clicked
    filterBtn.addEventListener('click', function () {
        modal.classList.remove('hidden');
    });

    // Close modal when close button is clicked
    closeModalBtn.addEventListener('click', function () {
        modal.classList.add('hidden');
    });

    // Close modal if clicked outside of it
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
</script>
@endsection

