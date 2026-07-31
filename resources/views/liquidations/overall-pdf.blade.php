<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; margin: 0; }
        h1 { font-size: 14px; font-weight: bold; margin: 0 0 2px; }
        .subtitle { font-size: 9px; color: #64748b; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead tr { background-color: #1e40af; color: #fff; }
        th { padding: 5px 6px; text-align: left; font-size: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
        td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background-color: #f8fafc; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 20px; font-size: 7.5px; font-weight: 600; }
        .completed    { background:#d1fae5; color:#065f46; }
        .rejected     { background:#fee2e2; color:#991b1b; }
        .pending      { background:#fef3c7; color:#92400e; }
        .validation   { background:#e0f2fe; color:#075985; }
        .collection   { background:#ede9fe; color:#4c1d95; }
        .approval     { background:#e0e7ff; color:#3730a3; }
        .liquidation  { background:#cffafe; color:#155e75; }
        .in-progress  { background:#f1f5f9; color:#475569; }
        .amount { text-align: right; font-variant-numeric: tabular-nums; }
        tfoot td { font-weight: bold; background: #f1f5f9; border-top: 2px solid #cbd5e1; }
    </style>
</head>
<body>
    <h1>Cash Voucher Status Report</h1>
    <p class="subtitle">Period: {{ $dateFrom }} &ndash; {{ $dateTo }} &nbsp;&bull;&nbsp; Generated: {{ now()->format('M d, Y h:i A') }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>CVR Number</th>
                <th>Type</th>
                <th>Company</th>
                <th>Truck</th>
                <th>Expense</th>
                <th>Request</th>
                <th style="text-align:right">Requested</th>
                <th style="text-align:right">Approved</th>
                <th style="text-align:right">Liquidated</th>
                <th>Status</th>
                <th>Date</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalReq = 0; $totalApproved = 0; $totalLiq = 0;
                $badgeMap = [
                    'Completed'            => 'completed',
                    'Rejected CVR'         => 'rejected',
                    'Rejected Liquidation' => 'rejected',
                    'Pending Cash Approval'=> 'pending',
                    'For Validation'       => 'validation',
                    'For Collection'       => 'collection',
                    'For Approval'         => 'approval',
                    'For Liquidation'      => 'liquidation',
                    'In Progress'          => 'in-progress',
                ];
            @endphp
            @foreach($rows as $i => $row)
                @php
                    $totalReq      += (float) $row->requested_amount;
                    $totalApproved += (float) $row->approved_amount;
                    $totalLiq      += (float) $row->liquidated_cash;
                    $badgeClass = $badgeMap[$row->overall_status] ?? 'in-progress';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->cvr_number }}</td>
                    <td>{{ strtoupper($row->cvr_type) }}</td>
                    <td>{{ $row->company_code }}</td>
                    <td>{{ $row->truck_name ?? '-' }}</td>
                    <td>{{ $row->expense_code ?? '-' }}</td>
                    <td>{{ $row->request_code ?? '-' }}</td>
                    <td class="amount">{{ number_format((float) $row->requested_amount, 2) }}</td>
                    <td class="amount">{{ number_format((float) $row->approved_amount, 2) }}</td>
                    <td class="amount">{{ number_format((float) $row->liquidated_cash, 2) }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $row->overall_status }}</span></td>
                    <td>{{ $row->date_created }}</td>
                    <td>{{ $row->created_by ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" style="text-align:right">TOTAL</td>
                <td class="amount">{{ number_format($totalReq, 2) }}</td>
                <td class="amount">{{ number_format($totalApproved, 2) }}</td>
                <td class="amount">{{ number_format($totalLiq, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top:14px; font-size:8px; color:#94a3b8; text-align:right">
        {{ count($rows) }} record(s) exported
    </p>
</body>
</html>
