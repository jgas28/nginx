<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\DeliveryRequest;
use App\Models\Area;
use App\Models\DeliveryStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\DeliveryRequestExport;
use App\Models\CashVoucher;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function deliveryRequestReport(Request $request)
    {
        // Get the current date and start of the month
        $currentDate = Carbon::now();
        $startOfMonth = $currentDate->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $currentDate->copy()->endOfMonth()->format('Y-m-d');

        // Fetch area and status options for dropdowns
        $areas = Area::all(); // Assuming Area is the model for the Area data
        $statuses = DeliveryStatus::all(); // Assuming DeliveryStatus is the model for the Status data

        // Prepare the query builder
        $query = DeliveryRequest::with(['lineItems' => function ($query) {
            $query->where('status', '!=', 0); // Line items filter
        }]);

        // Apply filters based on the user's inputs
        if ($request->date_from && $request->date_to) {
            $dateFrom = Carbon::parse($request->date_from)->startOfDay()->format('Y-m-d H:i:s');
            $dateTo = Carbon::parse($request->date_to)->endOfDay()->format('Y-m-d H:i:s');
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        } else {
            $query->whereBetween('created_at', [
                Carbon::parse($startOfMonth)->startOfDay(),
                Carbon::parse($endOfMonth)->endOfDay()
            ]);
        }

        $query->when($request->mtm, function ($query) use ($request) {
            return $query->where('mtm', 'like', '%' . $request->mtm . '%');
        })
        ->when($request->delivery_date, function ($query) use ($request) {
            return $query->where('delivery_date', '=', $request->delivery_date);
        })
        ->when($request->area, function ($query) use ($request) {
            return $query->whereHas('area', function ($query) use ($request) {
                $query->where('area_code', 'like', '%' . $request->area . '%');
            });
        })
        ->when($request->status, function ($query) use ($request) {
            return $query->whereHas('deliveryStatus', function ($query) use ($request) {
                $query->where('status_name', 'like', '%' . $request->status . '%');
            });
        });

        // Get the data
        $deliveryRequests = $query->get();

        // Calculate the total accessorial rate for each delivery request
        foreach ($deliveryRequests as $request) {
            $request->total_accessorial_rate = $request->lineItems->sum('accessorial_rate');
        }

        // Return the view with the filtered delivery requests data and dropdown data
        return view('reports.deliveryRequest', compact('deliveryRequests', 'areas', 'statuses'));
    }

    // Method to export to Excel
    public function export(Request $request)
    {
        // Pass the filters to the export class
        $filters = $request->only(['mtm', 'date_from', 'date_to', 'area', 'status']);
        
        return Excel::download(new DeliveryRequestExport($filters), 'delivery_requests.xlsx');
    }

    public function cashVoucherReport(Request $request)
    {
        // Get the current month and year
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Retrieve CashVouchers with their related cvrApprovals and liquidations, filtered by current month and year
        $adminCV = CashVoucher::with('cvrApprovals', 'liquidations')
            ->where('cvr_type', 'admin')
            ->whereMonth('created_at', $currentMonth) // Filter by current month
            ->whereYear('created_at', $currentYear)   // Filter by current year
            ->get();

        // Map through the vouchers and assign a single combined status text
        $voucherStatuses = $adminCV->map(function ($voucher) {
            // Default status
            $status = '';

            // Check if the CashVoucher status is 3 (Rejected)
            if ($voucher->status == 3) {
                $status = 'Rejected';
            }
            // If no cvrApprovals, it's for approval
            elseif ($voucher->cvrApprovals->isEmpty()) {
                $status = 'For Approval';
            }
            // If there's cvrApprovals but no liquidation, it's for liquidation
            elseif ($voucher->cvrApprovals->isNotEmpty() && $voucher->liquidations->isEmpty()) {
                $status = 'For Liquidation';
            }
            // If both cvrApprovals and liquidations exist, check the liquidation status
            elseif ($voucher->cvrApprovals->isNotEmpty() && $voucher->liquidations->isNotEmpty()) {
                // Assuming the first liquidation record is the correct one
                $liquidation = $voucher->liquidations->first();

                if ($liquidation->status == 1) {
                    $status = 'For Validation';
                } elseif ($liquidation->status == 3) {
                    $status = 'For Collection';
                } elseif ($liquidation->status == 4) {
                    $status = 'Approved';
                } else {
                    $status = 'Liquidation Status Unknown';
                }
            }

            // Assign the status text to the voucher object
            $voucher->status_text = $status;

            return $voucher;
        });

        // Pass the voucher statuses to the view
        return view('reports.cashVoucher', compact('voucherStatuses'));
    }

}
