@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 shadow rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CVR Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($liquidations as $item)
                    @php
                        $cashVoucher = $item['cash_voucher'] ?? null;
                        $deliveryRequest = $cashVoucher['delivery_request'] ?? null;
                        $allocation = $item['allocations'][0] ?? null;
                        $truck = $allocation['truck'] ?? null;

                        $cvrNumber = isset($item['cvr_number']) 
                            ? preg_replace('/\/\d+$/', '', $item['cvr_number']) 
                            : 'N/A';

                        $truckName = $truck['truck_name'] ?? 'N/A';
                        $companyCode = $company['company_code'] ?? 'N/A';
                        $expenseCode = $deliveryRequest['expenseType']['expense_code'] ?? 'N/A';
                    @endphp

                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $cvrNumber }}-{{ $truckName }}-{{ $companyCode }}{{ $expenseCode }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ₱{{ number_format($item['cash_voucher']['amount'] ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $companyCode ?: 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                         {{ \Carbon\Carbon::parse($item['created_at'])->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <a href="#" class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
