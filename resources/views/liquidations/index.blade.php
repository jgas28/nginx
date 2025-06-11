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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requestor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($data as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ preg_replace('/\/\d+$/', '',$item->cashVoucher->cvr_number ?? 'N/A') }}-{{ $item->allocation?->truck?->truck_name }}-{{ $item->cashVoucher->deliveryRequest->company->company_code ?? 'N/A' }}{{ $item->cashVoucher->deliveryRequest->expenseType->expense_code ?? '' }}
                        </td>
                        <td>{{$item->cashVoucher->deliveryRequest->allocations->first()
                        ?->truck_id}}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item->amount, 2) }}
                        </td>
                       <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 max-w-xs break-words">
                            {{ $item->cashVoucher->deliveryRequest->company->company_code ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->cashVoucher->employee->fname ?? 'N/A' }} {{ $item->cashVoucher->employee->lname ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                           {{ optional($item->cashVoucher->created_at)->format('Y-m-d') ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 space-x-2">
                            <a href="{{ route('liquidations.liquidate', $item->id) }}" 
                            class="inline-block px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                            Liquidate
                            </a>
                            <a href="{{ route('liquidations.edit', $item->id) }}" 
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
