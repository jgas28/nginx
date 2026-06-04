@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="bg-white p-6 rounded-lg shadow-lg space-y-6">
        
        {{-- Filter Form --}}
        <form method="GET" action="{{ route('liquidations.liquidationList') }}" class="flex flex-wrap gap-4">
            <div>
                <label for="cvr_number" class="block text-sm font-medium text-gray-700">CVR Number</label>
                <input type="text" name="cvr_number" id="cvr_number" placeholder="CVR Number"
                    value="{{ request('cvr_number') }}"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring focus:ring-blue-300 focus:border-blue-500">
            </div>

            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring focus:ring-blue-300 focus:border-blue-500">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring focus:ring-blue-300 focus:border-blue-500">
            </div>

            <div class="self-end">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 transition">
                    Filter
                </button>
            </div>

            <div class="self-end">
                <a href="{{ route('liquidations.liquidationList.exportExcel', request()->query()) }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 transition">
                    Download Excel
                </a>
            </div>
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">CVR Number</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Company Code</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">CV Type</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Date Created</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
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

                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                @if (in_array($cvrType, $allowedTypes))
                                    {{ $cvrNumber }}-{{ $truckName }}-{{ $companyCode }}{{ $expenseCode }}
                                @elseif ($cvrType === 'admin')
                                    {{ $cvrNumber }}-{{ $cashVoucher->company->company_code }}{{ $cashVoucher->expenseTypes->expense_code }}
                                @elseif ($cvrType === 'rpm')
                                    {{ $cvrNumber }}-{{ $cashVoucher->trucks?->truck_name }}-{{ $cashVoucher->company->company_code }}{{ $cashVoucher->expenseTypes->expense_code }}
                                @else
                                    {{ $cvrNumber }} 
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                {{ number_format($item->total_expense, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                @if (in_array($cvrType, $allowedTypes))
                                    {{ $companyCode }}
                                @elseif ($cvrType === 'admin' || $cvrType === 'rpm')
                                    {{ $cashVoucher->company->company_code }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                {{ $cashVoucher->cvr_type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                {{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                {{ $statusText }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pt-4">
            {{ $liquidations->links() }}
        </div>
    </div>
</div>
@endsection
