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
        // Fetch the request types from the cvr_request_type table
        $requestTypes = DB::table('fczcnyx.cvr_request_type')->get();

        // Get the current date to filter by the current month
        $currentMonthStart = now()->startOfMonth()->toDateString();
        $currentMonthEnd = now()->endOfMonth()->toDateString();

        // Build the query with the basic joins and where condition
        $query = DB::table('fczcnyx.cash_vouchers AS cv')
            ->join('fczcnyx.cvr_approvals AS ca', 'cv.id', '=', 'ca.cvr_id')
            ->join('fczcnyx.liquidations AS l', 'cv.id', '=', 'l.cvr_id')
            ->leftJoin('fczcnyx.companies AS c', 'cv.company_id', '=', 'c.id')
            ->leftJoin('fczcnyx.expense_types AS et', 'cv.expense_type_id', '=', 'et.id')
            ->leftJoin('fczcnyx.cvr_request_type AS r', 'cv.request_type', '=', 'r.id')
            ->whereBetween('cv.created_at', [$currentMonthStart, $currentMonthEnd]);  // Filter by current month

        // Apply additional filters if they exist in the request
        if ($request->has('cvr_number') && $request->cvr_number) {
            $query->where('cv.cvr_number', 'like', '%' . $request->cvr_number . '%');
        }

        if ($request->has('status') && $request->status) {
            $query->where('cv.status', '=', $request->status);
        }

        if ($request->has('request_type') && $request->request_type) {
            $query->where('cv.request_type', '=', $request->request_type);
        }

        // Apply the date range filter if provided
        if ($request->has('start_date') && $request->start_date && $request->has('end_date') && $request->end_date) {
            // Ensure the start date is at the beginning of the day and the end date is at the end of the day
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();

            // Apply the date filter to the query
            $query->whereBetween('cv.created_at', [$start, $end]);
        }

        // Select the necessary fields
        $results = $query->where('cv.cvr_type', 'admin')
            ->select(
                DB::raw("CONCAT(REGEXP_REPLACE(cv.cvr_number, '/[0-9]+$', ''), '-', c.company_code, '-', et.expense_code) AS cvr_number_1"),
                'c.company_name',
                'et.expense_code',
                'cv.supplier_id',
                'cv.request_type',
                DB::raw('r.request_type AS request_type_name'),
                DB::raw('SUM(
                    COALESCE(l.allowance, 0) + 
                    COALESCE(l.manpower, 0) + 
                    COALESCE(l.hauling, 0) + 
                    COALESCE(l.right_of_way, 0) + 
                    COALESCE(l.roro_expense, 0) + 
                    COALESCE(l.cash_charge, 0) + 
                    COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(l.others, "$[0].amount")) AS DECIMAL(10,2)), 0)
                ) AS total_liquidation_cash'),
                DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN JSON_UNQUOTE(JSON_EXTRACT(l.gasoline, "$[0].type")) = "card" 
                        THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(l.gasoline, "$[0].amount")) AS DECIMAL(10,2))
                        ELSE 0 
                    END
                ), 0) + COALESCE(SUM(
                    CASE 
                        WHEN JSON_UNQUOTE(JSON_EXTRACT(l.rfid, "$[0].type")) = "card" 
                        THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(l.rfid, "$[0].amount")) AS DECIMAL(10,2))
                        ELSE 0 
                    END
                ), 0) + COALESCE(SUM(
                    CASE 
                        WHEN JSON_UNQUOTE(JSON_EXTRACT(l.rfid, "$[1].type")) = "card" 
                        THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(l.rfid, "$[1].amount")) AS DECIMAL(10,2))
                        ELSE 0 
                    END
                ), 0) AS total_liquidation_card'),
                DB::raw('CASE 
                    WHEN cv.status = 3 THEN "Rejected"
                    WHEN MAX(ca.cvr_id) IS NULL AND cv.status != 3 THEN "Pending Approval"
                    WHEN MAX(ca.cvr_id) IS NOT NULL AND MAX(l.cvr_id) IS NULL THEN "Pending Liquidation"
                    WHEN MAX(ca.cvr_id) IS NOT NULL AND MAX(l.cvr_id) IS NOT NULL THEN 
                        CASE
                            WHEN MAX(l.status) = 1 THEN "for Validation"
                            WHEN MAX(l.status) = 3 THEN "For Collection"
                            WHEN MAX(l.status) = 4 THEN "for Approval"
                            WHEN MAX(l.status) = 5 THEN "Completed"
                            WHEN MAX(l.status) = 10 THEN "Rejected Liquidation"
                            ELSE "Unknown"
                        END
                    ELSE "Unknown"
                END AS status_check')
            )
            ->groupBy(
                'cv.cvr_number', 
                'cv.company_id', 
                'cv.expense_type_id', 
                'cv.supplier_id', 
                'cv.request_type',
                'cv.status',
                'c.company_code', 
                'et.expense_code', 
                'r.request_type'
            )
            ->paginate(10);

        // Return the results and the requestTypes to the view
        return view('reports.cashVoucher', compact('results', 'requestTypes'));
    }


}
