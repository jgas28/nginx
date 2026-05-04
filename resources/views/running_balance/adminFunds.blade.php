@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Filter form: No approver select, fixed to approver 2 --}}
    <form method="GET" action="{{ route('running_balance.adminFunds') }}" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="border rounded px-4 py-2 w-full">
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="border rounded px-4 py-2 w-full">

            <input type="hidden" name="approver_id" value="2"> {{-- Fixed approver_id --}}
            
            <select name="adjustment_type" class="border rounded px-4 py-2 w-full">
                <option value="">All Movement Types</option>
                <option value="In" {{ request('adjustment_type') == 'In' ? 'selected' : '' }}>In</option>
                <option value="Out" {{ request('adjustment_type') == 'Out' ? 'selected' : '' }}>Out</option>
                <option value="Float" {{ request('adjustment_type') == 'Float' ? 'selected' : '' }}>Float</option>
            </select>

            <select name="sort" class="border rounded px-4 py-2 w-full">
                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Sort by Date</option>
                <option value="amount" {{ request('sort') == 'amount' ? 'selected' : '' }}>Sort by Amount</option>
            </select>

            <select name="direction" class="border rounded px-4 py-2 w-full">
                <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Desc</option>
                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Asc</option>
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">Filter</button>
        </div>
    </form>

    {{-- Summary Card for Laguna Only --}}
    <div class="mb-6">
        <h3 class="text-xl font-semibold mb-2">Running Balance - Laguna</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @php $id = 2; @endphp
            <div class="p-4 border rounded shadow-sm bg-white">
                <div class="text-sm text-gray-600">{{ $approvers->firstWhere('id', $id)->name ?? 'Laguna Funds' }}</div>
                <div class="text-lg font-bold {{ ($runningTotalsByApprover[$id] ?? 0) < 0 ? 'text-red-600' : 'text-green-600' }}">
                    ₱{{ number_format($runningTotalsByApprover[$id] ?? 0, 2) }}
                </div>
                @if (isset($uncollectedByApprover[$id]) && $uncollectedByApprover[$id] != 0)
                    <div class="text-sm text-red-600 mt-1">
                        Uncollected: ₱{{ number_format($uncollectedByApprover[$id], 2) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full border text-sm text-left">
            <thead class="bg-gray-100 text-gray-700 uppercase font-bold">
                <tr>
                    <th class="border px-4 py-2">Date</th>
                    <th class="border px-4 py-2">Source</th>
                    <th class="border px-4 py-2">Type</th>
                    <th class="border px-4 py-2">Movement Type</th>
                    <th class="border px-4 py-2">Amount</th>
                    <th class="border px-4 py-2">Description</th>
                    <th class="border px-4 py-2">Employee</th>
                    <th class="border px-4 py-2">Created By</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @forelse($balances as $balance)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $balance->created_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-2">{{ $balance->approver->name ?? 'N/A' }}</td>
                    <td class="px-4 py-2">
                        @switch($balance->type)
                            @case(1) Top-up @break
                            @case(2) Collected @break
                            @case(3) Refund @break
                            @case(4) Uncollected Funds @break
                            @case(5) Salary Deduction @break
                            @case(6) Liquidated Amount @break
                            @case(7) Transfer @break
                            @case(8) Release Approved Amount @break
                            @case(10) Transfer @break
                            @case(11) Adjustment @break
                            @case(12) Adjustment for Uncollected @break
                            @default Reimbursement
                        @endswitch
                    </td>
                    <td class="px-4 py-2">{{ $balance->adjustment_type }}</td>
                    <td class="px-4 py-2 font-semibold {{ $balance->amount < 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($balance->amount, 2) }}
                    </td>
                    <td class="px-4 py-2">{{ $balance->description }}</td>
                    <td class="px-4 py-2">{{ $balance->employee->fname ?? 'N/A' }}</td>
                    <td class="px-4 py-2">{{ $balance->creator->fname ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">No transactions found for this filter.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
