<?php

namespace App\Exports;

use App\Models\CashVoucher;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RpmCashVoucherExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        // Start the query with CashVouchers and related models
        $query = CashVoucher::with(['cvrApprovals', 'liquidations', 'suppliers', 'cvrTypes'])
            ->where('cvr_type', 'rpm'); // Filter by rpm type

        // Apply filters based on the request
        if (isset($this->filters['start_date']) && isset($this->filters['end_date'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->filters['start_date'])->startOfDay(),
                Carbon::parse($this->filters['end_date'])->endOfDay()
            ]);
        }

        // Apply CVR Type filter if provided
        if (isset($this->filters['cvr_type']) && $this->filters['cvr_type']) {
            $query->where('request_type', $this->filters['cvr_type']);
        }

        // Apply Supplier filter if provided
        if (isset($this->filters['supplier']) && $this->filters['supplier']) {
            $query->whereHas('suppliers', function ($q) {
                $q->where('supplier_id', $this->filters['supplier']);
            });
        }

        // Execute the query and get the results
        $cashVouchers = $query->get();

        // Process the results and map them to a collection of rows for export
        return $cashVouchers->map(function ($voucher) {
            // Calculate liquidation cash and card totals from related liquidations
            $totalCash = 0;
            $totalCard = 0;

            // Sum the fields for 'others', 'gasoline', and 'rfid' within the liquidations relationship
            $voucher->liquidations->each(function ($liquidation) use (&$totalCash, &$totalCard) {
                $othersTotal = $this->sumJsonOrArray($liquidation->others);
                $gasolineTotal = $this->sumJsonOrArray($liquidation->gasoline, 'cash');
                $rfidTotal = $this->sumJsonOrArray($liquidation->rfid, 'cash');
                $gasolineCardTotal = $this->sumJsonOrArray($liquidation->gasoline, 'card');
                $rfidCardTotal = $this->sumJsonOrArray($liquidation->rfid, 'card');

                // Accumulate the totals
                $totalCash += $othersTotal + $gasolineTotal + $rfidTotal;
                $totalCard += $gasolineCardTotal + $rfidCardTotal;
            });

            // Add the liquidation cash and card totals to the voucher object
            $voucher->liquidation_cash = $totalCash;
            $voucher->liquidation_card = $totalCard;

            // Add the status text using the helper function
            $voucher->status_text = $this->getVoucherStatus($voucher);

            return $voucher;
        });
    }

    public function headings(): array
    {
        return [
            'Voucher ID',
            'CVR Type',
            'Supplier',
            'Amount',
            'Approved Amount',
            'Liquidation Cash',
            'Liquidation Card',
            'Status'
        ];
    }

    public function map($voucher): array
    {
        // Return the mapped data for each row in the export
        return [
            $voucher->cvr_number,
            $voucher->cvrTypes->request_type ?? '',
            $voucher->suppliers->supplier_name ?? '',
            number_format(array_sum(json_decode($voucher->amount_details, true) ?? []), 2),
            number_format($voucher->cvrApprovals->sum('amount'), 2),
            number_format($voucher->liquidation_cash, 2),
            number_format($voucher->liquidation_card, 2),
            $voucher->status_text, // Adding the status here
        ];
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

    // Helper function to get the status of a voucher
    private function getVoucherStatus($voucher)
    {
        $status = '';

        // Check the status of the voucher
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

        return $status;
    }
}
