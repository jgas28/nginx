@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-semibold mb-6">Cash Vouchers Status</h1>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border-b border-gray-300 text-left text-gray-700 text-sm font-medium">CVR Type</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-left text-gray-700 text-sm font-medium">CVR Number</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-left text-gray-700 text-sm font-medium">Truck ID</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-left text-gray-700 text-sm font-medium">Company ID</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-left text-gray-700 text-sm font-medium">Expense Type ID</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-right text-gray-700 text-sm font-medium">Requested Amount</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-right text-gray-700 text-sm font-medium">Approved Amount</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-right text-gray-700 text-sm font-medium">Liquidated (Cash)</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-right text-gray-700 text-sm font-medium">Liquidated (Card)</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-left text-gray-700 text-sm font-medium">Cash Voucher Status</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-left text-gray-700 text-sm font-medium">Approval Status</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-left text-gray-700 text-sm font-medium">Liquidation Status</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-left text-gray-700 text-sm font-medium">Overall Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cashVouchers as $voucher)
                <tr class="even:bg-gray-50">
                    <td class="px-4 py-2 border-b border-gray-200 text-sm">{{ $voucher->cvr_type }}</td>
                    <td class="px-4 py-2 border-b border-gray-200 text-sm">{{ $voucher->cvr_number }}</td>
                    <td class="px-4 py-2 border-b border-gray-200 text-sm">{{ $voucher->truck_id }}</td>
                    <td class="px-4 py-2 border-b border-gray-200 text-sm">{{ $voucher->company_id }}</td>
                    <td class="px-4 py-2 border-b border-gray-200 text-sm">{{ $voucher->expense_type_id }}</td>
                    <td class="px-4 py-2 border-b border-gray-200 text-sm text-right">{{ number_format($voucher->requested_amount, 2) }}</td>
                    <td class="px-4 py-2 border-b border-gray-200 text-sm text-right">{{ number_format($voucher->approved_amount, 2) }}</td>
                    <td class="px-4 py-2 border-b border-gray-200 text-sm text-right">{{ number_format($voucher->liquidated_amount_cash, 2) }}</td>
                    <td class="px-4 py-2 border-b border-gray-200 text-sm text-right">{{ number_format($voucher->liquidated_amount_card, 2) }}</td>
                    <td class="px-4 py-2 border-b border-gray-200 text-sm">{{ $voucher->cash_voucher_status }}</td>
                    <td class="px-4 py-2 border-b border-gray-200 text-sm">{{ $voucher->approval_status }}</td>
                    <td class="px-4 py-2 border-b border-gray-200 text-sm">{{ $voucher->liquidation_status }}</td>
                    <td class="px-4 py-2 border-b border-gray-200 text-sm">{{ ucfirst($voucher->overall_status) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
