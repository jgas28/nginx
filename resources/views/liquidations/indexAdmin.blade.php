@extends('layouts.app')

@section('content')
<div class="mx-auto sm:px-6 lg:px-8 py-8">
    <div class="overflow-x-auto rounded-lg shadow-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">CVR Number</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Company Code</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Supplier Code</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Request Type</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Created</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
             
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($data as $approval)
                    @php
                        $voucher = $approval->cashVoucher;
                        $amountDetails = json_decode($voucher->amount_details ?? '[]', true);
                        $totalAmount = is_array($amountDetails) ? array_sum(array_map('floatval', $amountDetails)) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition duration-150 ease-in-out">
                        @if($voucher->cvr_type === 'admin')
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $voucher->cvr_number ?? '-' }}-{{ $voucher->company->company_code ?? '-' }}{{ ucfirst($voucher->expenseTypes->expense_code ?? '-') }}
                            </td>
                        @elseif($voucher->cvr_type === 'rpm')
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $voucher->cvr_number ?? '-' }}-{{ ucfirst($voucher->trucks->truck_name ?? '-' ) }}-{{ $voucher->company->company_code ?? '-' }}{{ ucfirst($voucher->expenseTypes->expense_code ?? '-' ) }}
                            </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 font-semibold">
                            ₱{{ number_format($totalAmount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $voucher->company->company_code ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $voucher->suppliers->supplier_code ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ ucfirst($voucher->expenseTypes->expense_code ?? '-') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($voucher->created_at)->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="{{ route('liquidations.liquidate', $approval->id) }}" 
                                class="inline-block px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                {{ $approval->id }}
                            </a>
                            <a href="" 
                                class="inline-block px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Edit
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
