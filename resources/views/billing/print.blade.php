@extends('layouts.app')

@section('title', 'Print SOA - ' . ($soa->soa_number ?? 'N/A'))

@section('content')
<div class="min-h-screen bg-white p-8 print:p-4">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8 print:mb-4">
            <h1 class="text-3xl font-bold text-gray-900">STATEMENT OF ACCOUNT</h1>
            <p class="text-lg text-gray-600 mt-2">{{ $soa->soa_number ?? 'N/A' }}</p>
        </div>

        <div class="border-b-2 border-gray-300 pb-6 mb-6">
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">FCZCNYX</h2>
                    <p class="text-gray-600">123 Business Address</p>
                    <p class="text-gray-600">City, State, ZIP Code</p>
                    <p class="text-gray-600">Phone: (123) 456-7890</p>
                    <p class="text-gray-600">Email: info@fczcnyx.com</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-600"><strong>Statement Date:</strong> {{ $soa->statement_date->format('M d, Y') }}</p>
                    <p class="text-gray-600"><strong>Billing Period:</strong> {{ $soa->billing_period_from->format('M d, Y') }} - {{ $soa->billing_period_to->format('M d, Y') }}</p>
                    @if($soa->due_date)
                        <p class="text-gray-600"><strong>Due Date:</strong> {{ $soa->due_date->format('M d, Y') }}</p>
                    @endif
                    <p class="text-gray-600"><strong>Status:</strong> {{ ucfirst($soa->status) }}</p>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Bill To:</h3>
            <div class="border border-gray-300 rounded p-4">
                @if($soa->company)
                    <p class="font-semibold">{{ $soa->company->company_name }}</p>
                    <p>{{ $soa->company->company_location ?? 'N/A' }}</p>
                @endif
                @if($soa->customer)
                    <p class="font-semibold">{{ $soa->customer->name }}</p>
                @endif
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Services Provided</h3>
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
                    @foreach($attachedDeliveryRequests as $dr)
                        @php
                            $billingType  = $dr->billing_type ?? 'both';
                            $drAmt        = (float)($dr->delivery_rate_amount ?? 0);
                            $acAmt        = (float)($dr->accessorial_rate_amount ?? 0);
                            $billingLabel = match($billingType) {
                                'delivery_only'    => 'Delivery Only',
                                'accessorial_only' => 'Accessorial Only',
                                default            => 'Both',
                            };
                        @endphp
                        <tr>
                            <td class="border border-gray-300 px-4 py-2">{{ $dr->mtm }}</td>
                            <td class="border border-gray-300 px-4 py-2">
                                {{ $dr->delivery_date ? \Carbon\Carbon::parse($dr->delivery_date)->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                Delivery Service - {{ $dr->company_name ?? 'N/A' }}
                                @if($dr->customer_name) ({{ $dr->customer_name }}) @endif
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-center text-xs font-semibold">{{ $billingLabel }}</td>
                            <td class="border border-gray-300 px-4 py-2 text-right">
                                {{ $billingType !== 'accessorial_only' ? '₱'.number_format($drAmt, 2) : '—' }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-right">
                                {{ $billingType !== 'delivery_only' ? '₱'.number_format($acAmt, 2) : '—' }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-right font-semibold">₱{{ number_format($dr->amount ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    @php
                        $subtotal             = (float)($soa->subtotal_amount ?? $soa->total_amount);
                        $discountAmt          = (float)($soa->discount_amount ?? 0);
                        $adjustmentAmt        = (float)($soa->adjustment_amount ?? 0);
                        $vatAmount            = (float)($soa->vat_amount ?? 0);
                        $withholdingTaxRate   = (float)($soa->withholding_tax_rate ?? 0);
                        $withholdingTaxAmount = (float)($soa->withholding_tax_amount ?? 0);
                        $hasAdj               = $discountAmt != 0 || $adjustmentAmt != 0 || $vatAmount != 0 || $withholdingTaxAmount != 0;
                    @endphp
                    @if($hasAdj)
                    <tr>
                        <td colspan="6" class="border border-gray-300 px-4 py-2 text-right font-semibold text-gray-600">Subtotal:</td>
                        <td class="border border-gray-300 px-4 py-2 text-right text-gray-600">₱{{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @if($discountAmt > 0)
                    <tr class="bg-red-50">
                        <td colspan="6" class="border border-gray-300 px-4 py-2 text-right text-red-700">
                            <span class="font-semibold">{{ $soa->discount_type === 'dispute' ? 'Dispute' : 'Discount' }}:</span>
                            @if($soa->discount_remarks)
                                <span class="italic text-xs text-red-500 ml-1">({{ $soa->discount_remarks }})</span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-red-700">-₱{{ number_format($discountAmt, 2) }}</td>
                    </tr>
                    @endif
                    @if($adjustmentAmt != 0)
                    <tr class="bg-blue-50">
                        <td colspan="6" class="border border-gray-300 px-4 py-2 text-right text-blue-700">
                            <span class="font-semibold">Manual Adjustment:</span>
                            @if($soa->adjustment_remarks)
                                <span class="italic text-xs text-blue-500 ml-1">({{ $soa->adjustment_remarks }})</span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-blue-700">{{ $adjustmentAmt >= 0 ? '+' : '' }}₱{{ number_format($adjustmentAmt, 2) }}</td>
                    </tr>
                    @endif
                    @endif
                    @if($vatAmount > 0)
                    <tr>
                        <td colspan="6" class="border border-gray-300 px-4 py-2 text-right font-semibold text-orange-700">VAT (12%):</td>
                        <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-orange-700">+₱{{ number_format($vatAmount, 2) }}</td>
                    </tr>
                    @endif
                    @if($withholdingTaxAmount > 0)
                    <tr>
                        <td colspan="6" class="border border-gray-300 px-4 py-2 text-right font-semibold text-indigo-700">WHT ({{ $withholdingTaxRate }}%):</td>
                        <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-indigo-700">-₱{{ number_format($withholdingTaxAmount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="6" class="border border-gray-300 px-4 py-3 text-right font-bold">Final Total:</td>
                        <td class="border border-gray-300 px-4 py-3 text-right font-bold text-lg text-green-700">₱{{ number_format($soa->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="border border-gray-300 px-4 py-3 text-right font-semibold">Paid Amount:</td>
                        <td class="border border-gray-300 px-4 py-3 text-right">₱{{ number_format($soa->paid_amount, 2) }}</td>
                    </tr>
                    <tr class="bg-red-50">
                        <td colspan="6" class="border border-gray-300 px-4 py-3 text-right font-semibold">Outstanding Amount:</td>
                        <td class="border border-gray-300 px-4 py-3 text-right font-bold text-red-600">₱{{ number_format($soa->outstanding_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($soa->notes)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Notes:</h3>
            <div class="border border-gray-300 rounded p-4 bg-gray-50">
                <p class="text-gray-700">{{ $soa->notes }}</p>
            </div>
        </div>
        @endif

        <div class="border-t-2 border-gray-300 pt-6 mt-8">
            <div class="grid grid-cols-2 gap-8 text-sm text-gray-600">
                <div>
                    <p><strong>Generated by:</strong> {{ $soa->creator->name ?? 'System' }}</p>
                    <p><strong>Generated on:</strong> {{ now()->format('M d, Y H:i') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-gray-900">FCZCNYX</p>
                    <p>Thank you for your business!</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-8 print:hidden">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                <i class="fas fa-print mr-2"></i>Print SOA
            </button>
            <a href="{{ route('billing.showSoa', $soa->id) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg ml-4">
                <i class="fas fa-arrow-left mr-2"></i>Back to Details
            </a>
        </div>
    </div>
</div>

<style>
@media print {
    body {
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    .print\:hidden {
        display: none !important;
    }
    .print\:p-4 {
        padding: 1rem !important;
    }
    .print\:mb-4 {
        margin-bottom: 1rem !important;
    }
}
</style>
@endsection
