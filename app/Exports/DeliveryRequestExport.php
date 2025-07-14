<?php

namespace App\Exports;

use App\Models\DeliveryRequest;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DeliveryRequestExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = DeliveryRequest::with(['lineItems', 'area', 'deliveryStatus']);

        // Apply filters based on request
        if (isset($this->filters['mtm']) && $this->filters['mtm']) {
            $query->where('mtm', 'like', '%' . $this->filters['mtm'] . '%');
        }

        if (isset($this->filters['date_from']) && isset($this->filters['date_to'])) {
            $dateFrom = Carbon::parse($this->filters['date_from'])->startOfDay();
            $dateTo = Carbon::parse($this->filters['date_to'])->endOfDay();
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }

        if (isset($this->filters['area']) && $this->filters['area']) {
            $query->whereHas('area', function ($query) {
                $query->where('area_code', 'like', '%' . $this->filters['area'] . '%');
            });
        }

        if (isset($this->filters['status']) && $this->filters['status']) {
            $query->whereHas('deliveryStatus', function ($query) {
                $query->where('status_name', 'like', '%' . $this->filters['status'] . '%');
            });
        }

        // Fetch the filtered data
        $deliveryRequests = $query->get();

        // Calculate total accessorial rate for each delivery request
        foreach ($deliveryRequests as $request) {
            $request->total_accessorial_rate = $request->lineItems->sum('accessorial_rate');
        }

        return $deliveryRequests;
    }

    public function headings(): array
    {
        return [
            'MTM', 'Booking Date', 'Delivery Date', 'Delivery Rate', 'Accessorial Rate', 'Area', 'Status'
        ];
    }
}
