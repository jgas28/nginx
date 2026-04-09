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
use App\Exports\RpmCashVoucherExport;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ReportsController extends Controller
{
    public function deliveryRequestReport(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 10);
        $allowedPerPage = [5, 10, 25, 50];

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $currentDate = Carbon::now();
        $startOfMonth = $request->input('date_from', $currentDate->copy()->startOfMonth()->format('Y-m-d'));
        $endOfMonth = $request->input('date_to', $currentDate->copy()->endOfMonth()->format('Y-m-d'));

        $areas = Area::all();
        $statuses = DeliveryStatus::all();
        $customers = Customer::all();
        $companies = Company::all();

        $query = $this->buildDeliveryRequestReportQuery($request, $search, $startOfMonth, $endOfMonth);

        $overviewRecords = (clone $query)->get(['id', 'delivery_type']);
        $overview = [
            'visible_requests' => $overviewRecords->count(),
            'regular' => $overviewRecords->where('delivery_type', 'Regular')->count(),
            'multi_drop' => $overviewRecords->where('delivery_type', 'Multi-Drop')->count(),
            'multi_pickup' => $overviewRecords->where('delivery_type', 'Multi Pick-Up')->count(),
        ];

        $deliveryRequests = $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('reports.partials.deliveryRequest-table', [
                    'deliveryRequests' => $deliveryRequests,
                    'search' => $search,
                    'perPage' => $perPage,
                    'overview' => $overview,
                ])->render(),
            ]);
        }

        return view('reports.deliveryRequest', compact(
            'deliveryRequests',
            'areas',
            'statuses',
            'customers',
            'companies',
            'search',
            'perPage',
            'overview',
            'startOfMonth',
            'endOfMonth'
        ));
    } 

    private function buildDeliveryRequestReportQuery(Request $request, string $search = '', ?string $startOfMonth = null, ?string $endOfMonth = null): Builder
    {
        $startOfMonth = $startOfMonth ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $endOfMonth ?: Carbon::now()->endOfMonth()->format('Y-m-d');

        return DeliveryRequest::query()
            ->with([
                'area',
                'deliveryStatus',
                'company',
                'customer',
                'deliveryType',
            ])
            ->withSum([
                'lineItems as total_accessorial_rate' => function ($query) {
                    $query->where('status', '!=', 0);
                }
            ], 'accessorial_rate')
            ->whereBetween('created_at', [
                Carbon::parse($request->input('date_from', $startOfMonth))->startOfDay(),
                Carbon::parse($request->input('date_to', $endOfMonth))->endOfDay(),
            ])
            ->when($request->filled('mtm'), function ($query) use ($request) {
                $query->where('mtm', 'like', '%' . $request->input('mtm') . '%');
            })
            ->when($request->filled('delivery_date'), function ($query) use ($request) {
                $query->whereDate('delivery_date', $request->input('delivery_date'));
            })
            ->when($request->filled('area'), function ($query) use ($request) {
                $query->where('area_id', $request->input('area'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('delivery_status', $request->input('status'));
            })
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->input('customer_id'));
            })
            ->when($request->filled('company_id'), function ($query) use ($request) {
                $query->where('company_id', $request->input('company_id'));
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('mtm', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%")
                        ->orWhere('delivery_type', 'like', "%{$search}%")
                        ->orWhere('delivery_rate', 'like', "%{$search}%")
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
        $filters = $request->only(['mtm', 'date_from', 'date_to', 'delivery_date', 'area', 'status', 'customer_id', 'company_id', 'search']);
        
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
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 10);
        $allowedPerPage = [5, 10, 25, 50];

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $suppliers = Supplier::all();
        $cvrTypes = cvr_request_type::all();

        $query = CashVoucher::with([
                'cvrApprovals',
                'liquidations',
                'cvrTypes',
                'suppliers',
                'trucks',
                'company',
                'expenseTypes',
            ])
            ->where('cvr_type', 'rpm')
            ->when($search !== '', function ($query) use ($search) {
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
            });

        if (!$request->filled('start_date') && !$request->filled('end_date')) {
            $query->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear);
        }

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

        $rpmCV = $query->get();

        if ($request->filled('status')) {
            $rpmCV = $rpmCV->filter(function ($voucher) use ($request) {
                $status = $this->getVoucherStatus($voucher);
                return $status === $request->status;
            })->values();
        }

        $voucherStatuses = $rpmCV->map(function ($voucher) {
            $status = $this->getVoucherStatus($voucher);
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

            $fieldsToSum = ['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense', 'cashcharge'];
            foreach ($fieldsToSum as $field) {
                $totalCash += (float) ($voucher->$field ?? 0);
            }

            $voucher->liquidation_cash = $totalCash;
            $voucher->liquidation_card = $totalCard;
            $voucher->status_text = $status;

            return $voucher;
        })->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedStatuses = new LengthAwarePaginator(
            $voucherStatuses->forPage($currentPage, $perPage)->values(),
            $voucherStatuses->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        if ($request->ajax()) {
            return response()->json([
                'html' => view('reports.partials.rpmCashVoucher-table', [
                    'voucherStatuses' => $paginatedStatuses,
                    'search' => $search,
                    'perPage' => $perPage,
                ])->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $paginatedStatuses->total(),
            ]);
        }

        return view('reports.rpmCashVoucher', [
            'voucherStatuses' => $paginatedStatuses,
            'suppliers' => $suppliers,
            'cvrTypes' => $cvrTypes,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }

    public function RPMexport(Request $request)
    {
        // Get the filters passed from the request
        $filters = $request->only(['start_date', 'end_date', 'status', 'cvr_type', 'supplier']);
        
        // Pass the filters to the export class
        return Excel::download(new RpmCashVoucherExport($filters), 'rpm_cash_voucher_report.xlsx');
    }
}

    
