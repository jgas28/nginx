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
            <h1 style="font-size:22px">Cash Voucher Request</h1>
            <div class="series-no">
                <div class="label" style="font-size:12px">Series No</div>
                <div class="value" style="font-size:12px">{{ $cashVoucherRequest->cvr_number ?? 'N/A' }}</div> 
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
                                    {{ $employees->first_name ?? 'N/A' }} {{ $employees->last_name ?? 'N/A' }}
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
                    <tr>
                        <td style="height: 150px; font-size:12px">
                            @if ($deliveryRequest->name === 'ADM' || $deliveryRequest->name === 'FE' || $deliveryRequest->name === 'ND' || $deliveryRequest->name === 'OPS-INC')
                                @foreach($deliveryLineItems as $index => $deliveryLineItem)    
                                    <p style="word-wrap: break-word;">
                                        {{ preg_replace('/\s+/', ' ', trim($deliveryLineItem->delivery_address)) }}
                                    </p>
                                    @if ($index < count($deliveryLineItems) - 1)
                                        <br><br> <!-- Adds space only if it's not the last item -->
                                    @endif
                                @endforeach
                            @else
                                @foreach($deliveryLineItems as $index => $deliveryLineItem)
                                    {{ $requestTypes->request_type }} - {{ $deliveryLineItem->site_name }}<br>
                                    <p style="word-wrap: break-word;">
                                        {{ preg_replace('/\s+/', ' ', trim($deliveryLineItem->delivery_address)) }}
                                    </p>
                                    {{ $deliveryLineItem->mtm }} - {{ $deliveryLineItem->delivery_number }}<br>

                                    @if ($index < count($deliveryLineItems) - 1)
                                        <br><br>
                                    @endif
                                @endforeach
                            @endif
                            <br><br>
                            @foreach($remarks as $index => $remark)
                                {{ $remark }}
                                @if ($index < count($remarks) - 1)
                                    <br><br>
                                @endif
                            @endforeach
                        </td>
                        <td style="height: 150px; font-size:15px">{{ $cashVoucherRequest->approved_amount ?? '' }}</td>
                    </tr>

                    <tr>
                        <td colspan="1" style="height: 10px; padding: 15px; font-size:15px; display: flex; justify-content: space-between;">
                        
                        @if($drivers->employee_code === 'NONE' || $deliveryRequest->name === 'ADM' || $deliveryRequest->name === 'FE' || $deliveryRequest->name === 'ND' || $deliveryRequest->name === 'OPS-INC')
                            <strong style="text-align: left; font-size:12px;"></strong>
                            <strong style="text-align: right; color:red;">TOTAL:</strong>
                        @elseif(strtoupper($drivers->first_name . ' ' . $drivers->last_name) === strtoupper($fleets->account_name))
                            <strong style="text-align: left; font-size:12px;">DRIVER: {{$drivers->first_name}} {{$drivers->last_name}} W/ FLEET CARD</strong>
                            <strong style="text-align: right; color:red;">TOTAL:</strong>
                        @elseif(strtoupper($drivers->first_name . ' ' . $drivers->last_name) !== strtoupper($fleets->account_name))
                            <strong style="text-align: left; font-size:12px;">DRIVER: {{$drivers->first_name}} {{$drivers->last_name}} W/ FLEET CARD OF {{$fleets->account_name}}</strong>
                            <strong style="text-align: right; color:red;">TOTAL:</strong>
                        @endif

                        </td>
                        <td style="height: 10px; text-align: right; padding-right: 10px;">
                            <strong style="font-size:15px; color:red;">{{ $cashVoucherRequest->approved_amount ?? '' }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- New section under the first table without borders -->
            <table class="no-border-table">
                <tr>
                    <td>
                        <div class="label" style="font-size: 10px;">_________________________</div>
                        <div class="label" style="font-size: 10px;">{{$approvers->name}}</div>
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
                        <div class="label" style="font-size: 10px;">{{ $employees->first_name ?? 'N/A' }} {{ $employees->last_name ?? 'N/A' }}</div>
                        <div class="label" style="font-size: 10px;">REQUEST BY:</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Button to trigger print -->
        <div class="no-print">
            <button 
                onclick="printAndUpdateStatusSingle(this);" 
                class="btn"
                data-cvr-id="{{ $cashVoucherRequest->cvr_approvals_id }}" 
                data-voucher-id="{{ $cashVoucherRequest->cash_vouchers_id }}">
                Print
            </button>
        </div>
    </div>

</body>
</html>
<script>
function printAndUpdateStatusSingle(button) {
    const cvrId = button.getAttribute('data-cvr-id');
    const voucherId = button.getAttribute('data-voucher-id');

    fetch('/update-print-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            cvr_ids: [cvrId],
            voucher_ids: [voucherId]
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Status updated:', data);
        window.print();
    })
    .catch(error => {
        console.error('Error updating status:', error);
        window.print();
    });
}
</script>
