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
                <div style="font-size:12px">

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
   
                        </th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ccc;">Particulars</th>
                        <th style="border: 1px solid #ccc;">Amount</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                         <td style="text-align: center; font-size: 12px; border-bottom: none; height: 150px; vertical-align: top; overflow: auto;">

                        </td>
                        <td style="text-align: center; font-size: 12px; border-bottom: none; height: 150px; vertical-align: top; overflow: auto;">
    
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left; vertical-align: top; font-size: 12px; padding: 10px;">

                        </td>
                        <td style="border: 1px solid #ccc; padding: 0;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                                <colgroup>
                                    <col style="width: 60%;">
                                    <col style="width: 40%;">
                                </colgroup>
                                <tr>

                                </tr>
                                <tr>

                                </tr>
                                <tr>
 
                                </tr>
                                <tr>

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

                    </td>
                    <td>

                    </td>
                    <td>
                       
                    </td>
                </tr>
            </table>
        </div>
</body>
</html>
