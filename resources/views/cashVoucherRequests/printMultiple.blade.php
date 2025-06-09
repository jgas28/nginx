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
    $cashVoucherRequest = $data['cashVoucherRequest'] ?? null;
    $amountInWords = $data['amountInWords'] ?? 'N/A';
    $deliveryLineItems = $data['deliveryLineItems'] ?? [];
    $employees = $data['employees'] ?? null;
    $approvers = $data['approvers'] ?? null;
    $drivers = $data['drivers'] ?? null;
    $fleets = $data['fleets'] ?? null;
    $requestTypes = $data['requestTypes'] ?? null;
    $deliveryRequest = $data['deliveryRequest'] ?? null;
    $remarks = $data['remarks'] ?? [];
    $cvrApprovals = $data['cvrApprovals'] ?? null;
    $allocations = $data['allocations'] ?? null;
    @endphp
  <div class="container" data-cvr-id="{{ $cashVoucherRequest->id }}" data-voucher-id="{{ $cvrApprovals->id }}">
    <div class="header">
      <div class="date">
        <div style="font-size:12px">Date</div>
        <div style="font-size:12px">{{ \Carbon\Carbon::now()->format('F j, Y') }}</div>
      </div>
      <h1 style="font-size:15px">Cash Voucher Request</h1>
      <div class="series-no">
        <div style="font-size:12px">Series No</div>
        <div style="font-size:12px">{{ preg_replace('/\/\d+$/', '', $cashVoucherRequest->cvr_number ?? 'N/A') }}-{{ $allocations->truck->truck_name ?? '' }}-{{$deliveryRequest->company->company_code}}{{$deliveryRequest->expenseType->expense_code}}</div>
      </div>
    </div>

    <div class="voucher-details">
      <table>
        <thead>
          <tr>
            <th colspan="2" style="text-align: left; font-size: 18px;">PAID TO: {{ $employees->fname ?? 'N/A' }} {{ $employees->lname ?? 'N/A' }}</th>
          </tr>
          <tr>
            <th style="width: 70%;">Particulars</th>
            <th style="width: 30%;">Amount</th>
          </tr>
        </thead>
        <tbody>
          @php
          $baseAmount = $cashVoucherRequest->tax_based_amount ?? 0;
          $vatAmount = $baseAmount * 0.12;
          $taxDeduction = $baseAmount * ($cashVoucherRequest->withholdingTax->percentage ?? 0);
          $finalAmount = $baseAmount + $vatAmount - $taxDeduction;
          @endphp

          {{-- Grouped Delivery Items --}}
          @if (in_array($deliveryRequest->name, ['ADM', 'FE', 'ND', 'OPS-INC']))
          <tr>
            <td style="text-align: center; font-size: 12px; border-bottom: none; height: 150px; vertical-align: top; overflow: auto;">
              @foreach($deliveryLineItems as $item)
              {{ $item->delivery_address }}<br />
              @endforeach
            </td>
            <td style="text-align: right; font-size: 16px; color: red; border-bottom: none; height: 150px; vertical-align: top;">₱ {{ $cashVoucherRequest->amount }}</td>
          </tr>
          @else
          <tr>
            <td style="text-align: center; font-size: 12px; border-bottom: none; height: 150px; vertical-align: top; overflow: auto;">
              @foreach($deliveryLineItems as $item)
              {{ $requestTypes->request_type }} - {{ $item->site_name }}<br />
              {{ $item->delivery_address }}<br />
              {{ $item->mtm }} - {{ $item->delivery_number }}<br /><br />
              @endforeach
            </td>
            <td style="text-align: right; font-size: 16px; color: red; border-bottom: none; height: 150px; vertical-align: top;">₱ {{ $cashVoucherRequest->amount }}</td>
          </tr>
          @endif

          {{-- Driver & Fleet Info and Tax Summary --}}
          @if ($cashVoucherRequest->voucher_type === 'with_tax')
          <tr>
            <td style="text-align: left; vertical-align: top; font-size: 12px; padding: 10px;">
              {{-- Driver & Fleet Info --}}
              @if((isset($drivers) && $drivers && $drivers->employee_code === 'NONE') || 
                in_array($deliveryRequest->name, ['ADM', 'FE', 'ND', 'OPS-INC']))
                DRIVER: N/A
              @elseif(empty($fleets->account_name))
                DRIVER: {{ $drivers->fname ?? 'N/A' }} {{ $drivers->lname ?? '' }}
              @elseif(strtoupper(trim(($drivers->fname ?? '') . ' ' . ($drivers->lname ?? ''))) === strtoupper(trim($fleets->account_name ?? '')))
                DRIVER: {{ $drivers->fname ?? '' }} {{ $drivers->lname ?? '' }} W/ FLEET CARD
              @else
                DRIVER: {{ $drivers->fname ?? '' }} {{ $drivers->lname ?? '' }} W/ FLEET CARD OF {{ $fleets->account_name ?? '' }}
              @endif
              <br />
              {{-- Remarks --}}
                @if (!empty($remarks))
                                <strong>Remarks:</strong><br>
                                @foreach($remarks as $remark)
                                    {{ $remark }}<br>
                                @endforeach
                @endif

              {{-- Transfer Charge / Cash Charge --}}
                            @if(!empty($cvrApprovals->charge) && $cvrApprovals->charge != 0)
                                <strong>Transfer Charge:</strong> ₱ {{ number_format($cvrApprovals->charge, 2) }} <br>
                               <strong>Reference:</strong>
                                @if(isset($cvrApprovals->payment_type) && strtolower($cvrApprovals->payment_type) !== 'cash')
                                    {{ $cvrApprovals->payment_name }} /
                                @endif
                                {{ $cvrApprovals->reference_number }}
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
                  <td style="text-align: left; padding: 4px;">Less: Tax Deduction ({{ $cashVoucherRequest->withholdingTax->percentage ?? 0 }}%)</td>
                  <td style="text-align: right; padding: 4px;">₱ {{ number_format($taxDeduction, 2) }}</td>
                </tr>
                <tr style="font-weight: bold; border-top: 1px solid #ccc;">
                  <td style="text-align: left; padding: 4px;">Final Amount</td>
                  <td style="text-align: right; padding: 4px; color: red;">₱ {{ number_format($finalAmount, 2) }}</td>
                </tr>
              </table>
            </td>
          </tr>
          @elseif ($cashVoucherRequest->voucher_type === 'regular')
                    <tr>
                        <td style="text-align: left; vertical-align: top; font-size: 12px; padding: 10px;">
                            {{-- Driver & Fleet Info --}}
                            @if(
                                $drivers->employee_code === 'NONE' || 
                                in_array($deliveryRequest->name, ['ADM', 'FE', 'ND', 'OPS-INC'])
                            )
                                DRIVER: N/A
                            @elseif(empty($fleets->account_name))
                                DRIVER: {{ $drivers->fname }} {{ $drivers->lname }}
                            @elseif(strtoupper($drivers->fname . ' ' . $drivers->lname) === strtoupper($fleets->account_name))
                                DRIVER: {{ $drivers->fname }} {{ $drivers->lname }} W/ FLEET CARD
                            @else
                                DRIVER: {{ $drivers->fname }} {{ $drivers->lname }} W/ FLEET CARD OF {{ $fleets->account_name }}
                            @endif
                            <br>
                             {{-- Remarks --}}
                            @if (!empty($remarks))
                               <strong>Remarks:</strong><br>
                                @foreach($remarks as $remark)
                                    {{ $remark }}<br>
                                @endforeach
                            @endif

                            {{-- Transfer Charge / Cash Charge --}}
                            @if(isset($cvrApprovals) && floatval($cvrApprovals->charge) > 0)
                            <strong>Transfer Charge:</strong> ₱ {{ number_format($cvrApprovals->charge, 2) }} <br>
                            <strong>Reference:</strong>
                            @if(isset($cvrApprovals->payment_type) && strtolower($cvrApprovals->payment_type) !== 'cash')
                                {{ $cvrApprovals->payment_name }} /
                            @endif
                            {{ $cvrApprovals->reference_number }}
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

      <!-- Signature and Receipt Section -->
        <table class="no-border-table">
            <tr>
                <td>
                    <div style="font-size: 10px;">_________________________</div>
                    <div style="font-size: 10px;">{{$approvers->name}}</div>
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

  <!-- Button to trigger print -->
        <div class="no-print">
          <button onclick="printAllVouchers()" class="btn">Print All</button>
        </div>
</body>
</html>
<script>
document.addEventListener('DOMContentLoaded', function () {
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

    function printAllVouchers() {
        const containers = document.querySelectorAll('.container');
        const cvrIds = [];
        const voucherIds = [];

        containers.forEach(container => {
            const cvrId = container.getAttribute('data-cvr-id');
            const voucherId = container.getAttribute('data-voucher-id');
            if (cvrId) cvrIds.push(cvrId);
            if (voucherId) voucherIds.push(voucherId);
        });

        if (cvrIds.length === 0 || voucherIds.length === 0) {
            alert('No vouchers found to print.');
            return;
        }

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
            console.log('Status updated for all:', data);
            window.print();
        })
        .catch(error => {
            console.error('Error updating status:', error);
            window.print();
        });
    }

    // Attach the function globally (if needed for inline `onclick`)
    window.printAllVouchers = printAllVouchers;
});
</script>
