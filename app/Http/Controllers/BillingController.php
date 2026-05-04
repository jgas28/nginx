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
    public function index(Request $request)
    {
        $query = $this->buildSoaIndexQuery($request);

        $soas = $query->orderBy('created_at', 'desc')->get();

        // Calculate stats
        $stats = [
            'total_billings' => Soa::count(),
            'total_amount' => Soa::sum('total_amount'),
            'paid_amount' => Soa::sum('paid_amount'),
            'outstanding_amount' => Soa::sum('outstanding_amount'),
        ];

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

        $editableDeliveryRequests = $this->getEditableDeliveryRequests($soa);
        $selectedDeliveryRequestIds = collect($soa->delivery_request_ids ?? [])
            ->merge(
                $this->getAttachedDeliveryRequests($soa)
            ->pluck('delivery_request_id')
            ->map(fn ($id) => (int) $id)
            )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return view('billing.edit-soa', compact('soa', 'editableDeliveryRequests', 'selectedDeliveryRequestIds'));
    }

    public function updateSOA(Request $request, Soa $soa)
    {
        $validator = Validator::make($request->all(), [
            'billing_period_from' => 'required|date',
            'billing_period_to' => 'required|date|after_or_equal:billing_period_from',
            'booking_date' => 'nullable|date',
            'status' => 'required|string|in:draft,pending,approved,paid,overdue',
            'delivery_request_ids' => 'required|array|min:1',
            'delivery_request_ids.*' => 'integer',
            'notes' => 'nullable|string|max:1000',
            'discount_type' => 'nullable|in:discount,dispute',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_remarks' => 'nullable|string|max:1000',
            'adjustment_amount' => 'nullable|numeric',
            'adjustment_remarks' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $deliveryRequestIds = collect($request->delivery_request_ids)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (Schema::hasTable('soa_delivery_requests')) {
            $existingAttachedIds = DB::table('soa_delivery_requests')
                ->whereIn('delivery_request_id', $deliveryRequestIds)
                ->where('soa_id', '!=', $soa->id)
                ->pluck('delivery_request_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (!empty($existingAttachedIds)) {
                return back()
                    ->withErrors([
                        'delivery_request_ids' => 'One or more selected delivery requests are already attached to another SOA.',
                    ])
                    ->withInput();
            }
        }

        $deliveryRequestTable = Schema::hasTable('delivery_request') ? 'delivery_request' : 'delivery_requests';

        $selectedRequests = DB::table($deliveryRequestTable)
            ->select('id', 'delivery_rate')
            ->whereIn('id', $deliveryRequestIds)
            ->get()
            ->keyBy('id');

        $totalAmount = collect($deliveryRequestIds)->sum(function ($id) use ($selectedRequests) {
            return (float) optional($selectedRequests->get($id))->delivery_rate;
        });

        DB::beginTransaction();

        try {
            $discountType     = $request->discount_type ?: null;
            $discountAmount   = (float) ($request->discount_amount ?? 0);
            $adjustmentAmount = (float) ($request->adjustment_amount ?? 0);
            $finalAmount      = max(0, $totalAmount - $discountAmount + $adjustmentAmount);

            $soa->update([
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
                'total_amount' => $finalAmount,
                'outstanding_amount' => max(0, $finalAmount - (float) $soa->paid_amount),
                'status' => $request->status,
                'notes' => $request->notes,
                'delivery_request_ids' => $deliveryRequestIds,
            ]);

            if (Schema::hasTable('soa_delivery_requests')) {
                DB::table('soa_delivery_requests')->where('soa_id', $soa->id)->delete();

                foreach ($deliveryRequestIds as $deliveryRequestId) {
                    DB::table('soa_delivery_requests')->insert([
                        'soa_id' => $soa->id,
                        'delivery_request_id' => $deliveryRequestId,
                        'amount' => (float) optional($selectedRequests->get($deliveryRequestId))->delivery_rate,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
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
            if (Schema::hasTable('soa_delivery_requests')) {
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
        // Calculate stats for dashboard
        $stats = [
            'total_billings' => Soa::count(),
            'total_amount' => Soa::sum('total_amount'),
            'paid_amount' => Soa::sum('paid_amount'),
            'outstanding_amount' => Soa::sum('outstanding_amount'),
        ];

        return view('billing.dashboard', compact('stats'));
    }

    public function createSOAForm(Request $request)
    {
        $companies = Company::orderBy('company_name')->get();
        $customers = Customer::orderBy('name')->get();

        // Debug information
        $debug = [
            'delivery_requests_table_exists' => Schema::hasTable('delivery_requests') || Schema::hasTable('delivery_request'),
            'delivery_request_source_table' => Schema::hasTable('delivery_request') ? 'delivery_request' : 'delivery_requests',
            'delivery_request_line_items_table_exists' => Schema::hasTable('delivery_request_line_items'),
            'soa_delivery_requests_table_exists' => Schema::hasTable('soa_delivery_requests'),
        ];

        if ($debug['delivery_requests_table_exists']) {
            $deliveryRequestTable = $debug['delivery_request_source_table'];
            $debug['total_delivery_requests'] = DB::table($deliveryRequestTable)->count();
            $debug['delivery_request_statuses'] = DB::table($deliveryRequestTable)->select('status')->distinct()->pluck('status')->toArray();
        }

        if ($debug['delivery_request_line_items_table_exists']) {
            $debug['total_line_items'] = \App\Models\DeliveryRequestLineItem::count();
        }

        // Get delivery request line items that are not yet included in any SOA
        $deliveryLineItems = collect(); // Start with empty collection

        if ($debug['delivery_request_line_items_table_exists']) {
            $deliveryRequestTable = $debug['delivery_request_source_table'];

            // Pull display data from the delivery request source table used by the live database.
            $deliveryLineItems = \App\Models\DeliveryRequestLineItem::query()
                ->leftJoin($deliveryRequestTable, function ($join) use ($deliveryRequestTable) {
                    $join->on($deliveryRequestTable . '.id', '=', 'delivery_request_line_items.dr_id')
                        ->orOn($deliveryRequestTable . '.mtm', '=', 'delivery_request_line_items.mtm');
                })
                ->leftJoin('companies', 'companies.id', '=', $deliveryRequestTable . '.company_id')
                ->leftJoin('customers', 'customers.id', '=', $deliveryRequestTable . '.customer_id')
                ->leftJoin('delivery_status as request_delivery_status', 'request_delivery_status.id', '=', $deliveryRequestTable . '.delivery_status')
                ->with('deliveryStatus')
                ->select([
                    'delivery_request_line_items.*',
                    $deliveryRequestTable . '.booking_date as delivery_request_booking_date',
                    $deliveryRequestTable . '.delivery_date as delivery_request_delivery_date',
                    $deliveryRequestTable . '.delivery_rate as delivery_request_amount',
                    $deliveryRequestTable . '.status as delivery_request_status',
                    $deliveryRequestTable . '.delivery_status as delivery_request_delivery_status',
                    $deliveryRequestTable . '.company_id as delivery_request_company_id',
                    $deliveryRequestTable . '.customer_id as delivery_request_customer_id',
                    'companies.company_name as joined_company_name',
                    'customers.name as joined_customer_name',
                    'request_delivery_status.status_name as joined_delivery_status_name',
                ])
                ->where('delivery_request_line_items.status', '1')
                ->orderBy('delivery_request_line_items.created_at', 'desc')
                ->limit(100) // Limit results to prevent timeout
                ->get();

            // Filter out items already in SOAs if the pivot table exists
            if ($debug['soa_delivery_requests_table_exists']) {
                $usedDeliveryRequestIds = DB::table('soa_delivery_requests')->pluck('delivery_request_id')->toArray();
                $deliveryLineItems = $deliveryLineItems->filter(function ($item) use ($usedDeliveryRequestIds) {
                    $deliveryRequestId = $item->dr_id ?: null;
                    return !$deliveryRequestId || !in_array($deliveryRequestId, $usedDeliveryRequestIds);
                });
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

            $discountType    = $request->discount_type ?: null;
            $discountAmount  = (float) ($request->discount_amount ?? 0);
            $adjustmentAmount = (float) ($request->adjustment_amount ?? 0);
            $totalAmount = max(0, $subtotalAmount - $discountAmount + $adjustmentAmount);

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
                'total_amount' => $totalAmount,
                'outstanding_amount' => $totalAmount,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'delivery_request_ids' => $deliveryRequestIds,
            ]);

            if (Schema::hasTable('soa_delivery_requests')) {
                foreach ($requestSummaries as $summary) {
                    DB::table('soa_delivery_requests')->insert([
                        'soa_id'                  => $soa->id,
                        'delivery_request_id'     => $summary['delivery_request_id'],
                        'amount'                  => $summary['amount'],
                        'delivery_rate_amount'    => $summary['delivery_rate_amount'],
                        'accessorial_rate_amount' => $summary['accessorial_rate_amount'],
                        'billing_type'            => $summary['billing_type'],
                        'created_at'              => now(),
                        'updated_at'              => now(),
                    ]);
                }
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
        if (!Schema::hasTable('soa_delivery_requests')) {
            return collect();
        }

        $deliveryRequestTable = Schema::hasTable('delivery_request') ? 'delivery_request' : 'delivery_requests';

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

    private function getEditableDeliveryRequests(Soa $soa)
    {
        if (!Schema::hasTable('soa_delivery_requests')) {
            return collect($soa->delivery_request_ids ?? []);
        }

        $deliveryRequestTable = Schema::hasTable('delivery_request') ? 'delivery_request' : 'delivery_requests';
        $selectedIds = collect($soa->delivery_request_ids ?? [])
            ->merge(
                $this->getAttachedDeliveryRequests($soa)->pluck('delivery_request_id')
            )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();
        $usedElsewhereIds = DB::table('soa_delivery_requests')
            ->where('soa_id', '!=', $soa->id)
            ->pluck('delivery_request_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return DB::table($deliveryRequestTable)
            ->leftJoin('companies', 'companies.id', '=', $deliveryRequestTable . '.company_id')
            ->leftJoin('customers', 'customers.id', '=', $deliveryRequestTable . '.customer_id')
            ->leftJoin('delivery_status', 'delivery_status.id', '=', $deliveryRequestTable . '.delivery_status')
            ->select([
                $deliveryRequestTable . '.id',
                $deliveryRequestTable . '.mtm',
                $deliveryRequestTable . '.booking_date',
                $deliveryRequestTable . '.delivery_date',
                $deliveryRequestTable . '.delivery_rate',
                $deliveryRequestTable . '.company_id',
                $deliveryRequestTable . '.customer_id',
                'companies.company_name',
                'customers.name as customer_name',
                'delivery_status.status_name',
            ])
            ->where($deliveryRequestTable . '.company_id', $soa->company_id)
            ->where($deliveryRequestTable . '.customer_id', $soa->customer_id)
            ->whereBetween($deliveryRequestTable . '.delivery_date', [$soa->billing_period_from->format('Y-m-d'), $soa->billing_period_to->format('Y-m-d')])
            ->whereNotIn($deliveryRequestTable . '.id', $usedElsewhereIds)
            ->orWhere(function ($query) use ($deliveryRequestTable, $selectedIds, $soa) {
                $query->whereIn($deliveryRequestTable . '.id', $selectedIds)
                    ->where($deliveryRequestTable . '.company_id', $soa->company_id)
                    ->where($deliveryRequestTable . '.customer_id', $soa->customer_id);
            })
            ->orderByRaw('CASE WHEN ' . $deliveryRequestTable . '.id IN (' . (count($selectedIds) ? implode(',', $selectedIds) : '0') . ') THEN 0 ELSE 1 END')
            ->orderBy($deliveryRequestTable . '.delivery_date')
            ->get()
            ->unique('id')
            ->values();
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
