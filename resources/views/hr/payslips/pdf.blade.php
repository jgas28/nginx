<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 12px; }
        h1, h2, h3, p { margin: 0; }
        .header { margin-bottom: 20px; }
        .section { margin-bottom: 20px; }
        .grid { width: 100%; }
        .grid td { vertical-align: top; padding: 6px 0; }
        .label { color: #666; }
        .card-table, .attendance-table { width: 100%; border-collapse: collapse; }
        .card-table td, .attendance-table th, .attendance-table td { border: 1px solid #d6d6d6; padding: 8px; }
        .attendance-table th { background: #f3f4f6; text-align: left; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payslip</h1>
        <p>{{ $payslip['employee']->fname }} {{ $payslip['employee']->lname }}</p>
        <p>{{ $payslip['employee']->employee_code }} | {{ $payslip['employee']->position ?? 'N/A' }}</p>
        <p>Payroll No: {{ $payslip['payroll']->payroll_no }}</p>
        <p>Cutoff: {{ $payslip['payroll']->cutoff_from->format('M d, Y') }} - {{ $payslip['payroll']->cutoff_to->format('M d, Y') }}</p>
    </div>

    <div class="section">
        <table class="grid">
            <tr>
                <td width="50%">
                    <p><span class="label">TIN:</span> {{ $payslip['employee']->tin_no ?: 'N/A' }}</p>
                    <p><span class="label">SSS:</span> {{ $payslip['employee']->sss_no ?: 'N/A' }}</p>
                    <p><span class="label">PhilHealth:</span> {{ $payslip['employee']->philhealth_no ?: 'N/A' }}</p>
                </td>
                <td width="50%">
                    <p><span class="label">Days Worked:</span> {{ number_format($payslip['payroll']->total_days_worked, 0) }}</p>
                    <p><span class="label">Hours Worked:</span> {{ number_format($payslip['payroll']->total_hours_worked, 2) }}</p>
                    <p><span class="label">Status:</span> {{ $payslip['payroll']->payroll_status }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Earnings And Deductions</h3>
        <table class="card-table">
            <tr>
                <td width="50%">
                    <strong>Earnings</strong>
                    @foreach($payslip['earnings'] as $earning)
                        <p>{{ $earning['label'] }}: P{{ number_format($earning['amount'], 2) }}</p>
                    @endforeach
                    <p class="total">Total Earnings: P{{ number_format($payslip['payroll']->gross_salary + $payslip['payroll']->total_allowance, 2) }}</p>
                </td>
                <td width="50%">
                    <strong>Deductions</strong>
                    @foreach($payslip['deductions'] as $deduction)
                        <p>{{ $deduction['label'] }}: P{{ number_format($deduction['amount'], 2) }}</p>
                    @endforeach
                    <p class="total">Total Deductions: P{{ number_format($payslip['payroll']->total_deduction, 2) }}</p>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="total">Net Pay: P{{ number_format($payslip['payroll']->net_salary, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Attendance Breakdown</h3>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Hours</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payslip['attendance_records'] as $record)
                    @php
                        $timeIn = $record->time_in ? $record->time_in->format('h:i A') : ($record->status === 'Present' ? '08:00 AM' : '-');
                        $timeOut = $record->time_out ? $record->time_out->format('h:i A') : ($record->status === 'Present' ? '05:00 PM' : '-');
                        $hours = !is_null($record->total_hours) ? number_format($record->total_hours, 2) : ($record->status === 'Present' ? '8.00' : '0.00');
                    @endphp
                    <tr>
                        <td>{{ $record->date->format('M d, Y') }}</td>
                        <td>{{ $record->status }}</td>
                        <td>{{ $timeIn }}</td>
                        <td>{{ $timeOut }}</td>
                        <td>{{ $hours }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No attendance records found for this cutoff.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
