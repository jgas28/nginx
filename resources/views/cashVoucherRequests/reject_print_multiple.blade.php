<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Cash Voucher Request</title>
    <style>
        /* Your existing styles here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }
        
        .no-print {
            text-align: center;
            margin-top: 20px;
        }

        .container {
            width: 8.5in; /* full page width */
            height: 5.3in; /* half page height minus margin */
            margin: 0 auto 0.4in; /* center + spacing below */
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            box-sizing: border-box;
            page-break-inside: avoid;
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

        .voucher-details {
            margin-top: 20px;
        }

        .voucher-details table {
            width: 100%;
            text-align: center;
            border-collapse: collapse;
        }

        .voucher-details table, .voucher-details th, .voucher-details td {
            border: 1px solid #ccc;
        }

        .voucher-details th, .voucher-details td {
            padding: 10px;
            text-align: center;
        }

        .no-border-table {
            width: 100%;
            margin-top: 20px;
            text-align: center;
            border: none;
        }

        .no-border-table td {
            padding: 10px;
            border: none;
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
            color: rgba(255, 0, 0, 0.15);
            white-space: nowrap;
            pointer-events: none;
            z-index: 999;
            font-weight: bold;
        }

        @media print {
            @page {
                size: 8.5in 11in;
                margin: 1in;
            }

            .no-print {
                display: none;
            }

            .container {
                height: 5.3in; /* half page */
                margin-bottom: 0.4in; /* spacing between vouchers */
                box-shadow: none;
                border: none;
                padding: 10px;
                max-width: 100%;
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .voucher-details table {
                page-break-inside: avoid;
            }

            .container::before {
                content: "CANCELLED";
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    @foreach ($vouchers as $voucher)
        @php
            $cashVoucherRequest = $voucher['cashVoucherRequest'] ?? null;
            $allocations = $voucher['allocations'] ?? null;
            $deliveryRequest = $voucher['deliveryRequest'] ?? null;
            $employees = $voucher['employees'] ?? null;
            $drivers = $voucher['drivers'] ?? null;
            $fleets = $voucher['fleets'] ?? null;
            $requestTypes = $voucher['requestTypes'] ?? null;
            $deliveryLineItems = $voucher['deliveryLineItems'] ?? [];
            $cvrApprovals = $voucher['cvrApprovals'] ?? null;
            $remarks = $voucher['remarks'] ?? [];
            $rejectRemarks = $voucher['rejectRemarks'] ?? [];
            $amountInWords = $voucher['amountInWords'] ?? 'N/A';

            $baseAmount = $cashVoucherRequest->tax_based_amount ?? 0;
            $vatAmount = $baseAmount * 0.12;
            $taxDeduction = $baseAmount * ($cashVoucherRequest->withholdingTax->percentage ?? 0);
            $finalAmount = $baseAmount + $vatAmount - $taxDeduction;
        @endphp

        <div class="container">
            <div class="header">
                <div class="date">
                    <div style="font-size:12px">Date</div>
                    <div style="font-size:12px">{{ \Carbon\Carbon::now()->format('F j, Y') }}</div>
                </div>
                <h1 style="font-size:15px">Cash Voucher Request</h1>
                <div class="series-no">
                    <div style="font-size:12px">Series No</div>
                    <div style="font-size:12px">
                        {{ preg_replace('/\/\d+$/', '', $cashVoucherRequest->cvr_number ?? 'N/A') }}
                        -{{ $allocations->truck->truck_name ?? 'N/A' }}
                        -{{ $deliveryRequest->company->company_code ?? 'N/A' }}
                        {{ $deliveryRequest->expenseType->expense_code ?? '' }}
                    </div>
                </div>
            </div>

            <div class="voucher-details">
                <table>
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: left; font-size: 18px;">
                                PAID TO: {{ $employees->fname ?? 'N/A' }} {{ $employees->lname ?? 'N/A' }}
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 70%;">Particulars</th>
                            <th style="width: 30%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (in_array($deliveryRequest->name ?? '', ['ADM', 'FE', 'ND', 'OPS-INC']))
                            <tr>
                                <td style="text-align: center; font-size: 12px; border-bottom: none; height: 150px; vertical-align: top; overflow: auto;">
                                    @foreach($deliveryLineItems as $item)
                                        {{ $item->delivery_address }}<br>
                                    @endforeach
                                    <br>
                                    Reject Remarks:<br>
                                    @if(!empty($rejectRemarks))
                                        @foreach($rejectRemarks as $value)
                                            <strong>{{ $value }}</strong> 
                                        @endforeach
                                    @else
                                        <p class="text-gray-500 text-sm">No remarks provided.</p>
                                    @endif
                                </td>
                                <td style="text-align: right; font-size: 16px; color: red; border-bottom: none; height: 150px; vertical-align: top;">
                                    ₱ {{ number_format($cashVoucherRequest->amount, 2) }}
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td style="text-align: center; font-size: 12px; border-bottom: none; height: 150px; vertical-align: top; overflow: auto;">
                                    @foreach($deliveryLineItems as $item)
                                        {{ $requestTypes->request_type ?? '' }} - {{ $item->site_name }}<br>
                                        {{ $item->delivery_address }}<br>
                                        {{ $item->mtm }} - {{ $item->delivery_number }}<br><br>
                                    @endforeach
                                    <br>
                                    Reject Remarks:<br>
                                    @if(!empty($rejectRemarks))
                                        @foreach($rejectRemarks as $value)
                                            <strong>{{ $value }}</strong> 
                                        @endforeach
                                    @else
                                        <p class="text-gray-500 text-sm">No remarks provided.</p>
                                    @endif
                                </td>   
                                <td style="text-align: right; font-size: 16px; color: red; border-bottom: none; height: 150px; vertical-align: top;">
                                    ₱ {{ number_format($cashVoucherRequest->amount, 2) }}
                                </td>
                            </tr>
                        @endif

                        {{-- Driver & Fleet Info and Tax Summary --}}
                        @if ($cashVoucherRequest->voucher_type === 'with_tax')
                            <tr>
                                <td style="text-align: left; vertical-align: top; font-size: 12px; padding: 10px;">
                                    @if(
                                        ($drivers->employee_code ?? '') === 'NONE' || 
                                        in_array($deliveryRequest->name ?? '', ['ADM', 'FE', 'ND', 'OPS-INC'])
                                    )
                                        DRIVER: N/A
                                    @elseif(empty($fleets->account_name))
                                        DRIVER: {{ $drivers->fname ?? '' }} {{ $drivers->lname ?? '' }}
                                    @elseif(strtoupper(($drivers->fname ?? '') . ' ' . ($drivers->lname ?? '')) === strtoupper($fleets->account_name ?? ''))
                                        DRIVER: {{ $drivers->fname ?? '' }} {{ $drivers->lname ?? '' }} W/ FLEET CARD
                                    @else
                                        DRIVER: {{ $drivers->fname ?? '' }} {{ $drivers->lname ?? '' }} W/ FLEET CARD OF {{ $fleets->account_name ?? '' }}
                                    @endif

                                    @if (!empty($remarks))
                                        <br><br><strong>Remarks:</strong><br>
                                        @foreach($remarks as $remark)
                                            {{ $remark }}<br>
                                        @endforeach
                                    @endif

                                    @if(!empty($cvrApprovals->charge) && $cvrApprovals->charge != 0)
                                        <br><br><strong>Transfer Charge:</strong> ₱ {{ number_format($cvrApprovals->charge, 2) }}
                                    @endif
                                </td>
                                <td style="padding: 0;">
                                    <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                                        <tr>
                                            <td style="text-align: left; padding: 4px;">Subtotal</td>
                                            <td style="text-align: right; padding: 4px;">₱ {{ number_format($cashVoucherRequest->amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left; padding: 4px;">Net Amount</td>
                                            <td style="text-align: right; padding: 4px;">₱ {{ number_format($baseAmount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left; padding: 4px;">VAT (12%)</td>
                                            <td style="text-align: right; padding: 4px;">₱ {{ number_format($vatAmount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left; padding: 4px;">{{ $cashVoucherRequest->withholdingTax->description }}</td>
                                            <td style="text-align: right; padding: 4px;">₱ {{ number_format($taxDeduction, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left; font-weight: bold; padding: 4px; color: red;">Total</td>
                                            <td style="text-align: right; font-weight: bold; color: red; padding: 4px;">₱ {{ number_format($finalAmount, 2) }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        @elseif ($cashVoucherRequest->voucher_type === 'regular')
                            <tr>
                                <td style="text-align: left; vertical-align: top; font-size: 12px; padding: 10px;">
                                    @if(
                                        ($drivers->employee_code ?? '') === 'NONE' || 
                                        in_array($deliveryRequest->name ?? '', ['ADM', 'FE', 'ND', 'OPS-INC'])
                                    )
                                        DRIVER: N/A
                                    @elseif(empty($fleets->account_name))
                                        DRIVER: {{ $drivers->fname ?? '' }} {{ $drivers->lname ?? '' }}
                                    @elseif(strtoupper(($drivers->fname ?? '') . ' ' . ($drivers->lname ?? '')) === strtoupper($fleets->account_name ?? ''))
                                        DRIVER: {{ $drivers->fname ?? '' }} {{ $drivers->lname ?? '' }} W/ FLEET CARD
                                    @else
                                        DRIVER: {{ $drivers->fname ?? '' }} {{ $drivers->lname ?? '' }} W/ FLEET CARD OF {{ $fleets->account_name ?? '' }}
                                    @endif

                                    @if (!empty($remarks))
                                        <br><br><strong>Remarks:</strong><br>
                                        @foreach($remarks as $remark)
                                            {{ $remark }}<br>
                                        @endforeach
                                    @endif
                                    @if(!empty($cvrApprovals->charge) && $cvrApprovals->charge != 0)
                                        <br><br><strong>Transfer Charge:</strong> ₱ {{ number_format($cvrApprovals->charge, 2) }}
                                    @endif
                                </td>
                                <td style="padding: 0;">
                                    <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                                        <tr>
                                            <td style="text-align: left; padding: 4px;">Subtotal</td>
                                            <td style="text-align: right; padding: 4px;">₱ {{ number_format($cashVoucherRequest->amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left; padding: 4px;">Net Amount</td>
                                            <td style="text-align: right; padding: 4px;">₱ {{ number_format($cashVoucherRequest->amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left; padding: 4px;">VAT (12%)</td>
                                            <td style="text-align: right; padding: 4px;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left; padding: 4px;">Less Withholding Tax</td>
                                            <td style="text-align: right; padding: 4px;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left; font-weight: bold; padding: 4px; color: red;">Total</td>
                                            <td style="text-align: right; font-weight: bold; color: red; padding: 4px;">₱ {{ number_format($cashVoucherRequest->amount, 2) }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <table class="no-border-table">
                    <tr>
                        <td>
                            <div style="font-size: 10px;">_________________________</div>
                            <div style="font-size: 10px;">Approver</div>
                        </td>
                        <td>
                            <div style="font-size: 10px; text-align:left;">RECEIVED from {{$deliveryRequest->company->company_name}} the amount of</div>
                            <div style="font-size: 10px; text-align: left; text-transform: uppercase;">
                            <strong><u>{{ $amountInWords == 'N/A' ? 'Zero' : ($amountInWords ?? 'Zero') }}</u></strong>
                            </div>
                            <div style="font-size: 10px; text-align:left;">in full payment of amount described above</div>
                        </td>
                        <td>
                            <div style="font-size: 10px;">_________________________</div>
                            <div style="font-size: 10px;">{{ $employees->fname ?? 'N/A' }} {{ $employees->lname ?? 'N/A' }}</div>
                            <div style="font-size: 10px;">REQUEST BY:</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endforeach

    <div class="no-print">
        <button class="btn" onclick="window.print()">Print Cash Voucher Requests</button>
    </div>
</body>
</html>
