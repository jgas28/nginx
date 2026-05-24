<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SOA - {{ $soa->soa_number ?? 'N/A' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            background: #ffffff;
            padding: 32px;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .header h1 {
            font-size: 30px;
            font-weight: 800;
            color: #111827;
            letter-spacing: 1px;
        }

        .info-row {
            border-bottom: 2px solid #D1D5DB;
            padding-bottom: 20px;
            margin-bottom: 16px;
            width: 100%;
        }

        .company-name {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 6px;
            text-align: center;
            text-transform: uppercase;
        }

        .info-line {
            color: #4B5563;
            font-size: 12px;
        }

        .company-col {
            text-align: center;
            width: 100%;
        }

        .si-wrap {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            line-height: 1;
        }

        .info-meta {
            text-align: right;
            font-size: 12px;
            color: #4B5563;
            line-height: 1;
            margin-top: 6px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }

        .bill-to-box {
            border: 1px solid #D1D5DB;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }

        .bill-to-box p {
            color: #111827;
            margin-bottom: 2px;
        }

        .bill-to-box .bold {
            font-weight: 700;
        }

        .services-section {
            margin-bottom: 16px;
        }

        .services-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }

        .services-table th {
            background-color: #F3F4F6;
            border: 1px solid #D1D5DB;
            padding: 7px 10px;
            text-align: left;
            font-weight: 700;
            color: #111827;
        }

        .services-table th.right { text-align: right; }

        .services-table td {
            border: 1px solid #D1D5DB;
            padding: 6px 10px;
            color: #111827;
            vertical-align: top;
        }

        .services-table td.right { text-align: right; }

        .totals-row td {
            background-color: #F9FAFB;
            font-weight: 600;
            padding: 8px 10px;
        }

        .subtotal-row td { background-color: #F9FAFB; color: #4B5563; }
        .discount-row td { background-color: #FEF2F2; color: #B91C1C; font-weight: 600; }
        .adjustment-row td { background-color: #EFF6FF; color: #1D4ED8; font-weight: 600; }
        .vat-row td { background-color: #FFF7ED; color: #C2410C; font-weight: 600; }
        .wtax-row td { background-color: #EEF2FF; color: #4338CA; font-weight: 600; }
        .total-amount td { font-weight: 700; font-size: 13px; color: #065F46; background-color: #ECFDF5; }

        .outstanding-row td {
            background-color: #FEF2F2;
            font-weight: 700;
            color: #DC2626;
        }

        .remarks-note {
            font-size: 10px;
            font-style: italic;
            font-weight: 400;
            margin-left: 4px;
        }

        .notes-box {
            border: 1px solid #D1D5DB;
            border-radius: 4px;
            padding: 10px 14px;
            background-color: #F9FAFB;
            margin-bottom: 16px;
        }

        .notes-box p { color: #374151; }
    </style>
</head>
<body>
    <div class="header">
        <h1>STATEMENT OF ACCOUNT</h1>
    </div>

    <div class="info-row">
        <div class="company-col">
            <div class="company-name">{{ $soa->company->company_name ?? 'N/A' }}</div>
            <div class="info-line">TIN: {{ $soa->company->tin_no ?: 'N/A' }}</div>
            <div class="info-line">{{ $soa->company->company_location ?? 'N/A' }}</div>
        </div>
        <div class="info-meta">
            <div class="si-wrap"><strong>SI No.:</strong> {{ $soa->soa_number ?? 'N/A' }}</div>
            <div class="info-line" style="margin-top: 6px;"><strong>Statement Date:</strong> {{ $soa->statement_date->format('M d, Y') }}</div>
            <div class="info-line" style="margin-top: 6px;"><strong>Billing Period:</strong> {{ $soa->billing_period_from->format('M d, Y') }} - {{ $soa->billing_period_to->format('M d, Y') }}</div>
            @if($soa->due_date)
                <div class="info-line" style="margin-top: 6px;"><strong>Due Date:</strong> {{ $soa->due_date->format('M d, Y') }}</div>
            @endif
            <div class="info-line" style="margin-top: 6px;"><strong>Status:</strong> {{ ucfirst($soa->status) }}</div>
        </div>
    </div>

    <div class="section-title">Bill To:</div>
    <div class="bill-to-box">
        @if($soa->customer)
            <p><span class="bold">Customer Name:</span> {{ $soa->customer->name }}</p>
            <p><span class="bold">TIN:</span> {{ $soa->customer->tin_no ?: 'N/A' }}</p>
            <p><span class="bold">Registered Address:</span> {{ $soa->customer->customer_address ?: 'N/A' }}</p>
        @else
            <p><span class="bold">Customer Name:</span> N/A</p>
            <p><span class="bold">TIN:</span> N/A</p>
            <p><span class="bold">Registered Address:</span> N/A</p>
        @endif
    </div>

    <div class="services-section">
        <div class="section-title" style="margin-bottom:8px;">Services Provided</div>
        <table class="services-table">
            <thead>
                <tr>
                    <th style="width:14%;">MTM</th>
                    <th style="width:11%;">Date</th>
                    <th>Description</th>
                    <th style="width:13%;">Billed For</th>
                    <th class="right" style="width:12%;">Delivery Rate</th>
                    <th class="right" style="width:12%;">Accessorial</th>
                    <th class="right" style="width:12%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attachedDeliveryRequests as $dr)
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
                        <td>{{ $dr->mtm }}</td>
                        <td>{{ $dr->delivery_date ? \Carbon\Carbon::parse($dr->delivery_date)->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            Delivery Service - {{ $dr->company_name ?? 'N/A' }}
                            @if($dr->customer_name) ({{ $dr->customer_name }}) @endif
                        </td>
                        <td style="text-align:center; font-size:10px; font-weight:600;">{{ $billingLabel }}</td>
                        <td class="right" style="{{ $billingType === 'accessorial_only' ? 'color:#9CA3AF;' : '' }}">
                            {!! $billingType !== 'accessorial_only' ? '&#8369;'.number_format($drAmt, 2) : '&mdash;' !!}
                        </td>
                        <td class="right" style="{{ $billingType === 'delivery_only' ? 'color:#9CA3AF;' : '' }}">
                            {!! $billingType !== 'delivery_only' ? '&#8369;'.number_format($acAmt, 2) : '&mdash;' !!}
                        </td>
                        <td class="right">{!! '&#8369;'.number_format($dr->amount ?? 0, 2) !!}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color:#6B7280; padding:12px;">No delivery requests attached.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
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
                <tr class="totals-row subtotal-row">
                    <td colspan="6" class="right">Subtotal:</td>
                    <td class="right">{!! '&#8369;'.number_format($subtotal, 2) !!}</td>
                </tr>
                @if($discountAmt > 0)
                <tr class="discount-row">
                    <td colspan="6" class="right" style="border:1px solid #D1D5DB; padding:8px 10px;">
                        {{ $soa->discount_type === 'dispute' ? 'Dispute' : 'Discount' }}:
                        @if($soa->discount_remarks)
                            <span class="remarks-note">({{ $soa->discount_remarks }})</span>
                        @endif
                    </td>
                    <td class="right" style="border:1px solid #D1D5DB; padding:8px 10px;">-{!! '&#8369;'.number_format($discountAmt, 2) !!}</td>
                </tr>
                @endif
                @if($adjustmentAmt != 0)
                <tr class="adjustment-row">
                    <td colspan="6" class="right" style="border:1px solid #D1D5DB; padding:8px 10px;">
                        Manual Adjustment:
                        @if($soa->adjustment_remarks)
                            <span class="remarks-note">({{ $soa->adjustment_remarks }})</span>
                        @endif
                    </td>
                    <td class="right" style="border:1px solid #D1D5DB; padding:8px 10px;">{{ $adjustmentAmt >= 0 ? '+' : '' }}{!! '&#8369;'.number_format($adjustmentAmt, 2) !!}</td>
                </tr>
                @endif
                @endif
                @if($vatAmount > 0)
                <tr class="vat-row">
                    <td colspan="6" class="right" style="border:1px solid #D1D5DB; padding:8px 10px;">VAT (12%):</td>
                    <td class="right" style="border:1px solid #D1D5DB; padding:8px 10px;">+{!! '&#8369;'.number_format($vatAmount, 2) !!}</td>
                </tr>
                @endif
                @if($withholdingTaxAmount > 0)
                <tr class="wtax-row">
                    <td colspan="6" class="right" style="border:1px solid #D1D5DB; padding:8px 10px;">WHT ({{ $withholdingTaxRate }}%):</td>
                    <td class="right" style="border:1px solid #D1D5DB; padding:8px 10px;">-{!! '&#8369;'.number_format($withholdingTaxAmount, 2) !!}</td>
                </tr>
                @endif
                <tr class="totals-row total-amount">
                    <td colspan="6" class="right">Final Total:</td>
                    <td class="right">{!! '&#8369;'.number_format($soa->total_amount, 2) !!}</td>
                </tr>
                <tr class="totals-row">
                    <td colspan="6" class="right">Paid Amount:</td>
                    <td class="right">{!! '&#8369;'.number_format($soa->paid_amount, 2) !!}</td>
                </tr>
                <tr class="outstanding-row">
                    <td colspan="6" class="right" style="border:1px solid #D1D5DB; padding:8px 10px;">Outstanding Amount:</td>
                    <td class="right" style="border:1px solid #D1D5DB; padding:8px 10px;">{!! '&#8369;'.number_format($soa->outstanding_amount, 2) !!}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($soa->notes)
    <div class="section-title">Notes:</div>
    <div class="notes-box">
        <p>{{ $soa->notes }}</p>
    </div>
    @endif
</body>
</html>
