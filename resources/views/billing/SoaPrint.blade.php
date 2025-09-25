<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Statement of Account</title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 30px;
            background-color: white;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        header h1 {
            margin: 0;
            font-size: 2.5em;
        }

        .company-info {
            font-size: 0.9em;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .company-info p {
            margin: 5px 0;
        }

        .soa-header {
            text-align: center;
            margin: 30px 0;
        }

        .soa-header .soa-title {
            font-size: 1.8em;
            font-weight: bold;
        }

        .soa-header .soa-number {
            font-size: 1.2em;
            margin-top: 10px;
        }

        .soa-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .soa-details th, .soa-details td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .soa-details th {
            background-color: #f1f1f1;
            font-weight: bold;
        }

        .soa-details td {
            font-size: 0.9em;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 0.9em;
        }

        @media print {
            body {
                font-size: 12px;
                background-color: white;
            }

            .container {
                width: 100%;
                margin: 0;
                padding: 20px;
            }

            header h1 {
                font-size: 2em;
            }

            .company-info, .soa-header, .footer {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Company Information -->
        <header>
            <h1>FCBOIS TRUCKING SERVICES</h1>
            <div class="company-info">
                <p><strong>VAT REG. TIN #:</strong> 246-451-785-000</p>
                <p><strong>PROPRIETOR:</strong> MENINA ROMMEL L. PROP</p>
                <p><strong>ADDRESS:</strong> 405 BIANCA D BLGD. AMAIA STEP NUVALI BRGY, CANLUBANG CALAMBA CITY, LAGUNA</p>
            </div>
        </header>

        <!-- SOA Header Section -->
        <div class="soa-header">
            <div class="soa-title">STATEMENT OF ACCOUNT</div>
            <div class="soa-number">SOA Number: {{ $soa->soa_number }} | Date: {{ date('F j, Y') }}</div>
        </div>

        <!-- SOA Details Table -->
        <table class="soa-details">
            <thead>
                <tr>
                    <th>PROJECT</th>
                    <th>SITE ID</th>
                    <th>SITE / WHSE. ORIGIN</th>
                    <th>WSHE / STAGING DSTN</th>
                    <th>REGION</th>
                    <th>DN ID</th>
                    <th>RECEIVED DATE</th>
                    <th>Plate Number</th>
                    <th>TRUCK</th>
                    <th>TRUCK RATE</th>
                    <th>ADD ON</th>
                    <th>TOTAL TRUCK RATE</th>
                </tr>
            </thead>
            <tbody>
                {{$soa}}
                @foreach($soa->deliveryRequests as $deliveryRequest)
                    <tr>
                        <td>{{ $deliveryRequest->project_name }}</td>
                        <td>{{ $deliveryRequest->site_id }}</td>
                        <td>{{ $deliveryRequest->site_origin }}</td>
                        <td>{{ $deliveryRequest->wshe_staging_dstn }}</td>
                        <td>{{ $deliveryRequest->region }}</td>
                        <td>{{ $deliveryRequest->dn_id }}</td>
                        <td>{{ $deliveryRequest->received_date }}</td>
                        <td>{{ $deliveryRequest->plate_number }}</td>
                        <td>{{ $deliveryRequest->truck }}</td>
                        <td>{{ number_format($deliveryRequest->truck_rate, 2) }}</td>
                        <td>{{ number_format($deliveryRequest->add_on, 2) }}</td>
                        <td>{{ number_format($deliveryRequest->total_truck_rate, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Prepared By Section -->
        <div class="footer">
            <p><strong>Prepared By:</strong> {{ $soa->prepared_by }}</p>
        </div>
    </div>
</body>
</html>
