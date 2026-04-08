<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Summary PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { margin-bottom: 4px; }
        .meta { margin-bottom: 20px; color: #555; }
        .employee-block { margin-bottom: 24px; }
        .employee-head { margin-bottom: 8px; }
        .table-title { margin: 10px 0 4px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .stats { margin-top: 4px; }
        .stats span { margin-right: 14px; }
    </style>
</head>
<body>
    <h1>Attendance Summary</h1>
    <div class="meta">
        Month: {{ $selectedMonth ?: 'All' }}
        @if($employee)
            | Employee Filter: {{ $employee }}
        @endif
    </div>

    @foreach($employeeSummaries as $summary)
        <div class="employee-block">
            <div class="employee-head">
                <strong>{{ $summary->employee_name }}</strong> ({{ $summary->employee_code }}) - {{ $summary->position }}
                <div class="stats">
                    <span>Present: {{ $summary->present_count }}</span>
                    <span>Late: {{ $summary->late_count }}</span>
                    <span>Absent: {{ $summary->absent_count }}</span>
                    <span>Total Hours: {{ number_format($summary->total_hours, 2) }}</span>
                </div>
            </div>

            <div class="table-title">Schedule Table</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Total Hours</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary->records as $record)
                        @php
                            $displayTimeIn = $record->time_in
                                ? $record->time_in->format('h:i A')
                                : ($record->status === 'Present' ? '08:00 AM' : '-');
                            $displayTimeOut = $record->time_out
                                ? $record->time_out->format('h:i A')
                                : ($record->status === 'Present' ? '05:00 PM' : '-');
                            $displayTotalHours = $record->total_hours
                                ? number_format($record->total_hours, 2)
                                : ($record->status === 'Present' ? '8.00' : '-');
                        @endphp
                        <tr>
                            <td>{{ $record->date->format('M d, Y') }}</td>
                            <td>{{ $record->status }}</td>
                            <td>{{ $displayTimeIn }}</td>
                            <td>{{ $displayTimeOut }}</td>
                            <td>{{ $displayTotalHours }}</td>
                            <td>{{ $record->remarks ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
