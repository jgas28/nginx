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
                    <label class="block text-sm font-medium text-gray-700">Company Name</label>
                    <p class="mt-1">{{ $soa->company->company_name ?? 'N/A' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Company TIN</label>
                    <p class="mt-1">{{ $soa->company->tin_no ?? 'N/A' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Company Address</label>
                    <p class="mt-1">{{ $soa->company->company_location ?? 'N/A' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Customer Name</label>
                    <p class="mt-1">{{ $soa->customer->name ?? 'N/A' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Customer TIN</label>
                    <p class="mt-1">{{ $soa->customer->tin_no ?? 'N/A' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Customer Address</label>
                    <p class="mt-1">{{ $soa->customer->customer_address ?? 'N/A' }}</p>
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
                    <p class="text-3xl font-bold text-green-600 mt-2">{!! '&#8369;'.number_format($soa->total_amount, 2) !!}</p>
                </div>

                <div class="text-center">
                    <p class="text-gray-600 text-sm font-medium">Paid Amount</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{!! '&#8369;'.number_format($soa->paid_amount, 2) !!}</p>
                </div>

                <div class="text-center">
                    <p class="text-gray-600 text-sm font-medium">Outstanding Amount</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{!! '&#8369;'.number_format($soa->outstanding_amount, 2) !!}</p>
                </div>
            </div>

            @php
                $subtotal              = (float) ($soa->subtotal_amount ?? $soa->total_amount);
                $discountAmt           = (float) ($soa->discount_amount ?? 0);
                $adjustmentAmt         = (float) ($soa->adjustment_amount ?? 0);
                $vatAmount             = (float) ($soa->vat_amount ?? 0);
                $withholdingTaxRate    = (float) ($soa->withholding_tax_rate ?? 0);
                $withholdingTaxAmount  = (float) ($soa->withholding_tax_amount ?? 0);
                $hasAdjustments        = $discountAmt != 0 || $adjustmentAmt != 0 || $vatAmount != 0 || $withholdingTaxAmount != 0;
            @endphp

            @if($hasAdjustments)
            <div class="border border-gray-200 rounded-xl bg-gray-50 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-gray-400"></i> Amount Breakdown
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-medium">{!! '&#8369;'.number_format($subtotal, 2) !!}</span>
                    </div>

                    @if($discountAmt > 0)
                    <div class="flex flex-col gap-0.5">
                        <div class="flex justify-between text-red-600">
                            <span class="flex items-center gap-1.5">
                                <i class="fas fa-tag text-xs"></i>
                                {{ $soa->discount_type === 'dispute' ? 'Dispute' : 'Discount' }}
                            </span>
                            <span class="font-medium">-{!! '&#8369;'.number_format($discountAmt, 2) !!}</span>
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
                            <span class="font-medium">{{ $adjustmentAmt >= 0 ? '+' : '' }}{!! '&#8369;'.number_format($adjustmentAmt, 2) !!}</span>
                        </div>
                        @if($soa->adjustment_remarks)
                        <p class="text-xs text-gray-400 pl-5 italic">{{ $soa->adjustment_remarks }}</p>
                        @endif
                    </div>
                    @endif

                    @if($vatAmount > 0)
                    <div class="flex justify-between text-orange-600">
                        <span class="flex items-center gap-1.5"><i class="fas fa-percentage text-xs"></i> VAT (12%)</span>
                        <span class="font-medium">+{!! '&#8369;'.number_format($vatAmount, 2) !!}</span>
                    </div>
                    @endif
                    @if($withholdingTaxAmount > 0)
                    <div class="flex justify-between text-indigo-600">
                        <span class="flex items-center gap-1.5"><i class="fas fa-minus-circle text-xs"></i> WHT ({{ $withholdingTaxRate }}%)</span>
                        <span class="font-medium">-{!! '&#8369;'.number_format($withholdingTaxAmount, 2) !!}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-bold text-gray-900 border-t border-gray-300 pt-2">
                        <span>Final Total</span>
                        <span class="text-emerald-700">{!! '&#8369;'.number_format($soa->total_amount, 2) !!}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- ── Billed Deliveries Section (Payslip-style) ──────────────── --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">

            {{-- Section header --}}
            <div class="flex items-center justify-between bg-gray-800 px-6 py-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 text-white">
                        <i class="fas fa-file-invoice-dollar text-sm"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-white">Billed Delivery Statement</h2>
                        <p class="text-xs text-gray-400">SOA {{ $soa->soa_number }} • {{ $attachedDeliveryRequests->count() }} delivery request{{ $attachedDeliveryRequests->count() !== 1 ? 's' : '' }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold ring-1
                    @if($soa->status === 'paid') bg-emerald-500 text-white ring-emerald-400
                    @elseif($soa->status === 'approved') bg-blue-500 text-white ring-blue-400
                    @elseif($soa->status === 'pending') bg-amber-400 text-white ring-amber-300
                    @elseif($soa->status === 'overdue') bg-rose-500 text-white ring-rose-400
                    @else bg-gray-400 text-white ring-gray-300 @endif">
                    @if($soa->status === 'paid') <i class="fas fa-circle-check"></i>
                    @elseif($soa->status === 'approved') <i class="fas fa-thumbs-up"></i>
                    @elseif($soa->status === 'pending') <i class="fas fa-clock"></i>
                    @elseif($soa->status === 'overdue') <i class="fas fa-triangle-exclamation"></i>
                    @else <i class="fas fa-file-pen"></i>
                    @endif
                    {{ ucfirst($soa->status) }}
                </span>
            </div>

            @if($attachedDeliveryRequests->count() > 0)

                {{-- Per-delivery payslip cards --}}
                <div class="divide-y divide-gray-100">
                    @foreach($attachedDeliveryRequests as $idx => $dr)
                        @php
                            $billingType = $dr->billing_type ?? 'both';
                            $drAmt       = (float)($dr->delivery_rate_amount ?? 0);
                            $acAmt       = (float)($dr->accessorial_rate_amount ?? 0);
                            $totalDrAmt  = (float)($dr->amount ?? 0);
                            $showDr      = $billingType !== 'accessorial_only';
                            $showAc      = $billingType !== 'delivery_only';

                            $typeConfig  = match($billingType) {
                                'delivery_only'    => ['text' => 'Delivery Only',    'cls' => 'bg-blue-100 text-blue-700',   'icon' => 'fa-truck'],
                                'accessorial_only' => ['text' => 'Accessorial Only', 'cls' => 'bg-purple-100 text-purple-700','icon' => 'fa-wrench'],
                                default            => ['text' => 'Delivery + Accessorial', 'cls' => 'bg-teal-100 text-teal-700', 'icon' => 'fa-layer-group'],
                            };
                        @endphp

                        <div class="p-5 hover:bg-gray-50/60 transition-colors">
                            {{-- Card header row --}}
                            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-800 text-white font-bold text-sm shadow">
                                        {{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <p class="text-base font-bold text-gray-900 tracking-wide">{{ $dr->mtm ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $dr->company_name ?? 'N/A' }} • {{ $dr->customer_name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $typeConfig['cls'] }}">
                                        <i class="fas {{ $typeConfig['icon'] }} text-[10px]"></i>
                                        {{ $typeConfig['text'] }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold
                                        @if($soa->status === 'paid') bg-emerald-100 text-emerald-700
                                        @elseif($soa->status === 'approved') bg-blue-100 text-blue-700
                                        @elseif($soa->status === 'pending') bg-amber-100 text-amber-700
                                        @elseif($soa->status === 'overdue') bg-rose-100 text-rose-700
                                        @else bg-gray-100 text-gray-600 @endif">
                                        @if($soa->status === 'paid') <i class="fas fa-circle-check text-[10px]"></i> Paid
                                        @elseif($soa->status === 'approved') <i class="fas fa-thumbs-up text-[10px]"></i> Approved
                                        @elseif($soa->status === 'pending') <i class="fas fa-clock text-[10px]"></i> Pending Payment
                                        @elseif($soa->status === 'overdue') <i class="fas fa-triangle-exclamation text-[10px]"></i> Overdue
                                        @else <i class="fas fa-file-pen text-[10px]"></i> Draft @endif
                                    </span>
                                </div>
                            </div>

                            {{-- Delivery info row --}}
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4 text-xs">
                                <div class="rounded-lg bg-amber-50 border border-amber-100 px-3 py-2">
                                    <p class="text-amber-600 font-semibold uppercase tracking-wide mb-0.5">Booking Date</p>
                                    <p class="font-bold text-gray-800 text-sm">{{ $dr->booking_date ? \Carbon\Carbon::parse($dr->booking_date)->format('M d, Y') : '—' }}</p>
                                </div>
                                <div class="rounded-lg bg-emerald-50 border border-emerald-100 px-3 py-2">
                                    <p class="text-emerald-600 font-semibold uppercase tracking-wide mb-0.5">Delivery Date</p>
                                    <p class="font-bold text-gray-800 text-sm">{{ $dr->delivery_date ? \Carbon\Carbon::parse($dr->delivery_date)->format('M d, Y') : '—' }}</p>
                                </div>
                                <div class="rounded-lg bg-blue-50 border border-blue-100 px-3 py-2">
                                    <p class="text-blue-600 font-semibold uppercase tracking-wide mb-0.5">Billing Period</p>
                                    <p class="font-bold text-gray-800 text-sm">{{ $soa->billing_period_from->format('M d') }} – {{ $soa->billing_period_to->format('M d, Y') }}</p>
                                </div>
                                <div class="rounded-lg bg-purple-50 border border-purple-100 px-3 py-2">
                                    <p class="text-purple-600 font-semibold uppercase tracking-wide mb-0.5">Statement Date</p>
                                    <p class="font-bold text-gray-800 text-sm">{{ $soa->statement_date->format('M d, Y') }}</p>
                                </div>
                            </div>

                            {{-- Payslip-style billing breakdown --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                                {{-- Charges (like "Earnings" in payslip) --}}
                                <div class="rounded-xl border border-gray-200 overflow-hidden">
                                    <div class="bg-gray-700 px-4 py-2 flex items-center gap-2">
                                        <i class="fas fa-receipt text-gray-300 text-xs"></i>
                                        <span class="text-xs font-bold text-gray-100 uppercase tracking-wider">Charges</span>
                                    </div>
                                    <div class="bg-white divide-y divide-gray-50">
                                        @if($showDr)
                                        <div class="flex justify-between items-center px-4 py-2.5">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                                    <i class="fas fa-truck text-[9px]"></i>
                                                </span>
                                                <span class="text-sm text-gray-700">Delivery Rate</span>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-900">{!! '&#8369;'.number_format($drAmt, 2) !!}</span>
                                        </div>
                                        @endif
                                        @if($showAc)
                                        <div class="flex justify-between items-center px-4 py-2.5">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-purple-100 text-purple-600">
                                                    <i class="fas fa-wrench text-[9px]"></i>
                                                </span>
                                                <span class="text-sm text-gray-700">Accessorial Rate</span>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-900">{!! '&#8369;'.number_format($acAmt, 2) !!}</span>
                                        </div>
                                        @endif
                                        @if(!$showDr && !$showAc)
                                        <div class="px-4 py-3 text-sm text-gray-400 italic">No charges listed</div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Summary (like "Net Salary" in payslip) --}}
                                <div class="rounded-xl border border-gray-200 overflow-hidden">
                                    <div class="bg-gray-700 px-4 py-2 flex items-center gap-2">
                                        <i class="fas fa-calculator text-gray-300 text-xs"></i>
                                        <span class="text-xs font-bold text-gray-100 uppercase tracking-wider">Billing Summary</span>
                                    </div>
                                    <div class="bg-white divide-y divide-gray-50">
                                        @if($showDr && $drAmt > 0)
                                        <div class="flex justify-between items-center px-4 py-2.5">
                                            <span class="text-sm text-gray-500">Delivery</span>
                                            <span class="text-sm text-gray-700">{!! '&#8369;'.number_format($drAmt, 2) !!}</span>
                                        </div>
                                        @endif
                                        @if($showAc && $acAmt > 0)
                                        <div class="flex justify-between items-center px-4 py-2.5">
                                            <span class="text-sm text-gray-500">Accessorial</span>
                                            <span class="text-sm text-gray-700">{!! '&#8369;'.number_format($acAmt, 2) !!}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between items-center px-4 py-3 bg-gray-50">
                                            <span class="text-sm font-bold text-gray-800">Total Billed</span>
                                            <span class="text-base font-extrabold text-emerald-700">{!! '&#8369;'.number_format($totalDrAmt, 2) !!}</span>
                                        </div>
                                        <div class="px-4 py-2.5">
                                            <div class="flex items-center justify-between text-xs text-gray-500">
                                                <span>SOA Reference</span>
                                                <span class="font-semibold text-gray-700">{{ $soa->soa_number }}</span>
                                            </div>
                                            @if($soa->due_date)
                                            <div class="flex items-center justify-between text-xs text-gray-500 mt-1">
                                                <span>Due Date</span>
                                                <span class="font-semibold {{ $soa->status === 'overdue' ? 'text-rose-600' : 'text-gray-700' }}">{{ $soa->due_date->format('M d, Y') }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- SOA-level totals footer --}}
                <div class="border-t-2 border-gray-200 bg-gray-50 px-6 py-5">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-4 flex items-center gap-2">
                        <i class="fas fa-sigma"></i> SOA Total Summary
                    </h3>
                    <div class="space-y-2 max-w-sm ml-auto text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal ({{ $attachedDeliveryRequests->count() }} deliveries)</span>
                            <span class="font-medium">{!! '&#8369;'.number_format($subtotal, 2) !!}</span>
                        </div>
                        @if($discountAmt > 0)
                        <div class="flex flex-col gap-0.5">
                            <div class="flex justify-between text-red-600">
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-tag text-xs"></i>
                                    {{ $soa->discount_type === 'dispute' ? 'Dispute' : 'Discount' }}
                                </span>
                                <span class="font-medium">-{!! '&#8369;'.number_format($discountAmt, 2) !!}</span>
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
                                <span class="font-medium">{{ $adjustmentAmt >= 0 ? '+' : '' }}{!! '&#8369;'.number_format($adjustmentAmt, 2) !!}</span>
                            </div>
                            @if($soa->adjustment_remarks)
                            <p class="text-xs text-gray-400 pl-5 italic">{{ $soa->adjustment_remarks }}</p>
                            @endif
                        </div>
                        @endif
                        @if($vatAmount > 0)
                        <div class="flex justify-between text-orange-600">
                            <span class="flex items-center gap-1.5"><i class="fas fa-percentage text-xs"></i> VAT (12%)</span>
                            <span class="font-medium">+{!! '&#8369;'.number_format($vatAmount, 2) !!}</span>
                        </div>
                        @endif
                        @if($withholdingTaxAmount > 0)
                        <div class="flex justify-between text-indigo-600">
                            <span class="flex items-center gap-1.5"><i class="fas fa-minus-circle text-xs"></i> WHT ({{ $withholdingTaxRate }}%)</span>
                            <span class="font-medium">-{!! '&#8369;'.number_format($withholdingTaxAmount, 2) !!}</span>
                        </div>
                        @endif
                        <div class="border-t-2 border-gray-300 pt-3 flex justify-between font-extrabold text-gray-900 text-base">
                            <span>Final Total</span>
                            <span class="text-emerald-700">{!! '&#8369;'.number_format($soa->total_amount, 2) !!}</span>
                        </div>
                        <div class="flex justify-between text-blue-600 text-sm">
                            <span class="flex items-center gap-1.5"><i class="fas fa-circle-check text-xs"></i> Paid Amount</span>
                            <span class="font-semibold">{!! '&#8369;'.number_format($soa->paid_amount, 2) !!}</span>
                        </div>
                        <div class="flex justify-between text-rose-600 text-sm">
                            <span class="flex items-center gap-1.5"><i class="fas fa-hourglass-half text-xs"></i> Outstanding</span>
                            <span class="font-semibold">{!! '&#8369;'.number_format($soa->outstanding_amount, 2) !!}</span>
                        </div>
                    </div>
                </div>

            @else
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <i class="fas fa-truck-ramp-box text-5xl mb-4 text-gray-300"></i>
                    <p class="text-base font-medium text-gray-500">No delivery requests linked to this SOA</p>
                    <p class="text-sm mt-1">Deliveries will appear here once added to this statement.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
