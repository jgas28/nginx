<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $payslip['payroll']->payroll_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111; background: #fff; }

        /* ── Salary Acknowledgement ─────────────────────── */
        .ack-wrap { padding: 18px 30px 10px; }
        .ack-title { text-align: center; font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
        .ack-name  { font-size: 12px; margin-bottom: 4px; }
        .ack-period{ font-size: 11px; margin-bottom: 4px; }
        .ack-received { display: flex; gap: 30px; margin-bottom: 12px; }
        .ack-received .date-box { font-weight: bold; font-size: 12px; }
        .ack-received .amount-box { font-size: 12px; }
        .ack-confirm { font-size: 11px; color: #1a5276; margin-bottom: 14px; }
        .ack-sig-section { margin-top: 8px; }
        .ack-sig-section p { font-size: 11px; margin-bottom: 3px; }
        .ack-sig-section .sig-line { display: inline-block; width: 180px; border-bottom: 1px solid #333; margin-left: 6px; vertical-align: bottom; }

        /* ── Payslip Body ───────────────────────────────── */
        .payslip-wrap { padding: 14px 30px 20px; }
        .payslip-title { text-align: center; font-weight: bold; font-size: 12px; border: 1px solid #333; padding: 6px 4px 2px; }
        .payslip-subtitle { text-align: center; font-weight: bold; font-size: 11px; border: 1px solid #333; border-top: none; padding: 2px 4px 6px; }

        .info-table { width: 100%; border-collapse: collapse; margin-top: 0; }
        .info-table td { border: 1px solid #555; padding: 4px 7px; font-size: 11px; }
        .info-table .lbl { font-weight: bold; width: 22%; }
        .info-table .val { width: 28%; }

        .earn-table { width: 100%; border-collapse: collapse; margin-top: 0; }
        .earn-table th { border: 1px solid #555; padding: 4px 7px; font-size: 11px; font-weight: bold; text-align: center; background: #fff; }
        .earn-table td { border: 1px solid #555; padding: 3px 7px; font-size: 11px; }
        .earn-table .right { text-align: right; }
        .earn-table .bold { font-weight: bold; }
        .earn-table .red { color: #c0392b; font-weight: bold; }
        .earn-table .blank { background: #fff; }
        .earn-table .total-row td { font-weight: bold; }
        .earn-table .netpay-row td { font-weight: bold; color: #c0392b; }

        .amount-words { border: 1px solid #555; border-top: none; padding: 6px 8px; font-size: 11px; min-height: 30px; }
        .salary-paid-row { width: 100%; border-collapse: collapse; }
        .salary-paid-row td { border: 1px solid #555; border-top: none; padding: 8px 10px; font-size: 11px; vertical-align: middle; }
        .cb { display: inline-block; width: 11px; height: 11px; border: 1px solid #333; vertical-align: middle; margin-right: 3px; }
        .sig-row { width: 100%; border-collapse: collapse; }
        .sig-row td { border: 1px solid #555; border-top: none; padding: 10px 10px 4px; font-size: 11px; text-align: center; }
        .sig-line-long { display: block; border-top: 1px solid #333; width: 140px; margin: 18px auto 4px; }
        .footer-row td { border: 1px solid #555; border-top: none; padding: 5px 10px; font-size: 10px; text-align: center; }
    </style>
</head>
<body>

@php
    $emp      = $payslip['employee'];
    $pr       = $payslip['payroll'];
    $earnings = $payslip['earnings'];
    $deductions = $payslip['deductions'];

    $totalEarnings   = collect($earnings)->sum('amount');
    $totalDeductions = (float) $pr->total_deduction;
    $netPay          = (float) $pr->net_salary;

    $cutoffLabel = \Carbon\Carbon::parse($pr->cutoff_from)->format('M d') . '-' . \Carbon\Carbon::parse($pr->cutoff_to)->format('d, Y');
    $fullName    = trim(($emp->fname ?? '') . ' ' . ($emp->lname ?? ''));
    $position    = $emp->position ?? 'N/A';
    $empCode     = $emp->employee_code ?? 'N/A';
@endphp

{{-- ═══════════════════════════════════════════════ --}}
{{--  SALARY ACKNOWLEDGEMENT                         --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="ack-wrap">
    <div class="ack-title">Salary Acknowledgement</div>

    <p class="ack-name"><strong>{{ $fullName }}</strong></p>
    <p class="ack-period">for the period covering <strong>{{ strtoupper($cutoffLabel) }}</strong>.&nbsp;&nbsp;&nbsp;&nbsp;The said amount was received on</p>

    <div class="ack-received" style="margin-top:6px;">
        <span class="date-box">{{ now()->format('M d, Y') }}</span>
        <span class="amount-box">₱{{ number_format($netPay, 2) }}</span>
    </div>

    <p class="ack-confirm">This receipt confirms that I have received the salary in full for the mentioned period.</p>

    <div class="ack-sig-section">
        <p>Received by:</p>
        <p>Name: <span class="sig-line"></span></p>
        <p>Ref No. <span class="sig-line"></span></p>
        <p>Signature: <span class="sig-line"></span></p>
        <p>Position: <span class="sig-line"></span></p>
        <p>Date: <span class="sig-line"></span></p>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{--  PAYSLIP                                        --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="payslip-wrap">
    <div class="payslip-title">Payslip</div>
    <div class="payslip-subtitle">For the Period of {{ strtoupper($cutoffLabel) }}</div>

    {{-- Employee info --}}
    <table class="info-table">
        <tr>
            <td class="lbl">Payslip for the Period:</td>
            <td class="val">{{ strtoupper($cutoffLabel) }}</td>
            <td class="lbl">Employee name:</td>
            <td class="val">{{ $fullName }}</td>
        </tr>
        <tr>
            <td class="lbl">Designation:</td>
            <td class="val">{{ $position }}</td>
            <td class="lbl">Employee ID No.:</td>
            <td class="val">{{ $empCode }}</td>
        </tr>
    </table>

    {{-- Earnings / Deductions table --}}
    <table class="earn-table">
        <thead>
            <tr>
                <th style="width:24%">Earnings</th>
                <th style="width:18%">Amount</th>
                <th style="width:40%">Deductions</th>
                <th style="width:18%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Align earnings and deductions row-by-row
                $maxRows = max(count($earnings), count($deductions));
            @endphp
            @for($i = 0; $i < $maxRows; $i++)
                @php
                    $e = $earnings[$i]  ?? null;
                    $d = $deductions[$i] ?? null;
                @endphp
                <tr>
                    <td>{{ $e ? $e['label'] . ':' : '' }}</td>
                    <td class="right">{{ ($e && $e['amount'] > 0) ? '₱'.number_format($e['amount'], 2) : ($e ? '' : '') }}</td>
                    <td>{{ $d ? $d['label'] . ':' : '' }}</td>
                    <td class="right">{{ ($d && $d['amount'] > 0) ? '₱'.number_format($d['amount'], 2) : '' }}</td>
                </tr>
            @endfor
            {{-- Totals row --}}
            <tr class="total-row">
                <td class="bold">Total Earnings:</td>
                <td class="right bold">₱{{ number_format($totalEarnings, 2) }}</td>
                <td class="right bold">Total Deductions:</td>
                <td class="right bold">₱{{ number_format($totalDeductions, 2) }}</td>
            </tr>
            {{-- Net Pay row --}}
            <tr>
                <td colspan="2" class="blank"></td>
                <td class="right red">Net Pay:</td>
                <td class="right red">₱{{ number_format($netPay, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Amount in words + net pay signature line --}}
    <div class="amount-words">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;₱{{ number_format($netPay, 2) }}
    </div>

    {{-- Salary Paid row --}}
    <table class="salary-paid-row">
        <tr>
            <td style="width:18%; font-weight:bold;">Salary Paid</td>
            <td>
                <span class="cb"></span> CASH &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="cb"></span> Bank Transfer &nbsp;&nbsp;&nbsp;&nbsp;
            </td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td><span class="cb"></span> Others; pls specify</td>
            <td style="border-bottom:1px solid #333;">&nbsp;</td>
        </tr>
    </table>

    {{-- Signature row --}}
    <table class="sig-row">
        <tr>
            <td style="width:50%;">
                <strong>Employer Signature</strong>
                <span class="sig-line-long"></span>
            </td>
            <td style="width:50%;">
                <strong>Employee Signature</strong>
                <span class="sig-line-long"></span>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <table class="sig-row">
        <tr class="footer-row">
            <td>Full details of your pay for this covered period are given above. Please check carefully and any questions concerning the accuracy of this statement should be taken up with the office</td>
        </tr>
    </table>
</div>

</body>
</html>
