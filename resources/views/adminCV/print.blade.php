<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cash Voucher Request</title>
    <style>
        .container { 
            width: 8.5in;   /* 8.5 inches width */
            /* height: 11in;   11 inches height */
            margin: 0 auto; /* Center the container horizontally */
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative; /* For absolute positioning inside container */
            box-sizing: border-box; /* Ensure padding doesn't affect the overall size */
        }

        .header {
            position: relative;
            width: 100%;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: bold;
            text-decoration: underline;
            text-align: center;
            margin: 0;
        }

        .header .date,
        .header .series-no {
            position: absolute;
            top: 0;
            font-size: 14px;
            font-weight: bold;
        }

        .header .date {
            left: 0;
        }

        .header .series-no {
            right: 0;
            text-align: right;
        }

        .header .series-no .value {
            max-width: 100%;
        }

        .voucher-details {
            margin-top: 20px;
        }

        .voucher-details table {
            width: 100%;
            text-align: center;
            border-collapse: collapse;
        }

        .voucher-details table, .voucher-details th, .voucher-details td {
            border: 1px solid #ccc; /* Add border to the first table */
        }

        .voucher-details th, .voucher-details td {
            padding: 10px;
            text-align: center;
        }

        .voucher-details .field {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            border: 1px solid #ccc;
            padding: 8px;
            border-radius: 5px;
        }

        /* New section below the table */
        .no-border-table {
            width: 100%;
            margin-top: 20px;
            text-align: center;
            border: none;
        }

        .no-border-table .label{
            font-size: 12px;
            border: none;
        }

        .no-border-table td {
            padding: 10px;
            border: none;
        }

        /* Button styles */
        .no-print {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #45a049;
        }

        /* Print specific styling */
        @media print {
            @page {
                size: 8.5in 11in;
                margin: 1in;
            }

            .voucher-details table,
            .no-border-table {
                page-break-inside: avoid;
            }

            .container {
                page-break-inside: avoid;
            }

            body {
                margin: 0;
                padding: 0;
                font-size: 12px;
            }

            .container {
                max-width: 100%;
                padding: 10px;
                border: none;
                box-sizing: border-box;
            }

            .header h1 {
                font-size: 24px;
                text-decoration: underline;
            }

            .voucher-details p {
                font-size: 14px;
            }

            .voucher-details .label {
                font-size: 14px;
            }

            .voucher-details .field .label {
                font-size: 14px;
            }

            .voucher-details .field .value {
                font-size: 14px;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="date">
            <div style="font-size:12px">Date</div>
            <div style="font-size:12px">{{ \Carbon\Carbon::now()->format('F j, Y') }}</div>
        </div>
        <h1 style="font-size:22px">Cash Voucher Request</h1>
        <div class="series-no">
            <div style="font-size:12px">Series No</div>
            <div style="font-size:12px">
                @if(strtolower($vouchers->cashVoucher->cvr_type ?? '') === 'admin')
                    {{ preg_replace('/\/\d+$/', '',$vouchers->cvr_number ?? 'N/A') }}-{{ $vouchers->cashVoucher->company->company_code ?? 'N/A' }}{{ $vouchers->cashVoucher->expenseTypes->expense_code ?? 'N/A' }}
                @elseif(strtolower($vouchers->cashVoucher->cvr_type ?? '') === 'rpm')
                    {{ preg_replace('/\/\d+$/', '',$vouchers->cvr_number ?? 'N/A') }}-{{ $vouchers->cashVoucher->trucks->truck_name ?? 'N/A' }}-{{ $vouchers->cashVoucher->company->company_code ?? 'N/A' }}{{ $vouchers->cashVoucher->expenseTypes->expense_code ?? 'N/A' }}
                @endif
            </div>
        </div>
    </div>

    <div class="voucher-details">
        <table>
            <thead>
            <tr>
                <th colspan="2" style="text-align: left; font-size: 18px;">PAID TO: {{ $vouchers->cashVoucher->suppliers->supplier_name ?? 'N/A' }}</th>
            </tr>
            <tr>
                <th style="width: 70%;">Particulars</th>
                <th style="width: 30%;">Amount</th>
            </tr>
            </thead>
            <tbody>
            @php
                $descriptions = json_decode($vouchers->cashVoucher->description ?? '[]', true);
                $amounts = json_decode($vouchers->cashVoucher->amount_details ?? '[]', true);
                $remarks = json_decode($vouchers->cashVoucher->remarks ?? '[]', true);
                $totalAmount = collect($amounts)->filter(fn($amt) => is_numeric($amt))->sum();
                $taxBase = $vouchers->cashVoucher->tax_based_amount ?? 0;
                $vat = $taxBase * 0.12;
                $withholding = $taxBase * ($vouchers->cashVoucher->withholdingTax->percentage ?? 0);
                $final = $taxBase + $vat - $withholding;
            @endphp

            <tr>
                 <td style="text-align: center; font-size: 12px; border-bottom: none; height: 150px; vertical-align: top; overflow: auto;">
                    @foreach ($descriptions as $desc) {{ $desc }}<br> @endforeach
                </td>
                <td style="text-align: right; font-size: 16px; color: red; border-bottom: none; height: 150px; vertical-align: top;">
                    @foreach ($amounts as $amt) ₱ {{ number_format($amt, 2) }}<br> @endforeach
                </td>
            </tr>

            <tr>
                @if ($vouchers->cashVoucher->voucher_type === 'with_tax')
                 <td style="text-align: left; vertical-align: top; font-size: 12px; padding: 10px;">
                    @if (!empty($remarks))
                        <strong>Remarks:</strong><br>
                        @foreach ($remarks as $remark)
                            {{ $remark }}<br>
                        @endforeach
                    @endif

                    @if(!empty($vouchers->charge) && $vouchers->charge != 0)
                        <br><strong>Transfer Charge:</strong> ₱ {{ number_format($vouchers->charge, 2) }}
                    @endif
                </td>
                <td style="padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                        <tr>
                            <td style="text-align: left; padding: 4px;">Subtotal</td>
                            <td style="text-align: right; padding: 4px;">₱ {{ number_format($totalAmount, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding: 4px;">Net Amount</td>
                            <td style="text-align: right; padding: 4px;">
                                ₱ {{ number_format(($vouchers->cashVoucher->voucher_type === 'with_tax') ? $taxBase : $totalAmount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding: 4px;">VAT (12%)</td>
                            <td style="text-align: right; padding: 4px;">@if($vouchers->cashVoucher->voucher_type === 'with_tax') ₱ {{ number_format($vat, 2) }} @endif</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding: 4px;">{{ $vouchers->withholdingTax->description }}</td>
                            <td style="text-align: right; padding: 4px;">@if($vouchers->cashVoucher->voucher_type === 'with_tax') ₱ {{ number_format($withholding, 2) }} @endif</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: bold; color: red;">Total</td>
                            <td style="text-align: right; font-weight: bold; color: red;">
                                ₱ {{ number_format(($vouchers->cashVoucher->voucher_type === 'with_tax') ? $final : $totalAmount, 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
                @elseif($vouchers->cashVoucher->voucher_type === 'regular')
                <td style="text-align: left; vertical-align: top; font-size: 12px; padding: 10px;">
                    @if (!empty($remarks))
                        <strong>Remarks:</strong><br>
                        @foreach ($remarks as $remark)
                            {{ $remark }}<br>
                        @endforeach
                    @endif

                    @if(!empty($vouchers->charge) && $vouchers->charge != 0)
                        <br><strong>Transfer Charge:</strong> ₱ {{ number_format($vouchers->charge, 2) }}
                    @endif
                </td>
                <td style="padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                        <tr>
                            <td style="text-align: left; padding: 4px;">Subtotal</td>
                            <td style="text-align: right; padding: 4px;">₱ {{ number_format($totalAmount, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding: 4px;">Net Amount</td>
                            <td style="text-align: right; padding: 4px;">
                                ₱ {{ number_format(($vouchers->cashVoucher->voucher_type === 'with_tax') ? $taxBase : $totalAmount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding: 4px;">VAT (12%)</td>
                            <td style="text-align: right; padding: 4px;">@if($vouchers->cashVoucher->voucher_type === 'with_tax') ₱ {{ number_format($vat, 2) }} @endif</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding: 4px;">Less Withholding Tax</td>
                            <td style="text-align: right; padding: 4px;">@if($vouchers->cashVoucher->voucher_type === 'with_tax') ₱ {{ number_format($withholding, 2) }} @endif</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: bold; padding: 4px; color: red;">Total</td>
                            <td style="text-align: right; font-weight: bold; color: red; padding: 4px;">
                                ₱ {{ number_format(($vouchers->cashVoucher->voucher_type === 'with_tax') ? $final : $totalAmount, 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
                @endif
            </tr>
            </tbody>
        </table>

        <table class="no-border-table">
            <tr>
                <td>
                    <div style="font-size: 10px;">_________________________</div>
                    <div style="font-size: 10px;">{{ $approvers->name }}</div>
                    <div style="font-size: 10px;">Approver</div>
                </td>
                <td>
                    <div style="font-size: 10px; text-align:left;">RECEIVED from <strong>{{ $vouchers->cashVoucher->company->company_name ?? 'N/A' }}</strong>, the amount of</div>
                    <div style="font-size: 10px; text-align: left; text-transform: uppercase;"><strong><u>{{ $amountInWords ?? 'N/A' }}</u></strong></div>
                    <div style="font-size: 10px; text-align:left;">in full payment of amount described above</div>
                </td>
                <td>
                    <div style="font-size: 10px;">_________________________</div>
                    <div style="font-size: 10px;">{{ $vouchers->cashVoucher->suppliers->supplier_name ?? 'N/A' }}</div>
                    <div style="font-size: 10px;">Request By</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="no-print">
        <button 
            onclick="printAndUpdateStatusSingle(this);" 
            class="btn"
            data-cvr-id="{{ $vouchers->id }}" 
            data-voucher-id="{{ $vouchers->cashVoucher->id }}">
            Print
        </button>
    </div>
</div>

<script>
    function printAndUpdateStatusSingle(button) {
        const cvrId = button.getAttribute('data-cvr-id');
        const voucherId = button.getAttribute('data-voucher-id');

        fetch('/update-print-admin-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                cvr_ids: [cvrId],
                voucher_ids: [voucherId]
            })
        })
        .then(res => res.json())
        .then(data => {
            console.log('Status updated:', data);
            window.print();
        })
        .catch(err => {
            console.error('Error updating status:', err);
            window.print();
        });
    }
</script>
</body>
</html>
