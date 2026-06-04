<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LiquidationListExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection $liquidations)
    {
    }

    public function headings(): array
    {
        return [
            'CVR Number',
            'Amount',
            'Company Code',
            'CV Type',
            'Date Created',
            'Status',
        ];
    }

    public function collection(): Collection
    {
        $statusLabels = [
            1 => 'Prepared',
            2 => 'Rejected',
            3 => 'For Collection',
            4 => 'For Approval',
            5 => 'Approved',
        ];

        return $this->liquidations->map(function ($item) use ($statusLabels) {
            $cashVoucher = $item->cashVoucher ?? null;
            $deliveryRequest = $cashVoucher?->deliveryRequest;
            $allocation = $item->allocations[0] ?? null;
            $truck = $allocation?->truck;

            $cvrNumber = $cashVoucher?->cvr_number
                ? preg_replace('/\/\d+$/', '', $cashVoucher->cvr_number)
                : 'N/A';

            $truckName = $truck?->truck_name ?? 'N/A';
            $companyCode = $deliveryRequest?->company?->company_code ?? 'N/A';
            $expenseCode = $deliveryRequest?->expenseType?->expense_code ?? 'N/A';

            $cvrType = $cashVoucher->cvr_type ?? null;
            $allowedTypes = ['delivery', 'pullout', 'others', 'freight'];

            $formattedCvrNumber = $cvrNumber;
            if (in_array($cvrType, $allowedTypes, true)) {
                $formattedCvrNumber = $cvrNumber . '-' . $truckName . '-' . $companyCode . $expenseCode;
            } elseif ($cvrType === 'admin') {
                $formattedCvrNumber = $cvrNumber . '-' . ($cashVoucher->company->company_code ?? 'N/A') . ($cashVoucher->expenseTypes->expense_code ?? 'N/A');
            } elseif ($cvrType === 'rpm') {
                $formattedCvrNumber = $cvrNumber . '-' . ($cashVoucher->trucks?->truck_name ?? 'N/A') . '-' . ($cashVoucher->company->company_code ?? 'N/A') . ($cashVoucher->expenseTypes->expense_code ?? 'N/A');
            }

            $companyCodeColumn = '';
            if (in_array($cvrType, $allowedTypes, true)) {
                $companyCodeColumn = $companyCode;
            } elseif ($cvrType === 'admin' || $cvrType === 'rpm') {
                $companyCodeColumn = $cashVoucher->company->company_code ?? '';
            }

            return [
                $formattedCvrNumber,
                number_format((float) ($item->total_expense ?? 0), 2),
                $companyCodeColumn,
                $cashVoucher->cvr_type ?? '',
                Carbon::parse($item->created_at)->format('Y-m-d'),
                $statusLabels[$item?->status] ?? 'Unknown',
            ];
        });
    }
}
