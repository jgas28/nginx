<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cash Voucher Requests</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }

        .container {
            width: 8.5in;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            page-break-inside: avoid;
            break-inside: avoid;
            outline: 1px dashed red; /* REMOVE this after layout is confirmed */
        }

        .header {
            position: relative;
            width: 100%;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            text-decoration: underline;
            text-align: center;
            margin: 0;
        }

        .header .date,
        .header .series-no {
            position: absolute;
            top: 0;
            font-size: 12px;
            font-weight: bold;
        }

        .header .date { left: 0; }
        .header .series-no { right: 0; }

        .voucher-details {
            margin-top: 10px;
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

        .no-print {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            text-align: center;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #45a049;
        }

        /* Print styling */
        @media print {
            @page {
                size: letter portrait;
                margin: 0.25in;
            }

            body {
                margin: 0;
                padding: 0;
                font-size: 11px;
            }

            .container {
                height: 5.25in; /* Half of 10.5in (11in - 0.5in margin) */
                page-break-inside: avoid;
                break-inside: avoid;
                border-bottom: 1px dashed #000; /* Cutting guide */
                margin-bottom: 0.25in;
            }

            .voucher-details td,
            .voucher-details th {
                padding: 4px;
                font-size: 11px;
            }

            .header h1 {
                font-size: 18px;
                text-decoration: underline;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
@foreach($allData as $data)
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="date">
                <div class="label" style="font-size:12px">Date</div>
                <div class="value" style="font-size:12px">{{ \Carbon\Carbon::now()->format('F j, Y') }}</div>
            </div>
            <h1 style="font-size:22px">Cash Voucher Request</h1>
            <div class="series-no">
                <div class="label" style="font-size:12px">Series No</div>
                <div class="value" style="font-size:12px">{{ $data['cashVoucherRequest']->cvr_number ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Voucher Table -->
        <div class="voucher-details">
            <table class="border-table">
                <thead>
                    <tr>
                        <th colspan="2">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="text-align: left; flex: 1; font-size:15px">PAID TO:</span>
                                <span style="text-align: left; flex: 1; padding-right: 40px; font-size:15px">
                                {{ $data['employees']->first_name ?? 'N/A' }} {{ $data['employees']->last_name ?? 'N/A' }}
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
                            @if (in_array($data['deliveryRequest']->name, ['ADM', 'FE', 'ND', 'OPS-INC']))
                                @foreach($data['deliveryLineItems'] as $index => $deliveryLineItem)
                                <p style="word-wrap: break-word;">
                                    {{ preg_replace('/\s+/', ' ', trim($deliveryLineItem->delivery_address)) }}
                                </p>
                                    @if ($index < count($data['deliveryLineItems']) - 1)<br>@endif
                                @endforeach
                            @else
                                @foreach($data['deliveryLineItems'] as $index => $deliveryLineItem)
                                    {{ $data['requestTypes']->request_type }} - {{ $deliveryLineItem->site_name }}<br>
                                    <p style="word-wrap: break-word;">
                                        {{ preg_replace('/\s+/', ' ', trim($deliveryLineItem->delivery_address)) }}
                                    </p>
                                    {{ $deliveryLineItem->mtm }} - {{ $deliveryLineItem->delivery_number }}<br>
                                    @if ($index < count($data['deliveryLineItems']) - 1)<br>@endif
                                @endforeach
                            @endif
                            <br>
                            @foreach($data['remarks'] as $index => $remark)
                                {{ $remark }}
                                @if ($index < count($data['remarks']) - 1)<br>@endif
                            @endforeach
                        </td>
                        <td style="height: 150px; font-size:15px">{{ $data['cashVoucherRequest']->approved_amount ?? '' }}</td>
                    </tr>
                    <tr>
                    <td colspan="1" style="height: 10px; padding: 15px; font-size:15px; display: flex; justify-content: space-between;">
                            @php
                                $driverName = strtoupper($data['drivers']->first_name . ' ' . $data['drivers']->last_name);
                                $fleetName = strtoupper($data['fleets']->account_name);
                            @endphp
                            @if($data['drivers']->employee_code === 'NONE' || in_array($data['deliveryRequest']->name, ['ADM', 'FE', 'ND', 'OPS-INC']))
                                &nbsp;
                            @elseif($driverName === $fleetName)
                                DRIVER: {{ $data['drivers']->first_name }} {{ $data['drivers']->last_name }} W/ FLEET CARD
                            @else
                                DRIVER: {{ $data['drivers']->first_name }} {{ $data['drivers']->last_name }} W/ FLEET CARD OF {{ $data['fleets']->account_name }}
                            @endif
                            <strong style="text-align: right; color:red;">TOTAL:</strong>
                        </td>
                        <td style="height: 10px; text-align: right; padding-right: 10px;">
                            <strong style="font-size:15px; color:red;">{{ $cashVoucherRequest->approved_amount ?? '' }}</strong>
                        </td>
                        <td style="height: 10px; text-align: right; padding-right: 10px;">
                            <strong style="font-size:15px; color:red;">{{ $data['cashVoucherRequest']->approved_amount ?? '' }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Signature Section -->
            <table class="no-border-table" style="width: 100%; margin-top: 10px;">
                <tr>
                    <td style="font-size: 10px;">
                        _________________________<br>
                        {{ $data['approvers']->name ?? 'N/A' }}<br>
                        Approver
                    </td>
                    <td style="font-size: 10px;">
                        RECEIVED from the amount of<br>
                        <strong><u>{{ $data['amountInWords'] ?? 'N/A' }}</u></strong><br>
                        in full payment of amount described above
                    </td>
                    <td style="font-size: 10px;">
                        _________________________<br>
                        {{ $data['employees']->first_name ?? 'N/A' }} {{ $data['employees']->last_name ?? 'N/A' }}<br>
                        REQUEST BY:
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endforeach

<div class="no-print">
    @php
        $cvrApprovalIds = collect($allData)->pluck('cashVoucherRequest')->pluck('cvr_approvals_id')->filter()->values();
        $cashVoucherIds = collect($allData)->pluck('cashVoucherRequest')->pluck('cash_vouchers_id')->filter()->values();
    @endphp

    <button 
        onclick="printAndUpdateStatus();" 
        class="btn"
        id="printButton"
        data-cvr-ids='@json($cvrApprovalIds)'
        data-voucher-ids='@json($cashVoucherIds)'>
        Print All
    </button>
</div>
</body>
</html>

<script>
function printAndUpdateStatus() {
    const btn = document.getElementById('printButton');
    
    try {
        const cvrIds = JSON.parse(btn.dataset.cvrIds);
        const voucherIds = JSON.parse(btn.dataset.voucherIds);

        // Send POST request to Laravel
        fetch('/update-print-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                cvr_ids: cvrIds,
                voucher_ids: voucherIds
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Status updated:', data);
            window.print();
        })
        .catch(error => {
            console.error('Error updating status:', error);
            window.print(); // still print even if update fails
        });

    } catch (error) {
        console.error("Invalid JSON in data attributes:", error);
        window.print();
    }
}

</script>

