<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { margin-bottom: 6px; }
        .meta { margin-bottom: 16px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d6d6d6; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Payslip List</h1>
    <div class="meta">
        @if($month)
            Month: {{ $month }}
        @else
            Month: All
        @endif
        @if($search)
            | Search: {{ $search }}
        @endif
        @if($status)
            | Status: {{ $status }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Payroll No.</th>
                <th>Employee</th>
                <th>Code</th>
                <th>Cutoff</th>
                <th>Gross</th>
                <th>SSS</th>
                <th>PhilHealth</th>
                <th>Tax</th>
                <th>Allowance</th>
                <th>Total Deduction</th>
                <th>Net Salary</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payslips as $payslip)
                <tr>
                    <td>{{ $payslip->payroll_no }}</td>
                    <td>{{ $payslip->user->fname ?? '' }} {{ $payslip->user->lname ?? '' }}</td>
                    <td>{{ $payslip->user->employee_code ?? 'N/A' }}</td>
                    <td>{{ $payslip->cutoff_from->format('M d, Y') }} - {{ $payslip->cutoff_to->format('M d, Y') }}</td>
                    <td>{{ number_format((float) $payslip->gross_salary, 2) }}</td>
                    <td>{{ number_format((float) ($payslip->sss_deduction ?? 0), 2) }}</td>
                    <td>{{ number_format((float) ($payslip->philhealth_deduction ?? 0), 2) }}</td>
                    <td>{{ number_format((float) ($payslip->tax_deduction ?? 0), 2) }}</td>
                    <td>{{ number_format((float) $payslip->total_allowance, 2) }}</td>
                    <td>{{ number_format((float) $payslip->total_deduction, 2) }}</td>
                    <td>{{ number_format((float) $payslip->net_salary, 2) }}</td>
                    <td>{{ $payslip->payroll_status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12">No payslips found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
