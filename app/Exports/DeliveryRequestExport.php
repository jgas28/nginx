<?php

namespace App\Exports;

use App\Models\DeliveryRequest;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DeliveryRequestExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        // Store the filters passed from the controller
        $this->filters = $filters;
    }

    public function collection()
    {
        // Prepare the query with relationships (lineItems, area, and deliveryStatus)
        $query = DeliveryRequest::with(['lineItems', 'area', 'deliveryStatus']);

        // Apply date filters if provided
        if (isset($this->filters['date_from']) && isset($this->filters['date_to'])) {
            $dateFrom = Carbon::parse($this->filters['date_from'])->startOfDay();
            $dateTo = Carbon::parse($this->filters['date_to'])->endOfDay();
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }

        // Apply other filters (MTM, Area, Status, Customer) based on the request
        $query->when(isset($this->filters['mtm']) && $this->filters['mtm'], function ($query) {
            return $query->where('mtm', 'like', '%' . $this->filters['mtm'] . '%');
        })
        ->when(isset($this->filters['area']) && $this->filters['area'], function ($query) {
            return $query->whereHas('area', function ($query) {
                $query->where('area_code', 'like', '%' . $this->filters['area'] . '%');
            });
        })
        ->when(isset($this->filters['status']) && $this->filters['status'], function ($query) {
            return $query->whereHas('deliveryStatus', function ($query) {
                $query->where('status_name', 'like', '%' . $this->filters['status'] . '%');
            });
        })
        ->when(isset($this->filters['customer_id']) && $this->filters['customer_id'], function ($query) {
            return $query->where('customer_id', '=', $this->filters['customer_id']);
        });

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

    public function map($deliveryRequest): array
    {
        // Map the delivery request data to the correct columns
        return [
            $deliveryRequest->mtm, // MTM
            Carbon::parse($deliveryRequest->created_at)->format('Y-m-d'), // Booking Date
            Carbon::parse($deliveryRequest->delivery_date)->format('Y-m-d'), // Delivery Date
            $deliveryRequest->delivery_rate, // Delivery Rate (adjust if necessary)
            $deliveryRequest->total_accessorial_rate, // Accessorial Rate (calculated)
            $deliveryRequest->area->area_code, // Area
            $deliveryRequest->deliveryStatus->status_name ?? 'N/A', // Status
        ];
    }
}
