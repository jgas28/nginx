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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="text-center">
                    <p class="text-gray-600 text-sm font-medium">Total Amount</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">₱{{ number_format($soa->total_amount, 2) }}</p>
                </div>

                <div class="text-center">
                    <p class="text-gray-600 text-sm font-medium">Paid Amount</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">₱{{ number_format($soa->paid_amount, 2) }}</p>
                </div>

                <div class="text-center">
                    <p class="text-gray-600 text-sm font-medium">Outstanding Amount</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">₱{{ number_format($soa->outstanding_amount, 2) }}</p>
                </div>
            </div>

            @php
                $subtotal       = (float) ($soa->subtotal_amount ?? $soa->total_amount);
                $discountAmt    = (float) ($soa->discount_amount ?? 0);
                $adjustmentAmt  = (float) ($soa->adjustment_amount ?? 0);
                $hasAdjustments = $discountAmt != 0 || $adjustmentAmt != 0;
            @endphp

            @if($hasAdjustments)
            <div class="border border-gray-200 rounded-xl bg-gray-50 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-gray-400"></i> Amount Breakdown
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-medium">₱{{ number_format($subtotal, 2) }}</span>
                    </div>

                    @if($discountAmt > 0)
                    <div class="flex flex-col gap-0.5">
                        <div class="flex justify-between text-red-600">
                            <span class="flex items-center gap-1.5">
                                <i class="fas fa-tag text-xs"></i>
                                {{ $soa->discount_type === 'dispute' ? 'Dispute' : 'Discount' }}
                            </span>
                            <span class="font-medium">-₱{{ number_format($discountAmt, 2) }}</span>
                        </div>
                        @if($soa->discount_remarks)
                        <p class="text-xs text-gray-400 pl-5 italic">{{ $soa->discount_remarks }}</p>
                        @endif
                    </div>
                    @endif

                    @if($adjustmentAmt != 0)
                    <div class="flex flex-col gap-0.5">
                        <div class="flex justify-between {{ $adjustmentAmt >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                            <span class="flex items-center gap-1.5">
                                <i class="fas fa-sliders-h text-xs"></i> Manual Adjustment
                            </span>
                            <span class="font-medium">{{ $adjustmentAmt >= 0 ? '+' : '' }}₱{{ number_format($adjustmentAmt, 2) }}</span>
                        </div>
                        @if($soa->adjustment_remarks)
                        <p class="text-xs text-gray-400 pl-5 italic">{{ $soa->adjustment_remarks }}</p>
                        @endif
                    </div>
                    @endif

                    <div class="flex justify-between font-bold text-gray-900 border-t border-gray-300 pt-2">
                        <span>Final Total</span>
                        <span class="text-emerald-700">₱{{ number_format($soa->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
            @endif
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
                                <th class="px-4 py-3">Billed For</th>
                                <th class="px-4 py-3">Delivery Rate</th>
                                <th class="px-4 py-3">Accessorial</th>
                                <th class="px-4 py-3">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($attachedDeliveryRequests as $dr)
                                @php
                                    $billingType = $dr->billing_type ?? 'both';
                                    $drAmt = (float)($dr->delivery_rate_amount ?? 0);
                                    $acAmt = (float)($dr->accessorial_rate_amount ?? 0);
                                    $billingLabel = match($billingType) {
                                        'delivery_only'    => ['text' => 'Delivery Only',    'cls' => 'bg-blue-100 text-blue-700'],
                                        'accessorial_only' => ['text' => 'Accessorial Only', 'cls' => 'bg-purple-100 text-purple-700'],
                                        default            => ['text' => 'Both',              'cls' => 'bg-green-100 text-green-700'],
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium">{{ $dr->mtm ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ $dr->booking_date ? \Carbon\Carbon::parse($dr->booking_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ $dr->delivery_date ? \Carbon\Carbon::parse($dr->delivery_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ $dr->company_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ $dr->customer_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $billingLabel['cls'] }}">{{ $billingLabel['text'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 {{ $billingType === 'accessorial_only' ? 'text-gray-300' : 'text-gray-700' }}">
                                        {{ $billingType !== 'accessorial_only' ? '₱'.number_format($drAmt, 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 {{ $billingType === 'delivery_only' ? 'text-gray-300' : 'text-gray-700' }}">
                                        {{ $billingType !== 'delivery_only' ? '₱'.number_format($acAmt, 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-gray-900">₱{{ number_format($dr->amount ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right font-semibold text-gray-700">Subtotal:</td>
                                <td class="px-4 py-3 font-semibold text-gray-700">₱{{ number_format($subtotal, 2) }}</td>
                            </tr>
                            @if($discountAmt > 0)
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right text-red-600 text-sm">
                                    {{ $soa->discount_type === 'dispute' ? 'Dispute' : 'Discount' }}
                                    @if($soa->discount_remarks)
                                        <span class="text-gray-400 font-normal italic ml-1">({{ $soa->discount_remarks }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-red-600 text-sm font-medium">-₱{{ number_format($discountAmt, 2) }}</td>
                            </tr>
                            @endif
                            @if($adjustmentAmt != 0)
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right text-blue-600 text-sm">
                                    Manual Adjustment
                                    @if($soa->adjustment_remarks)
                                        <span class="text-gray-400 font-normal italic ml-1">({{ $soa->adjustment_remarks }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-blue-600 text-sm font-medium">{{ $adjustmentAmt >= 0 ? '+' : '' }}₱{{ number_format($adjustmentAmt, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="border-t-2 border-gray-300">
                                <td colspan="5" class="px-4 py-3 text-right font-bold">Final Total:</td>
                                <td class="px-4 py-3 font-bold text-emerald-700">₱{{ number_format($soa->total_amount, 2) }}</td>
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
