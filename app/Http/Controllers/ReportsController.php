<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\DeliveryRequest;
use App\Models\Area;
use App\Models\Supplier;
use App\Models\DeliveryStatus;
use App\Models\cvr_request_type;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\DeliveryRequestExport;
use App\Models\CashVoucher;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CashVoucherReportExport;
use App\Models\Customer;

class ReportsController extends Controller
{
    public function deliveryRequestReport(Request $request)
    {
        // Get the current date and start of the month
        $currentDate = Carbon::now();
        $startOfMonth = $currentDate->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $currentDate->copy()->endOfMonth()->format('Y-m-d');

        // Fetch area, status, and customer options for dropdowns
        $areas = Area::all();
        $statuses = DeliveryStatus::all();
        $customers = Customer::all();

        // Prepare the query builder
        $query = DeliveryRequest::with(['lineItems' => function ($query) {
            $query->where('status', '!=', 0); // Line items filter
        }]);

        // Apply date filters if they exist, otherwise default to current month range
        if ($request->date_from && $request->date_to) {
            $dateFrom = Carbon::parse($request->date_from)->startOfDay()->format('Y-m-d H:i:s');
            $dateTo = Carbon::parse($request->date_to)->endOfDay()->format('Y-m-d H:i:s');
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        } else {
            // Default to the current month's date range
            $query->whereBetween('created_at', [
                Carbon::parse($startOfMonth)->startOfDay(),
                Carbon::parse($endOfMonth)->endOfDay()
            ]);
        }

        // Apply additional filters (MTM, Delivery Date, Area, Status, Customer) independently
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
        })
        ->when($request->customer_id, function ($query) use ($request) {
            return $query->where('customer_id', '=', $request->customer_id);
        });

        // Get the filtered data
        $deliveryRequests = $query->get();

        // Calculate the total accessorial rate for each delivery request
        foreach ($deliveryRequests as $request) {
            $request->total_accessorial_rate = $request->lineItems->sum('accessorial_rate');
        }

        // Return the view with the filtered delivery requests data and dropdown data
        return view('reports.deliveryRequest', compact('deliveryRequests', 'areas', 'statuses', 'customers'));
    }

    public function export(Request $request)
    {
        // Get the current date and start of the month
        $currentDate = Carbon::now();
        $startOfMonth = $currentDate->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $currentDate->copy()->endOfMonth()->format('Y-m-d');

        // Default to the current month if no date filters are provided
        if (!$request->date_from && !$request->date_to) {
            $request->merge([
                'date_from' => $startOfMonth,
                'date_to' => $endOfMonth,
            ]);
        }

        // Pass the filters from the request (which come from the view) to the export class
        $filters = $request->only(['mtm', 'date_from', 'date_to', 'area', 'status', 'customer_id']);
        
        // Pass filters to the export class and generate the Excel file
        return Excel::download(new DeliveryRequestExport($filters), 'delivery_requests.xlsx');
    }


    public function cashVoucherReport(Request $request)
    {
        // Get the current month and year
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $suppliers = Supplier::all();
        $cvrTypes = cvr_request_type::all();

        // Start the query with CashVouchers and related models
        $query = CashVoucher::with('cvrApprovals', 'liquidations')  // Load liquidations relationship
            ->where('cvr_type', 'admin');

        // Apply filters for current month and year if no date range is provided
        if (!$request->filled('start_date') && !$request->filled('end_date')) {
            $query->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear);
        }

        // Apply date filters if provided
        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date);
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->end_date);
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Apply CVR Type filter if provided
        if ($request->filled('cvr_type')) {
            $query->where('request_type', $request->cvr_type);
        }

        // Apply Supplier filter if provided
        if ($request->filled('supplier')) {
            $query->whereHas('suppliers', function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier);
            });
        }

        // Execute the query to fetch the results
        $adminCV = $query->get();

        // Filter by status AFTER fetching the results
        if ($request->filled('status')) {
            $adminCV = $adminCV->filter(function ($voucher) use ($request) {
                // Map through and apply status logic
                $status = $this->getVoucherStatus($voucher);
                return $status === $request->status;
            });
        }

        // Map through the vouchers and assign a single combined status text
        $voucherStatuses = $adminCV->map(function ($voucher) {
            // Default status
            $status = $this->getVoucherStatus($voucher);

            // Calculate liquidation cash and card totals from related liquidations
            $totalCash = 0;
            $totalCard = 0; // New variable to hold the total for card type

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

            // Add other non-JSON fields if necessary
            $fieldsToSum = ['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense', 'cashcharge'];
            foreach ($fieldsToSum as $field) {
                $totalCash += (float) ($voucher->$field ?? 0);
            }

            // Add the totalCash to the voucher object
            $voucher->liquidation_cash = $totalCash;
            $voucher->liquidation_card = $totalCard;

            // Assign the status text to the voucher object
            $voucher->status_text = $status;

            return $voucher;
        });

        // Pass the vouchers to the view
        return view('reports.cashVoucher', compact('voucherStatuses', 'suppliers', 'cvrTypes'));
    }

    public function AdminExport(Request $request)
    {
        return Excel::download(new CashVoucherReportExport($request), 'cash_voucher_report.xlsx');
    }

    private function getVoucherStatus($voucher)
    {
        $status = '';

        // Status logic
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
    
    public function rpmCashVoucherReport(Request $request)
    {
        // Get the current month and year
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $suppliers = Supplier::all();
        $cvrTypes = cvr_request_type::all();

        // Start the query with CashVouchers and related models
        $query = CashVoucher::with('cvrApprovals', 'liquidations')  // Load liquidations relationship
            ->where('cvr_type', 'rpm');  // Change cvr_type to 'rpm'

        // Apply filters for current month and year if no date range is provided
        if (!$request->filled('start_date') && !$request->filled('end_date')) {
            $query->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear);
        }

        // Apply date filters if provided
        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date);
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->end_date);
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Apply CVR Type filter if provided
        if ($request->filled('cvr_type')) {
            $query->where('request_type', $request->cvr_type);
        }

        // Apply Supplier filter if provided
        if ($request->filled('supplier')) {
            $query->whereHas('suppliers', function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier);
            });
        }

        // Execute the query to fetch the results
        $rpmCV = $query->get();

        // Filter by status AFTER fetching the results
        if ($request->filled('status')) {
            $rpmCV = $rpmCV->filter(function ($voucher) use ($request) {
                // Map through and apply status logic
                $status = $this->getVoucherStatus($voucher);
                return $status === $request->status;
            });
        }

        // Map through the vouchers and assign a single combined status text
        $voucherStatuses = $rpmCV->map(function ($voucher) {
            // Default status
            $status = $this->getVoucherStatus($voucher);

            // Calculate liquidation cash and card totals from related liquidations
            $totalCash = 0;
            $totalCard = 0; // New variable to hold the total for card type

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

            // Add other non-JSON fields if necessary
            $fieldsToSum = ['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense', 'cashcharge'];
            foreach ($fieldsToSum as $field) {
                $totalCash += (float) ($voucher->$field ?? 0);
            }

            // Add the totalCash to the voucher object
            $voucher->liquidation_cash = $totalCash;
            $voucher->liquidation_card = $totalCard;

            // Assign the status text to the voucher object
            $voucher->status_text = $status;

            return $voucher;
        });

        // Pass the vouchers to the view
        return view('reports.rpmCashVoucher', compact('voucherStatuses', 'suppliers', 'cvrTypes'));
    }
}

    