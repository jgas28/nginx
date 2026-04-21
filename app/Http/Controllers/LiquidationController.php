<?php

namespace App\Http\Controllers;

use App\Models\Approver;
use App\Models\Liquidation;
use App\Models\cvr_approval;
use App\Models\CashVoucher;
use App\Models\Allocation;
use App\Models\Company;
use App\Models\RunningBalance;
use App\Models\Supplier;
use App\Models\DeliveryRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class LiquidationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        $query = cvr_approval::with([
            'cashVoucher.deliveryRequest.company',
            'cashVoucher.deliveryRequest.expenseType',
            'cashVoucher.employee',
        ])
            ->where('status', '1')
            ->whereHas('cashVoucher', function ($query) {
                $query->whereIn('cvr_type', ['delivery', 'pullout', 'accessorial', 'freight', 'others'])
                    ->where('status', '2');
            });

        if ($request->filled('requestor')) {
            $query->whereHas('cashVoucher', function ($cashVoucherQuery) use ($request) {
                $cashVoucherQuery->where('requestor', $request->requestor);
            });
        }

        if ($search !== '') {
            $query->where(function ($approvalQuery) use ($search) {
                $approvalQuery->whereHas('cashVoucher', function ($cashVoucherQuery) use ($search) {
                    $cashVoucherQuery->where('cvr_number', 'like', '%' . $search . '%')
                        ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                            $employeeQuery->where('fname', 'like', '%' . $search . '%')
                                ->orWhere('lname', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('deliveryRequest.company', function ($companyQuery) use ($search) {
                            $companyQuery->where('company_code', 'like', '%' . $search . '%')
                                ->orWhere('company_name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('deliveryRequest.expenseType', function ($expenseTypeQuery) use ($search) {
                            $expenseTypeQuery->where('expense_code', 'like', '%' . $search . '%');
                        });
                });
            });
        }

        $data = $query
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        foreach ($data as $item) {
            $cashVoucher = $item->cashVoucher;
            $drId = $cashVoucher->deliveryRequest->id ?? null;
            $cvrType = $cashVoucher->cvr_type ?? null;

            $allocation = null;

            if ($drId && $cvrType) {
                $allocation = Allocation::where('dr_id', $drId)
                    ->where('trip_type', $cvrType)
                    ->where('sequence', $cashVoucher->sequence)
                    ->first();
            }

            // Attach allocation to item
            $item->allocation = $allocation;
        }

        $employees = User::where('status', '!=', 0)
            ->orderBy('fname')
            ->orderBy('lname')
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('liquidations.partials.index-table', compact('data', 'search', 'perPage'))->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $data->total(),
            ]);
        }

        return view('liquidations.index', compact('data', 'employees', 'search', 'perPage'));
    }


    public function indexAdmin(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        // Initialize the query builder for cvr_approval
        $query = cvr_approval::with([
            'cashVoucher.company',
            'cashVoucher.suppliers',
            'cashVoucher.expenseTypes',
            'cashVoucher.trucks',
        ])
            ->where('status', '1')
            ->whereHas('cashVoucher', function ($query) {
                $query->whereIn('cvr_type', ['admin', 'rpm'])
                    ->where('status', '2');
            });

        // Apply Supplier filter if exists
        if ($request->has('supplier_id') && $request->supplier_id != '') {
            $query->whereHas('cashVoucher.suppliers', function ($query) use ($request) {
                $query->where('suppliers.id', $request->supplier_id);
            });
        }

        if ($search !== '') {
            $query->where(function ($approvalQuery) use ($search) {
                $approvalQuery->whereHas('cashVoucher', function ($cashVoucherQuery) use ($search) {
                    $cashVoucherQuery->where('cvr_number', 'like', '%' . $search . '%')
                        ->orWhereHas('company', function ($companyQuery) use ($search) {
                            $companyQuery->where('company_code', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('suppliers', function ($supplierQuery) use ($search) {
                            $supplierQuery->where('supplier_code', 'like', '%' . $search . '%')
                                ->orWhere('supplier_name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('expenseTypes', function ($expenseQuery) use ($search) {
                            $expenseQuery->where('expense_code', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('trucks', function ($truckQuery) use ($search) {
                            $truckQuery->where('truck_name', 'like', '%' . $search . '%');
                        });
                });
            });
        }

        $data = $query
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        // Fetch all suppliers for the select filter
        $suppliers = Supplier::orderBy('supplier_name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('liquidations.partials.indexAdmin-table', compact('data', 'search', 'perPage'))->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $data->total(),
            ]);
        }

        // Return view with filtered data and suppliers
        return view('liquidations.indexAdmin', compact('data', 'suppliers', 'search', 'perPage'));
    }


    public function storeSummary(Request $request, $id)
    {
        $validated = $request->validate([
            'expenses' => 'array',
            'gasoline' => 'array',
            'rfid' => 'array',
            'others' => 'array',
            'cvr_id' => 'required|integer',
            'cvr_number' => 'required|string',
            'cvr_approval_id' => 'nullable|integer',
            'prepared_by' => 'required|integer',
            'noted_by' => 'required|integer',
            'validated_by' => 'nullable|integer',
            'collected_by' => 'nullable|integer',
            'approved_by' => 'nullable|integer',
        ]);

        $expenses = $request->input('expenses', []);
        $gasoline = $request->input('gasoline', []);
        $rfid = $request->input('rfid', []);
        $others = $request->input('others', []);

        // Calculate total liquidated amount (CASH ONLY for gasoline & RFID)
        $totalLiquidated = 0;
        $totalLiquidated += floatval($expenses['allowance'] ?? 0);
        $totalLiquidated += floatval($expenses['manpower'] ?? 0);
        $totalLiquidated += floatval($expenses['hauling'] ?? 0);
        $totalLiquidated += floatval($expenses['right_of_way'] ?? 0);
        $totalLiquidated += floatval($expenses['roro_expense'] ?? 0);
        $totalLiquidated += floatval($expenses['cash_charge'] ?? 0);

        foreach ($gasoline as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalLiquidated += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($rfid as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalLiquidated += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($others as $item) {
            $totalLiquidated += floatval($item['amount'] ?? 0);
        }

        // Get approved amount from CashVoucher
        $cashVoucher = CashVoucher::find($request->input('cvr_id'));
        $approvedAmount = floatval($cashVoucher->amount ?? 0);

        // Determine Liquidation status
        $status = 1; // default

        // Prepare data
        $data = [
            'cvr_id' => $request->input('cvr_id'),
            'cvr_number' => $request->input('cvr_number'),
            'cvr_approval_id' => $request->input('cvr_approval_id'),

            'allowance' => $expenses['allowance'] ?? null,
            'manpower' => $expenses['manpower'] ?? null,
            'hauling' => $expenses['hauling'] ?? null,
            'right_of_way' => $expenses['right_of_way'] ?? null,
            'roro_expense' => $expenses['roro_expense'] ?? null,
            'cash_charge' => $expenses['cash_charge'] ?? null,

            'gasoline' => array_values($gasoline),
            'rfid' => array_values($rfid),
            'others' => array_values($others),

            'status' => $status,
            'prepared_by' => $request->input('prepared_by'),
            'noted_by' => $request->input('noted_by'),
            'validated_by' => null,
            'collected_by' => null,
            'approved_by' => null,
        ];

        // Create the Liquidation record
        Liquidation::create($data);
        $cvrId = $request->input('cvr_id');
        CashVoucher::where('id', $cvrId)->update(['status' => 4]);

        return redirect()->route('liquidations.index')->with('success', 'Liquidation submitted successfully.');
    }



    public function liquidate($id)
    {
        $liquidation = cvr_approval::with('cashVoucher')->findOrFail($id);
        $employees = User::whereIn('id', [1, 41, 15, 5, 9, 16, 22])->get();
        $preparers = User::where('status', '!=', 0)->get();
        
        return view('liquidations.liquidate', compact('liquidation', 'employees', 'preparers'));
    }

    public function reviewList(Request $request)
    {
        $search = trim((string) $request->input('search', $request->input('cvr_number', '')));
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;
        $cvrType = trim((string) $request->input('cvr_type', ''));

        $query = Liquidation::with([
            'preparedBy',
            'notedBy',
            'cashVoucher.company',
            'cashVoucher.expenseTypes',
            'cashVoucher.trucks',
            'cashVoucher.deliveryRequest.company',
            'cashVoucher.deliveryRequest.expenseType',
        ])->where('status', 1);

        if ($cvrType !== '') {
            $query->whereHas('cashVoucher', function ($cashVoucherQuery) use ($cvrType) {
                $cashVoucherQuery->where('cvr_type', $cvrType);
            });
        }

        if ($search !== '') {
            $query->where(function ($liquidationQuery) use ($search) {
                $liquidationQuery->whereHas('cashVoucher', function ($cashVoucherQuery) use ($search) {
                    $cashVoucherQuery->where('cvr_number', 'like', '%' . $search . '%')
                        ->orWhere('cvr_type', 'like', '%' . $search . '%')
                        ->orWhereHas('company', function ($companyQuery) use ($search) {
                            $companyQuery->where('company_code', 'like', '%' . $search . '%')
                                ->orWhere('company_name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('suppliers', function ($supplierQuery) use ($search) {
                            $supplierQuery->where('supplier_code', 'like', '%' . $search . '%')
                                ->orWhere('supplier_name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('expenseTypes', function ($expenseQuery) use ($search) {
                            $expenseQuery->where('expense_code', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('trucks', function ($truckQuery) use ($search) {
                            $truckQuery->where('truck_name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('deliveryRequest.company', function ($drCompanyQuery) use ($search) {
                            $drCompanyQuery->where('company_code', 'like', '%' . $search . '%')
                                ->orWhere('company_name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('deliveryRequest.expenseType', function ($drExpenseQuery) use ($search) {
                            $drExpenseQuery->where('expense_code', 'like', '%' . $search . '%');
                        });
                })
                ->orWhereHas('preparedBy', function ($preparedByQuery) use ($search) {
                    $preparedByQuery->where('fname', 'like', '%' . $search . '%')
                        ->orWhere('lname', 'like', '%' . $search . '%');
                })
                ->orWhereHas('notedBy', function ($notedByQuery) use ($search) {
                    $notedByQuery->where('fname', 'like', '%' . $search . '%')
                        ->orWhere('lname', 'like', '%' . $search . '%');
                });
            });
        }

        $liquidations = $query
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        // Iterate through liquidations to attach allocation and deliveryRequest
        foreach ($liquidations as $liquidation) {
            $cashVoucher = $liquidation->cashVoucher;

            if (!$cashVoucher) {
                continue; // Skip if no associated CashVoucher
            }

            $cvrType = $cashVoucher->cvr_type;
            $dr = $cashVoucher->deliveryRequest ?? null;

            // Only get allocation for these CVR types
            if (in_array($cvrType, ['delivery', 'others', 'rpm', 'freight', 'accessorial', 'pullout']) && $dr) {
                $allocation = Allocation::where('dr_id', $dr->id)
                    ->where('trip_type', $cvrType)
                    ->where('sequence', $cashVoucher->sequence)
                    ->first();

                $liquidation->allocation = $allocation;
                $liquidation->deliveryRequest = $dr;
            }
        }

        $availableTypes = CashVoucher::query()
            ->whereNotNull('cvr_type')
            ->distinct()
            ->orderBy('cvr_type')
            ->pluck('cvr_type');

        $overview = [
            'total' => $liquidations->total(),
            'admin' => $liquidations->filter(fn ($liquidation) => optional($liquidation->cashVoucher)->cvr_type === 'admin')->count(),
            'rpm' => $liquidations->filter(fn ($liquidation) => optional($liquidation->cashVoucher)->cvr_type === 'rpm')->count(),
            'delivery_related' => $liquidations->filter(fn ($liquidation) => in_array(optional($liquidation->cashVoucher)->cvr_type, ['delivery', 'pullout', 'accessorial', 'freight', 'others']))->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('liquidations.partials.review-list-table', compact('liquidations', 'search', 'perPage', 'overview'))->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $liquidations->total(),
            ]);
        }

        return view('liquidations.reviewList', compact('liquidations', 'search', 'perPage', 'cvrType', 'availableTypes', 'overview'));
    }


    public function review($id)
    {
        // Load liquidation with related data
        $liquidation = Liquidation::with('cashVoucher', 'cvrApproval', 'preparedBy', 'notedBy')->findOrFail($id);
        $employees = User::whereIn('id', [41,5,15,53,1,9,16,22])->get(); // You can adjust this condition as needed
        $staffs = User::where('status', '!=', 0)->get();
        $approvers = Approver::all();
        $collectors = User::whereIn('id', [15,35,54])->get();

        // Calculate total liquidated cash (cash only)
        $totalCash = 0;

        foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field) {
            $totalCash += floatval($liquidation->$field ?? 0);
        }

        $totalCash += floatval($liquidation->cash_charge ?? 0);

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->others ?? [] as $item) {
            $totalCash += floatval($item['amount'] ?? 0);
        }

        // Calculate total non-cash (card) expenses
        $totalCard = 0;

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        // Approved amount from CVR
        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0) + floatval($liquidation->cvrApproval->charge ?? 0);

        // Raw difference (before adjustments)
        $difference = $totalCash - $approvedAmount;

        $totalLiquidated = $totalCash;
        // Load running balances
        $runningRefunds = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '3') // Refund
            ->get();

        $runningReturns = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '2') // Returned cash
            ->get();

        $runningUncollected = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '4') // Uncollected
            ->get();

        // Sum of existing refunds
        $refundTotal = $runningRefunds->sum(function ($item) {
            return isset($item->amount) ? abs($item->amount) : 0;
        });

        // Combine returns and uncollected
        $combinedReturns = collect();
        if ($runningReturns) {
            $combinedReturns = $combinedReturns->merge($runningReturns);
        }
        if ($runningUncollected) {
            $combinedReturns = $combinedReturns->merge($runningUncollected);
        }

        $returnedTotal = $combinedReturns->sum(function ($item) {
            return isset($item->amount) ? abs($item->amount) : 0;
        });

        // Adjusted difference
        $adjustedDifference = round($difference + $refundTotal + $returnedTotal, 2);

        // Decide next step
        $nextStatus = 4; // default to "For Approval"
        $refund = false;
        $return = false;

        if (round($adjustedDifference, 2) == 0) {
            // Fully reconciled: either no transaction, or liquidated + refund/return balances match the CVR
            $nextStatus = 4;
        } elseif ($adjustedDifference > 0) {
            // User is owed money (over-liquidated)
            $refund = true;
            $nextStatus = 4;
        } elseif ($adjustedDifference < 0) {
            // User owes money (under-liquidated)
            $return = true;
            $nextStatus = 3;
        }

        // Return view
        return view('liquidations.review', compact(
            'liquidation', 
            'employees',
            'approvers',
            'totalCash',
            'totalCard',
            'approvedAmount',
            'difference',
            'refund',
            'return',
            'nextStatus',
            'staffs',
            'runningRefunds',
            'runningReturns',
            'runningUncollected',
            'collectors' 
        ));
    }

    public function validateLiquidation(Request $request, $id)
    {
        $liquidation = Liquidation::findOrFail($id);

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0) + floatval($liquidation->cvrApproval->charge ?? 0);

        // Recalculate the total like in your `validated` method
        $totalCash = 0;
        foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field) {
            $totalCash += floatval($liquidation->$field ?? 0);
        }
        $totalCash += floatval($liquidation->cash_charge ?? 0);

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->others ?? [] as $item) {
            $totalCash += floatval($item['amount'] ?? 0);
        }

        $difference = round($totalCash - $approvedAmount, 2);

        // Base validation (always needed)
        $rules = [
            'validated_by' => 'required|exists:users,id',
        ];

        // If under-liquidated (needs return), require collector
        if ($difference < 0) {
            $rules['collector_id'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);

        // Assign next status
        $liquidation->status = $difference < 0 ? 3 : 4;
        $liquidation->validated_by = $validated['validated_by'];
        $liquidation->validated_at = now();

        // Optional: store collector
        if ($difference < 0 && isset($validated['collector_id'])) {
            $liquidation->collector_id = $validated['collector_id']; // only if this field exists in the table
        }

        $liquidation->save();

        return redirect()->route('liquidations.reviewList')->with('success', 'Liquidation validated successfully.');
    }


    public function validatedList(Request $request)
    {
        $user = Auth::user();
        $search = trim((string) $request->input('search', $request->input('cvr_number', '')));
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;
        $cvrType = trim((string) $request->input('cvr_type', ''));

        $query = Liquidation::with([
            'preparedBy',
            'notedBy',
            'collector',
            'cashVoucher.company',
            'cashVoucher.suppliers',
            'cashVoucher.expenseTypes',
            'cashVoucher.trucks',
            'cashVoucher.deliveryRequest.company',
            'cashVoucher.deliveryRequest.expenseType',
        ])->where('status', 3);

        if (!in_array($user->id, [1, 53, 54])) {
            $query->where('collector_id', $user->id);
        }

        if ($cvrType !== '') {
            $query->whereHas('cashVoucher', function ($cashVoucherQuery) use ($cvrType) {
                $cashVoucherQuery->where('cvr_type', $cvrType);
            });
        }

        if ($search !== '') {
            $query->where(function ($liquidationQuery) use ($search) {
                $liquidationQuery
                    ->whereHas('cashVoucher', function ($cashVoucherQuery) use ($search) {
                        $cashVoucherQuery
                            ->where('cvr_number', 'like', '%' . $search . '%')
                            ->orWhere('cvr_type', 'like', '%' . $search . '%')
                            ->orWhereHas('company', function ($companyQuery) use ($search) {
                                $companyQuery
                                    ->where('company_code', 'like', '%' . $search . '%')
                                    ->orWhere('company_name', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('suppliers', function ($supplierQuery) use ($search) {
                                $supplierQuery
                                    ->where('supplier_code', 'like', '%' . $search . '%')
                                    ->orWhere('supplier_name', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('expenseTypes', function ($expenseTypeQuery) use ($search) {
                                $expenseTypeQuery->where('expense_code', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('trucks', function ($truckQuery) use ($search) {
                                $truckQuery->where('truck_name', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('deliveryRequest.company', function ($deliveryCompanyQuery) use ($search) {
                                $deliveryCompanyQuery
                                    ->where('company_code', 'like', '%' . $search . '%')
                                    ->orWhere('company_name', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('deliveryRequest.expenseType', function ($deliveryExpenseTypeQuery) use ($search) {
                                $deliveryExpenseTypeQuery->where('expense_code', 'like', '%' . $search . '%');
                            });
                    })
                    ->orWhereHas('preparedBy', function ($preparedByQuery) use ($search) {
                        $preparedByQuery
                            ->where('fname', 'like', '%' . $search . '%')
                            ->orWhere('lname', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('notedBy', function ($notedByQuery) use ($search) {
                        $notedByQuery
                            ->where('fname', 'like', '%' . $search . '%')
                            ->orWhere('lname', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('collector', function ($collectorQuery) use ($search) {
                        $collectorQuery
                            ->where('fname', 'like', '%' . $search . '%')
                            ->orWhere('lname', 'like', '%' . $search . '%');
                    });
            });
        }

        $liquidations = $query->latest()->paginate($perPage)->appends($request->query());

        foreach ($liquidations as $liquidation) {
            $cashVoucher = $liquidation->cashVoucher;

            if (!$cashVoucher) {
                continue;
            }

            $currentType = $cashVoucher->cvr_type;
            $dr = $cashVoucher->deliveryRequest ?? null;

            if (in_array($currentType, ['delivery', 'others', 'rpm', 'freight', 'accessorial', 'pullout']) && $dr) {
                $allocation = Allocation::where('dr_id', $dr->id)
                    ->where('trip_type', $currentType)
                    ->where('sequence', $cashVoucher->sequence)
                    ->first();

                $liquidation->allocation = $allocation;
                $liquidation->deliveryRequest = $dr;
            }
        }

        $availableTypes = CashVoucher::query()
            ->whereNotNull('cvr_type')
            ->where('cvr_type', '<>', '')
            ->distinct()
            ->orderBy('cvr_type')
            ->pluck('cvr_type');

        $overview = [
            'total' => (clone $query)->count(),
            'admin' => (clone $query)->whereHas('cashVoucher', fn ($cashVoucherQuery) => $cashVoucherQuery->where('cvr_type', 'admin'))->count(),
            'rpm' => (clone $query)->whereHas('cashVoucher', fn ($cashVoucherQuery) => $cashVoucherQuery->where('cvr_type', 'rpm'))->count(),
            'delivery_related' => (clone $query)->whereHas('cashVoucher', fn ($cashVoucherQuery) => $cashVoucherQuery->whereIn('cvr_type', ['delivery', 'pullout', 'accessorial', 'freight', 'others']))->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('liquidations.partials.validated-list-table', [
                    'liquidations' => $liquidations,
                    'search' => $search,
                    'perPage' => $perPage,
                    'overview' => $overview,
                ])->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $liquidations->total(),
            ]);
        }

        return view('liquidations.validatedList', compact(
            'liquidations',
            'search',
            'perPage',
            'cvrType',
            'availableTypes',
            'overview'
        ));
    }

    public function validate(Request $request, $id)
    {
        $liquidation = Liquidation::with('cashVoucher', 'cvrApproval', 'preparedBy', 'notedBy')->findOrFail($id);
        $employees = User::whereIn('id', [54, 15, 35, 5, 15])->get();
        $staffs = User::where('status', '!=', 0)->get();
        $approvers = Approver::all();

        // Total Liquidated Cash (Only cash items)
        $totalCash = 0;

        foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field) {
            $totalCash += floatval($liquidation->$field ?? 0);
        }

        $totalCash += floatval($liquidation->cash_charge ?? 0);

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->others ?? [] as $item) {
            $totalCash += floatval($item['amount'] ?? 0);
        }

        // Total Card Expenses (non-cash)
        $totalCard = 0;

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0) + floatval($liquidation->cvrApproval->charge ?? 0);
        $difference = $totalCash - $approvedAmount;

        // Logic for display and next step status
        $nextStatus = 4; // default: approval
        $refund = false;
        $return = false;

        if ($difference > 0) {
            $refund = true;
            $nextStatus = 4; // can still go to approval but shows refund button
        } elseif ($difference < 0) {
            $return = true;
            $nextStatus = 3; // needs collection
        }

        $runningRefunds = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '3')
            ->get();

        $runningReturns = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '2')
            ->get();

        $runningUncollected = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '4')
            ->get();

        return view('liquidations.validated', compact(
            'liquidation',
            'employees',
            'approvers',
            'totalCash',
            'totalCard',
            'approvedAmount',
            'difference',
            'refund',
            'return',
            'nextStatus',
            'staffs',
            'runningRefunds',
            'runningReturns',
            'runningUncollected'
        ));
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:1000',
            'validated_by' => 'required|exists:users,id',
        ]);

        $liquidation = Liquidation::findOrFail($id);
        $liquidation->status = 10; // Rejected
        $liquidation->validated_by = $request->validated_by;
        $liquidation->validated_at = now();
        $liquidation->remarks = $request->remarks;
        $liquidation->save();

        return redirect()->route('liquidations.index')->with('error', 'Liquidation has been rejected.');
    }

    public function collectedLiquidation(Request $request, $id)
    {
        $request->validate([
            'collected_by' => 'required|exists:users,id',  // adjust table name accordingly
        ]);

        $liquidation = Liquidation::findOrFail($id);

        $liquidation->collected_by = $request->validated_by;
        $liquidation->collected_at = now();
        $liquidation->status = 4;
        $liquidation->save();

        return redirect()->route('liquidations.reviewList')->with('success', 'Liquidation validated successfully.');
    }

    public function approvalList(Request $request)
    {
        $search = trim((string) $request->input('search', $request->input('cvr_number', '')));
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;
        $cvrType = trim((string) $request->input('cvr_type', ''));

        $query = Liquidation::with([
            'preparedBy',
            'notedBy',
            'collector',
            'cashVoucher.company',
            'cashVoucher.suppliers',
            'cashVoucher.expenseTypes',
            'cashVoucher.trucks',
            'cashVoucher.deliveryRequest.company',
            'cashVoucher.deliveryRequest.expenseType',
        ])->where('status', 4);

        if ($cvrType !== '') {
            $query->whereHas('cashVoucher', function ($cashVoucherQuery) use ($cvrType) {
                $cashVoucherQuery->where('cvr_type', $cvrType);
            });
        }

        if ($search !== '') {
            $query->where(function ($liquidationQuery) use ($search) {
                $liquidationQuery
                    ->whereHas('cashVoucher', function ($cashVoucherQuery) use ($search) {
                        $cashVoucherQuery
                            ->where('cvr_number', 'like', '%' . $search . '%')
                            ->orWhere('cvr_type', 'like', '%' . $search . '%')
                            ->orWhereHas('company', function ($companyQuery) use ($search) {
                                $companyQuery
                                    ->where('company_code', 'like', '%' . $search . '%')
                                    ->orWhere('company_name', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('suppliers', function ($supplierQuery) use ($search) {
                                $supplierQuery
                                    ->where('supplier_code', 'like', '%' . $search . '%')
                                    ->orWhere('supplier_name', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('expenseTypes', function ($expenseTypeQuery) use ($search) {
                                $expenseTypeQuery->where('expense_code', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('trucks', function ($truckQuery) use ($search) {
                                $truckQuery->where('truck_name', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('deliveryRequest.company', function ($deliveryCompanyQuery) use ($search) {
                                $deliveryCompanyQuery
                                    ->where('company_code', 'like', '%' . $search . '%')
                                    ->orWhere('company_name', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('deliveryRequest.expenseType', function ($deliveryExpenseTypeQuery) use ($search) {
                                $deliveryExpenseTypeQuery->where('expense_code', 'like', '%' . $search . '%');
                            });
                    })
                    ->orWhereHas('preparedBy', function ($preparedByQuery) use ($search) {
                        $preparedByQuery
                            ->where('fname', 'like', '%' . $search . '%')
                            ->orWhere('lname', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('notedBy', function ($notedByQuery) use ($search) {
                        $notedByQuery
                            ->where('fname', 'like', '%' . $search . '%')
                            ->orWhere('lname', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('collector', function ($collectorQuery) use ($search) {
                        $collectorQuery
                            ->where('fname', 'like', '%' . $search . '%')
                            ->orWhere('lname', 'like', '%' . $search . '%');
                    });
            });
        }

        $liquidations = $query->latest()->paginate($perPage)->appends($request->query());

        foreach ($liquidations as $liquidation) {
            $cashVoucher = $liquidation->cashVoucher;

            if (!$cashVoucher) {
                continue; // Skip if no associated CashVoucher
            }

            $cvrType = $cashVoucher->cvr_type;
            $dr = $cashVoucher->deliveryRequest ?? null;

            // Only get allocation for these CVR types
            if (in_array($cvrType, ['delivery', 'others', 'rpm', 'freight', 'accessorial', 'pullout']) && $dr) {
                $allocation = Allocation::where('dr_id', $dr->id)
                    ->where('trip_type', $cvrType)
                    ->where('sequence', $cashVoucher->sequence)
                    ->first();

                $liquidation->allocation = $allocation;
                $liquidation->deliveryRequest = $dr;
            }
        }

        $availableTypes = CashVoucher::query()
            ->whereNotNull('cvr_type')
            ->where('cvr_type', '<>', '')
            ->distinct()
            ->orderBy('cvr_type')
            ->pluck('cvr_type');

        $overview = [
            'total' => (clone $query)->count(),
            'admin' => (clone $query)->whereHas('cashVoucher', fn ($cashVoucherQuery) => $cashVoucherQuery->where('cvr_type', 'admin'))->count(),
            'rpm' => (clone $query)->whereHas('cashVoucher', fn ($cashVoucherQuery) => $cashVoucherQuery->where('cvr_type', 'rpm'))->count(),
            'delivery_related' => (clone $query)->whereHas('cashVoucher', fn ($cashVoucherQuery) => $cashVoucherQuery->whereIn('cvr_type', ['delivery', 'pullout', 'accessorial', 'freight', 'others']))->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('liquidations.partials.approval-list-table', [
                    'liquidations' => $liquidations,
                    'search' => $search,
                    'perPage' => $perPage,
                    'overview' => $overview,
                ])->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $liquidations->total(),
            ]);
        }

        return view('liquidations.approvalList', compact(
            'liquidations',
            'search',
            'perPage',
            'cvrType',
            'availableTypes',
            'overview'
        ));
    }

    public function approval(Request $request, $id)
    {
        $liquidation = Liquidation::with(['cashVoucher', 'cvrApproval', 'preparedBy', 'notedBy', 'runningBalances'])
                        ->findOrFail($id);

        $employees = User::whereIn('id', [54])->get();
        $approvers = Approver::all();
        $staffs = User::where('status', '!=', 0)->get();

        // Calculate total liquidated cash
        $totalCash = 0;
        foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field) {
            $totalCash += floatval($liquidation->$field ?? 0);
        }
        $totalCash += floatval($liquidation->cash_charge ?? 0);

        // Decode JSON fields
        $gasoline = is_array($liquidation->gasoline) ? $liquidation->gasoline : json_decode($liquidation->gasoline, true) ?? [];
        $rfid = is_array($liquidation->rfid) ? $liquidation->rfid : json_decode($liquidation->rfid, true) ?? [];
        $others = is_array($liquidation->others) ? $liquidation->others : json_decode($liquidation->others, true) ?? [];

        // Add cash-based gasoline
        foreach ($gasoline as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        // Add cash-based RFID
        foreach ($rfid as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        // Add all "others" amounts
        foreach ($others as $item) {
            $totalCash += floatval($item['amount'] ?? 0);
        }

        // Total Card Expenses
        $totalCard = 0;
        foreach ($gasoline as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($rfid as $item) {
            if (($item['type'] ?? '') === 'card') {
                $totalCard += floatval($item['amount'] ?? 0);
            }
        }

        // Properly calculate each running balance type
        $refundTotal = $liquidation->runningBalances
            ->where('type', '3') // Refund
            ->sum(fn($item) => abs($item->amount));

        $returnTotal = $liquidation->runningBalances
            ->where('type', '2') // Return
            ->sum(fn($item) => abs($item->amount));

        $uncollectedTotal = $liquidation->runningBalances
            ->where('type', '4') // Uncollected
            ->sum(fn($item) => abs($item->amount));

         // Separate running balances for display
        $runningRefunds = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '3')->get();

        $runningReturns = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '2')->get();

        $runningUncollected = RunningBalance::where('cvr_number', $liquidation->cvr_number)
            ->where('type', '4')->get();


        // Approved amount
        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0) + floatval($liquidation->cvrApproval->charge ?? 0);

        $finalLiquidated = $totalCash;

        // Calculate raw cash difference
        $rawDifference = $approvedAmount - $totalCash;

        // Adjust based on actual return/refund made
        if ($rawDifference > 0) {
            // Underspent — Return expected
            $difference = $rawDifference - ($returnTotal + $uncollectedTotal);
        } elseif ($rawDifference < 0) {
            // Overspent — Refund expected
            $difference = $rawDifference + $refundTotal; // Refunds are money already returned
        } else {
            $difference = 0;
        }

        // Use epsilon for floating point tolerance
        $epsilon = 0.01; // 1 cent tolerance

        if (abs($difference) < $epsilon) {
            $difference = 0;
            $refund = false;
            $return = false;
        } else {
            $refund = $difference < 0;
            $return = $difference > 0;
        }

        $nextStatus = $refund ? 3 : ($return ? 4 : null);

       
        return view('liquidations.approval', compact(
            'liquidation',
            'employees',
            'approvers',
            'totalCash',
            'totalCard',
            'refundTotal',
            'returnTotal',
            'uncollectedTotal',
            'finalLiquidated',
            'approvedAmount',
            'difference',
            'refund',
            'return',
            'nextStatus',
            'runningRefunds',
            'runningReturns',
            'runningUncollected',
            'gasoline',
            'rfid',
            'others',
            'staffs'
        ));
    }


    public function approvedLiquidation(Request $request, $id)
    {

        $liquidation = Liquidation::findOrFail($id);

        $liquidation->approved_by = 54;
        $liquidation->approved_at = now();
        $liquidation->status = 5;
        $liquidation->save();

        return redirect()->route('liquidations.approvalList')->with('success', 'Liquidation validated successfully.');
    }

    public function approvalLiquidation(Request $request, $id)
    {
        $request->validate([
            'validated_by' => 'required|exists:users,id',  // adjust table name accordingly
        ]);

        $liquidation = Liquidation::findOrFail($id);

        $approvedAmount = floatval($liquidation->cvrApproval->amount ?? 0) + floatval($liquidation->cvrApproval->charge ?? 0);
 
        // Recalculate the total like in your `validated` method
        $totalCash = 0;

        foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field) {
            $totalCash += floatval($liquidation->$field ?? 0);
        }

        $totalCash += floatval($liquidation->cash_charge ?? 0);

        foreach ($liquidation->gasoline ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->rfid ?? [] as $item) {
            if (($item['type'] ?? '') === 'cash') {
                $totalCash += floatval($item['amount'] ?? 0);
            }
        }

        foreach ($liquidation->others ?? [] as $item) {
            $totalCash += floatval($item['amount'] ?? 0);
        }


        $difference = $totalCash - $approvedAmount;

        // Assign status based on difference
        if ($difference > 0) {
            $liquidation->status = 4; // Refund, go to approval
        } else {
            $liquidation->status = 3; // Returned cash, go to collection
        }

        $liquidation->validated_by = $request->validated_by;
        $liquidation->validated_at = now();
        $liquidation->save();

        return redirect()->route('liquidations.approvalList')->with('success', 'Liquidation validated successfully.');
    }

    public function liquidationList(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $cvrNumber = $request->input('cvr_number');

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end   = Carbon::parse($endDate)->endOfDay();
        } else {
            $start = Carbon::today();
            $end   = Carbon::today()->endOfDay();
        }

        // Base query
        $query = Liquidation::with('cashVoucher')
            ->whereBetween('created_at', [$start, $end]);

        // Add CVR filter if present
        if ($cvrNumber) {
            $query->whereHas('cashVoucher', function ($q) use ($cvrNumber) {
                $q->where('cvr_number', 'like', '%' . $cvrNumber . '%');
            });
        }

        // Paginate results (10 per page)
        $liquidations = $query->paginate(10)->withQueryString();

        // Compute total expenses for each item
        $liquidations->getCollection()->transform(function ($liquidation) {
            $cashVoucher = $liquidation->cashVoucher;
            $totalExpenses = 0;

            if ($cashVoucher) {
                $cashVoucher->load([
                    'deliveryRequest.company',
                    'deliveryRequest.expenseType',
                    'withholdingTax',
                ]);

                $deliveryRequest = $cashVoucher->deliveryRequest;

                $allocationRelation = match ($cashVoucher->cvr_type) {
                    'delivery'     => 'deliveryAllocations',
                    'pullout'      => 'pulloutAllocations',
                    'accessorial'  => 'accessorialAllocations',
                    'freight'      => 'freightAllocations',
                    'others', 'admin', 'rpm' => 'othersAllocations',
                    default        => null,
                };

                $liquidation->allocations = ($deliveryRequest && $allocationRelation && method_exists($deliveryRequest, $allocationRelation))
                    ? $deliveryRequest->$allocationRelation()->with('truck')->get()
                    : collect();

                // Add direct expense fields
                $totalExpenses += (float) $liquidation->allowance;
                $totalExpenses += (float) $liquidation->manpower;
                $totalExpenses += (float) $liquidation->hauling;
                $totalExpenses += (float) $liquidation->right_of_way;
                $totalExpenses += (float) $liquidation->roro_expense;
                $totalExpenses += (float) $liquidation->cash_charge;

                // 'others' field
                $others = is_string($liquidation->others)
                    ? json_decode($liquidation->others, true)
                    : $liquidation->others;
                if (is_array($others)) {
                    foreach ($others as $item) {
                        $totalExpenses += isset($item['amount']) ? (float) $item['amount'] : 0;
                    }
                }

                // 'gasoline' field (cash only)
                $gasoline = is_string($liquidation->gasoline)
                    ? json_decode($liquidation->gasoline, true)
                    : $liquidation->gasoline;
                if (is_array($gasoline)) {
                    foreach ($gasoline as $item) {
                        if (($item['type'] ?? '') === 'cash') {
                            $totalExpenses += isset($item['amount']) ? (float) $item['amount'] : 0;
                        }
                    }
                }

                // 'rf_id' field (cash only)
                $rf_id = is_string($liquidation->rf_id)
                    ? json_decode($liquidation->rf_id, true)
                    : $liquidation->rf_id;
                if (is_array($rf_id)) {
                    foreach ($rf_id as $item) {
                        if (($item['type'] ?? '') === 'cash') {
                            $totalExpenses += isset($item['amount']) ? (float) $item['amount'] : 0;
                        }
                    }
                }
            } else {
                $liquidation->allocations = collect();
            }

            $liquidation->total_expense = $totalExpenses;
            return $liquidation;
        });

        return view('liquidations.liquidationList', compact('liquidations', 'startDate', 'endDate', 'cvrNumber'));
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $approval = cvr_approval::find($id);

        if ($approval) {
            return redirect()->route('liquidations.liquidate', $approval->id);
        }

        $liquidation = Liquidation::findOrFail($id);

        return match ((string) $liquidation->status) {
            '1' => redirect()->route('liquidations.review', $liquidation->id),
            '3' => redirect()->route('liquidations.validated', $liquidation->id),
            '4' => redirect()->route('liquidations.approval', $liquidation->id),
            '10' => redirect()->route('liquidations.rejectEdit', $liquidation->id),
            default => redirect()->route('liquidations.review', $liquidation->id),
        };
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Liquidation $liquidation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Liquidation $liquidation)
    {
        //
    }

    public function approvedLiqUpdate(Request $request, $id)
    {
        // Find the liquidation by ID
        $liquidation = Liquidation::findOrFail($id);

        // Validate the request data (e.g., approved_by and expenses)
        $request->validate([
            'allowance' => 'nullable|numeric',
            'manpower' => 'nullable|numeric',
            'hauling' => 'nullable|numeric',
            'right_of_way' => 'nullable|numeric',
            'roro_expense' => 'nullable|numeric',
            'cash_charge' => 'nullable|numeric',
            'gasoline' => 'nullable|array',
            'gasoline.*.type' => 'nullable|string',
            'gasoline.*.amount' => 'nullable|numeric',
            'rfid' => 'nullable|array',
            'rfid.*.tag' => 'nullable|string',
            'rfid.*.type' => 'nullable|string',
            'rfid.*.amount' => 'nullable|numeric',
            'others' => 'nullable|array',
            'others.*.description' => 'nullable|string',
            'others.*.amount' => 'nullable|numeric',
        ]);

        // Update fields with the form data
        $liquidation->update([
            'approved_by' => $request->approved_by,
            'allowance' => $request->allowance ?? 0,
            'manpower' => $request->manpower ?? 0,
            'hauling' => $request->hauling ?? 0,
            'right_of_way' => $request->right_of_way ?? 0,
            'roro_expense' => $request->roro_expense ?? 0,
            'cash_charge' => $request->cash_charge ?? 0,
            'gasoline' => array_values($request->gasoline ?? []),
            'rfid' => array_values($request->rfid ?? []),
            'others' => array_values($request->others ?? []),
        ]);

        // Redirect with success message
        return redirect()->route('liquidations.approval', $liquidation->id)
        ->with('success', 'Liquidation details updated!');
    } 

    public function approvedCollection(Request $request, $id)
    {
        // Find the liquidation by ID
        $liquidation = Liquidation::findOrFail($id);

        // Validate the request data (e.g., approved_by and expenses)
        $request->validate([
            'allowance' => 'nullable|numeric',
            'manpower' => 'nullable|numeric',
            'hauling' => 'nullable|numeric',
            'right_of_way' => 'nullable|numeric',
            'roro_expense' => 'nullable|numeric',
            'cash_charge' => 'nullable|numeric',
            'gasoline' => 'nullable|array',
            'gasoline.*.type' => 'nullable|string',
            'gasoline.*.amount' => 'nullable|numeric',
            'rfid' => 'nullable|array',
            'rfid.*.tag' => 'nullable|string',
            'rfid.*.type' => 'nullable|string',
            'rfid.*.amount' => 'nullable|numeric',
            'others' => 'nullable|array',
            'others.*.description' => 'nullable|string',
            'others.*.amount' => 'nullable|numeric',
        ]);

        // Update fields with the form data
        $liquidation->update([
            'approved_by' => $request->approved_by,
            'allowance' => $request->allowance ?? 0,
            'manpower' => $request->manpower ?? 0,
            'hauling' => $request->hauling ?? 0,
            'right_of_way' => $request->right_of_way ?? 0,
            'roro_expense' => $request->roro_expense ?? 0,
            'cash_charge' => $request->cash_charge ?? 0,
            'gasoline' => array_values($request->gasoline ?? []),
            'rfid' => array_values($request->rfid ?? []),
            'others' => array_values($request->others ?? []),
        ]);

        // Redirect with success message
        return redirect()->route('liquidations.approvalList') // Or wherever you want to redirect
            ->with('success', 'Liquidation updated and approved successfully!');
    }

    public function approvedValidation(Request $request, $id)
    {
        // Find the liquidation by ID
        $liquidation = Liquidation::findOrFail($id);

        // Validate the request data (e.g., approved_by and expenses)
        $request->validate([
            'allowance' => 'nullable|numeric',
            'manpower' => 'nullable|numeric',
            'hauling' => 'nullable|numeric',
            'right_of_way' => 'nullable|numeric',
            'roro_expense' => 'nullable|numeric',
            'cash_charge' => 'nullable|numeric',
            'gasoline' => 'nullable|array',
            'gasoline.*.type' => 'nullable|string',
            'gasoline.*.amount' => 'nullable|numeric',
            'rfid' => 'nullable|array',
            'rfid.*.tag' => 'nullable|string',
            'rfid.*.type' => 'nullable|string',
            'rfid.*.amount' => 'nullable|numeric',
            'others' => 'nullable|array',
            'others.*.description' => 'nullable|string',
            'others.*.amount' => 'nullable|numeric',
        ]);

        // Update fields with the form data
        $liquidation->update([
            'approved_by' => $request->approved_by,
            'allowance' => $request->allowance ?? 0,
            'manpower' => $request->manpower ?? 0,
            'hauling' => $request->hauling ?? 0,
            'right_of_way' => $request->right_of_way ?? 0,
            'roro_expense' => $request->roro_expense ?? 0,
            'cash_charge' => $request->cash_charge ?? 0,
            'gasoline' => array_values($request->gasoline ?? []),
            'rfid' => array_values($request->rfid ?? []),
            'others' => array_values($request->others ?? []),
        ]);

        // Redirect with success message
        return redirect()->route('liquidations.approvalList') // Or wherever you want to redirect
            ->with('success', 'Liquidation updated and approved successfully!');
    }

    public function rejectedList()
    {
        // Fetch ALL liquidations with their immediate cashVoucher
        $liquidations = Liquidation::with('cashVoucher')
        ->where('status', 10)
        ->get();

        $liquidations->each(function ($liquidation) {
            $cashVoucher = $liquidation->cashVoucher;

            // Initialize total expenses
            $totalExpenses = 0;

            if ($cashVoucher) {
                // Load nested relationships
                $cashVoucher->load([
                    'deliveryRequest.company',
                    'deliveryRequest.expenseType',
                    'withholdingTax',
                ]);

                $deliveryRequest = $cashVoucher->deliveryRequest;

                // Load allocation relation dynamically
                $allocationRelation = match ($cashVoucher->cvr_type) {
                    'delivery'     => 'deliveryAllocations',
                    'pullout'      => 'pulloutAllocations',
                    'accessorial'  => 'accessorialAllocations',
                    'freight'      => 'freightAllocations',
                    'others', 'admin', 'rpm' => 'othersAllocations',
                    default        => null,
                };

                if ($deliveryRequest && $allocationRelation && method_exists($deliveryRequest, $allocationRelation)) {
                    $liquidation->allocations = $deliveryRequest->$allocationRelation()->with('truck')->get();
                } else {
                    $liquidation->allocations = collect();
                }

                // Direct expense fields (numeric)
                $totalExpenses += (float) $liquidation->allowance;
                $totalExpenses += (float) $liquidation->manpower;
                $totalExpenses += (float) $liquidation->hauling;
                $totalExpenses += (float) $liquidation->right_of_way;
                $totalExpenses += (float) $liquidation->roro_expense;
                $totalExpenses += (float) $liquidation->cash_charge;

                // Parse 'others' JSON
                $others = $liquidation->others;
                if (is_string($others)) {
                    $others = json_decode($others, true);
                }
                if (is_array($others)) {
                    foreach ($others as $item) {
                        $totalExpenses += isset($item['amount']) ? (float) $item['amount'] : 0;
                    }
                }

                // Parse 'gasoline' JSON - only type == 'cash'
                $gasoline = $liquidation->gasoline;
                if (is_string($gasoline)) {
                    $gasoline = json_decode($gasoline, true);
                }
                if (is_array($gasoline)) {
                    foreach ($gasoline as $item) {
                        if (($item['type'] ?? '') === 'cash') {
                            $totalExpenses += isset($item['amount']) ? (float) $item['amount'] : 0;
                        }
                    }
                }

                // Parse 'rf_id' JSON - only type == 'cash'
                $rf_id = $liquidation->rf_id;
                if (is_string($rf_id)) {
                    $rf_id = json_decode($rf_id, true);
                }
                if (is_array($rf_id)) {
                    foreach ($rf_id as $item) {
                        if (($item['type'] ?? '') === 'cash') {
                            $totalExpenses += isset($item['amount']) ? (float) $item['amount'] : 0;
                        }
                    }
                }
            } else {
                $liquidation->allocations = collect(); // fallback
            }

            // Attach total expense to the liquidation instance
            $liquidation->total_expense = $totalExpenses;
        });

          return view('liquidations.rejectList', compact('liquidations'));
    }

    public function rejectEdit($id)
    {
        $liquidation = Liquidation::with('cashVoucher')->findOrFail($id);

        $employees = User::whereIn('id', [1, 41, 15, 5, 22])->get();
        $preparers = User::where('status', '!=', 0)->get();

        return view('liquidations.rejectEdit', compact('liquidation', 'preparers', 'employees'));
    }
 
    public function rejectUpdate(Request $request, $id)
    {
        // Log the raw incoming request data
        Log::info('Reject Update Request:', $request->all());

        $liquidation = Liquidation::findOrFail($id);

        $data = $request->validate([
            'allowance' => 'nullable|numeric',
            'manpower' => 'nullable|numeric',
            'hauling' => 'nullable|numeric',
            'right_of_way' => 'nullable|numeric',
            'roro_expense' => 'nullable|numeric',
            'cash_charge' => 'nullable|numeric',
            'gasoline' => 'nullable|array',
            'rfid' => 'nullable|array',
            'others' => 'nullable|array',
            'prepared_by' => 'required|exists:users,id',
            'noted_by' => 'nullable|exists:users,id',
        ]);

        // Log extracted arrays individually
        Log::info('Parsed Arrays:', [
            'gasoline' => $request->gasoline,
            'rfid' => $request->rfid,
            'others' => $request->others,
        ]);

        $liquidation->update([
            'approved_by' => $request->approved_by ?? null,
            'allowance' => $request->allowance ?? 0,
            'manpower' => $request->manpower ?? 0,
            'hauling' => $request->hauling ?? 0,
            'right_of_way' => $request->right_of_way ?? 0,
            'roro_expense' => $request->roro_expense ?? 0,
            'cash_charge' => $request->cash_charge ?? 0,
            'gasoline' => array_values($request->gasoline ?? []),
            'rfid' => array_values($request->rfid ?? []),
            'others' => array_values($request->others ?? []),
            'prepared_by' => $request->prepared_by,
            'noted_by' => $request->noted_by,
            'status' => 1,
        ]);

        Log::info('Liquidation Updated:', $liquidation->toArray());

        return redirect()->route('liquidations.rejectedList')->with('success', 'Liquidation updated successfully.');
    }

    public function Overall(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'request_code' => ['nullable', 'string', 'max:100'],
            'cvr_number' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:1,3,5,10,for_validation,for_collection,for_approval,liquidation_in_progress,for_liquidation'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $companyId = $validated['company_id'] ?? null;
        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->endOfMonth()->toDateString();
        $requestCode = $validated['request_code'] ?? null;
        $cvrNumber = trim((string) ($validated['cvr_number'] ?? ''));
        $status = $validated['status'] ?? null;
        $perPage = (int) ($validated['per_page'] ?? 25);
        $page = (int) ($validated['page'] ?? 1);

        $conditions = "WHERE DATE(cv.created_at) BETWEEN ? AND ?";
        $params = [$dateFrom, $dateTo];

        if ($requestCode) {
            $conditions .= " AND crt.request_type = ?";
            $params[] = $requestCode;
        }

        if ($companyId) {
            $conditions .= " AND (
                (cv.cvr_type IN ('admin','rpm') AND cv.company_id = ?) OR
                (cv.cvr_type NOT IN ('admin','rpm') AND dr.company_id = ?)
            )";
            $params[] = $companyId;
            $params[] = $companyId;
        }

        if ($cvrNumber !== '') {
            $conditions .= " AND cv.cvr_number LIKE ?";
            $params[] = '%' . $cvrNumber . '%';
        }

        if ($status) {
            switch ($status) {
                case '1':
                    $conditions .= " AND cv.status = 1";
                    break;
                case '3':
                    $conditions .= " AND cv.status = 3";
                    break;
                case '5':
                    $conditions .= " AND l.status = 5";
                    break;
                case '10':
                    $conditions .= " AND l.status = 10";
                    break;
                case 'for_validation':
                    $conditions .= " AND l.status = 1";
                    break;
                case 'for_collection':
                    $conditions .= " AND l.status = 3";
                    break;
                case 'for_approval':
                    $conditions .= " AND l.status = 4";
                    break;
                case 'liquidation_in_progress':
                    $conditions .= " AND l.status NOT IN (1, 3, 4, 5, 10)";
                    break;
                case 'for_liquidation':
                    $conditions .= " AND ca.status = 1 AND l.status IS NULL";
                    break;
            }
        }

        $baseSql = "
            SELECT
                cv.id AS cash_voucher_id,
                cv.cvr_type,
                cv.sequence,
                cv.cvr_number,
                CASE 
                    WHEN cv.cvr_type IN ('admin','rpm') THEN cv.truck_id 
                    ELSE a.truck_id 
                END AS truck_id,
                CASE 
                    WHEN cv.cvr_type IN ('admin','rpm') THEN cv.company_id 
                    ELSE dr.company_id 
                END AS company_id, 
                CASE 
                    WHEN cv.cvr_type IN ('admin','rpm') THEN cv.expense_type_id 
                    ELSE dr.expense_type_id 
                END AS expense_type_id,
                t.truck_name,
                c.company_code,
                et.expense_code,
                crt.request_type AS request_code,
                CASE 
                    WHEN cv.cvr_type IN ('admin','rpm') THEN (
                        SELECT SUM(CAST(JSON_UNQUOTE(amt.value) AS DECIMAL(10,2)))
                        FROM JSON_TABLE(cv.amount_details, '$[*]' COLUMNS (value JSON PATH '$')) AS amt
                    )
                    ELSE cv.amount
                END AS requested_amount,
                COALESCE((
                    SELECT SUM(amount)
                    FROM fczcnyx.cvr_approvals ca2
                    WHERE ca2.cvr_id = cv.id
                ), 0) AS approved_amount,
                COALESCE(l.allowance,0) + COALESCE(l.manpower,0) + COALESCE(l.hauling,0) +
                COALESCE(l.right_of_way,0) + COALESCE(l.roro_expense,0) +
                COALESCE((
                    SELECT SUM(CAST(j.value->>'$.amount' AS DECIMAL(10,2)))
                    FROM JSON_TABLE(l.gasoline,'$[*]' COLUMNS(value JSON PATH '$')) j
                    WHERE j.value->>'$.type' = 'cash'
                ), 0) +
                COALESCE((
                    SELECT SUM(CAST(j.value->>'$.amount' AS DECIMAL(10,2)))
                    FROM JSON_TABLE(l.rfid,'$[*]' COLUMNS(value JSON PATH '$')) j
                    WHERE j.value->>'$.type' = 'cash'
                ), 0) +
                COALESCE((
                    SELECT SUM(CAST(j.value->>'$.amount' AS DECIMAL(10,2)))
                    FROM JSON_TABLE(l.others,'$[*]' COLUMNS(value JSON PATH '$')) j
                ), 0) AS liquidated_amount_cash,
                (
                    COALESCE((
                        SELECT SUM(CAST(j.value->>'$.amount' AS DECIMAL(10,2)))
                        FROM JSON_TABLE(l.gasoline,'$[*]' COLUMNS(value JSON PATH '$')) j
                        WHERE j.value->>'$.type' = 'card'
                    ), 0) +
                    COALESCE((
                        SELECT SUM(CAST(j.value->>'$.amount' AS DECIMAL(10,2)))
                        FROM JSON_TABLE(l.rfid,'$[*]' COLUMNS(value JSON PATH '$')) j
                        WHERE j.value->>'$.type' = 'card'
                    ), 0)
                ) AS liquidated_amount_card,
                cv.status AS cash_voucher_status,
                ca.status AS approval_status,
                l.status AS liquidation_status,
                CASE
                    WHEN l.id IS NULL AND ca.status = '1' AND cv.status = 3 THEN 'Rejected CVR'
                    WHEN cv.status = 3 AND ca.status = '1' THEN 'Rejected CVR'
                    WHEN l.id IS NULL AND ca.status IS NULL AND cv.status = 3 THEN 'Rejected CVR'
                    WHEN l.id IS NOT NULL THEN
                        CASE
                            WHEN l.status = '1' THEN 'For Validation'
                            WHEN l.status = '3' THEN 'For Collection'
                            WHEN l.status = '4' THEN 'For Approval'
                            WHEN l.status = '5' THEN 'Completed'
                            WHEN l.status = '10' THEN 'Rejected Liquidation'
                            ELSE 'Liquidation In Progress'
                        END
                    WHEN ca.id IS NOT NULL THEN
                        CASE
                            WHEN ca.status = '1' THEN 'For Liquidation'
                            WHEN ca.status = '3' THEN 'Rejected CVR'
                            ELSE 'Pending Cash Approval'
                        END
                    ELSE 'Pending Cash Approval'
                END AS overall_status
            FROM fczcnyx.cash_vouchers cv
            LEFT JOIN fczcnyx.delivery_request dr ON dr.id = cv.dr_id
            LEFT JOIN (
                SELECT * FROM (
                    SELECT *, 
                        ROW_NUMBER() OVER (PARTITION BY dr_id, trip_type, sequence ORDER BY id) AS row_num
                    FROM fczcnyx.allocations
                ) AS ranked_allocations
                WHERE row_num = 1
            ) a ON a.dr_id = cv.dr_id AND a.trip_type = cv.cvr_type AND a.sequence = cv.sequence
            LEFT JOIN fczcnyx.cvr_approvals ca ON ca.cvr_id = cv.id
            LEFT JOIN fczcnyx.liquidations l ON l.cvr_approval_id = ca.id
            LEFT JOIN fczcnyx.trucks t ON t.id = CASE 
                                                    WHEN cv.cvr_type IN ('admin','rpm') THEN cv.truck_id 
                                                    ELSE a.truck_id 
                                                END
            LEFT JOIN fczcnyx.companies c ON c.id = CASE 
                                                        WHEN cv.cvr_type IN ('admin','rpm') THEN cv.company_id 
                                                        ELSE dr.company_id 
                                                    END
            LEFT JOIN fczcnyx.expense_types et ON et.id = CASE 
                                                                    WHEN cv.cvr_type IN ('admin','rpm') THEN cv.expense_type_id 
                                                                    ELSE dr.expense_type_id 
                                                                END
            LEFT JOIN fczcnyx.cvr_request_type crt ON crt.id = cv.request_type
            $conditions
        ";

        $summaryRow = DB::selectOne(
            "
                SELECT
                    COUNT(*) AS total_rows,
                    COALESCE(SUM(filtered_rows.requested_amount), 0) AS total_requested,
                    COALESCE(SUM(filtered_rows.approved_amount), 0) AS total_approved,
                    COALESCE(SUM(filtered_rows.liquidated_amount_cash), 0) AS total_cash,
                    COALESCE(SUM(filtered_rows.liquidated_amount_card), 0) AS total_card
                FROM ($baseSql) AS filtered_rows
            ",
            $params
        );

        $total = (int) ($summaryRow->total_rows ?? 0);
        $offset = max(0, ($page - 1) * $perPage);
        $cashVouchers = DB::select(
            "
                SELECT * FROM ($baseSql) AS filtered_rows
                ORDER BY filtered_rows.company_id ASC, filtered_rows.cvr_number ASC
                LIMIT ? OFFSET ?
            ",
            [...$params, $perPage, $offset]
        );

        $cashVouchers = new LengthAwarePaginator(
            collect($cashVouchers),
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $summary = [
            'requested' => (float) ($summaryRow->total_requested ?? 0),
            'approved' => (float) ($summaryRow->total_approved ?? 0),
            'cash' => (float) ($summaryRow->total_cash ?? 0),
            'card' => (float) ($summaryRow->total_card ?? 0),
            'liquidated' => (float) (($summaryRow->total_cash ?? 0) + ($summaryRow->total_card ?? 0)),
            'rows' => $total,
        ];

        $companies = Company::query()
            ->orderBy('company_code')
            ->get(['id', 'company_code', 'company_name']);

        $requestTypes = DB::table('cvr_request_type')
            ->orderBy('request_type')
            ->pluck('request_type');

        $statusOptions = [
            '1' => 'Pending Cash Approval',
            '3' => 'Rejected CVR',
            '5' => 'Completed',
            '10' => 'Rejected Liquidation',
            'for_validation' => 'For Validation',
            'for_collection' => 'For Collection',
            'for_approval' => 'For Approval',
            'for_liquidation' => 'For Liquidation',
        ];

        return view('liquidations.overall', compact(
            'cashVouchers',
            'summary',
            'companies',
            'requestTypes',
            'statusOptions',
            'perPage'
        ));
    }


} 
