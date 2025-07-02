@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">

    <!-- Filter Section -->
    <div class="flex items-center justify-between mb-6">
        <form action="{{ route('liquidations.validatedList') }}" method="GET" class="flex space-x-4 w-full">
            <!-- CVR Number Filter -->
            <div class="flex-1">
                <label for="cvr_number" class="block text-sm font-medium text-gray-700">CVR Number</label>
                <input type="text" name="cvr_number" id="cvr_number" value="{{ request('cvr_number') }}" 
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="Search by CVR Number">
            </div>

            <!-- Filter Button -->
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Table Section -->
    <table class="min-w-full border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-3 border">CVR Number</th>
                <th class="p-3 border">Prepared By</th>
                <th class="p-3 border">Noted By</th>
                <th class="p-3 border">Collector</th>
                <th class="p-3 border">Date Created</th>
                <th class="p-3 border">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($liquidations as $liquidation)
                <tr class="hover:bg-gray-50">
                    @php
                        $cashVoucher = optional($liquidation->cashVoucher);
                        $cvrType = $cashVoucher->cvr_type ?? null;

                        $cvrNumber =  preg_replace('/\/\d+$/', '', $cashVoucher->cvr_number ?? 'N/A');
                        $details = ''; // This will hold the appended details based on cvr_type

                        if ($cvrType === 'rpm') {
                            $truckName = optional($cashVoucher->trucks)->truck_name ?? 'N/A';
                            $companyCode = optional($cashVoucher->company)->company_code ?? 'N/A';
                             $expenseCodeVal = optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A';

                            $details = "-$truckName-$companyCode$expenseCodeVal";

                        } elseif ($cvrType === 'admin') {
                            $companyId = $cashVoucher->company->company_code ?? 'N/A';
                            $expenseCodeVal = $cashVoucher->expenseTypes->expense_code ?? 'N/A';

                            $details = "-$companyId$expenseCodeVal";

                        } elseif (in_array($cvrType, ['delivery', 'pullout', 'accessorial', 'freight', 'others'])) {
                            $truckId = optional($liquidation->allocation->truck)->truck_name ?? 'N/A';
                            $companyId = optional($liquidation->deliveryRequest->company)->company_code ?? 'N/A';
                            $expenseCodeVal = optional($liquidation->deliveryRequest->expenseType)->expense_code ?? 'N/A';

                            $details = "-$truckId-$companyId$expenseCodeVal";
                        } 
                    @endphp

                    <td class="p-3 border">
                        {{ $cvrNumber }}{!! $details !!}
                    </td>
                    <td class="p-3 border">{{ $liquidation->preparedBy->fname ?? '' }} {{ $liquidation->preparedBy->lname ?? '' }}</td>
                    <td class="p-3 border">{{ $liquidation->notedBy->fname ?? '' }} {{ $liquidation->notedBy->lname ?? '' }}</td>
                     <td class="p-3 border">{{ $liquidation->collector->fname ?? '' }} {{ $liquidation->collector->lname ?? '' }}</td>
                    <td class="p-3 border">{{ $liquidation->created_at->format('Y-m-d') }}</td>
                    <td class="p-3 border">
                        <a href="{{ route('liquidations.validated', $liquidation->id) }}" class="text-indigo-600 hover:text-indigo-900 underline">Review</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-3 text-center text-gray-500">No liquidations found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $liquidations->links() }}
    </div>
</div>
@endsection
