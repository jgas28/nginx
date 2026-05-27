<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BillingHuaweiExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Project',
            'SiteID / Site Name',
            'Origin / Warehouse',
            'Destination / Delivery Address',
            'Region',
            'Delivery Number',
            'ID / MTM Number',
            'ReceivedDate / Updated_At',
            'PlateNo',
            'TruckType',
            'TruckRate / Delivery_Rate',
            'MultiDrop',
            'AddOns',
            'TotalTruckRate',
        ];
    }
}
