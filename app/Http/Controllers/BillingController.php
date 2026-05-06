<?php

namespace App\Http\Controllers;

use App\Exports\SoaListExport;
use App\Models\Soa;
use App\Models\Company;
use App\Models\Customer;
use App\Models\DeliveryRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BillingController extends Controller
{
    // ── Table-existence cache — Schema::hasTable() hits information_schema; resolve once per process ──
    private static array $tableCache = [];
    private static array $columnCache = [];

    private function tableExists(string $table): bool
    {
        return static::$tableCache[$table] ??= Schema::hasTable($table);
    }

    private function columnExists(string $table, string $column): bool
    {
        $key = $table . '.' . $column;

        if (array_key_exists($key, static::$columnCache)) {
            return static::$columnCache[$key];
        }

        return static::$columnCache[$key] = $this->tableExists($table) && Schema::hasColumn($table, $column);
    }

    private function drTable(): string
    {
        return $this->tableExists('delivery_request') ? 'delivery_request' : 'delivery_requests';
    }

    private function getDeliveryStatusMap()
    {
        if (!$this->tableExists('delivery_status')) {
            return collect();
        }

        return DB::table('delivery_status')
            ->select('id', 'status_name')
            ->get()
            ->mapWithKeys(fn ($status) => [(int) $status->id => (string) $status->status_name]);
    }

    private function getDeliveredStatusIds($deliveryStatusMap): array
    {
        if ($deliveryStatusMap->has(1)) {
            return [1];
        }

        $matchedIds = $deliveryStatusMap
            ->filter(function ($statusName) {
                $normalizedStatusName = strtolower($statusName);
                return str_contains($normalizedStatusName, 'deliver') || str_contains($normalizedStatusName, 'complet');
            })
            ->keys()
            ->all();

        return !empty($matchedIds) ? $matchedIds : [1];
    }

    private function soaStats(): array
    {
        $raw = DB::table('soas')->selectRaw('
            COUNT(*) as total_billings,
            COALESCE(SUM(total_amount), 0) as total_amount,
            COALESCE(SUM(paid_amount), 0) as paid_amount,
            COALESCE(SUM(outstanding_amount), 0) as outstanding_amount
        ')->first();
        return (array) $raw;
    }

    public function index(Request $request)
    {
        $query = $this->buildSoaIndexQuery($request);

        $soas  = $query->orderBy('created_at', 'desc')->get();
        $stats = $this->soaStats();

        return view('billing.index', compact('soas', 'stats'));
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new SoaListExport($request->only(['company', 'date_from', 'date_to', 'status'])),
            'soa_list.xlsx'
        );
    }

    public function editSOAForm(Soa $soa)
    {
        $soa->load(['company', 'customer', 'creator']);

        $companies = Company::orderBy('company_name')->get(['id', 'company_name']);
        $customers = Customer::orderBy('name')->get(['id', 'name']);
        $attachedDeliveryRequests = $this->getAttachedDeliveryRequests($soa);
        $editableDeliveryRequests = $this->getEditableDeliveryRequests($soa, $attachedDeliveryRequests, $companies, $customers);
        $selectedDeliveryRequestIds = collect($soa->delivery_request_ids ?? [])
            ->merge(
                $attachedDeliveryRequests
            ->pluck('delivery_request_id')
            ->map(fn ($id) => (int) $id)
            )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $currentBillingSelections = $attachedDeliveryRequests
            ->mapWithKeys(fn ($request) => [(int) $request->delivery_request_id => $request->billing_type ?? 'both'])
            ->all();

        return view('billing.edit-soa', compact('soa', 'companies', 'customers', 'editableDeliveryRequests', 'selectedDeliveryRequestIds', 'currentBillingSelections'));
    }

    public function updateSOA(Request $request, Soa $soa)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'customer_id' => 'required|exists:customers,id',
            'billing_period_from' => 'required|date',
            'billing_period_to' => 'required|date|after_or_equal:billing_period_from',
            'booking_date' => 'nullable|date',
            'status' => 'required|string|in:draft,pending,approved,paid,overdue',
            'delivery_request_ids' => 'required|array|min:1',
            'delivery_request_ids.*' => 'integer',
            'item_billing' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
            'discount_type' => 'nullable|in:discount,dispute',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_remarks' => 'nullable|string|max:1000',
            'adjustment_amount' => 'nullable|numeric',
            'adjustment_remarks' => 'nullable|string|max:1000',
            'vat_amount' => 'nullable|numeric|min:0',
            'withholding_tax_rate' => 'nullable|numeric|in:0,2,5,10',
            'withholding_tax_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $deliveryRequestIds = collect($request->delivery_request_ids)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $itemBilling = collect($request->input('item_billing', []))
            ->mapWithKeys(fn ($billingType, $id) => [(int) $id => (string) $billingType]);

        if (Schema::hasTable('soa_delivery_requests')) {
            $existingBillingMap = DB::table('soa_delivery_requests')
                ->select('delivery_request_id', 'billing_type')
                ->whereIn('delivery_request_id', $deliveryRequestIds)
                ->where('soa_id', '!=', $soa->id)
                ->get()
                ->groupBy('delivery_request_id')
                ->map(function ($records) {
                    $hasDelivery = $records->contains(fn ($record) => in_array($record->billing_type, ['delivery_only', 'both'], true) || is_null($record->billing_type));
                    $hasAccessorial = $records->contains(fn ($record) => in_array($record->billing_type, ['accessorial_only', 'both'], true) || is_null($record->billing_type));

                    if ($hasDelivery && $hasAccessorial) {
                        return 'both';
                    }

                    if ($hasDelivery) {
                        return 'delivery_only';
                    }

                    if ($hasAccessorial) {
                        return 'accessorial_only';
                    }

                    return null;
                });

            $conflictingIds = collect($deliveryRequestIds)->filter(function ($id) use ($itemBilling, $existingBillingMap) {
                $selectedType = $itemBilling->get($id, 'both');
                $existingType = $existingBillingMap->get($id);

                if (!$existingType) {
                    return false;
                }

                $selectedHasDelivery = in_array($selectedType, ['delivery_only', 'both'], true);
                $selectedHasAccessorial = in_array($selectedType, ['accessorial_only', 'both'], true);
                $existingHasDelivery = in_array($existingType, ['delivery_only', 'both'], true);
                $existingHasAccessorial = in_array($existingType, ['accessorial_only', 'both'], true);

                return ($selectedHasDelivery && $existingHasDelivery)
                    || ($selectedHasAccessorial && $existingHasAccessorial);
            })->all();

            if (!empty($conflictingIds)) {
                return back()
                    ->withErrors([
                        'delivery_request_ids' => 'One or more selected delivery requests already have the same billing component attached to another SOA.',
                    ])
                    ->withInput();
            }
        }

        $deliveryRequestTable = Schema::hasTable('delivery_request') ? 'delivery_request' : 'delivery_requests';

        $selectedRequests = DB::table($deliveryRequestTable)
            ->select('id', 'mtm', 'delivery_rate')
            ->whereIn('id', $deliveryRequestIds)
            ->get()
            ->keyBy('id');

        $accessorialMap = collect();
        if (Schema::hasTable('delivery_request_line_items')) {
            [$accessorialMap] = $this->buildDeliveryRequestLineItemMaps($selectedRequests->values());
        }

        $requestSummaries = collect($deliveryRequestIds)->map(function ($id) use ($selectedRequests, $accessorialMap, $itemBilling) {
            $deliveryRateAmount = (float) optional($selectedRequests->get($id))->delivery_rate;
            $accessorialAmount = (float) ($accessorialMap->get($id) ?? 0);
            $billingType = $itemBilling->get($id, 'both');

            $amount = match ($billingType) {
                'delivery_only' => $deliveryRateAmount,
                'accessorial_only' => $accessorialAmount,
                default => $deliveryRateAmount + $accessorialAmount,
            };

            return [
                'delivery_request_id' => $id,
                'delivery_rate_amount' => $deliveryRateAmount,
                'accessorial_rate_amount' => $accessorialAmount,
                'billing_type' => $billingType,
                'amount' => $amount,
            ];
        })->filter(function ($summary) {
            $hasSelectedComponent = match ($summary['billing_type']) {
                'delivery_only' => $summary['delivery_rate_amount'] > 0,
                'accessorial_only' => $summary['accessorial_rate_amount'] > 0,
                default => ($summary['delivery_rate_amount'] + $summary['accessorial_rate_amount']) > 0,
            };

            return $hasSelectedComponent;
        })->values();

        if ($requestSummaries->isEmpty()) {
            return back()
                ->withErrors([
                    'delivery_request_ids' => 'Select at least one billable delivery request component.',
                ])
                ->withInput();
        }

        $deliveryRequestIds = $requestSummaries->pluck('delivery_request_id')->all();
        $totalAmount = $requestSummaries->sum('amount');

        DB::beginTransaction();

        try {
            $discountType         = $request->discount_type ?: null;
            $discountAmount       = (float) ($request->discount_amount ?? 0);
            $adjustmentAmount     = (float) ($request->adjustment_amount ?? 0);
            $vatAmount            = (float) ($request->vat_amount ?? 0);
            $withholdingTaxRate   = (float) ($request->withholding_tax_rate ?? 0);
            $withholdingTaxAmount = (float) ($request->withholding_tax_amount ?? 0);
            $netAmount            = $totalAmount - $discountAmount + $adjustmentAmount;
            $finalAmount          = max(0, $netAmount + $vatAmount - $withholdingTaxAmount);

            $soa->update([
                'company_id' => $request->company_id,
                'customer_id' => $request->customer_id,
                'billing_period_from' => $request->billing_period_from,
                'billing_period_to' => $request->billing_period_to,
                'statement_date' => $request->booking_date,
                'due_date' => $request->booking_date,
                'subtotal_amount' => $totalAmount,
                'discount_type' => $discountType,
                'discount_amount' => $discountAmount,
                'discount_remarks' => $request->discount_remarks,
                'adjustment_amount' => $adjustmentAmount,
                'adjustment_remarks' => $request->adjustment_remarks,
                'vat_amount' => $vatAmount,
                'withholding_tax_rate' => $withholdingTaxRate > 0 ? $withholdingTaxRate : null,
                'withholding_tax_amount' => $withholdingTaxAmount,
                'total_amount' => $finalAmount,
                'outstanding_amount' => max(0, $finalAmount - (float) $soa->paid_amount),
                'status' => $request->status,
                'notes' => $request->notes,
                'delivery_request_ids' => $deliveryRequestIds,
            ]);

            if ($this->tableExists('soa_delivery_requests')) {
                DB::table('soa_delivery_requests')->where('soa_id', $soa->id)->delete();

                if ($requestSummaries->isNotEmpty()) {
                    $now = now();
                    DB::table('soa_delivery_requests')->insert(
                        $requestSummaries->map(fn ($s) => [
                            'soa_id'                  => $soa->id,
                            'delivery_request_id'     => $s['delivery_request_id'],
                            'amount'                  => $s['amount'],
                            'delivery_rate_amount'    => $s['delivery_rate_amount'],
                            'accessorial_rate_amount' => $s['accessorial_rate_amount'],
                            'billing_type'            => $s['billing_type'],
                            'created_at'              => $now,
                            'updated_at'              => $now,
                        ])->all()
                    );
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update SOA: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('billing.showSoa', $soa->id)->with('success', 'SOA updated successfully.');
    }

    public function destroySOA(Soa $soa)
    {
        DB::beginTransaction();

        try {
            if ($this->tableExists('soa_delivery_requests')) {
                DB::table('soa_delivery_requests')->where('soa_id', $soa->id)->delete();
            }

            $soa->delete();
            DB::commit();

            return redirect()->route('billing.index')->with('success', 'SOA deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Unable to delete SOA: ' . $e->getMessage()]);
        }
    }

    public function markAsPaid(Soa $soa)
    {
        try {
            $totalAmount = (float) ($soa->total_amount ?? 0);

            $soa->update([
                'status' => 'paid',
                'paid_amount' => $totalAmount,
                'outstanding_amount' => 0,
            ]);

            return redirect()->route('billing.index')->with('success', 'SOA marked as paid successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Unable to mark SOA as paid: ' . $e->getMessage()]);
        }
    }

    public function dashboard()
    {
        return view('billing.dashboard', ['stats' => $this->soaStats()]);
    }

    public function createSOAForm(Request $request)
    {
        $companies = Company::orderBy('company_name')->get();
        $customers = Customer::orderBy('name')->get();

        $hasLineItems   = $this->tableExists('delivery_request_line_items');
        $hasSoaDR       = $this->tableExists('soa_delivery_requests');
        $deliveryRequestTable = $this->drTable();

        // Keep a minimal debug bag — no extra count/distinct queries on production
        $debug = [
            'delivery_requests_table_exists'      => true,
            'delivery_request_source_table'       => $deliveryRequestTable,
            'delivery_request_line_items_table_exists' => $hasLineItems,
            'soa_delivery_requests_table_exists'  => $hasSoaDR,
        ];

        $deliveryLineItems = collect();

        if ($hasLineItems) {
            $hasDeliveryStatusTable = $this->tableExists('delivery_status');
            $hasDeliveryStatusColumn = $this->columnExists($deliveryRequestTable, 'delivery_status');
            $hasDeliveryRequestStatusColumn = $this->columnExists($deliveryRequestTable, 'status');
            $hasDeliveryRequestCreatedAt = $this->columnExists($deliveryRequestTable, 'created_at');
            $hasLineItemCreatedAt = $this->columnExists('delivery_request_line_items', 'created_at');

            $fullyBilledIds = [];
            if ($hasSoaDR) {
                $fullyBilledIds = DB::table('soa_delivery_requests')
                    ->selectRaw('delivery_request_id')
                    ->groupBy('delivery_request_id')
                    ->havingRaw("
                        MAX(CASE WHEN billing_type IN ('delivery_only','both') OR billing_type IS NULL THEN 1 ELSE 0 END) = 1
                        AND MAX(CASE WHEN billing_type IN ('accessorial_only','both') OR billing_type IS NULL THEN 1 ELSE 0 END) = 1
                    ")
                    ->pluck('delivery_request_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }

            $deliveryStatusMap = $this->getDeliveryStatusMap();
            $deliveredStatusIds = $this->getDeliveredStatusIds($deliveryStatusMap);

            // Load every active line item attached to a delivered request that is not fully billed yet.
            $selectColumns = [
                'delivery_request_line_items.*',
                $deliveryRequestTable . '.id as delivery_request_id',
                $deliveryRequestTable . '.booking_date as delivery_request_booking_date',
                $deliveryRequestTable . '.delivery_date as delivery_request_delivery_date',
                $deliveryRequestTable . '.delivery_rate as delivery_request_amount',
                $deliveryRequestTable . '.company_id as delivery_request_company_id',
                $deliveryRequestTable . '.customer_id as delivery_request_customer_id',
                'companies.company_name as joined_company_name',
                'customers.name as joined_customer_name',
            ];

            $selectColumns[] = $hasDeliveryRequestStatusColumn
                ? $deliveryRequestTable . '.status as delivery_request_status'
                : DB::raw('NULL as delivery_request_status');

            $selectColumns[] = $hasDeliveryStatusColumn
                ? $deliveryRequestTable . '.delivery_status as delivery_request_delivery_status'
                : DB::raw('NULL as delivery_request_delivery_status');

            $deliveryLineItemsQuery = \App\Models\DeliveryRequestLineItem::query()
                ->leftJoin($deliveryRequestTable, function ($join) use ($deliveryRequestTable) {
                    $join->on($deliveryRequestTable . '.id', '=', 'delivery_request_line_items.dr_id')
                        ->orOn($deliveryRequestTable . '.mtm', '=', 'delivery_request_line_items.mtm');
                })
                ->leftJoin('companies', 'companies.id', '=', $deliveryRequestTable . '.company_id')
                ->leftJoin('customers', 'customers.id', '=', $deliveryRequestTable . '.customer_id')
                ->select($selectColumns)
                ->where('delivery_request_line_items.status', '1');

            if ($hasDeliveryStatusTable && $hasDeliveryStatusColumn) {
                $deliveryLineItemsQuery
                    ->leftJoin('delivery_status as request_delivery_status', 'request_delivery_status.id', '=', $deliveryRequestTable . '.delivery_status')
                    ->addSelect('request_delivery_status.status_name as joined_delivery_status_name')
                    ->whereIn($deliveryRequestTable . '.delivery_status', $deliveredStatusIds);
            } elseif ($hasDeliveryStatusColumn) {
                $deliveryLineItemsQuery->where($deliveryRequestTable . '.delivery_status', 1);
            } elseif ($hasDeliveryRequestStatusColumn) {
                $deliveryLineItemsQuery->where(function ($query) use ($deliveryRequestTable) {
                    $query->where($deliveryRequestTable . '.status', '1')
                        ->orWhere($deliveryRequestTable . '.status', 'like', '%deliver%')
                        ->orWhere($deliveryRequestTable . '.status', 'like', '%complet%');
                });
            } else {
                $deliveryLineItemsQuery->whereRaw('1 = 0');
            }

            $deliveryLineItemsQuery
                ->when(!empty($fullyBilledIds), function ($query) use ($deliveryRequestTable, $fullyBilledIds) {
                    $query->whereNotIn($deliveryRequestTable . '.id', $fullyBilledIds);
                });

            if ($hasDeliveryRequestCreatedAt) {
                $deliveryLineItemsQuery->orderByDesc($deliveryRequestTable . '.created_at');
            } else {
                $deliveryLineItemsQuery->orderByDesc($deliveryRequestTable . '.id');
            }

            if ($hasLineItemCreatedAt) {
                $deliveryLineItemsQuery->orderByDesc('delivery_request_line_items.created_at');
            } else {
                $deliveryLineItemsQuery->orderByDesc('delivery_request_line_items.id');
            }

            $deliveryLineItems = $deliveryLineItemsQuery->get();

            if ($hasSoaDR && $deliveryLineItems->isNotEmpty()) {
                $deliveryRequestIdsInView = $deliveryLineItems
                    ->map(fn ($item) => (int) ($item->delivery_request_id ?: $item->dr_id))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $usedBillingMap = DB::table('soa_delivery_requests')
                    ->selectRaw("delivery_request_id,
                        MAX(CASE WHEN billing_type IN ('delivery_only','both') OR billing_type IS NULL THEN 1 ELSE 0 END) as has_delivery,
                        MAX(CASE WHEN billing_type IN ('accessorial_only','both') OR billing_type IS NULL THEN 1 ELSE 0 END) as has_accessorial")
                    ->whereIn('delivery_request_id', $deliveryRequestIdsInView)
                    ->groupBy('delivery_request_id')
                    ->get()
                    ->mapWithKeys(function ($row) {
                        $type = ($row->has_delivery && $row->has_accessorial) ? 'both'
                            : ($row->has_delivery ? 'delivery_only'
                            : ($row->has_accessorial ? 'accessorial_only' : null));

                        return [(int) $row->delivery_request_id => $type];
                    });

                $deliveryLineItems = $deliveryLineItems
                    ->map(function ($item) use ($usedBillingMap) {
                        $requestId = (int) ($item->delivery_request_id ?: $item->dr_id);
                        $item->already_billed = $requestId ? $usedBillingMap->get($requestId) : null;
                        return $item;
                    })
                    ->filter(function ($item) {
                        $alreadyBilled = $item->already_billed;
                        if (!$alreadyBilled) {
                            return true;
                        }

                        $drAmt  = (float) ($item->delivery_request_amount ?? 0);
                        $acRate = is_array($item->accessorial_rate) ? array_sum(array_map('floatval', (array) $item->accessorial_rate)) : (float) ($item->accessorial_rate ?? 0);
                        $addOn  = is_array($item->add_on_rate)      ? array_sum(array_map('floatval', (array) $item->add_on_rate))      : (float) ($item->add_on_rate ?? 0);
                        $acAmt  = $acRate + $addOn;

                        // If delivery is the only available component and it's already billed, hide this item
                        if ($alreadyBilled === 'delivery_only' && $acAmt === 0.0) {
                            return false;
                        }

                        // If accessorial is the only available component and it's already billed, hide this item
                        if ($alreadyBilled === 'accessorial_only' && $drAmt === 0.0) {
                            return false;
                        }

                        return true;
                    })
                    ->values();
            }
        } else {
            // Mock data for testing when tables don't exist
            $deliveryLineItems = collect([
                (object) [
                    'id' => 1,
                    'mtm' => 'MTM2026041600898',
                    'accessorial_rate' => [2400.00], // Keep as array to match model casting
                    'add_on_rate' => [1000.00], // Keep as array to match model casting
                    'site_name' => ['56A0JNM/Philippines Smart LTE Project 2023'],
                    'deliveryRequest' => (object) [
                        'delivery_date' => '2026-04-16',
                        'company_id' => '2',
                        'customer_id' => '1',
                        'company' => (object) [
                            'company_name' => 'Sample Company'
                        ],
                        'customer' => (object) [
                            'name' => 'Sample Customer'
                        ]
                    ]
                ],
                (object) [
                    'id' => 2,
                    'mtm' => 'MTM2026041700456',
                    'accessorial_rate' => [1500.00],
                    'add_on_rate' => [500.00],
                    'site_name' => ['Another Project Site'],
                    'deliveryRequest' => (object) [
                        'delivery_date' => '2026-04-17',
                        'company_id' => '2',
                        'customer_id' => '1',
                        'company' => (object) [
                            'company_name' => 'Sample Company'
                        ],
                        'customer' => (object) [
                            'name' => 'Sample Customer'
                        ]
                    ]
                ],
                (object) [
                    'id' => 3,
                    'mtm' => 'MTM2026041900789',
                    'accessorial_rate' => [3200.00],
                    'add_on_rate' => [800.00],
                    'site_name' => ['Third Project Location'],
                    'deliveryRequest' => (object) [
                        'delivery_date' => '2026-04-19',
                        'company_id' => '3',
                        'customer_id' => '2',
                        'company' => (object) [
                            'company_name' => 'Another Company'
                        ],
                        'customer' => (object) [
                            'name' => 'Another Customer'
                        ]
                    ]
                ]
            ]);
        }

        $companyItemCounts = $deliveryLineItems->groupBy('delivery_request_company_id')->map->count();
        $customerItemCounts = $deliveryLineItems->groupBy('delivery_request_customer_id')->map->count();

        return view('billing.create-soa', compact('companies', 'customers', 'deliveryLineItems', 'debug', 'companyItemCounts', 'customerItemCounts'));
    }

    public function createSOA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required_without:customer_id|exists:companies,id',
            'customer_id' => 'required_without:company_id|exists:customers,id',
            'billing_period_from' => 'required|date',
            'billing_period_to' => 'required|date|after_or_equal:billing_period_from',
            'booking_date' => 'nullable|date',
            'delivery_line_item_ids' => 'required|array|min:1',
            'delivery_line_item_ids.*' => 'exists:delivery_request_line_items,id',
            'notes' => 'nullable|string|max:1000',
            'discount_type' => 'nullable|in:discount,dispute',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_remarks' => 'nullable|string|max:1000',
            'adjustment_amount' => 'nullable|numeric',
            'adjustment_remarks' => 'nullable|string|max:1000',
            'vat_amount' => 'nullable|numeric|min:0',
            'withholding_tax_rate' => 'nullable|numeric|in:0,2,5,10',
            'withholding_tax_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate SOA number
            $soaNumber = $this->generateSoaNumber();

            // Calculate total amount from selected delivery request line items
            $deliveryLineItemIds = $request->delivery_line_item_ids;
            $deliveryRequestTable = Schema::hasTable('delivery_request') ? 'delivery_request' : 'delivery_requests';

            $selectedLineItems = \App\Models\DeliveryRequestLineItem::query()
                ->leftJoin($deliveryRequestTable, function ($join) use ($deliveryRequestTable) {
                    $join->on($deliveryRequestTable . '.id', '=', 'delivery_request_line_items.dr_id')
                        ->orOn($deliveryRequestTable . '.mtm', '=', 'delivery_request_line_items.mtm');
                })
                ->select([
                    'delivery_request_line_items.id',
                    'delivery_request_line_items.dr_id',
                    'delivery_request_line_items.mtm',
                    'delivery_request_line_items.accessorial_rate',
                    'delivery_request_line_items.add_on_rate',
                    $deliveryRequestTable . '.id as delivery_request_id',
                    $deliveryRequestTable . '.delivery_rate as delivery_request_amount',
                ])
                ->whereIn('delivery_request_line_items.id', $deliveryLineItemIds)
                ->get();

            $itemBilling = $request->input('item_billing', []);

            $requestSummaries = $selectedLineItems
                ->groupBy(function ($lineItem) {
                    return $lineItem->delivery_request_id ?: $lineItem->dr_id ?: $lineItem->mtm;
                })
                ->map(function ($group) use ($itemBilling) {
                    $firstItem = $group->first();
                    $requestId = $firstItem->delivery_request_id ?: $firstItem->dr_id;

                    // Compute per-component amounts for the group
                    $deliveryRateAmt = 0;
                    $accessorialAmt  = 0;
                    foreach ($group as $lineItem) {
                        $drAmt = (float) ($lineItem->delivery_request_amount ?? 0);
                        $acRate = is_array($lineItem->accessorial_rate) ? array_sum($lineItem->accessorial_rate) : (float) ($lineItem->accessorial_rate ?? 0);
                        $addOn  = is_array($lineItem->add_on_rate)      ? array_sum($lineItem->add_on_rate)      : (float) ($lineItem->add_on_rate ?? 0);
                        $deliveryRateAmt += $drAmt;
                        $accessorialAmt  += $acRate + $addOn;
                    }

                    // Determine billing type from JS selection (keyed by line item id)
                    $lineItemId  = $firstItem->id;
                    $billingType = $itemBilling[$lineItemId] ?? 'both';

                    $amount = match ($billingType) {
                        'delivery_only'    => $deliveryRateAmt,
                        'accessorial_only' => $accessorialAmt,
                        default            => $deliveryRateAmt > 0 ? ($deliveryRateAmt + $accessorialAmt) : $accessorialAmt,
                    };

                    return [
                        'delivery_request_id'    => $requestId,
                        'amount'                 => $amount,
                        'delivery_rate_amount'   => $deliveryRateAmt,
                        'accessorial_rate_amount'=> $accessorialAmt,
                        'billing_type'           => $billingType,
                    ];
                })
                ->filter(fn ($summary) => !empty($summary['delivery_request_id']))
                ->values();

            $subtotalAmount = $requestSummaries->sum('amount');
            $deliveryRequestIds = $requestSummaries->pluck('delivery_request_id')->map(fn ($id) => (int) $id)->all();

            $discountType         = $request->discount_type ?: null;
            $discountAmount       = (float) ($request->discount_amount ?? 0);
            $adjustmentAmount     = (float) ($request->adjustment_amount ?? 0);
            $vatAmount            = (float) ($request->vat_amount ?? 0);
            $withholdingTaxRate   = (float) ($request->withholding_tax_rate ?? 0);
            $withholdingTaxAmount = (float) ($request->withholding_tax_amount ?? 0);
            $netAmount            = $subtotalAmount - $discountAmount + $adjustmentAmount;
            $totalAmount          = max(0, $netAmount + $vatAmount - $withholdingTaxAmount);

            // Create SOA
            $soa = Soa::create([
                'soa_number' => $soaNumber,
                'company_id' => $request->company_id,
                'customer_id' => $request->customer_id,
                'billing_period_from' => $request->billing_period_from,
                'billing_period_to' => $request->billing_period_to,
                'statement_date' => $request->booking_date,
                'due_date' => $request->booking_date,
                'subtotal_amount' => $subtotalAmount,
                'discount_type' => $discountType,
                'discount_amount' => $discountAmount,
                'discount_remarks' => $request->discount_remarks,
                'adjustment_amount' => $adjustmentAmount,
                'adjustment_remarks' => $request->adjustment_remarks,
                'vat_amount' => $vatAmount,
                'withholding_tax_rate' => $withholdingTaxRate > 0 ? $withholdingTaxRate : null,
                'withholding_tax_amount' => $withholdingTaxAmount,
                'total_amount' => $totalAmount,
                'outstanding_amount' => $totalAmount,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'delivery_request_ids' => $deliveryRequestIds,
            ]);

            if ($this->tableExists('soa_delivery_requests') && $requestSummaries->isNotEmpty()) {
                $now = now();
                DB::table('soa_delivery_requests')->insert(
                    $requestSummaries->map(fn ($s) => [
                        'soa_id'                  => $soa->id,
                        'delivery_request_id'     => $s['delivery_request_id'],
                        'amount'                  => $s['amount'],
                        'delivery_rate_amount'    => $s['delivery_rate_amount'],
                        'accessorial_rate_amount' => $s['accessorial_rate_amount'],
                        'billing_type'            => $s['billing_type'],
                        'created_at'              => $now,
                        'updated_at'              => $now,
                    ])->all()
                );
            }

            DB::commit();

            return redirect()->route('billing.showSoa', $soa->id)
                           ->with('success', 'SOA created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create SOA: ' . $e->getMessage()])->withInput();
        }
    }

    public function showSoa(Soa $soa)
    {
        $soa->load(['company', 'customer', 'creator']);

        $attachedDeliveryRequests = $this->getAttachedDeliveryRequests($soa);

        return view('billing.show-soa', compact('soa', 'attachedDeliveryRequests'));
    }

    public function print($id)
    {
        $soa = Soa::with(['company', 'customer', 'creator'])->findOrFail($id);

        $attachedDeliveryRequests = $this->getAttachedDeliveryRequests($soa);

        return view('billing.print', compact('soa', 'attachedDeliveryRequests'));
    }

    public function downloadPdf($id)
    {
        $soa = Soa::with(['company', 'customer', 'creator'])->findOrFail($id);
        $attachedDeliveryRequests = $this->getAttachedDeliveryRequests($soa);

        $pdf = Pdf::loadView('billing.pdf', compact('soa', 'attachedDeliveryRequests'))
            ->setPaper('a4', 'portrait');

        return $pdf->download(($soa->soa_number ?? 'soa') . '.pdf');
    }

    private function getAttachedDeliveryRequests(Soa $soa)
    {
        if (!$this->tableExists('soa_delivery_requests')) {
            return collect();
        }

        $deliveryRequestTable = $this->drTable();

        return DB::table('soa_delivery_requests')
            ->join($deliveryRequestTable, $deliveryRequestTable . '.id', '=', 'soa_delivery_requests.delivery_request_id')
            ->leftJoin('companies', 'companies.id', '=', $deliveryRequestTable . '.company_id')
            ->leftJoin('customers', 'customers.id', '=', $deliveryRequestTable . '.customer_id')
            ->select([
                'soa_delivery_requests.delivery_request_id',
                'soa_delivery_requests.amount',
                'soa_delivery_requests.delivery_rate_amount',
                'soa_delivery_requests.accessorial_rate_amount',
                'soa_delivery_requests.billing_type',
                $deliveryRequestTable . '.mtm',
                $deliveryRequestTable . '.booking_date',
                $deliveryRequestTable . '.delivery_date',
                'companies.company_name',
                'customers.name as customer_name',
            ])
            ->where('soa_delivery_requests.soa_id', $soa->id)
            ->orderBy($deliveryRequestTable . '.delivery_date')
            ->get();
    }

    private function getEditableDeliveryRequests(Soa $soa, $attachedDeliveryRequests = null, $companies = null, $customers = null)
    {
        if (!$this->tableExists('soa_delivery_requests')) {
            return collect($soa->delivery_request_ids ?? []);
        }

        $deliveryRequestTable = $this->drTable();
        $attachedDeliveryRequests = $attachedDeliveryRequests ?: $this->getAttachedDeliveryRequests($soa);
        $companyNameMap = ($companies ?: Company::query()->get(['id', 'company_name']))->pluck('company_name', 'id');
        $customerNameMap = ($customers ?: Customer::query()->get(['id', 'name']))->pluck('name', 'id');
        $hasDeliveryStatusColumn = $this->columnExists($deliveryRequestTable, 'delivery_status');
        $hasDeliveryRequestStatusColumn = $this->columnExists($deliveryRequestTable, 'status');
        $hasDeliveryRequestCreatedAt = $this->columnExists($deliveryRequestTable, 'created_at');

        $selectedIds = collect($soa->delivery_request_ids ?? [])
            ->merge($attachedDeliveryRequests->pluck('delivery_request_id'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $currentBillingMap = $attachedDeliveryRequests->keyBy('delivery_request_id');

        // Use SQL GROUP BY/HAVING — DB returns only IDs, no PHP-side row grouping needed
        $deliveryStatusMap = $this->getDeliveryStatusMap();
        $deliveredStatusIds = $this->getDeliveredStatusIds($deliveryStatusMap);

        $selectColumns = [
            $deliveryRequestTable . '.id',
            $deliveryRequestTable . '.mtm',
            $deliveryRequestTable . '.booking_date',
            $deliveryRequestTable . '.delivery_date',
            $deliveryRequestTable . '.delivery_rate',
            $deliveryRequestTable . '.company_id',
            $deliveryRequestTable . '.customer_id',
        ];

        $selectColumns[] = $hasDeliveryStatusColumn
            ? $deliveryRequestTable . '.delivery_status'
            : DB::raw('NULL as delivery_status');

        $resultQuery = DB::table($deliveryRequestTable)
            ->select($selectColumns)
            ->where(function ($query) use ($deliveryRequestTable, $deliveredStatusIds, $selectedIds, $hasDeliveryStatusColumn, $hasDeliveryRequestStatusColumn) {
                $query->where(function ($eligibleQuery) use ($deliveryRequestTable, $deliveredStatusIds, $hasDeliveryStatusColumn, $hasDeliveryRequestStatusColumn) {
                    if ($hasDeliveryStatusColumn && !empty($deliveredStatusIds)) {
                        $eligibleQuery->whereIn($deliveryRequestTable . '.delivery_status', $deliveredStatusIds);
                    } elseif ($hasDeliveryStatusColumn) {
                        $eligibleQuery->where($deliveryRequestTable . '.delivery_status', 1);
                    } elseif ($hasDeliveryRequestStatusColumn) {
                        $eligibleQuery->where(function ($fallbackQuery) use ($deliveryRequestTable) {
                            $fallbackQuery->where($deliveryRequestTable . '.status', '1')
                                ->orWhere($deliveryRequestTable . '.status', 'like', '%deliver%')
                                ->orWhere($deliveryRequestTable . '.status', 'like', '%complet%');
                        });
                    } else {
                        $eligibleQuery->whereRaw('1 = 0');
                    }
                });

                if (!empty($selectedIds)) {
                    $query->orWhereIn($deliveryRequestTable . '.id', $selectedIds);
                }
            })
            ->orderByRaw('CASE WHEN ' . $deliveryRequestTable . '.id IN (' . (count($selectedIds) ? implode(',', $selectedIds) : '0') . ') THEN 0 ELSE 1 END');

        if ($hasDeliveryRequestCreatedAt) {
            $resultQuery->orderByDesc($deliveryRequestTable . '.created_at');
        } else {
            $resultQuery->orderByDesc($deliveryRequestTable . '.id');
        }

        $result = $resultQuery
            ->orderByDesc($deliveryRequestTable . '.delivery_date')
            ->get()
            ->values();

        // Load billing types only for IDs in this result set — not the whole table
        $usedElsewhereMap = collect();
        if ($result->isNotEmpty()) {
            $resultIds = $result->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();
            $usedElsewhereMap = DB::table('soa_delivery_requests')
                ->selectRaw("delivery_request_id,
                    MAX(CASE WHEN billing_type IN ('delivery_only','both') OR billing_type IS NULL THEN 1 ELSE 0 END) as has_delivery,
                    MAX(CASE WHEN billing_type IN ('accessorial_only','both') OR billing_type IS NULL THEN 1 ELSE 0 END) as has_accessorial")
                ->whereIn('delivery_request_id', $resultIds)
                ->where('soa_id', '!=', $soa->id)
                ->groupBy('delivery_request_id')
                ->get()
                ->mapWithKeys(function ($row) {
                    $type = ($row->has_delivery && $row->has_accessorial) ? 'both'
                        : ($row->has_delivery    ? 'delivery_only'
                        : ($row->has_accessorial ? 'accessorial_only' : null));
                    return [(int) $row->delivery_request_id => $type];
                });

            $selectedIdLookup = array_fill_keys($selectedIds, true);
            $result = $result->filter(function ($item) use ($usedElsewhereMap, $selectedIdLookup) {
                if (isset($selectedIdLookup[(int) $item->id])) {
                    return true;
                }

                return $usedElsewhereMap->get((int) $item->id) !== 'both';
            })->values();
        }

        // Attach accessorial totals, site names, company/customer names, and billing flags
        if ($this->tableExists('delivery_request_line_items') && $result->isNotEmpty()) {
            [$accessorialMap, $siteMap] = $this->buildDeliveryRequestLineItemMaps($result);
        } else {
            [$accessorialMap, $siteMap] = [collect(), collect()];
        }

        $result = $result->map(function ($item) use ($accessorialMap, $siteMap, $usedElsewhereMap, $currentBillingMap, $companyNameMap, $customerNameMap, $deliveryStatusMap) {
            $currentBilling = $currentBillingMap->get($item->id);
            $item->company_name         = $companyNameMap->get((int) $item->company_id, 'N/A');
            $item->customer_name        = $customerNameMap->get((int) $item->customer_id, 'N/A');
            $item->status_name          = $deliveryStatusMap->get((int) $item->delivery_status, 'Delivered');
            $item->accessorial_total    = (float) ($accessorialMap->get($item->id) ?? 0);
            $item->site_name            = $siteMap->get($item->id, '');
            $item->already_billed       = $usedElsewhereMap->get($item->id);
            $item->current_billing_type = $currentBilling ? ($currentBilling->billing_type ?? 'both') : null;
            return $item;
        });

        // Remove items where the only available billing component is already billed elsewhere
        $result = $result->filter(function ($item) use ($selectedIdLookup) {
            if (isset($selectedIdLookup[(int) $item->id])) {
                return true;
            }

            $alreadyBilled = $item->already_billed;
            if (!$alreadyBilled) {
                return true;
            }

            $drAmt = (float) ($item->delivery_rate ?? 0);
            $acAmt = (float) ($item->accessorial_total ?? 0);

            if ($alreadyBilled === 'delivery_only'    && $acAmt === 0.0) return false;
            if ($alreadyBilled === 'accessorial_only' && $drAmt === 0.0) return false;

            return true;
        })->values();

        return $result;
    }

    private function buildDeliveryRequestLineItemMaps($deliveryRequests)
    {
        $requests = collect($deliveryRequests)->filter(fn ($request) => filled($request->id))->values();

        if ($requests->isEmpty() || !$this->tableExists('delivery_request_line_items')) {
            return [collect(), collect()];
        }

        $requestIds = $requests->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        $mtmToRequestId = $requests->filter(fn ($request) => filled($request->mtm))
            ->mapWithKeys(fn ($request) => [(string) $request->mtm => (int) $request->id])
            ->all();
        $mtms = array_keys($mtmToRequestId);

        if (empty($requestIds) && empty($mtms)) {
            return [collect(), collect()];
        }

        $lineItems = collect();
        $lineItemColumns = ['dr_id', 'mtm', 'accessorial_rate', 'add_on_rate', 'site_name'];

        $matchedRequestIds = [];

        if (!empty($requestIds)) {
            $directLineItems = DB::table('delivery_request_line_items')
                ->select($lineItemColumns)
                ->whereIn('dr_id', $requestIds)
                ->get();

            $lineItems = $lineItems->concat($directLineItems);
            $matchedRequestIds = $directLineItems
                ->pluck('dr_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        if (!empty($mtms)) {
            $fallbackMtms = collect($mtmToRequestId)
                ->reject(fn ($requestId) => in_array($requestId, $matchedRequestIds, true))
                ->keys()
                ->values()
                ->all();

            if (!empty($fallbackMtms)) {
                $lineItems = $lineItems->concat(
                    DB::table('delivery_request_line_items')
                        ->select($lineItemColumns)
                        ->whereNull('dr_id')
                        ->whereIn('mtm', $fallbackMtms)
                        ->get()
                );
            }
        }

        if ($lineItems->isEmpty()) {
            return [collect(), collect()];
        }

        $accessorialTotals = [];
        $siteNames = [];

        foreach ($lineItems as $lineItem) {
            $requestId = $lineItem->dr_id
                ? (int) $lineItem->dr_id
                : ($mtmToRequestId[(string) $lineItem->mtm] ?? null);

            if (!$requestId) {
                continue;
            }

            $accessorial = $this->normalizeNumericField($lineItem->accessorial_rate ?? 0);
            $addOn = $this->normalizeNumericField($lineItem->add_on_rate ?? 0);
            $accessorialTotals[$requestId] = ($accessorialTotals[$requestId] ?? 0) + $accessorial + $addOn;

            foreach ($this->normalizeStringListField($lineItem->site_name ?? null) as $site) {
                if ($site === '') {
                    continue;
                }
                $siteNames[$requestId][$site] = true;
            }
        }

        return [
            collect($accessorialTotals),
            collect($siteNames)->map(fn ($sites) => implode(', ', array_keys($sites))),
        ];
    }

    private function normalizeNumericField($value): float
    {
        if (is_array($value)) {
            return array_sum(array_map(fn ($item) => (float) $item, $value));
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $trimmedValue = trim($value);
            $decoded = json_decode($trimmedValue, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return array_sum(array_map(fn ($item) => (float) $item, $decoded));
            }

            return is_numeric($trimmedValue) ? (float) $trimmedValue : 0.0;
        }

        return 0.0;
    }

    private function normalizeStringListField($value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->values()
                ->all();
        }

        if (is_string($value)) {
            $trimmedValue = trim($value);
            $decoded = json_decode($trimmedValue, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return collect($decoded)
                    ->map(fn ($item) => trim((string) $item))
                    ->filter()
                    ->values()
                    ->all();
            }

            return $trimmedValue !== '' ? [$trimmedValue] : [];
        }

        return [];
    }

    private function buildSoaIndexQuery(Request $request)
    {
        $query = Soa::with(['company', 'customer', 'creator']);

        if ($request->filled('company')) {
            $query->whereHas('company', function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->company . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->where('billing_period_from', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('billing_period_to', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    private function generateSoaNumber()
    {
        $date = now()->format('Ymd');
        $lastSoa = Soa::where('soa_number', 'like', "SOA-{$date}%")
                     ->orderBy('soa_number', 'desc')
                     ->first();

        if ($lastSoa) {
            $lastNumber = (int) substr($lastSoa->soa_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "SOA-{$date}-{$newNumber}";
    }

    // Placeholder methods for accessorial SOA (to be implemented)
    public function indexAccessorial()
    {
        return view('billing.accessorial');
    }

    public function createSOAFormAccessorial()
    {
        return view('billing.create-accessorial-soa');
    }

    public function createAccessorialSOA(Request $request)
    {
        // Implementation for accessorial SOA
        return back()->with('info', 'Accessorial SOA creation not yet implemented');
    }
}
