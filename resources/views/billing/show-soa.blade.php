@extends('layouts.app')

@section('title', 'SOA Details - ' . ($soa->soa_number ?? 'N/A'))

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-5xl mx-auto px-4">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">SOA Details</h1>
                <p class="mt-2 text-gray-600">Statement of Account: {{ $soa->soa_number ?? 'N/A' }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('soa.print', $soa->id) }}" target="_blank" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    <i class="fas fa-print mr-2"></i>Print SOA
                </a>
                <a href="{{ route('soa.downloadPdf', $soa->id) }}" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700">
                    <i class="fas fa-file-pdf mr-2"></i>Download PDF
                </a>
                <a href="{{ route('billing.index') }}" class="inline-flex items-center rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </a>
            </div>
        </div>

        @php
            $subtotal = (float) ($soa->subtotal_amount ?? $soa->total_amount);
            $discountAmt = (float) ($soa->discount_amount ?? 0);
            $adjustmentAmt = (float) ($soa->adjustment_amount ?? 0);
            $vatAmount = (float) ($soa->vat_amount ?? 0);
            $grossAmount = max(0, ($subtotal - $discountAmt + $adjustmentAmt) + $vatAmount);
            $withholdingTaxRate = (float) ($soa->withholding_tax_rate ?? 0);
            $withholdingTaxAmount = (float) ($soa->withholding_tax_amount ?? 0);
            $hasAdj = $discountAmt != 0 || $adjustmentAmt != 0 || $vatAmount != 0 || $withholdingTaxAmount != 0;
        @endphp

        <div class="rounded-lg bg-white p-8 shadow-lg">
            <div class="mb-6 text-center">
                <h1 class="text-5xl font-extrabold tracking-wide text-gray-900">STATEMENT OF ACCOUNT</h1>
            </div>

            <div class="mb-6 border-b-2 border-gray-300 pb-6">
                <div class="mx-auto max-w-3xl text-center">
                    <h2 class="text-3xl font-extrabold uppercase tracking-wide text-gray-900">{{ $soa->company->company_name ?? 'N/A' }}</h2>
                    <p class="mt-2 text-xl text-gray-500">TIN: {{ $soa->company->tin_no ?: 'N/A' }}</p>
                    <p class="mt-2 text-2xl text-gray-500">{{ $soa->company->company_location ?? 'N/A' }}</p>
                </div>
                <div class="mt-3 text-right">
                    <p class="whitespace-nowrap text-xl font-semibold leading-none text-gray-700"><strong>SI No.:</strong> {{ $soa->soa_number ?? 'N/A' }}</p>
                    <p class="mt-2 whitespace-nowrap text-xl leading-none text-gray-600"><strong>Statement Date:</strong> {{ $soa->statement_date->format('M d, Y') }}</p>
                    <p class="mt-2 whitespace-nowrap text-xl leading-none text-gray-600"><strong>Billing Period:</strong> {{ $soa->billing_period_from->format('M d, Y') }} - {{ $soa->billing_period_to->format('M d, Y') }}</p>
                    @if($soa->due_date)
                        <p class="mt-2 whitespace-nowrap text-xl leading-none text-gray-600"><strong>Due Date:</strong> {{ $soa->due_date->format('M d, Y') }}</p>
                    @endif
                    <p class="mt-2 whitespace-nowrap text-xl leading-none text-gray-600"><strong>Status:</strong> {{ ucfirst($soa->status) }}</p>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="mb-2 text-lg font-semibold text-gray-900">Bill To:</h3>
                <div class="rounded border border-gray-300 p-4">
                    @if($soa->customer)
                        <p><span class="font-semibold">Customer Name:</span> {{ $soa->customer->name }}</p>
                        <p><span class="font-semibold">TIN:</span> {{ $soa->customer->tin_no ?: 'N/A' }}</p>
                        <p><span class="font-semibold">Registered Address:</span> {{ $soa->customer->customer_address ?: 'N/A' }}</p>
                    @else
                        <p><span class="font-semibold">Customer Name:</span> N/A</p>
                        <p><span class="font-semibold">TIN:</span> N/A</p>
                        <p><span class="font-semibold">Registered Address:</span> N/A</p>
                    @endif
                </div>
            </div>

            <div class="mb-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Services Provided</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-300 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-4 py-2 text-left">MTM</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Date</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Description</th>
                                <th class="border border-gray-300 px-4 py-2 text-center">Billed For</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Delivery Rate</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Accessorial</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attachedDeliveryRequests as $dr)
                                @php
                                    $billingType = $dr->billing_type ?? 'both';
                                    $drAmt = (float) ($dr->delivery_rate_amount ?? 0);
                                    $acAmt = (float) ($dr->accessorial_rate_amount ?? 0);
                                    $billingLabel = match($billingType) {
                                        'delivery_only' => 'Delivery Only',
                                        'accessorial_only' => 'Accessorial Only',
                                        default => 'Both',
                                    };
                                @endphp
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2">{{ $dr->mtm }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $dr->delivery_date ? \Carbon\Carbon::parse($dr->delivery_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        Delivery Service - {{ $dr->company_name ?? 'N/A' }}
                                        @if($dr->customer_name) ({{ $dr->customer_name }}) @endif
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-center text-xs font-semibold">{{ $billingLabel }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-right">
                                        {!! $billingType !== 'accessorial_only' ? '&#8369;'.number_format($drAmt, 2) : '&mdash;' !!}
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-right">
                                        {!! $billingType !== 'delivery_only' ? '&#8369;'.number_format($acAmt, 2) : '&mdash;' !!}
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-right font-semibold">{!! '&#8369;'.number_format($dr->amount ?? 0, 2) !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="border border-gray-300 px-4 py-4 text-center text-gray-500">No delivery requests attached.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50">
                            @if($hasAdj)
                            <tr>
                                <td colspan="6" class="border border-gray-300 px-4 py-2 text-right font-semibold text-gray-600">Subtotal:</td>
                                <td class="border border-gray-300 px-4 py-2 text-right text-gray-600">{!! '&#8369;'.number_format($subtotal, 2) !!}</td>
                            </tr>
                            @if($discountAmt > 0)
                            <tr class="bg-red-50">
                                <td colspan="6" class="border border-gray-300 px-4 py-2 text-right text-red-700">
                                    <span class="font-semibold">{{ $soa->discount_type === 'dispute' ? 'Dispute' : 'Discount' }}:</span>
                                    @if($soa->discount_remarks)
                                        <span class="ml-1 text-xs italic text-red-500">({{ $soa->discount_remarks }})</span>
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-red-700">-{!! '&#8369;'.number_format($discountAmt, 2) !!}</td>
                            </tr>
                            @endif
                            @if($adjustmentAmt != 0)
                            <tr class="bg-blue-50">
                                <td colspan="6" class="border border-gray-300 px-4 py-2 text-right text-blue-700">
                                    <span class="font-semibold">Manual Adjustment:</span>
                                    @if($soa->adjustment_remarks)
                                        <span class="ml-1 text-xs italic text-blue-500">({{ $soa->adjustment_remarks }})</span>
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-blue-700">{{ $adjustmentAmt >= 0 ? '+' : '' }}{!! '&#8369;'.number_format($adjustmentAmt, 2) !!}</td>
                            </tr>
                            @endif
                            @endif
                            @if($vatAmount > 0)
                            <tr>
                                <td colspan="6" class="border border-gray-300 px-4 py-2 text-right font-semibold text-orange-700">VAT (12%):</td>
                                <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-orange-700">+{!! '&#8369;'.number_format($vatAmount, 2) !!}</td>
                            </tr>
                            @endif
                            @if($vatAmount > 0 || $withholdingTaxAmount > 0)
                            <tr>
                                <td colspan="6" class="border border-gray-300 px-4 py-2 text-right font-semibold text-teal-700">Total:</td>
                                <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-teal-700">{!! '&#8369;'.number_format($grossAmount, 2) !!}</td>
                            </tr>
                            @endif
                            @if($withholdingTaxAmount > 0)
                            <tr>
                                <td colspan="6" class="border border-gray-300 px-4 py-2 text-right font-semibold text-indigo-700">WHT ({{ $withholdingTaxRate }}%):</td>
                                <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-indigo-700">-{!! '&#8369;'.number_format($withholdingTaxAmount, 2) !!}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="6" class="border border-gray-300 px-4 py-3 text-right font-bold">Final Total:</td>
                                <td class="border border-gray-300 px-4 py-3 text-right text-lg font-bold text-green-700">{!! '&#8369;'.number_format($soa->total_amount, 2) !!}</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="border border-gray-300 px-4 py-3 text-right font-semibold">Paid Amount:</td>
                                <td class="border border-gray-300 px-4 py-3 text-right">{!! '&#8369;'.number_format($soa->paid_amount, 2) !!}</td>
                            </tr>
                            <tr class="bg-red-50">
                                <td colspan="6" class="border border-gray-300 px-4 py-3 text-right font-semibold">Outstanding Amount:</td>
                                <td class="border border-gray-300 px-4 py-3 text-right font-bold text-red-600">{!! '&#8369;'.number_format($soa->outstanding_amount, 2) !!}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($soa->notes)
            <div class="mb-2">
                <h3 class="mb-2 text-lg font-semibold text-gray-900">Notes:</h3>
                <div class="rounded border border-gray-300 bg-gray-50 p-4">
                    <p class="text-gray-700">{{ $soa->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
