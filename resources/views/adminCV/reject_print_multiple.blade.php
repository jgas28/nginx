<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cash Voucher Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }

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

        .container::before {
            content: "CANCELLED";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(255, 0, 0, 0.15); /* Light red, transparent */
            white-space: nowrap;
            pointer-events: none;
            z-index: 999;
            font-weight: bold;
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

            .container::before {
                content: "CANCELLED";
                -webkit-print-color-adjust: exact; /* Ensure it appears in print */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Series No and Date on the edges -->
        <div class="header">
            <div class="date">
                <div class="label" style="font-size:12px">Date</div>
                <div class="value" style="font-size:12px">{{ \Carbon\Carbon::now()->format('F j, Y') }}</div>
            </div>
            <h1 style="font-size:22px">Cash Voucher Request</h1>
            <div class="series-no">
                <div class="label" style="font-size:12px">Series No</div>
                <div style="font-size:12px">
                    @if(isset($vouchers->cvr_type) && strtolower($vouchers->cvr_type) === 'admin')
                        <!-- When cvr_type is admin -->
                        {{ preg_replace('/\/\d+$/', '', $vouchers->cvr_number ?? 'N/A') }}-{{ $vouchers->company->company_code ?? 'N/A' }}{{ $vouchers->expenseTypes->expense_code ?? 'N/A' }}
                    @elseif(isset($vouchers->cvr_type) && strtolower($vouchers->cvr_type) === 'rpm')
                        <!-- When cvr_type is rpm -->
                        {{ preg_replace('/\/\d+$/', '', $vouchers->cvr_number ?? 'N/A') }}-{{ $vouchers->trucks->truck_name ?? 'N/A' }}-{{ $vouchers->company->company_code ?? 'N/A' }}{{ $vouchers->expenseTypes->expense_code ?? 'N/A' }}
                    @else
                        {{ $vouchers->cvr_number ?? 'N/A' }}
                    @endif
                </div>
            </div>
        </div>

        <div class="voucher-details">
            <!-- First table with borders -->
            <table style="width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 12px;">
                <colgroup>
                    <col style="width: 70%;">
                    <col style="width: 30%;">
                </colgroup>
                <thead>
                    <tr>
                        <th colspan="2" style="text-align: left; font-size: 18px; border: 1px solid #ccc;">
                            PAID TO: {{ $vouchers->suppliers->supplier_name ?? 'N/A' }}
                        </th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ccc;">Particulars</th>
                        <th style="border: 1px solid #ccc;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $descriptions = json_decode($vouchers->description ?? '[]');
                        $amounts = json_decode($vouchers->amount_details ?? '[]'); 
                    @endphp
                    <tr>
                         <td style="text-align: center; font-size: 12px; border-bottom: none; height: 150px; vertical-align: top; overflow: auto;">
                            @foreach ($descriptions as $desc)
                                {{ $desc }}<br>
                            @endforeach
                            <br>
                            Reject Remarks:<br>
                            @if(!empty($rejectRemarks))
                                @foreach($rejectRemarks as $key => $value)
                                <strong>{{ $value }}</strong> 
                                @endforeach
                            @else
                                <p class="text-gray-500 text-sm">No remarks provided.</p>
                            @endif
                        </td>
                         <td style="text-align: center; font-size: 12px; border-bottom: none; height: 150px; vertical-align: top; overflow: auto;">
                            @foreach ($amounts as $amt)
                                ₱ {{ number_format($amt, 2) }}<br>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left; vertical-align: top; font-size: 12px; padding: 10px;">
                        @php
                            $remarks = json_decode($vouchers->remarks);
                        @endphp
                        @if (!empty($remarks))
                            <strong>Remarks:</strong><br>
                            @foreach($remarks as $remark)
                                {{ $remark }}<br>
                            @endforeach
                        @endif
                        @if(!empty($vouchers->charge) && $vouchers->charge != 0)
                            <br><br><strong>Transfer Charge:</strong> ₱ {{ number_format($cvrApprovals->charge, 2) }}
                         @endif 
                        </td>
                        <td style="border: 1px solid #ccc; padding: 0;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                                <colgroup>
                                    <col style="width: 60%;">
                                    <col style="width: 40%;">
                                </colgroup>
                                <tr>
                                    <td style="text-align: left; padding: 4px;">Net Amount</td>

                                    @if($vouchers->voucher_type === 'with_tax')
                                        <td style="text-align: right; padding: 4px;">₱ {{ number_format($vouchers->tax_based_amount, 2) }}</td>

                                    @elseif($vouchers->voucher_type === 'regular')
                                        @php
                                            $totalAmount = 0;
                                            $amountDetails = json_decode($vouchers->amount_details); // ✅ Corrected here

                                            if (is_array($amountDetails)) {
                                                foreach ($amountDetails as $item) {
                                                    if (is_numeric($item)) {
                                                        $totalAmount += $item;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <td style="text-align: right; padding: 4px;">₱ {{ number_format($totalAmount, 2) }}</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding: 4px;">VAT (12%)</td>
                                    <td style="text-align: right; padding: 4px;">
                                        @if($vouchers->voucher_type === 'with_tax')
                                            ₱ {{ number_format($vouchers->tax_based_amount * 0.12, 2) }}
                                        @elseif($vouchers->voucher_type === 'regular')
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding: 4px;">Less Withholding Tax</td>
                                    <td style="text-align: right; padding: 4px;">
                                        @if($vouchers->voucher_type === 'with_tax')
                                            ₱ {{ number_format($vouchers->tax_based_amount * $vouchers->withholdingTax->percentage, 2) }}
                                         @elseif($vouchers->voucher_type === 'regular')
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; font-weight: bold; padding: 4px; color: red;">Total</td>
                                    <td style="text-align: right; font-weight: bold; color: red; padding: 4px;">
                                        @if($vouchers->voucher_type === 'with_tax')
                                            @php
                                                $taxAmount = $vouchers->tax_based_amount * 0.12;
                                                $withholdingAmount = $vouchers->tax_based_amount * $vouchers->withholdingTax->percentage;
                                                $finalAmount = $vouchers->tax_based_amount + $taxAmount - $withholdingAmount;
                                            @endphp

                                            ₱ {{ number_format($finalAmount, 2) }}

                                        @elseif($vouchers->voucher_type === 'regular')
                                            ₱ {{ number_format($totalAmount, 2) }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- New section under the first table without borders -->
            <table class="no-border-table">
                <tr>
                    <td>
                        <div class="label" style="font-size: 10px;">_________________________</div>
                        <div class="label" style="font-size: 10px;">Approver</div>
                    </td>
                    <td>
                        <div style="font-size: 10px; text-align:left;">RECEIVED from {{ $vouchers->company->company_name ?? 'N/A' }} the amount of</div>
                        <div style="font-size: 10px; text-align: left; text-transform: uppercase;">
                            <strong><u>{{ $amountInWords ?? 'N/A' }}</u></strong>
                        </div>
                        <div style="font-size: 10px; text-align:left;">in full payment of amount described above</div>
                    </td>
                    <td>
                        <div class="label" style="font-size: 10px;">_________________________</div>
                        <div style="font-size: 10px;"> {{ $vouchers->suppliers->supplier_name ?? 'N/A' }}</div>
                        <div class="label" style="font-size: 10px;">REQUEST BY:</div>
                    </td>
                </tr>
            </table>
        </div>
    <div class="no-print">
        <button class="btn" onclick="window.print()">Print</button>
    </div>
</body>
</html>
