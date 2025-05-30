@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Date Filter Form -->
    <form method="GET" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="border rounded px-4 py-2 w-full">
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="border rounded px-4 py-2 w-full">

            <select name="approver_id" class="border rounded px-4 py-2 w-full">
                <option value="">Source Funds</option>
                @foreach($approvers as $approver)
                    <option value="{{ $approver->id }}" {{ request('approver_id') == $approver->id ? 'selected' : '' }}>
                        {{ $approver->name }}
                    </option>
                @endforeach
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

    <!-- Transaction Form -->
    <form method="POST" action="{{ route('running_balance.store') }}" class="mb-6">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-6 gap-4">
            <select name="type" class="border px-3 py-2 rounded" required>
                <option value="">Select Type</option>
                <option value="1">Top-up</option>
                <option value="5">Salary Deduction</option>
            </select>

            <input type="number" step="0.01" name="amount" class="border px-3 py-2 rounded" placeholder="Amount" required>

            <input type="text" name="description" class="border px-3 py-2 rounded" placeholder="Description">

            <select name="employee_id" class="border px-3 py-2 rounded">
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->fname }} {{ $emp->lname }}</option>
                @endforeach
            </select>

            <select name="approver_id" class="border px-3 py-2 rounded" required>
                <option value="">Select Approver</option>
                @foreach($approvers as $ap)
                    <option value="{{ $ap->id }}">{{ $ap->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded w-full">Submit</button>
        </div>
    </form>

    <!-- Running Balances Per Source -->
    <div class="mb-6">
        <h3 class="text-xl font-semibold mb-2">Running Balances by Source</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($approvers as $approver)
                <div class="p-4 border rounded shadow-sm bg-white">
                    <div class="text-sm text-gray-600">{{ $approver->name }}</div>
                    <div class="text-lg font-bold {{ ($runningTotalsByApprover[$approver->id] ?? 0) < 0 ? 'text-red-600' : 'text-green-600' }}">
                        ₱{{ number_format($runningTotalsByApprover[$approver->id] ?? 0, 2) }}
                    </div>
                    @if (isset($uncollectedByApprover[$approver->id]) && $uncollectedByApprover[$approver->id] != 0)
                        <div class="text-sm text-red-600 mt-1">
                            Uncollected: ₱{{ number_format($uncollectedByApprover[$approver->id], 2) }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full border text-sm text-left">
            <thead class="bg-gray-100 text-gray-700 uppercase font-bold">
                <tr>
                    <th class="border px-4 py-2">Date</th>
                    <th class="border px-4 py-2">Source</th>
                    <th class="border px-4 py-2">Type</th>
                    <th class="border px-4 py-2">Amount</th>
                    <th class="border px-4 py-2">Description</th>
                    <th class="border px-4 py-2">Employee</th>
                    <th class="border px-4 py-2">Created By</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach($balances as $balance)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $balance->created_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-2">{{ $balance->approver->name ?? 'N/A' }}</td>
                    <td class="px-4 py-2">
                        @if($balance->type == 1)
                            Top-up
                        @elseif($balance->type == 2)
                            Collected
                        @elseif($balance->type == 3)
                            Refund
                        @elseif($balance->type == 4)
                            Uncollected Funds
                        @elseif($balance->type == 5)
                            Salary Deduction
                        @elseif($balance->type == 6)
                            Liquidated Amount
                        @elseif($balance->type == 7)
                            Transfer
                        @elseif($balance->type == 8)
                            Release Aprroved Amount
                        @else
                            Reimbursement
                        @endif
                    </td>
                    <td class="px-4 py-2 font-semibold {{ $balance->amount < 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($balance->amount, 2) }}
                    </td>
                    <td class="px-4 py-2">{{ $balance->description }}</td>
                    <td class="px-4 py-2">{{ $balance->employee->fname ?? 'N/A' }}</td>
                    <td class="px-4 py-2">{{ $balance->creator->fname ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
