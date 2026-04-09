<?php

namespace App\Exports;

use App\Models\CashVoucher;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $search = trim((string) ($this->filters['search'] ?? ''));

        $query = CashVoucher::with([
                'cvrApprovals',
                'liquidations',
                'suppliers',
                'cvrTypes',
                'trucks',
                'company',
                'expenseTypes',
            ])
            ->where('cvr_type', 'rpm');

        if (
            !empty($this->filters['start_date']) &&
            !empty($this->filters['end_date'])
        ) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->filters['start_date'])->startOfDay(),
                Carbon::parse($this->filters['end_date'])->endOfDay()
            ]);
        } elseif (empty($this->filters['start_date']) && empty($this->filters['end_date'])) {
            $query->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear);
        }

        if (isset($this->filters['cvr_type']) && $this->filters['cvr_type']) {
            $query->where('request_type', $this->filters['cvr_type']);
        }

        if (isset($this->filters['supplier']) && $this->filters['supplier']) {
            $query->whereHas('suppliers', function ($q) {
                $q->where('supplier_id', $this->filters['supplier']);
            });
        }

        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('cvr_number', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhereHas('cvrTypes', function ($typeQuery) use ($search) {
                        $typeQuery->where('request_type', 'like', "%{$search}%");
                    })
                    ->orWhereHas('suppliers', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('supplier_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('trucks', function ($truckQuery) use ($search) {
                        $truckQuery->where('truck_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('company', function ($companyQuery) use ($search) {
                        $companyQuery->where('company_name', 'like', "%{$search}%")
                            ->orWhere('company_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('expenseTypes', function ($expenseQuery) use ($search) {
                        $expenseQuery->where('expense_name', 'like', "%{$search}%")
                            ->orWhere('expense_code', 'like', "%{$search}%");
                    });
            });
        }

        $cashVouchers = $query->get();

        if (!empty($this->filters['status'])) {
            $cashVouchers = $cashVouchers->filter(function ($voucher) {
                return $this->getVoucherStatus($voucher) === $this->filters['status'];
            })->values();
        }

        return $cashVouchers->map(function ($voucher) {
            $totalCash = 0;
            $totalCard = 0;

            $voucher->liquidations->each(function ($liquidation) use (&$totalCash, &$totalCard) {
                $othersTotal = $this->sumJsonOrArray($liquidation->others);
                $gasolineTotal = $this->sumJsonOrArray($liquidation->gasoline, 'cash');
                $rfidTotal = $this->sumJsonOrArray($liquidation->rfid, 'cash');
                $gasolineCardTotal = $this->sumJsonOrArray($liquidation->gasoline, 'card');
                $rfidCardTotal = $this->sumJsonOrArray($liquidation->rfid, 'card');

                $totalCash += $othersTotal + $gasolineTotal + $rfidTotal;
                $totalCard += $gasolineCardTotal + $rfidCardTotal;
            });

            $voucher->liquidation_cash = $totalCash;
            $voucher->liquidation_card = $totalCard;
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
        $voucherId = preg_replace('/\/\d+/', '', $voucher->cvr_number) . '-' . 
                 ($voucher->trucks->truck_name ?? '') . '-' . 
                 ($voucher->company->company_code ?? '') . 
                 ($voucher->expenseTypes->expense_code ?? '');
        return [
            $voucherId,
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
