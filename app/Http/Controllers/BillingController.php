<?php

namespace App\Http\Controllers;

use App\Models\Soa;
use App\Models\Company;
use App\Models\Customer;
use App\Models\DeliveryRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $query = Soa::with(['company', 'customer', 'creator']);

        // Apply filters
        if ($request->filled('company')) {
            $query->whereHas('company', function($q) use ($request) {
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

    public function editSOAForm(Soa $soa)
    {
        return view('billing.edit-soa', compact('soa'));
    }

    public function updateSOA(Request $request, Soa $soa)
    {
        $validator = Validator::make($request->all(), [
            'billing_period_from' => 'required|date',
            'billing_period_to' => 'required|date|after_or_equal:billing_period_from',
            'statement_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:statement_date',
            'status' => 'required|string|in:draft,pending,approved,paid,overdue',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $soa->update([
            'billing_period_from' => $request->billing_period_from,
            'billing_period_to' => $request->billing_period_to,
            'statement_date' => $request->statement_date,
            'due_date' => $request->due_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('billing.showSoa', $soa->id)->with('success', 'SOA updated successfully.');
    }

    public function destroySOA(Soa $soa)
    {
        DB::beginTransaction();

        try {
            if (Schema::hasTable('soa_delivery_line_items')) {
                $soa->deliveryRequestLineItems()->detach();
            }

            $soa->delete();
            DB::commit();

            return redirect()->route('billing.index')->with('success', 'SOA deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Unable to delete SOA: ' . $e->getMessage()]);
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
            'delivery_requests_table_exists' => Schema::hasTable('delivery_requests'),
            'delivery_request_line_items_table_exists' => Schema::hasTable('delivery_request_line_items'),
            'soa_delivery_line_items_table_exists' => Schema::hasTable('soa_delivery_line_items'),
        ];

        if ($debug['delivery_requests_table_exists']) {
            $debug['total_delivery_requests'] = \App\Models\DeliveryRequest::count();
            $debug['delivery_request_statuses'] = \App\Models\DeliveryRequest::select('status')->distinct()->pluck('status')->toArray();
        }

        if ($debug['delivery_request_line_items_table_exists']) {
            $debug['total_line_items'] = \App\Models\DeliveryRequestLineItem::count();
        }

        // Get delivery request line items that are not yet included in any SOA
        $deliveryLineItems = collect(); // Start with empty collection

        if ($debug['delivery_request_line_items_table_exists']) {
            $query = \App\Models\DeliveryRequestLineItem::with(['deliveryRequest.company', 'deliveryRequest.customer']);

            // Only add the delivery request filter if the relationship exists
            if ($debug['delivery_requests_table_exists']) {
                $query->whereHas('deliveryRequest', function($q) {
                    $q->whereIn('status', ['completed', 'delivered']);
                });
            }

            // Only filter out items already in SOAs if the pivot table exists
            if ($debug['soa_delivery_line_items_table_exists']) {
                $query->whereNotIn('id', function($subQuery) {
                    $subQuery->select('delivery_request_line_item_id')
                             ->from('soa_delivery_line_items');
                });
            }

            $deliveryLineItems = $query->orderBy('created_at', 'desc')->get();
        } else {
            // Mock data for testing when tables don't exist
            $deliveryLineItems = collect([
                (object) [
                    'id' => 1,
                    'mtm' => 'MTM2025061600898',
                    'truck_id' => '16',
                    'status' => 'completed',
                    'delivery_status' => 'delivered',
                    'distance_type' => '15',
                    'add_on_rate' => json_encode([1000.00]),
                    'accessorial_rate' => json_encode([2400.00]),
                    'dr_id' => '365',
                    'created_by' => '1',
                    'created_at' => '2025-06-17 08:43:44',
                    'updated_at' => '2025-07-09 07:09:00',
                    'deliveryRequest' => (object) [
                        'id' => 365,
                        'mtm' => 'MTM2025061600898',
                        'booking_date' => '2025-06-17',
                        'delivery_date' => '2025-06-17',
                        'delivery_type' => 'Regular',
                        'delivery_rate' => 3400.00,
                        'company_id' => '2',
                        'project_name' => '56A0JNM/Philippines Smart LTE Project 2023',
                        'region_id' => '19',
                        'status' => '1',
                        'customer_id' => '1',
                        'company' => (object) [
                            'id' => 2,
                            'company_name' => 'Sample Company'
                        ],
                        'customer' => (object) [
                            'id' => 1,
                            'name' => 'Sample Customer'
                        ]
                    ]
                ]
            ]);
        }

        return view('billing.create-soa', compact('companies', 'customers', 'deliveryLineItems', 'debug'));
    }

    public function createSOA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required_without:customer_id|exists:companies,id',
            'customer_id' => 'required_without:company_id|exists:customers,id',
            'billing_period_from' => 'required|date',
            'billing_period_to' => 'required|date|after_or_equal:billing_period_from',
            'statement_date' => 'required|date',
            'due_date' => 'nullable|date|after:statement_date',
            'delivery_line_item_ids' => 'required|array|min:1',
            'delivery_line_item_ids.*' => 'exists:delivery_request_line_items,id',
            'notes' => 'nullable|string|max:1000',
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
            $totalAmount = \App\Models\DeliveryRequestLineItem::whereIn('id', $deliveryLineItemIds)
                ->sum(\DB::raw('COALESCE(accessorial_rate, 0) + COALESCE(add_on_rate, 0)'));

            // Create SOA
            $soa = Soa::create([
                'soa_number' => $soaNumber,
                'company_id' => $request->company_id,
                'customer_id' => $request->customer_id,
                'billing_period_from' => $request->billing_period_from,
                'billing_period_to' => $request->billing_period_to,
                'statement_date' => $request->statement_date,
                'due_date' => $request->due_date,
                'total_amount' => $totalAmount,
                'outstanding_amount' => $totalAmount,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'delivery_request_line_item_ids' => $deliveryLineItemIds,
            ]);

            // Attach delivery request line items to SOA (only if pivot table exists)
            if (Schema::hasTable('soa_delivery_line_items')) {
                foreach ($deliveryLineItemIds as $lineItemId) {
                    $lineItem = \App\Models\DeliveryRequestLineItem::find($lineItemId);
                    $amount = ($lineItem->accessorial_rate ?? 0) + ($lineItem->add_on_rate ?? 0);
                    $soa->deliveryRequestLineItems()->attach($lineItemId, [
                        'amount' => $amount
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

        // Load delivery request line items if pivot table exists
        if (Schema::hasTable('soa_delivery_line_items')) {
            $soa->load('deliveryRequestLineItems.deliveryRequest');
        }

        return view('billing.show-soa', compact('soa'));
    }

    public function print($id)
    {
        $soa = Soa::with(['company', 'customer', 'creator'])->findOrFail($id);

        // Load delivery request line items if pivot table exists
        if (Schema::hasTable('soa_delivery_line_items')) {
            $soa->load('deliveryRequestLineItems.deliveryRequest');
        }

        return view('billing.print', compact('soa'));
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
