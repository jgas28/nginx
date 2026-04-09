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
        $query = DeliveryRequest::with(['lineItems', 'area', 'deliveryStatus', 'company', 'customer']);

        if (isset($this->filters['date_from']) && isset($this->filters['date_to']) && $this->filters['date_from'] && $this->filters['date_to']) {
            $dateFrom = Carbon::parse($this->filters['date_from'])->startOfDay();
            $dateTo = Carbon::parse($this->filters['date_to'])->endOfDay();
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }

        $query->when(isset($this->filters['mtm']) && $this->filters['mtm'], function ($query) {
            return $query->where('mtm', 'like', '%' . $this->filters['mtm'] . '%');
        })
        ->when(isset($this->filters['delivery_date']) && $this->filters['delivery_date'], function ($query) {
            return $query->whereDate('delivery_date', $this->filters['delivery_date']);
        })
        ->when(isset($this->filters['area']) && $this->filters['area'], function ($query) {
            if (is_numeric($this->filters['area'])) {
                return $query->where('area_id', $this->filters['area']);
            }

            return $query->whereHas('area', function ($areaQuery) {
                $areaQuery->where('area_code', 'like', '%' . $this->filters['area'] . '%');
            });
        })
        ->when(isset($this->filters['status']) && $this->filters['status'], function ($query) {
            if (is_numeric($this->filters['status'])) {
                return $query->where('delivery_status', $this->filters['status']);
            }

            return $query->whereHas('deliveryStatus', function ($statusQuery) {
                $statusQuery->where('status_name', 'like', '%' . $this->filters['status'] . '%');
            });
        })
        ->when(isset($this->filters['customer_id']) && $this->filters['customer_id'], function ($query) {
            return $query->where('customer_id', '=', $this->filters['customer_id']);
        })
        ->when(isset($this->filters['company_id']) && $this->filters['company_id'], function ($query) {
            return $query->where('company_id', '=', $this->filters['company_id']);
        })
        ->when(isset($this->filters['search']) && $this->filters['search'], function ($query) {
            $search = $this->filters['search'];

            return $query->where(function ($inner) use ($search) {
                $inner->where('mtm', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('delivery_type', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($companyQuery) use ($search) {
                        $companyQuery->where('company_name', 'like', "%{$search}%")
                            ->orWhere('company_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('area', function ($areaQuery) use ($search) {
                        $areaQuery->where('area_name', 'like', "%{$search}%")
                            ->orWhere('area_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('deliveryStatus', function ($statusQuery) use ($search) {
                        $statusQuery->where('status_name', 'like', "%{$search}%");
                    });
            });
        });

        // Fetch the filtered data
        $deliveryRequests = $query->orderByDesc('created_at')->get();

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
