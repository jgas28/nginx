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
        <!-- Header with Series No and Date on the edges -->
        <div class="header">
            <div class="date">
                <div class="label" style="font-size:12px">Date</div>
                <div class="value" style="font-size:12px">{{ \Carbon\Carbon::now()->format('F j, Y') }}</div>
            </div>
            <h1 style="font-size:15px">Cash Voucher Request</h1>
            <div class="series-no">
                <div class="label" style="font-size:12px">Series No</div>
              
                <div class="value" style="font-size:12px">{{ $cashVoucherRequest->cvr_number ?? 'N/A' }}-
                    {{$allocations->truck->truck_name}}-{{$deliveryRequest->company->company_code}}{{$deliveryRequest->expenseType->expense_code}}
                </div> 
            </div>
        </div>

        <div class="voucher-details">
            <!-- First table with borders -->
            <table  class="border-table">
                <thead>
                    <tr>
                        <th colspan="2">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="text-align: left; flex: 1; font-size:15px">PAID TO:</span>
                                <span style="text-align: left; flex: 1; padding-right: 40px; font-size:15px">
                                    {{ $employees->fname ?? 'N/A' }} {{ $employees->lname ?? 'N/A' }}
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <thead>
                    <tr>
                        <th style="width: 70%; font-size:15px">Particulars</th>
                        <th style="width: 30%; font-size:15px">Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $lineCount = 0;

                        // Estimate line count from delivery items
                        foreach ($deliveryLineItems as $item) {
                            $lineCount += in_array($deliveryRequest->name, ['ADM', 'FE', 'ND', 'OPS-INC']) ? 1 : 3;
                        }

                        // Add lines for remarks
                        $lineCount += !empty($remarks) ? count($remarks) + 1 : 0;

                        // Add one line if there's withholding tax
                        if ($cashVoucherRequest->voucher_type === 'with_tax') {
                            $lineCount += 1;
                        }

                        // Dynamically set font size
                        $fontSize = $lineCount > 12 ? '10px' : '12px';
                    @endphp
                    {{-- Start table body --}}
<tbody>

    {{-- Loop Delivery Info --}}
    @if (in_array($deliveryRequest->name, ['ADM', 'FE', 'ND', 'OPS-INC']))
        @foreach($deliveryLineItems as $item)
            <tr>
                <td style="text-align: left;">{{ $item->delivery_address }}</td>
                <td style="text-align: right;">₱ {{ number_format($cashVoucherRequest->amount ?? 0, 2) }}</td>
            </tr>
        @endforeach
    @else
        @foreach($deliveryLineItems as $item)
            <tr>
                <td style="text-align: left;">{{ $requestTypes->request_type }} - {{ $item->site_name }}</td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: left;">{{ $item->delivery_address }}</td>
                <td style="text-align: right;">₱ {{ number_format($cashVoucherRequest->amount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td style="text-align: left;">{{ $item->mtm }} - {{ $item->delivery_number }}</td>
                <td></td>
            </tr>
        @endforeach
    @endif

    {{-- Tax Base --}}
    @if($cashVoucherRequest->voucher_type === 'with_tax')
        <tr>
            <td style="text-align: left; font-size: 10px;">Tax Based Amount</td>
            <td style="text-align: right;">₱ {{ number_format($cashVoucherRequest->tax_based_amount ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align: left; font-size: 10px;">{{ $cashVoucherRequest->tax_description }}</td>
            <td style="text-align: right;">
                ₱ {{
                    number_format(
                        ($cashVoucherRequest->tax_based_amount ?? 0) * ($cashVoucherRequest->tax_percentage ?? 0),
                        2
                    )
                }}
            </td>
        </tr>
    @endif

    {{-- Remarks --}}
    @if (!empty($remarks))
        <tr>
            <td style="text-align: left;">Remarks:</td>
            <td></td>
        </tr>
        @foreach($remarks as $remark)
            <tr>
                <td style="text-align: left;">{{ $remark }}</td>
                <td></td>
            </tr>
        @endforeach
    @endif

    {{-- Transfer Charge --}}
    @if(!empty($cvrApprovals->charge) && $cvrApprovals->charge != 0)
        <tr>
            <td style="text-align: left;">Transfer Charge</td>
            <td style="text-align: right;">₱ {{ number_format($cvrApprovals->charge, 2) }}</td>
        </tr>
    @endif



                    {{-- TOTAL ROW --}}
                    <tr>
                        <td style="padding: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                {{-- LEFT SIDE: DRIVER INFO --}}
                                <div style="font-size: 12px;">
                                    @if(
                                        $drivers->employee_code === 'NONE' || 
                                        in_array($deliveryRequest->name, ['ADM', 'FE', 'ND', 'OPS-INC'])
                                    )
                                        {{-- No driver text --}}
                                    @elseif(empty($fleets->account_name))
                                        DRIVER: {{ $drivers->fname }} {{ $drivers->lname }}
                                    @elseif(strtoupper($drivers->fname . ' ' . $drivers->lname) === strtoupper($fleets->account_name))
                                        DRIVER: {{ $drivers->fname }} {{ $drivers->lname }} W/ FLEET CARD
                                    @else
                                        DRIVER: {{ $drivers->fname }} {{ $drivers->lname }} W/ FLEET CARD OF {{ $fleets->account_name }}
                                    @endif
                                </div>

                                {{-- RIGHT SIDE: TOTAL LABEL --}}
                                <div style="font-size: 12px; color: red; font-weight: bold;">
                                    TOTAL:
                                </div>
                            </div>
                        </td>
                        <td style="text-align: right; font-size: 14px;">
                            <strong style="color: red;">₱ {{ number_format($cashVoucherRequest->amount ?? 0, 2) }}</strong>
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
                        <div class="label" style="font-size: 10px; text-align:left;">RECEIVED from the amount of</div>
                        <div class="label" style="font-size: 10px; text-align: left; text-transform: uppercase;">
                            <strong><u>{{ $amountInWords ?? 'N/A' }}</u></strong>
                        </div>
                        <div class="label" style="font-size: 10px; text-align:left;">in full payment of amount described above</div>
                    </td>
                    <td>
                        <div class="label" style="font-size: 10px;">_________________________</div>
                        <div class="label" style="font-size: 10px;">{{ $employees->fname ?? 'N/A' }} {{ $employees->lname ?? 'N/A' }}</div>
                        <div class="label" style="font-size: 10px;">REQUEST BY:</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
