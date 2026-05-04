<?php

namespace App\Exports;

use App\Models\CashVoucher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CashVoucherReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        // Get the filtered data, similar to your controller method.
        $query = CashVoucher::with('cvrApprovals', 'liquidations', 'suppliers', 'cvrTypes')
            ->where('cvr_type', 'admin');

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Apply filters
        if (!$this->request->filled('start_date') && !$this->request->filled('end_date')) {
            $query->whereMonth('created_at', $currentMonth)
                  ->whereYear('created_at', $currentYear);
        }

        if ($this->request->filled('start_date')) {
            $startDate = \Carbon\Carbon::parse($this->request->start_date);
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($this->request->filled('end_date')) {
            $endDate = \Carbon\Carbon::parse($this->request->end_date);
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($this->request->filled('cvr_type')) {
            $query->where('request_type', $this->request->cvr_type);
        }

        if ($this->request->filled('supplier')) {
            $query->whereHas('suppliers', function ($q) {
                $q->where('supplier_id', $this->request->supplier);
            });
        }

        $adminCV = $query->get();

        // Filter by status AFTER fetching the results
        if ($this->request->filled('status')) {
            $adminCV = $adminCV->filter(function ($voucher) {
                $status = $this->getVoucherStatus($voucher);
                return $status === $this->request->status;
            });
        }

        return $adminCV;
    }

    public function headings(): array
    {
        return [
            'Voucher ID',
            'Voucher Date',
            'Supplier Name',
            'CVR Type',
            'Amount',
            'Approved Amount',
            'Liquidation Cash',
            'Liquidation Card',
            'Status',
        ];
    }

    public function map($voucher): array
    {
        // Calculate liquidation totals (cash and card)
        $totalCash = 0;
        $totalCard = 0;

        // Loop through liquidations to sum cash and card totals
        foreach ($voucher->liquidations as $liquidation) {
            $othersTotal = $this->sumJsonOrArray($liquidation->others);
            $gasolineTotal = $this->sumJsonOrArray($liquidation->gasoline, 'cash');
            $rfidTotal = $this->sumJsonOrArray($liquidation->rfid, 'cash');
            $gasolineCardTotal = $this->sumJsonOrArray($liquidation->gasoline, 'card');
            $rfidCardTotal = $this->sumJsonOrArray($liquidation->rfid, 'card');

            // Accumulate the totals
            $totalCash += $othersTotal + $gasolineTotal + $rfidTotal;
            $totalCard += $gasolineCardTotal + $rfidCardTotal;
        }

        // Map data for each voucher to be exported
        $status = $this->getVoucherStatus($voucher);

        return [
            preg_replace('/\/\d+/', '', $voucher->cvr_number) . '-' . $voucher->company->company_code . $voucher->expenseTypes->expense_code,
            $voucher->created_at->toDateString(),
            optional($voucher->suppliers)->supplier_name,
            $voucher->cvrTypes->request_type ?? '',
            number_format(array_sum(json_decode($voucher->amount_details, true) ?? []), 2),
            number_format($voucher->cvrApprovals->sum('amount'), 2),
            number_format($totalCash, 2),
            number_format($totalCard, 2),
            $status,
        ];
    }

    private function getVoucherStatus($voucher)
    {
        $status = '';

        // Status logic
        if ($voucher->status == 3) {
            $status = 'Rejected';
        } elseif ($voucher->cvrApprovals->isEmpty()) {
            $status = 'For Approval';
        } elseif ($voucher->cvrApprovals->isNotEmpty() && $voucher->liquidations->isEmpty()) {
            $status = 'For Liquidation';
        } elseif ($voucher->cvrApprovals->isNotEmpty() && $voucher->liquidations->isNotEmpty()) {
            $liquidation = $voucher->liquidations->first();

            if ($liquidation->status == 1) {
                $status = 'For Validation';
            } elseif ($liquidation->status == 3) {
                $status = 'For Collection';
            } elseif ($liquidation->status == 4) {
                $status = 'For Approved';
            } elseif ($liquidation->status == 5) {
                $status = 'Approved';
            } elseif ($liquidation->status == 10) {
                $status = 'Liquidation Reject';
            } else {
                $status = 'Liquidation Status Unknown';
            }
        }

        // Default to Unknown status if no condition is met
        if ($status === '') {
            $status = 'Unknown';
        }

        return $status;
    }

    // Helper function to handle JSON decoding or summing array values
    private function sumJsonOrArray($field, $type = null)
    {
        $total = 0;

        if (is_string($field)) {
            // If it's a string, decode the JSON
            $decoded = json_decode($field, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // If the decoding is successful and it's an array
                $field = $decoded;
            }
        }

        // If it's an array, sum the values
        if (is_array($field)) {
            foreach ($field as $item) {
                // If type is specified (e.g. 'cash' or 'card'), filter by that type
                if ($type && isset($item['type']) && $item['type'] === $type) {
                    $total += is_numeric($item['amount']) ? (float) $item['amount'] : 0;
                } elseif (!$type) {
                    $total += is_numeric($item['amount']) ? (float) $item['amount'] : 0;
                }
            }
        }

        return $total;
    }
}
