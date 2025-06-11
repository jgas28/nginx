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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CV Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody>
               @foreach ($liquidations as $item)
                    @php
                        $cashVoucher = $item->cashVoucher ?? null;
                        $deliveryRequest = $cashVoucher?->deliveryRequest;
                        $allocation = $item->allocations[0] ?? null;
                        $truck = $allocation?->truck;

                        $cvrNumber = $cashVoucher?->cvr_number 
                            ? preg_replace('/\/\d+$/', '', $cashVoucher->cvr_number) 
                            : 'N/A';

                        $truckName = $truck?->truck_name ?? 'N/A';
                        $companyCode = $deliveryRequest?->company?->company_code ?? 'N/A';
                        $expenseCode = $deliveryRequest?->expenseType?->expense_code ?? 'N/A';

                        $statusLabels = [
                            1 => 'Prepared',
                            2 => 'Rejected',
                            3 => 'For Collection',
                            4 => 'For Approval',
                            5 => 'Approved',
                        ];
                        
                        $statusText = $statusLabels[$item?->status] ?? 'Unknown';
                        $cvrType = $cashVoucher->cvr_type ?? null;
                        $allowedTypes = ['delivery', 'pullout', 'others', 'freight'];
                    @endphp 

                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if (in_array($cvrType, $allowedTypes))
                                {{ $cvrNumber }}-{{ $truckName }}-{{ $companyCode }}{{ $expenseCode }}
                            @elseif ($cvrType === 'admin')
                                {{ $cvrNumber }}-{{ $cashVoucher->company->company_code }}{{ $cashVoucher->expenseTypes->expense_code }}
                            @elseif ($cvrType === 'rpm')
                                {{ $cvrNumber }}-{{ $cashVoucher->truck?->truck_name }}-{{ $cashVoucher->company->company_code }}{{ $cashVoucher->expenseTypes->expense_code }}
                            @else
                                {{ $cvrNumber }} 
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($item->total_expense, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if (in_array($cvrType, $allowedTypes))
                                {{ $companyCode }}
                            @elseif ($cvrType === 'admin')
                                {{ $cashVoucher->company->company_code }}
                            @elseif ($cvrType === 'rpm')
                                {{ $cashVoucher->company->company_code }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $cashVoucher->cvr_type }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{  $statusText }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
