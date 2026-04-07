@extends('layouts.app')

@section('title', 'Print SOA - ' . ($soa->soa_number ?? 'N/A'))

@section('content')
<div class="min-h-screen bg-white p-8 print:p-4">
    <div class="max-w-4xl mx-auto">
        <!-- Print Header -->
        <div class="text-center mb-8 print:mb-4">
            <h1 class="text-3xl font-bold text-gray-900">STATEMENT OF ACCOUNT</h1>
            <p class="text-lg text-gray-600 mt-2">{{ $soa->soa_number ?? 'N/A' }}</p>
        </div>

        <!-- Company Header -->
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

        <!-- Bill To -->
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

        <!-- Delivery Requests Table -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Services Provided</h3>
            <table class="w-full border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border border-gray-300 px-4 py-2 text-left">MTM</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Date</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Description</th>
                        <th class="border border-gray-300 px-4 py-2 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($soa->deliveryRequests ?? [] as $dr)
                        <tr>
                            <td class="border border-gray-300 px-4 py-2">{{ $dr->mtm }}</td>
                            <td class="border border-gray-300 px-4 py-2">
                                {{ $dr->delivery_date ? \Carbon\Carbon::parse($dr->delivery_date)->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                Delivery Service - {{ $dr->company->company_name ?? 'N/A' }}
                                @if($dr->customer)
                                    ({{ $dr->customer->name }})
                                @endif
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-right">₱{{ number_format($dr->pivot->amount ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="border border-gray-300 px-4 py-3 text-right font-semibold">Total Amount:</td>
                        <td class="border border-gray-300 px-4 py-3 text-right font-bold text-lg">₱{{ number_format($soa->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border border-gray-300 px-4 py-3 text-right font-semibold">Paid Amount:</td>
                        <td class="border border-gray-300 px-4 py-3 text-right">₱{{ number_format($soa->paid_amount, 2) }}</td>
                    </tr>
                    <tr class="bg-red-50">
                        <td colspan="3" class="border border-gray-300 px-4 py-3 text-right font-semibold">Outstanding Amount:</td>
                        <td class="border border-gray-300 px-4 py-3 text-right font-bold text-red-600">₱{{ number_format($soa->outstanding_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes -->
        @if($soa->notes)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Notes:</h3>
            <div class="border border-gray-300 rounded p-4 bg-gray-50">
                <p class="text-gray-700">{{ $soa->notes }}</p>
            </div>
        </div>
        @endif

        <!-- Footer -->
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

        <!-- Print Button (hidden in print) -->
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
    .print\\:hidden {
        display: none !important;
    }
    .print\\:p-4 {
        padding: 1rem !important;
    }
    .print\\:mb-4 {
        margin-bottom: 1rem !important;
    }
}
</style>
@endsection