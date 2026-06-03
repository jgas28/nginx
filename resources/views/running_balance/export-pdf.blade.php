<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; margin: 0; }
        h1 { font-size: 14px; font-weight: bold; margin: 0 0 2px; }
        .subtitle { font-size: 9px; color: #64748b; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead tr { background-color: #4f46e5; color: #fff; }
        th { padding: 5px 6px; text-align: left; font-size: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
        td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; font-size: 8.5px; }
        tr:nth-child(even) td { background-color: #f8fafc; }
        .in  { color: #065f46; }
        .out { color: #991b1b; }
        .flt { color: #92400e; }
        .amount { text-align: right; font-variant-numeric: tabular-nums; }
        tfoot td { font-weight: bold; background: #f1f5f9; border-top: 2px solid #cbd5e1; }
    </style>
</head>
<body>
    <h1>Running Balance Report &mdash; {{ $locationLabel }}</h1>
    <p class="subtitle">
        Period: {{ $dateFrom }} &ndash; {{ $dateTo }}
        &nbsp;&bull;&nbsp;
        Generated: {{ now()->format('M d, Y h:i A') }}
        &nbsp;&bull;&nbsp;
        Total Records: {{ $records->count() }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>CVR Number</th>
                <th>Type</th>
                <th>Movement</th>
                <th>Description</th>
                <th>Employee / Supplier</th>
                <th>Source</th>
                <th>Created By</th>
                <th style="text-align:right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse($records as $record)
                @php
                    $party = '';
                    if ($record->employee) {
                        $party = trim(($record->employee->fname ?? '') . ' ' . ($record->employee->lname ?? ''));
                    } elseif ($record->suppliers) {
                        $party = $record->suppliers->supplier_name ?? '';
                    }
                    $total += (float) $record->amount;
                    $mvClass = $record->adjustment_type === 'Out' ? 'out' : ($record->adjustment_type === 'In' ? 'in' : 'flt');
                    $amtClass = (float) $record->amount < 0 ? 'out' : 'in';
                @endphp
                <tr>
                    <td>{{ optional($record->created_at)->format('M d, Y') }}</td>
                    <td>{{ $record->cvr_number ?? '—' }}</td>
                    <td>
                        @switch((int) $record->type)
                            @case(1) Top-up @break
                            @case(2) Collected @break
                            @case(3) Refund @break
                            @case(4) Uncollected Funds @break
                            @case(5) Salary Deduction @break
                            @case(6) Liquidated Amount @break
                            @case(7) Transfer @break
                            @case(8) Release Approved Amount @break
                            @case(10) Transfer @break
                            @case(11) Adjustment @break
                            @case(12) Adjustment for Uncollected @break
                            @default Reimbursement
                        @endswitch
                    </td>
                    <td class="{{ $mvClass }}">{{ $record->adjustment_type ?? '—' }}</td>
                    <td>{{ $record->description ?? '—' }}</td>
                    <td>{{ $party ?: '—' }}</td>
                    <td>{{ optional($record->approver)->name ?? '—' }}</td>
                    <td>{{ trim((optional($record->creator)->fname ?? '') . ' ' . (optional($record->creator)->lname ?? '')) ?: '—' }}</td>
                    <td class="amount {{ $amtClass }}">{{ number_format((float) $record->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; color:#94a3b8; padding:12px;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" style="text-align:right; font-size:9px;">Grand Total</td>
                <td class="amount {{ $total < 0 ? 'out' : 'in' }}">{{ number_format($total, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
