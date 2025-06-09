<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Cash Voucher Request</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 1;
      padding: 0;
      background-color: #f7f7f7;
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
      page-break-inside: avoid; /* avoid breaking inside container */
      /* Removed page-break-after */
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

    .voucher-details table,
    .voucher-details th,
    .voucher-details td {
      border: 1px solid #ccc;
    }

    .voucher-details th,
    .voucher-details td {
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

    .no-border-table .label {
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
      background-color: #4caf50;
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
        margin: 0.5in 0.5in; /* tighter margins for fitting two per page */
      }

      body {
        margin: 1;
        padding: 0;
        font-size: 12px;
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

      .header h1 {
        font-size: 24px;
        text-decoration: underline;
      }

      .voucher-details p,
      .voucher-details .label,
      .voucher-details .field .label,
      .voucher-details .field .value {
        font-size: 14px;
      }

      /* Hide print buttons */
      .no-print {
        display: none;
      }
    }
  </style>
</head>
<body>
@foreach ($allData as $data)
    @php
        $voucher = $data['cashVoucherRequest'] ?? null;
        $approver = $data['approvers'] ?? null;
        $amountInWords = $data['amountInWords'] ?? '';
        $cv = $voucher->cashVoucher ?? null;

        $descriptions = json_decode($cv->description ?? '[]', true);
        $amounts = json_decode($cv->amount_details ?? '[]', true);
        $remarks = json_decode($cv->remarks ?? '[]', true);

        $totalAmount = collect($amounts)->filter(fn($amt) => is_numeric($amt))->sum();
        $taxBase = $cv->tax_based_amount ?? 0;
        $vat = $taxBase * 0.12;
        $withholdingPercentage = $cv->withholdingTax->percentage ?? 0;
        $withholding = $taxBase * $withholdingPercentage;
        $final = $taxBase + $vat - $withholding;

        $voucherType = $cv->voucher_type ?? 'regular';
    @endphp


   <div class="container" 
         data-cvr-id="{{ $voucher->id }}" 
         data-voucher-id="{{ $cv->id }}">
    <div class="header">
        <div class="date">
            <div style="font-size:12px">Date</div>
            <div style="font-size:12px">{{ \Carbon\Carbon::now()->format('F j, Y') }}</div>
        </div>
        <h1 style="font-size:22px">Cash Voucher Request</h1>
        <div class="series-no">
            <div style="font-size:12px">Series No</div>
            <div style="font-size:12px">
                @if(strtolower($cv->cvr_type ?? '') === 'admin')
                    {{ preg_replace('/\/\d+$/', '', $voucher->cvr_number ?? 'N/A') }}-{{ $cv->company->company_code ?? 'N/A' }}{{ $cv->expenseTypes->expense_code ?? 'N/A' }}
                @elseif(strtolower($cv->cvr_type ?? '') === 'rpm')
                    {{ preg_replace('/\/\d+$/', '', $voucher->cvr_number ?? 'N/A') }}-{{ $cv->trucks->truck_name ?? 'N/A' }}-{{ $cv->company->company_code ?? 'N/A' }}{{ $cv->expenseTypes->expense_code ?? 'N/A' }}
                @else
                   N/A
                @endif
            </div>
        </div>
    </div>

    <div class="voucher-details">
        <table>
            <thead>
            <tr>
                <th colspan="2" style="text-align: left; font-size: 18px;">PAID TO: {{ $cv->suppliers->supplier_name ?? 'N/A' }}</th>
            </tr>
            <tr>
                <th style="width: 70%;">Particulars</th>
                <th style="width: 30%;">Amount</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                 <td style="text-align: center; font-size: 12px; border-bottom: none; height: 150px; vertical-align: top; overflow: auto;">
                    @foreach ($descriptions as $desc)
                      {{ $desc }}<br>
                    @endforeach
                </td>
                <td style="text-align: right; font-size: 16px; color: red; border-bottom: none; height: 150px; vertical-align: top;">
                    @foreach ($amounts as $amt)
                      ₱ {{ number_format($amt, 2) }}<br>
                    @endforeach
                </td>
            </tr>

            <tr>
                <td style="text-align: left; vertical-align: top; font-size: 12px; padding: 10px;">
                    @if (!empty($remarks))
                        <strong>Remarks:</strong><br>
                        @foreach ($remarks as $remark)
                            {{ $remark }}<br>
                        @endforeach
                    @endif

                    @if(!empty($voucher->charge) && $voucher->charge != 0)
                        <br><strong>Transfer Charge:</strong> ₱ {{ number_format($voucher->charge, 2) }}
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
                                ₱ {{ number_format(($voucherType === 'with_tax') ? $taxBase : $totalAmount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding: 4px;">VAT (12%)</td>
                            <td style="text-align: right; padding: 4px;">
                                @if($voucherType === 'with_tax')
                                    ₱ {{ number_format($vat, 2) }}
                                @else
                                    ₱ 0.00
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding: 4px;">{{ $cv->withholdingTax->description ?? 'Less Withholding Tax' }}</td>
                            <td style="text-align: right; padding: 4px;">
                                @if($voucherType === 'with_tax')
                                    ₱ {{ number_format($withholding, 2) }}
                                @else
                                    ₱ 0.00
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: bold; color: red; padding: 4px;">Total</td>
                            <td style="text-align: right; font-weight: bold; color: red; padding: 4px;">
                                ₱ {{ number_format(($voucherType === 'with_tax') ? $final : $totalAmount, 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>

        <table class="no-border-table">
            <tr>
                <td>
                    <div style="font-size: 10px;">_________________________</div>
                    <div style="font-size: 10px;">{{ $approver->name ?? 'N/A' }}</div>
                    <div style="font-size: 10px;">Approver</div>
                </td>
                <td style="text-align:left;">
                    <div style="font-size: 10px;">RECEIVED from <strong>{{ $cv->company->company_name ?? 'N/A' }}</strong>, the amount of</div>
                    <div style="font-size: 10px; text-transform: uppercase;"><strong><u>{{ $amountInWords }}</u></strong></div>
                    <div style="font-size: 10px;">in full payment of amount described above</div>
                </td>
                <td>
                    <div style="font-size: 10px;">_________________________</div>
                    <div style="font-size: 10px;">{{ $cv->suppliers->supplier_name ?? 'N/A' }}</div>
                    <div style="font-size: 10px;">Request By</div>
                </td>
            </tr>
        </table>
    </div>
  </div>
@endforeach

<div class="no-print">
    <button onclick="printAndUpdateAll()" class="btn">Print All</button>
</div>
</body>
</html>
<script>
    function printAndUpdateAll() {
        const containers = document.querySelectorAll('.container');
        const cvrIds = [];
        const voucherIds = [];

        containers.forEach(container => {
            const cvrId = container.getAttribute('data-cvr-id');
            const voucherId = container.getAttribute('data-voucher-id');

            if (cvrId && voucherId) {
                cvrIds.push(cvrId);
                voucherIds.push(voucherId);
            }
        });

        fetch('/update-print-admin-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                cvr_ids: cvrIds,
                voucher_ids: voucherIds
            })
        })
        .then(res => res.json())
        .then(data => {
            console.log('Statuses updated:', data);
            window.print();
        })
        .catch(err => {
            console.error('Error updating print statuses:', err);
            window.print();
        });
    }
</script>