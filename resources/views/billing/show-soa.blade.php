@extends('layouts.app')

@section('title', 'SOA Details - ' . ($soa->soa_number ?? 'N/A'))

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">SOA Details</h1>
                    <p class="text-gray-600 mt-2">Statement of Account: {{ $soa->soa_number ?? 'N/A' }}</p>
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('soa.print', $soa->id) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-print mr-2"></i>Print SOA
                    </a>
                    <a href="{{ route('soa.downloadPdf', $soa->id) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-file-pdf mr-2"></i>Download PDF
                    </a>
                    <a href="{{ route('billing.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">SOA Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">SOA Number</label>
                    <p class="mt-1 text-lg font-semibold">{{ $soa->soa_number }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <span class="mt-1 px-3 py-1 text-sm font-medium rounded-full inline-block
                        @if($soa->status == 'paid') bg-green-100 text-green-800
                        @elseif($soa->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($soa->status == 'approved') bg-blue-100 text-blue-800
                        @elseif($soa->status == 'overdue') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($soa->status) }}
                    </span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Company</label>
                    <p class="mt-1">{{ $soa->company->company_name ?? 'N/A' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Customer</label>
                    <p class="mt-1">{{ $soa->customer->name ?? 'N/A' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Billing Period</label>
                    <p class="mt-1">
                        {{ $soa->billing_period_from->format('M d, Y') }} - {{ $soa->billing_period_to->format('M d, Y') }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Statement Date</label>
                    <p class="mt-1">{{ $soa->statement_date->format('M d, Y') }}</p>
                </div>

                @if($soa->due_date)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Due Date</label>
                    <p class="mt-1">{{ $soa->due_date->format('M d, Y') }}</p>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Created By</label>
                    <p class="mt-1">{{ $soa->creator->name ?? 'N/A' }}</p>
                </div>
            </div>

            @if($soa->notes)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <p class="mt-1 text-gray-600">{{ $soa->notes }}</p>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Financial Summary</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-gray-600 text-sm font-medium">Total Amount</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">P{{ number_format($soa->total_amount, 2) }}</p>
                </div>

                <div class="text-center">
                    <p class="text-gray-600 text-sm font-medium">Paid Amount</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">P{{ number_format($soa->paid_amount, 2) }}</p>
                </div>

                <div class="text-center">
                    <p class="text-gray-600 text-sm font-medium">Outstanding Amount</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">P{{ number_format($soa->outstanding_amount, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Included Delivery Requests</h2>

            @if($attachedDeliveryRequests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="bg-gray-100 text-gray-900 font-semibold">
                            <tr>
                                <th class="px-4 py-3">MTM</th>
                                <th class="px-4 py-3">Booking Date</th>
                                <th class="px-4 py-3">Delivery Date</th>
                                <th class="px-4 py-3">Company</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($attachedDeliveryRequests as $deliveryRequest)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium">{{ $deliveryRequest->mtm ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ $deliveryRequest->booking_date ? \Carbon\Carbon::parse($deliveryRequest->booking_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ $deliveryRequest->delivery_date ? \Carbon\Carbon::parse($deliveryRequest->delivery_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ $deliveryRequest->company_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ $deliveryRequest->customer_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 font-semibold">P{{ number_format($deliveryRequest->amount ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right font-semibold">Total:</td>
                                <td class="px-4 py-3 font-bold text-green-600">P{{ number_format($soa->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-truck text-3xl mb-4"></i>
                    <p>No delivery requests found for this SOA</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
