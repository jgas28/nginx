@extends('layouts.app')
@section('content')
@php
    use Illuminate\Support\Str;
    $totals = collect($cashVouchers)->reduce(function ($carry, $item) {
        $carry['approved'] += $item->approved_amount;
        $carry['cash'] += $item->liquidated_amount_cash ?? 0;
        $carry['card'] += $item->liquidated_amount_card ?? 0;
        $carry['liquidated'] = $carry['cash'] + $carry['card'];
        return $carry;
    }, ['approved'=>0,'cash'=>0,'card'=>0,'liquidated'=>0]);
@endphp

<form method="GET" action="{{ route('liquidations.overall') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
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
    <!-- Submit -->
    <div class="flex items-end">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
    </div>
</form>

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
@endsection
