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

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            letter-spacing: 1px;
        }
        .header p {
            font-size: 13px;
            color: #4B5563;
            margin-top: 4px;
        }

        /* Company + Dates row */
        .info-row {
            border-bottom: 2px solid #D1D5DB;
            padding-bottom: 16px;
            margin-bottom: 16px;
            width: 100%;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            vertical-align: top;
            padding: 0;
        }
        .info-table .right-col {
            text-align: right;
        }
        .company-name {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }
        .info-line {
            color: #4B5563;
            margin-bottom: 2px;
        }

        /* Bill To */
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

        /* Services table */
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

        /* Totals rows */
        .totals-row td {
            background-color: #F9FAFB;
            font-weight: 600;
            padding: 8px 10px;
        }
        .total-amount td { font-weight: 700; font-size: 13px; }
        .outstanding-row td {
            background-color: #FEF2F2;
            font-weight: 700;
            color: #DC2626;
        }

        /* Notes */
        .notes-box {
            border: 1px solid #D1D5DB;
            border-radius: 4px;
            padding: 10px 14px;
            background-color: #F9FAFB;
            margin-bottom: 16px;
        }
        .notes-box p { color: #374151; }

        /* Footer */
        .footer {
            border-top: 2px solid #D1D5DB;
            padding-top: 16px;
            margin-top: 24px;
        }
        .footer-table { width: 100%; }
        .footer-table td { vertical-align: top; }
        .footer-table .right-col { text-align: right; }
        .footer-brand {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }
        .footer-sub { color: #4B5563; margin-top: 2px; }
        .footer-meta { color: #4B5563; margin-bottom: 2px; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h1>STATEMENT OF ACCOUNT</h1>
        <p>{{ $soa->soa_number ?? 'N/A' }}</p>
    </div>

    {{-- Company info + SOA dates --}}
    <div class="info-row">
        <table class="info-table">
            <tr>
                <td style="width:50%;">
                    <div class="company-name">FCZCNYX</div>
                    <div class="info-line">123 Business Address</div>
                    <div class="info-line">City, State, ZIP Code</div>
                    <div class="info-line">Phone: (123) 456-7890</div>
                    <div class="info-line">Email: info@fczcnyx.com</div>
                </td>
                <td class="right-col" style="width:50%;">
                    <div class="info-line"><strong>Statement Date:</strong> {{ $soa->statement_date->format('M d, Y') }}</div>
                    <div class="info-line"><strong>Billing Period:</strong> {{ $soa->billing_period_from->format('M d, Y') }} - {{ $soa->billing_period_to->format('M d, Y') }}</div>
                    @if($soa->due_date)
                        <div class="info-line"><strong>Due Date:</strong> {{ $soa->due_date->format('M d, Y') }}</div>
                    @endif
                    <div class="info-line"><strong>Status:</strong> {{ ucfirst($soa->status) }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Bill To --}}
    <div class="section-title">Bill To:</div>
    <div class="bill-to-box">
        @if($soa->company)
            <p class="bold">{{ $soa->company->company_name }}</p>
            <p>{{ $soa->company->company_location ?? 'N/A' }}</p>
        @endif
        @if($soa->customer)
            <p class="bold">{{ $soa->customer->name }}</p>
        @endif
    </div>

    {{-- Services Provided --}}
    <div class="services-section">
        <div class="section-title" style="margin-bottom:8px;">Services Provided</div>
        <table class="services-table">
            <thead>
                <tr>
                    <th style="width:18%;">MTM</th>
                    <th style="width:14%;">Date</th>
                    <th>Description</th>
                    <th class="right" style="width:16%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attachedDeliveryRequests as $dr)
                    <tr>
                        <td>{{ $dr->mtm }}</td>
                        <td>{{ $dr->delivery_date ? \Carbon\Carbon::parse($dr->delivery_date)->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            Delivery Service - {{ $dr->company_name ?? 'N/A' }}
                            @if($dr->customer_name)
                                ({{ $dr->customer_name }})
                            @endif
                        </td>
                        <td class="right">P{{ number_format($dr->amount ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center; color:#6B7280; padding:12px;">No delivery requests attached.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="totals-row total-amount">
                    <td colspan="3" class="right">Total Amount:</td>
                    <td class="right">P{{ number_format($soa->total_amount, 2) }}</td>
                </tr>
                <tr class="totals-row">
                    <td colspan="3" class="right">Paid Amount:</td>
                    <td class="right">P{{ number_format($soa->paid_amount, 2) }}</td>
                </tr>
                <tr class="outstanding-row">
                    <td colspan="3" class="right" style="border:1px solid #D1D5DB; padding:8px 10px;">Outstanding Amount:</td>
                    <td class="right" style="border:1px solid #D1D5DB; padding:8px 10px;">P{{ number_format($soa->outstanding_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Notes --}}
    @if($soa->notes)
    <div class="section-title">Notes:</div>
    <div class="notes-box">
        <p>{{ $soa->notes }}</p>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td style="width:50%;">
                    <div class="footer-meta"><strong>Generated by:</strong> {{ $soa->creator->name ?? 'System' }}</div>
                    <div class="footer-meta"><strong>Generated on:</strong> {{ now()->format('M d, Y H:i') }}</div>
                </td>
                <td class="right-col" style="width:50%;">
                    <div class="footer-brand">FCZCNYX</div>
                    <div class="footer-sub">Thank you for your business!</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
