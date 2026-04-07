<?php

namespace App\Exports;

use App\Models\Soa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SoaListExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private array $filters = [])
    {
    }

    public function collection()
    {
        $query = Soa::with(['company', 'customer', 'creator']);

        if (!empty($this->filters['company'])) {
            $company = $this->filters['company'];
            $query->whereHas('company', function ($q) use ($company) {
                $q->where('company_name', 'like', '%' . $company . '%');
            });
        }

        if (!empty($this->filters['date_from'])) {
            $query->where('billing_period_from', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->where('billing_period_to', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'SOA Number',
            'Statement Date',
            'Company',
            'Customer',
            'Billing Period From',
            'Billing Period To',
            'Total Amount',
            'Paid Amount',
            'Outstanding Amount',
            'Status',
            'Created By',
            'Created At',
        ];
    }

    public function map($soa): array
    {
        return [
            $soa->soa_number,
            optional($soa->statement_date)->format('Y-m-d'),
            $soa->company->company_name ?? 'N/A',
            $soa->customer->name ?? 'N/A',
            optional($soa->billing_period_from)->format('Y-m-d'),
            optional($soa->billing_period_to)->format('Y-m-d'),
            (float) $soa->total_amount,
            (float) $soa->paid_amount,
            (float) $soa->outstanding_amount,
            ucfirst($soa->status ?? 'draft'),
            $soa->creator->name ?? 'N/A',
            optional($soa->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
