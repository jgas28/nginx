<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; margin: 0; }
        h1 { font-size: 14px; font-weight: bold; margin: 0 0 2px; }
        .subtitle { font-size: 9px; color: #64748b; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .thead-row { background-color: #334155; color: #fff; }
        th { padding: 5px 6px; text-align: left; font-size: 8px; font-weight: 600; text-transform: uppercase; }
        td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; font-size: 8.5px; }
        .row-even td { background-color: #f8fafc; }
    </style>
</head>
<body>
    <h1>Audit Log Report</h1>
    <p class="subtitle">
        Generated: {{ now()->format('M d, Y h:i A') }}
        &nbsp;&bull;&nbsp;
        Total Records: {{ $logs->count() }}
    </p>

    <table>
        <thead>
            <tr class="thead-row">
                <th>Date</th>
                <th>Actor</th>
                <th>Event</th>
                <th>Model</th>
                <th>Record ID</th>
                <th>Description</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            @php $rowIdx = 0; @endphp
            @forelse($logs as $log)
                <tr class="{{ ($rowIdx++ % 2 === 1) ? 'row-even' : '' }}">
                    <td>{{ optional($log->created_at)->format('M d, Y h:i A') }}</td>
                    <td>{{ $log->actor_name ?? 'System' }}</td>
                    <td>{{ $log->event }}</td>
                    <td>{{ $log->auditable_type ? class_basename($log->auditable_type) : '' }}</td>
                    <td>{{ $log->auditable_id }}</td>
                    <td>{{ $log->description }}</td>
                    <td>{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
