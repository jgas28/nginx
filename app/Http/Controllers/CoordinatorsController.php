<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRequest;
use App\Models\DeliveryRequestLineItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Region;
use App\Models\DeliveryType;
use App\Models\Warehouse;
use App\Models\AddOnRate;
use App\Models\DeliveryStatus;
use App\Models\Truck;
use App\Models\DistanceType;
use App\Models\AccessorialType;
use App\Models\Customer;
use App\Models\TruckType;
use App\Models\Area;
use App\Models\Expense_Type;
use App\Models\Company;
use App\Models\Allocation;
use App\Models\FleetCard;
use App\Models\User;
use App\Models\MonthlySeriesNumber;
use App\Models\cvr_request_type;
use App\Models\WithholdingTax;
use App\Models\CashVoucher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use DateTime;

class CoordinatorsController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'list');

        // Load only the active tab's data
        $tabData = $this->getFilteredQuery($request, true);

        return view('coordinators.index', array_merge($tabData, compact('activeTab')));
    }

    public function loadTabData(Request $request)
    {
        $tab = $request->input('tab', 'list');
        $query = $this->getFilteredQuery($request, true)[$tab] ?? collect();
        $html = view('coordinators.partials.' . $tab, ['data' => $query])->render();

        if ($request->ajax()) {
            return response()->json([
                'html' => $html,
                'tab' => $tab,
                'total' => method_exists($query, 'total') ? $query->total() : 0,
            ]);
        }

        return $html;
    }

    private function getFilteredQuery(Request $request, bool $singleTab = false)
    {
        $user = Auth::user();
        $search = $request->input('search');
        $mtm = $request->input('mtm');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;
        $tab = $request->input('tab', 'list');
        $tabs = [
            'list' => [2, 5, 6],
            'status4' => [4, 7],
            'status8' => [8],
            'status9' => [14],
            'status10' => [1, 15],
            'status11' => [11, 12],
            'staging' => [3],
            'accessorial' => [16,17,18]
        ];

        if ($singleTab) {
            // Only build query for the active tab
            $statuses = $tabs[$tab] ?? [];

            $query = DeliveryRequest::with(['lineItems', 'truckType', 'area', 'region', 'company'])
                ->where('status', '!=', 0)
                ->whereIn('delivery_status', $statuses);

            if (!$user->isAdmin() && $user->company_id) {
                $query->where('company_id', $user->company_id);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('mtm', 'like', "%{$search}%")
                    ->orWhereHas('region', fn($q) => $q->where('province', 'like', "%{$search}%"))
                    ->orWhereHas('area', fn($q) => $q->where('area_name', 'like', "%{$search}%"))
                    ->orWhereHas('company', fn($q) => $q->where('company_code', 'like', "%{$search}%"));
                });
            }

            if ($mtm) {
                $query->where('mtm', 'like', "%{$mtm}%");
            }

            if ($dateFrom && $dateTo) {
                $start = \Carbon\Carbon::parse($dateFrom)->startOfDay();
                $end = \Carbon\Carbon::parse($dateTo)->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            } elseif ($dateFrom) {
                $start = \Carbon\Carbon::parse($dateFrom)->startOfDay();
                $query->where('created_at', '>=', $start);
            } elseif ($dateTo) {
                $end = \Carbon\Carbon::parse($dateTo)->endOfDay();
                $query->where('created_at', '<=', $end);
            }

            return [
                $tab => $query->orderBy('created_at', 'desc')
                    ->paginate($perPage, ['*'], $tab . '_page')
                    ->withPath(route('coordinators.index'))
                    ->appends([
                        ...$request->except($tab . '_page'),
                        'tab' => $tab
                    ])
            ];
        }

        // If not singleTab, process all tabs (e.g., for initial page load)
        $results = [];

        foreach ($tabs as $tabKey => $statuses) {
            $query = DeliveryRequest::with(['lineItems', 'truckType', 'area', 'region', 'company'])
                ->where('status', '!=', 0)
                ->whereIn('delivery_status', $statuses);

            if (!$user->isAdmin() && $user->company_id) {
                $query->where('company_id', $user->company_id);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('mtm', 'like', "%{$search}%")
                    ->orWhereHas('region', fn($q) => $q->where('province', 'like', "%{$search}%"))
                    ->orWhereHas('area', fn($q) => $q->where('area_name', 'like', "%{$search}%"))
                    ->orWhereHas('company', fn($q) => $q->where('company_code', 'like', "%{$search}%"));
                });
            }

            if ($mtm) {
                $query->where('mtm', 'like', "%{$mtm}%");
            }

            if ($dateFrom && $dateTo) {
                $start = \Carbon\Carbon::parse($dateFrom)->startOfDay();
                $end = \Carbon\Carbon::parse($dateTo)->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            } elseif ($dateFrom) {
                $start = \Carbon\Carbon::parse($dateFrom)->startOfDay();
                $query->where('created_at', '>=', $start);
            } elseif ($dateTo) {
                $end = \Carbon\Carbon::parse($dateTo)->endOfDay();
                $query->where('created_at', '<=', $end);
            }

            $results[$tabKey] = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], $tabKey . '_page')
                ->appends([
                    ...$request->except($tabKey . '_page'),
                    'tab' => $tabKey
                ]);
        }

        return $results;
    }

    public function create()
    {
        // Fetch necessary data for the view
        $companies = Company::all();
        $regions = Region::all();
        $deliveryTypes = DeliveryType::all();
        $warehouses = Warehouse::all();
        $AddOnRates_multiDrops = AddOnRate::where('delivery_type', 'Multi-Drop')->get();
        $AddOnRates_multiPickUps = AddOnRate::where('delivery_type', 'Multi Pick-Up')->get();
        $deliveryStatuses = DeliveryStatus::all();
        $trucks = Truck::all();
        $distances = DistanceType::all();
        $accessorialTypes = AccessorialType::all();
        $customers = Customer::all();
        $truckTypes = TruckType::all();
        $areas = Area::all();
        $expenseTypes = Expense_Type::all();

        // Render the create view with the necessary data
        return view('coordinators.create', compact('companies', 'regions', 'deliveryTypes', 'warehouses', 'AddOnRates_multiDrops', 'deliveryStatuses', 'trucks', 'distances', 'AddOnRates_multiPickUps', 'accessorialTypes', 'customers', 'truckTypes','areas', 'expenseTypes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $employeeCode = $user->id;

        Log::debug('Delivery Type:', ['delivery_type' => $request->delivery_type]);

        $validationRules = $this->getCoordinatorValidationRules($request);
        $validated = $request->validate($validationRules);
        $lineItemsPayload = $this->prepareCoordinatorLineItems($request);

        Log::debug('Validated data:', $validated);
        Log::debug('Prepared line items:', $lineItemsPayload);

        // Start a database transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // Create the delivery request
            $deliveryRequest = new DeliveryRequest([
                'mtm' => $request->mtm,
                'customer_id' => $request->customer_id,
                'booking_date' => $request->booking_date,
                'delivery_date' => $request->delivery_date,
                'delivery_type' => $request->delivery_type,
                'delivery_rate' => $request->delivery_rate,
                'truck_type_id' => $request->truck_type_id,
                'company_id' => $request->company_id,
                'project_name' => $request->project_name,
                'region_id' => $request->region_id,
                'area_id' => $request->area_id,
                'status' => 1,
                'expense_type_id' => $request->expense_type_id,
                'created_by' => $employeeCode,
                'delivery_status' => $request->delivery_status,
            ]);
            $deliveryRequest->save();

            $deliveryRequestId = $deliveryRequest->id;

            Log::debug('Get DR ID:', ['delivery_request_id' => $deliveryRequestId]);

            if (!empty($lineItemsPayload)) {
                $this->saveLineItems($lineItemsPayload, $request->mtm, $deliveryRequestId, $employeeCode);
            } else {
                Log::debug('No line items to save for delivery type.', ['delivery_type' => $request->delivery_type]);
            }

            // Commit the transaction if everything is successful
            DB::commit();

            return redirect()->route('coordinators.index')->with('success', 'Delivery Request created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving delivery request and line items: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create Delivery Request: ' . $e->getMessage());
        }
    }

    private function getCoordinatorValidationRules(Request $request, ?DeliveryRequest $deliveryRequest = null): array
    {
        $deliveryRequestTable = DeliveryRequest::resolveTableName();
        $mtmRule = [
            'required',
            Rule::unique($deliveryRequestTable, 'mtm')
                ->where(fn ($query) => $query->where('status', '!=', 0)),
        ];

        if ($deliveryRequest) {
            $mtmRule = [
                'required',
                Rule::unique($deliveryRequestTable, 'mtm')
                    ->ignore($deliveryRequest->id)
                    ->where(fn ($query) => $query->where('status', '!=', 0)),
            ];
        }

        $validationRules = [
            'mtm' => $mtmRule,
            'customer_id' => 'required',
            'booking_date' => 'required',
            'delivery_date' => 'required',
            'delivery_rate' => 'required',
            'truck_type_id' => 'required',
            'company_id' => 'required',
            'project_name' => 'required',
            'region_id' => 'required',
            'area_id' => 'required',
            'delivery_type' => 'required',
            'expense_type_id' => 'required',
            'delivery_status' => 'required',
        ];

        if ($request->delivery_type === 'Regular') {
            $validationRules['regular'] = 'required|array|min:1';
            $validationRules['regular.0.warehouse_id'] = 'required';
            $validationRules['regular.0.site_name'] = 'required|string';
            $validationRules['regular.0.delivery_number'] = 'required|string';
            $validationRules['regular.0.delivery_address'] = 'required|string';
            $validationRules['regular.*.accessorial_type'] = 'nullable|string';
            $validationRules['regular.*.accessorial_rate'] = 'nullable|numeric';
        }

        if ($request->delivery_type === 'Multi-Drop') {
            $validationRules['multi_drop'] = 'required|array|min:1';
            $validationRules['multi_drop.0.warehouse_id'] = 'required';
            $validationRules['multi_drop.0.add_on_rate'] = 'required';
            $validationRules['multi_drop.*.site_name'] = 'required|string';
            $validationRules['multi_drop.*.delivery_number'] = 'required|string';
            $validationRules['multi_drop.*.delivery_address'] = 'required|string';
            $validationRules['multi_drop.*.accessorial_type'] = 'nullable|string';
            $validationRules['multi_drop.*.accessorial_rate'] = 'nullable|numeric';
        }

        if ($request->delivery_type === 'Multi Pick-Up') {
            $validationRules['multi_pickup'] = 'required|array|min:1';
            $validationRules['multi_pickup.0.site_name'] = 'required|string';
            $validationRules['multi_pickup.0.delivery_address'] = 'required|string';
            $validationRules['multi_pickup.0.add_on_rate'] = 'required';
            $validationRules['multi_pickup.*.warehouse_id'] = 'required';
            $validationRules['multi_pickup.*.delivery_number'] = 'required|string';
            $validationRules['multi_pickup.*.accessorial_type'] = 'nullable|string';
            $validationRules['multi_pickup.*.accessorial_rate'] = 'nullable|numeric';
        }

        return $validationRules;
    }

    private function prepareCoordinatorLineItems(Request $request): array
    {
        return match ($request->delivery_type) {
            'Regular' => $this->normalizeLineItems($request->input('regular', [])),
            'Multi-Drop' => $this->normalizeMultiDropLineItems($request->input('multi_drop', [])),
            'Multi Pick-Up' => $this->normalizeMultiPickupLineItems($request->input('multi_pickup', [])),
            default => [],
        };
    }

    private function normalizeLineItems(array $lineItems): array
    {
        return array_values(array_filter(array_map(function ($lineItem) {
            return is_array($lineItem) ? $lineItem : [];
        }, $lineItems), fn ($lineItem) => !empty($lineItem)));
    }

    private function normalizeMultiDropLineItems(array $lineItems): array
    {
        $baseItem = $lineItems[0] ?? [];

        return array_values(array_filter(array_map(function ($lineItem, $index) use ($baseItem) {
            if (!is_array($lineItem)) {
                return [];
            }

            if ($index > 0) {
                $lineItem['warehouse_id'] = !empty($lineItem['warehouse_id']) ? $lineItem['warehouse_id'] : ($baseItem['warehouse_id'] ?? null);
                $lineItem['add_on_rate'] = !empty($lineItem['add_on_rate']) ? $lineItem['add_on_rate'] : ($baseItem['add_on_rate'] ?? null);
            }

            return $lineItem;
        }, $lineItems, array_keys($lineItems)), fn ($lineItem) => !empty($lineItem)));
    }

    private function normalizeMultiPickupLineItems(array $lineItems): array
    {
        $baseItem = $lineItems[0] ?? [];

        return array_values(array_filter(array_map(function ($lineItem) use ($baseItem) {
            if (!is_array($lineItem)) {
                return [];
            }

            $lineItem['site_name'] = !empty($lineItem['site_name']) ? $lineItem['site_name'] : ($baseItem['site_name'] ?? null);
            $lineItem['delivery_address'] = !empty($lineItem['delivery_address']) ? $lineItem['delivery_address'] : ($baseItem['delivery_address'] ?? null);
            $lineItem['add_on_rate'] = !empty($lineItem['add_on_rate']) ? $lineItem['add_on_rate'] : ($baseItem['add_on_rate'] ?? null);

            return $lineItem;
        }, $lineItems), fn ($lineItem) => !empty($lineItem)));
    }

    private function saveLineItems($lineItems, $mtm, $deliveryRequestId, $employeeCode)
    {
        foreach ($lineItems as $lineItem) {
            // Trim all fields to avoid unwanted whitespace issues
            $lineItem = array_map(function ($item) {
                return is_string($item) ? trim($item) : $item;
            }, $lineItem);

            Log::debug('Saving Line Item:', $lineItem);  // Log the line item before saving
            
            try {
                $deliveryRequestLineItem = new DeliveryRequestLineItem([
                    'mtm' => $mtm,
                    'warehouse_id' => $lineItem['warehouse_id'] ?? null,
                    'site_name' => $lineItem['site_name'] ?? null,
                    'delivery_number' => $lineItem['delivery_number'] ?? null,
                    'delivery_address' => $lineItem['delivery_address'] ?? null,
                    'distance_type' => $lineItem['distance_type'] ?? null,
                    'add_on_rate' => $lineItem['add_on_rate'] ?? null,
                    'accessorial_type' => $lineItem['accessorial_type'] ?? null,
                    'accessorial_rate' => $lineItem['accessorial_rate'] ?? null,
                    'status' => 1,
                    'dr_id' => $deliveryRequestId,
                    'created_by' => $employeeCode,
                ]);
                
                $deliveryRequestLineItem->save();
                Log::debug('Line Item saved successfully', ['mtm' => $mtm, 'lineItem' => $lineItem]);
            } catch (\Exception $e) {
                Log::error('Error saving delivery request line item: ' . $e->getMessage(), [
                    'mtm' => $mtm,
                    'lineItem' => $lineItem
                ]);
                throw $e;
            }
        }
    }

    public function edit(DeliveryRequest $deliveryRequest)
    {
        $deliveryRequestTable = DeliveryRequest::resolveTableName();
        // Fetch related delivery line items by joining with the correct table name
        $deliveryLineItems = DeliveryRequestLineItem::join($deliveryRequestTable, $deliveryRequestTable . '.mtm', '=', 'delivery_request_line_items.mtm')
        ->where($deliveryRequestTable . '.id', $deliveryRequest->id)
        ->where('delivery_request_line_items.status', '!=', 0)
        ->select('delivery_request_line_items.*', $deliveryRequestTable . '.id as request_id')
        ->get();

        // dd($deliveryLineItems);

        $companies = Company::all();
        $regions = Region::all();
        $deliveryTypes = DeliveryType::all();
        $warehouses = Warehouse::all();
        $AddOnRates_multiDrops = AddOnRate::where('delivery_type', 'Multi-Drop')->get();
        $AddOnRates_multiPickUps = AddOnRate::where('delivery_type', 'Multi Pick-Up')->get();
        $deliveryStatuses = DeliveryStatus::all();
        $trucks = Truck::all();
        $distances = DistanceType::all();
        $accessorialTypes = AccessorialType::all();
        $customers = Customer::all();
        $truckTypes = TruckType::all();
        $areas = Area::all();
        $expenseTypes = Expense_Type::all();

        return view('coordinators.edit', compact(
            'companies', 'regions', 'warehouses', 'AddOnRates_multiDrops', 
            'AddOnRates_multiPickUps', 'deliveryStatuses', 'trucks', 'distances', 'deliveryLineItems', 
            'deliveryRequest', 'deliveryTypes', 'accessorialTypes', 'customers', 'truckTypes', 'areas', 'expenseTypes'
        ));
    }

    public function update(Request $request, DeliveryRequest $deliveryRequest)
    {
        Log::debug('Delivery Type:', ['delivery_type' => $request->delivery_type]);

        $validationRules = $this->getCoordinatorValidationRules($request, $deliveryRequest);

        Log::debug('Starting Validation...', $request->all());

        $validated = $request->validate($validationRules);
        $lineItemsPayload = $this->prepareCoordinatorLineItems($request);
        
        Log::debug('Validated data:', $validated);
        Log::debug('Prepared line items:', $lineItemsPayload);
        Log::debug('Updating Delivery Request:', $deliveryRequest->toArray());
        // Start a database transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // Update the delivery request
            $deliveryRequest->mtm = $request->mtm;
            $deliveryRequest->customer_id = $request->customer_id;
            $deliveryRequest->booking_date = $request->booking_date;
            $deliveryRequest->delivery_date = $request->delivery_date;
            $deliveryRequest->delivery_type = $request->delivery_type;
            $deliveryRequest->delivery_rate = $request->delivery_rate;
            $deliveryRequest->truck_type_id = $request->truck_type_id;
            $deliveryRequest->company_id = $request->company_id;
            $deliveryRequest->project_name = $request->project_name;
            $deliveryRequest->region_id = $request->region_id;
            $deliveryRequest->area_id = $request->area_id;
            $deliveryRequest->expense_type_id = $request->expense_type_id;
            $deliveryRequest->delivery_status = $request->delivery_status;

            $deliveryRequest->update();
            Log::debug('DeliveryRequest saved:', $deliveryRequest->toArray());

            $this->updateLineItems($lineItemsPayload, $request->mtm, $deliveryRequest->id, $deliveryRequest->created_by);

            // Commit the transaction if everything is successful
            DB::commit();

            return redirect()->route('coordinators.index', [
                'tab' => $request->input('tab', 'list') // default to list if not provided
            ])->with('success', 'Delivery Request updated successfully.');
        } catch (\Exception $e) {
            // Rollback if there is an error
            DB::rollBack();
            Log::error('Error updating delivery request and line items: ' . $e->getMessage());
            return redirect()->route('coordinators.index', [
                'tab' => $request->input('tab', 'list') // default to list if not provided
            ])->with('error', 'Failed to update Delivery Request.');
        }
    }

    public function destroy(DeliveryRequest $deliveryRequest)
    {
        // Change the status of the DeliveryRequest to 0
        $deliveryRequest->status = 0;
        $deliveryRequest->update();

        // Change the status of all related DeliveryRequestLineItems to 0
        $deliveryRequest->lineItems()->update(['status' => 0]);

        return redirect()->route('coordinators.index')->with('success', 'Delivery Request deleted successfully.');
    }

    private function updateLineItems($lineItems, $mtm, $deliveryRequestId, $employeeCode)
    {
        // Start a database transaction
        DB::beginTransaction();
        try {
            foreach ($lineItems as $key => $lineItem) {
                // Ensure that 'id' is present for existing items, or handle the new item
                if (empty($lineItem['id'])) {
                    // Handle new line items (those without an ID)
                    $this->saveLineItems([$lineItem], $mtm, $deliveryRequestId, $employeeCode);
                    Log::debug('New Line Item created', ['mtm' => $mtm, 'lineItem' => $lineItem]);
                    continue;  // Skip the update logic for new items since they're already saved
                }
    
                // Trim all string fields to avoid unwanted whitespace issues
                $lineItem = array_map(function ($item) {
                    return is_string($item) ? trim($item) : $item;
                }, $lineItem);
    
                // Log the line item before updating for debugging purposes
                Log::debug('Updating Line Item:', $lineItem);
    
                // Explicitly check and set fields to null if they're empty
                $lineItem['accessorial_type'] = empty($lineItem['accessorial_type']) ? null : $lineItem['accessorial_type'];
                $lineItem['accessorial_rate'] = !empty($lineItem['accessorial_rate']) ? (float) $lineItem['accessorial_rate'] : null;
                $lineItem['delivery_address'] = empty($lineItem['delivery_address']) ? null : $lineItem['delivery_address'];
    
                // Check if the line item exists for this mtm (foreign key) and its unique id
                $deliveryRequestLineItem = DeliveryRequestLineItem::where('id', $lineItem['id'])->first();
    
                if ($deliveryRequestLineItem) {
                    // If the line item exists, update it with new values
                    $deliveryRequestLineItem->update([
                        'mtm' => $lineItem['mtm'] ?? $mtm,
                        'warehouse_id' => $lineItem['warehouse_id'] ?? $deliveryRequestLineItem->warehouse_id,
                        'site_name' => $lineItem['site_name'] ?? $deliveryRequestLineItem->site_name,
                        'delivery_number' => $lineItem['delivery_number'], // Update the delivery number
                        'delivery_address' => $lineItem['delivery_address'], // Use the modified value
                        'distance_type' => $lineItem['distance_type'] ?? $deliveryRequestLineItem->distance_type,
                        'add_on_rate' => $lineItem['add_on_rate'] ?? $deliveryRequestLineItem->add_on_rate,
                        'accessorial_type' => $lineItem['accessorial_type'], // Use the modified value
                        'accessorial_rate' => $lineItem['accessorial_rate'], // Use the modified value
                        'status' => 1,
                    ]);
    
                    // Log successful update
                    Log::debug('Line Item updated successfully', ['mtm' => $mtm, 'lineItem' => $lineItem]);
                } else {
                    // If no matching line item exists for this mtm and id, create a new one
                    $this->saveLineItems([$lineItem], $mtm, $deliveryRequestId, $employeeCode);
                    Log::debug('New Line Item created', ['mtm' => $mtm, 'lineItem' => $lineItem]);
                }
            }
    
            // Commit the transaction after all line items are updated or created successfully
            DB::commit();
        } catch (\Exception $e) {
            // If something goes wrong, rollback the transaction to ensure consistency
            DB::rollBack();
            Log::error('Error updating delivery request line items: ' . $e->getMessage(), [
                'mtm' => $mtm,
                'lineItems' => $lineItems
            ]);
            throw $e;  // Re-throw the exception to notify the caller about the failure
        }
    }

    public function splitView(DeliveryRequest $deliveryRequest){
        $deliveryRequestTable = DeliveryRequest::resolveTableName();
        // Fetch related delivery line items by joining with the correct table name
        $deliveryLineItems = DeliveryRequestLineItem::join($deliveryRequestTable, $deliveryRequestTable . '.mtm', '=', 'delivery_request_line_items.mtm')
        ->where($deliveryRequestTable . '.id', $deliveryRequest->id)
        ->where('delivery_request_line_items.status', '!=', 0)
        ->select('delivery_request_line_items.*', $deliveryRequestTable . '.id as request_id')
        ->get();

        // dd($deliveryLineItems);

        $companies = Company::all();
        $regions = Region::all();
        $deliveryTypes = DeliveryType::all();
        $warehouses = Warehouse::all();
        $AddOnRates_multiDrops = AddOnRate::where('delivery_type', 'Multi-Drop')->get();
        $AddOnRates_multiPickUps = AddOnRate::where('delivery_type', 'Multi Pick-Up')->get();
        $deliveryStatuses = DeliveryStatus::all();
        $trucks = Truck::all();
        $distances = DistanceType::all();
        $accessorialTypes = AccessorialType::all();
        $customers = Customer::all();
        $truckTypes = TruckType::all();
        $areas = Area::all();

        return view('coordinators.splitView', compact(
            'companies', 'regions', 'warehouses', 'AddOnRates_multiDrops', 
            'AddOnRates_multiPickUps', 'deliveryStatuses', 'trucks', 'distances', 'deliveryLineItems', 
            'deliveryRequest', 'deliveryTypes', 'accessorialTypes', 'customers', 'truckTypes' ,'areas'
        ));
    }

    public function showSplitForm($id, Request $request)
    {
        $requestId = $request->query('request_id');
        $deliveryRequestTable = DeliveryRequest::resolveTableName();

        $deliveryLineItems = DeliveryRequestLineItem::join($deliveryRequestTable, $deliveryRequestTable . '.mtm', '=', 'delivery_request_line_items.mtm')
        ->where('delivery_request_line_items.id', $id)
        ->where('delivery_request_line_items.status', '!=', 0)
        ->select('delivery_request_line_items.*', $deliveryRequestTable . '.id as request_id')
        ->get();


        $deliveryRequest = DeliveryRequest::find($requestId);

        $companies = Company::all();
        $regions = Region::all();
        $deliveryTypes = DeliveryType::all();
        $warehouses = Warehouse::all();
        $AddOnRates_multiDrops = AddOnRate::where('delivery_type', 'Multi-Drop')->get();
        $AddOnRates_multiPickUps = AddOnRate::where('delivery_type', 'Multi Pick-Up')->get();
        $deliveryStatuses = DeliveryStatus::all();
        $trucks = Truck::all();
        $distances = DistanceType::all();
        $accessorialTypes = AccessorialType::all();
        $customers = Customer::all();
        $truckTypes = TruckType::all();
        $areas = Area::all();
        $expenseTypes = Expense_Type::all();

        return view('coordinators.split', compact(
            'companies', 'regions', 'warehouses', 'AddOnRates_multiDrops', 
            'AddOnRates_multiPickUps', 'deliveryStatuses', 'trucks', 'distances', 'deliveryLineItems', 
            'deliveryRequest', 'deliveryTypes', 'accessorialTypes', 'customers', 'truckTypes' ,'areas', 'expenseTypes'
        ));
    }

    public function performSplit(Request $request, $id)
    {
        $user = Auth::user();
        $employeeCode = $user->id;

        // Step 1: Retrieve all form data
        $data = $request->all();

        // Step 2: Retrieve the existing delivery request using the provided $id
        $deliveryRequest = DeliveryRequest::findOrFail($id);

        // Step 3: Initialize the lineItemIds array
        $lineItemIds = [];

        // Step 4: Update line items and track lineItemIds for selected ones
        if (isset($data['regular'])) {
            foreach ($data['regular'] as $index => $lineItemData) {
                // Extract the line item ID
                $lineItemId = $lineItemData['id'];
                $lineItemIds[] = $lineItemId;

                // Find the corresponding DeliveryLineItem
                $lineItem = DeliveryRequestLineItem::find($lineItemId);

                // Check if the line item exists
                if ($lineItem) {
                    // Update MTM to append '-2' to line items that have been split
                    $lineItem->mtm = $lineItem->mtm; // Appending '-2' for split items
                    $lineItem->status = 0; // Set the status to 0 for items that have been split
                    $lineItem->save();
                }
            }
        }

        // Step 5: Update the DeliveryRequestLineItems that have not been selected (if needed)
        // If you're updating non-selected line items, you can uncomment the next block:
        // $allLineItems = DeliveryRequestLineItem::where('mtm', $deliveryRequest->mtm)->get();
        // foreach ($allLineItems as $lineItem) {
        //     if (!in_array($lineItem->id, $lineItemIds)) {
        //         $lineItem->mtm = $lineItem->mtm . '-1'; // Appending '-1' for non-selected items
        //         $lineItem->save();
        //     }
        // }

        // Step 6: Create the new DeliveryRequest with the provided data
        $deliveryRequestData = [
            'mtm' => $data['mtm'], // Assuming only 'mtm' is user-editable
            'booking_date' => $data['booking_date'],
            'delivery_date' => $data['delivery_date'],
            'delivery_rate' => $data['delivery_rate'],
            'truck_type_id' => $data['truck_type_id'],
            'company_id' => $data['company_id'],
            'project_name' => $data['project_name'],
            'region_id' => $data['region_id'],
            'area_id' => $data['area_id'],
            'expense_type_id' => $data['expense_type_id'],
            'customer_id' => $data['customer_id'],
            'delivery_type' => $data['delivery_type'],
            'status' => 1,
            'created_by' => $employeeCode,
            'delivery_status' => $data['delivery_status'],
        ];

        // Create the new DeliveryRequest entry in the database
        $deliveryRequest = DeliveryRequest::create($deliveryRequestData);
        $newlyCreatedId = $deliveryRequest->id;

        // Step 7: Handle DeliveryLineItems for the new request
        if (isset($data['regular'])) {
            foreach ($data['regular'] as $index => $lineItemData) {

                $accessorialRate = isset($lineItemData['accessorial_rate']) ? (float) $lineItemData['accessorial_rate'] : 0.0;

                // Create the new line item associated with the new delivery request
                $lineItem = [
                    'mtm' => $data['mtm'], // Using the provided MTM for the new request
                    'warehouse_id' => $lineItemData['warehouse_id'],
                    'delivery_number' => $lineItemData['delivery_number'],
                    'site_name' => $lineItemData['site_name'],
                    'delivery_address' => $lineItemData['delivery_address'],
                    'status' => 1,
                    'created_by' => $employeeCode,
                    'dr_id' => $newlyCreatedId,
                    'accessorial_type' => $lineItemData['accessorial_type'] ?? null, // Ensure default is null
                    'accessorial_rate' => $accessorialRate,  // Ensure default is 0
                ];

                // Create the DeliveryLineItem in the database
                DeliveryRequestLineItem::create($lineItem);
            }
        }

        // Step 9: Redirect or show a success message
        return redirect()->route('coordinators.index')->with('success', 'Delivery request created and line items updated successfully!');
    }

    public function editAllocation(DeliveryRequest $deliveryRequest)
    {

        $deliveryLineItems = DeliveryRequestLineItem::with('deliveryRequest')
            ->where('status', '!=', 0)
            ->whereHas('deliveryRequest', function ($query) use ($deliveryRequest) {
                $query->where('id', $deliveryRequest->id);
            })
        ->get();

        $allocate = DeliveryRequest::with('allocations')->find($deliveryRequest->id);

        $companies = Company::all();
        $regions = Region::all();
        $deliveryTypes = DeliveryType::all();
        $warehouses = Warehouse::all();
        $AddOnRates_multiDrops = AddOnRate::where('delivery_type', 'Multi-Drop')->get();
        $AddOnRates_multiPickUps = AddOnRate::where('delivery_type', 'Multi Pick-Up')->get();
        $deliveryStatuses = DeliveryStatus::all();
        $trucks = Truck::all();
        $distances = DistanceType::all();
        $accessorialTypes = AccessorialType::all();
        $customers = Customer::all();
        $truckTypes = TruckType::all();
        $areas = Area::all();
        $expenseTypes = Expense_Type::all();
        $fleetCards = FleetCard::where('status', 1)->get();
        $drivers = User::where('status', '!=', 0)->get();

        return view('coordinators.editAllocation', compact(
            'companies', 'regions', 'warehouses', 'AddOnRates_multiDrops', 
            'AddOnRates_multiPickUps', 'deliveryStatuses', 'trucks', 'distances', 'deliveryLineItems', 
            'deliveryRequest', 'deliveryTypes', 'accessorialTypes', 'customers', 'truckTypes', 'areas', 'expenseTypes',
            'fleetCards','drivers', 'allocate'
        ));
    }

    public function editAllocated(DeliveryRequest $deliveryRequest)
    {

        $deliveryLineItems = DeliveryRequestLineItem::with('deliveryRequest')
            ->where('status', '!=', 0)
            ->whereHas('deliveryRequest', function ($query) use ($deliveryRequest) {
                $query->where('id', $deliveryRequest->id);
            })
        ->get();

        $allocate = Allocation::where('dr_id', $deliveryRequest->id)
        ->where('trip_type', 'delivery')
        ->latest() // same as orderBy('created_at', 'desc')
        ->first();

        // dd($deliveryLineItems);

        $companies = Company::all();
        $regions = Region::all();
        $deliveryTypes = DeliveryType::all();
        $warehouses = Warehouse::all();
        $AddOnRates_multiDrops = AddOnRate::where('delivery_type', 'Multi-Drop')->get();
        $AddOnRates_multiPickUps = AddOnRate::where('delivery_type', 'Multi Pick-Up')->get();
        $deliveryStatuses = DeliveryStatus::all();
        $trucks = Truck::orderBy('truck_name')->get();
        $distances = DistanceType::all();
        $accessorialTypes = AccessorialType::all();
        $customers = Customer::all();
        $truckTypes = TruckType::all();
        $areas = Area::all();
        $expenseTypes = Expense_Type::all();
        $fleetCards = FleetCard::where('status', 1)
        ->orderBy('account_name')
        ->get();
        $drivers = User::where('status', '!=', 0)
               ->orderBy('fname')
               ->get();

        return view('coordinators.editAllocated', compact(
            'companies', 'regions', 'warehouses', 'AddOnRates_multiDrops', 
            'AddOnRates_multiPickUps', 'deliveryStatuses', 'trucks', 'distances', 'deliveryLineItems', 
            'deliveryRequest', 'deliveryTypes', 'accessorialTypes', 'customers', 'truckTypes', 'areas', 'expenseTypes',
            'fleetCards','drivers', 'allocate'
        ));
    }

    public function updateAllocation(Request $request, DeliveryRequest $deliveryRequest)
    {
        $user = Auth::user();
        $employeeCode = $user->id;

        // Start transaction
        DB::beginTransaction();

        try {
            // $request->validate([
            //     // Validation rules
            //     'allocation_id' => 'nullable|array',
            //     'allocation_id.*' => 'nullable|integer|exists:allocations,id',
            //     'amount' => 'required|array',
            //     'amount.*' => 'required|numeric|min:0',
            //     'fleet_card_id' => 'nullable|array',
            //     'fleet_card_id.*' => 'nullable|integer|exists:fleet_cards,id',
            //     'truck_id' => 'required|array',
            //     'truck_id.*' => 'required|integer|exists:trucks,id',
            //     'driver_id' => 'required|array',
            //     'driver_id.*' => 'required|integer|exists:users,id',
            //     'helper' => 'nullable|array',
            //     'helper.*' => 'nullable|array',
            //     'helper.*.*' => 'nullable|string',

            //     // DeliveryRequest fields
            //     'delivery_date' => 'required|date',
            //     'delivery_rate' => 'required|numeric',
            //     'truck_type_id' => 'required|integer|exists:truck_types,id',
            //     // Add any additional validation rules if needed
            // ]);

            // Update DeliveryRequest fields
            $deliveryRequest->update([
                'delivery_date' => $request->input('delivery_date'),
                'delivery_rate' => $request->input('delivery_rate'),
                'truck_type_id' => $request->input('truck_type_id'),
                'delivery_status' => $request->input('delivery_status'),
            ]);

            Log::info('DeliveryRequest updated', ['deliveryRequest' => $deliveryRequest->toArray()]);

            // Update Line Items - Regular
            if ($request->has('regular')) {
                foreach ($request->input('regular') as $item) {
                    $lineItem = DeliveryRequestLineItem::find($item['id']);
                    if ($lineItem) {
                        $lineItem->delivery_number = $item['delivery_number'] ?? $lineItem->delivery_number;
                        $lineItem->accessorial_type = $item['accessorial_type'] ?? null;
                        $lineItem->accessorial_rate = isset($item['accessorial_rate']) ? (float) $item['accessorial_rate'] : null;
                        $lineItem->save();
                        Log::info('Updated regular line item', ['id' => $lineItem->id]);
                    } else {
                        Log::warning('Regular line item not found', ['id' => $item['id']]);
                    }
                }
            }

            // Update Line Items - Multi-Drop
            if ($request->has('multi_drop')) {
                foreach ($request->input('multi_drop') as $item) {
                    $lineItem = DeliveryRequestLineItem::find($item['id']);
                    if ($lineItem) {
                        $lineItem->delivery_number = $item['delivery_number'] ?? $lineItem->delivery_number;
                        $lineItem->accessorial_type = $item['accessorial_type'] ?? null;
                        $lineItem->accessorial_rate = isset($item['accessorial_rate']) ? (float) $item['accessorial_rate'] : null;
                        $lineItem->save();
                        Log::info('Updated multi_drop line item', ['id' => $lineItem->id]);
                    } else {
                        Log::warning('Multi-drop line item not found', ['id' => $item['id']]);
                    }
                }
            }

            // Update Line Items - Multi-Pickup
            if ($request->has('multi_pickup')) {
                foreach ($request->input('multi_pickup') as $item) {
                    $lineItem = DeliveryRequestLineItem::find($item['id']);
                    if ($lineItem) {
                        $lineItem->delivery_number = $item['delivery_number'] ?? $lineItem->delivery_number;
                        $lineItem->accessorial_type = $item['accessorial_type'] ?? null;
                        $lineItem->accessorial_rate = isset($item['accessorial_rate']) ? (float) $item['accessorial_rate'] : null;
                        $lineItem->save();
                        Log::info('Updated multi_pickup line item', ['id' => $lineItem->id]);
                    } else {
                        Log::warning('Multi-pickup line item not found', ['id' => $item['id']]);
                    }
                }
            }

            // Handle allocations — update existing or create new
            // $allocationIds = $request->input('allocation_id', []);
            // $amounts = $request->input('amount');
            // $fleetCardIds = $request->input('fleet_card_id', []);
            // $truckIds = $request->input('truck_id');
            // $driverIds = $request->input('driver_id');
            // $helpers = $request->input('helper', []);
            // $tripTypes = $request->input('trip_type');

            // foreach ($amounts as $index => $amount) {
            //     $allocationId = $allocationIds[$index] ?? null;

            //     if ($allocationId) {
            //         $allocation = Allocation::find($allocationId);
            //         if (!$allocation) {
            //             Log::warning('Allocation ID not found, creating new', ['allocationId' => $allocationId]);
            //             $allocation = new Allocation();
            //             $allocation->dr_id = $deliveryRequest->id;
            //         }
            //     } else {
            //         $allocation = new Allocation();
            //         $allocation->dr_id = $deliveryRequest->id;
            //     }

            //     $allocation->amount = $amount;
            //     $allocation->trip_type = 'Pullout'; // Add this
            //     $allocation->fleet_card_id = $fleetCardIds[$index] ?? null;
            //     $allocation->truck_id = $truckIds[$index] ?? null;
            //     $allocation->driver_id = $driverIds[$index] ?? null;
            //     $allocation->helper = $helpers[$index] ?? [];
            //     $allocation->created_by = $employeeCode;
            //     $allocation->save();

            //     Log::info('Allocation saved', [
            //         'allocationId' => $allocation->id,
            //         'trip_type' => $allocation->trip_type,
            //         'allocation' => $allocation->toArray()
            //     ]);
            // }

            // Commit transaction if all succeed
            DB::commit();

            return redirect()->route('coordinators.index', ['tab' => $request->input('tab', 'status4')])
                ->with('success', 'Delivery request, line items and allocations updated successfully.');
        } catch (\Exception $e) {
            // Rollback on error
            DB::rollBack();

            Log::error('Failed to update allocation', ['error' => $e->getMessage()]);

            return back()->withErrors('Failed to update allocations. Please try again.');
        }
    } 

    public function updateAllocated(Request $request, DeliveryRequest $deliveryRequest)
    {
        $user = Auth::user();
        $employeeCode = $user->id;

        // Start transaction
        DB::beginTransaction();

        try {
            $request->validate([
                // Validation rules
                'allocation_id' => 'nullable|integer|exists:allocations,id',
                'amount' => 'required|numeric|min:0',
                'fleet_card_id' => 'nullable|integer|exists:fleet_cards,id',
                'truck_id' => 'required|integer|exists:trucks,id',
                'driver_id' => 'required|integer|exists:users,id',
                'requestor_id' => 'nullable|integer|exists:users,id',
                'helper' => 'nullable|array',
                'helper.*' => 'nullable|string',

                // DeliveryRequest fields
                'delivery_date' => 'required|date',
                'delivery_rate' => 'required|numeric',
                'truck_type_id' => 'required|integer|exists:truck_types,id',
                // Add any additional validation rules if needed
            ]);

            // Update DeliveryRequest fields
            $deliveryRequest->update([
                'delivery_date' => $request->input('delivery_date'),
                'delivery_rate' => $request->input('delivery_rate'),
                'truck_type_id' => $request->input('truck_type_id'),
                'delivery_status' => $request->input('delivery_status'),
            ]);

            Log::info('DeliveryRequest updated', ['deliveryRequest' => $deliveryRequest->toArray()]);

            // Update Line Items - Regular
            if ($request->has('regular')) {
                foreach ($request->input('regular') as $item) {
                    $lineItem = DeliveryRequestLineItem::find($item['id']);
                    if ($lineItem) {
                        $lineItem->delivery_number = $item['delivery_number'] ?? $lineItem->delivery_number;
                        $lineItem->accessorial_type = $item['accessorial_type'] ?? null;
                        $lineItem->accessorial_rate = isset($item['accessorial_rate']) ? (float) $item['accessorial_rate'] : null;
                        $lineItem->save();
                        Log::info('Updated regular line item', ['id' => $lineItem->id]);
                    } else {
                        Log::warning('Regular line item not found', ['id' => $item['id']]);
                    }
                }
            }

            // Update Line Items - Multi-Drop
            if ($request->has('multi_drop')) {
                foreach ($request->input('multi_drop') as $item) {
                    $lineItem = DeliveryRequestLineItem::find($item['id']);
                    if ($lineItem) {
                        $lineItem->delivery_number = $item['delivery_number'] ?? $lineItem->delivery_number;
                        $lineItem->accessorial_type = $item['accessorial_type'] ?? null;
                        $lineItem->accessorial_rate = isset($item['accessorial_rate']) ? (float) $item['accessorial_rate'] : null;
                        $lineItem->save();
                        Log::info('Updated multi_drop line item', ['id' => $lineItem->id]);
                    } else {
                        Log::warning('Multi-drop line item not found', ['id' => $item['id']]);
                    }
                }
            }

            // Update Line Items - Multi-Pickup
            if ($request->has('multi_pickup')) {
                foreach ($request->input('multi_pickup') as $item) {
                    $lineItem = DeliveryRequestLineItem::find($item['id']);
                    if ($lineItem) {
                        $lineItem->delivery_number = $item['delivery_number'] ?? $lineItem->delivery_number;
                        $lineItem->accessorial_type = $item['accessorial_type'] ?? null;
                        $lineItem->accessorial_rate = isset($item['accessorial_rate']) ? (float) $item['accessorial_rate'] : null;
                        $lineItem->save();
                        Log::info('Updated multi_pickup line item', ['id' => $lineItem->id]);
                    } else {
                        Log::warning('Multi-pickup line item not found', ['id' => $item['id']]);
                    }
                }
            }

            $allocationId = $request->input('allocation_id');
            $amount = $request->input('amount');
            $fleetCardId = $request->input('fleet_card_id');
            $truckId = $request->input('truck_id');
            $driverId = $request->input('driver_id');
            $requestorId = $request->input('requestor_id');
            $helpers = $request->input('helper', []);

            if ($allocationId) {
                $allocation = Allocation::where('id', $allocationId)
                    ->where('trip_type', 'delivery')
                    ->first();

                if (!$allocation) {
                    Log::warning('Allocation not found or not of trip_type "delivery"', [
                        'allocationId' => $allocationId
                    ]);
                } else {
                    $allocation->amount = $amount;
                    $allocation->fleet_card_id = $fleetCardId;
                    $allocation->truck_id = $truckId;
                    $allocation->driver_id = $driverId;
                    $allocation->helper = $helpers;
                    $allocation->created_by = $employeeCode;
                    $allocation->requestor_id = $requestorId;
                    $allocation->save();

                    Log::info('Allocation updated', [
                        'allocationId' => $allocation->id,
                        'trip_type' => $allocation->trip_type,
                        'allocation' => $allocation->toArray()
                    ]);
                }
            } else {
                Allocation::create([
                    'dr_id' => $deliveryRequest->id,
                    'amount' => $amount,
                    'fleet_card_id' => $fleetCardId,
                    'truck_id' => $truckId,
                    'driver_id' => $driverId,
                    'helper' => $helpers,
                    'created_by' => $employeeCode,
                    'requestor_id' => $requestorId,
                    'trip_type' => 'delivery',
                    'dr_stats' => 'Allocated',
                ]);

                Log::info('New allocation created for delivery request', ['dr_id' => $deliveryRequest->id]);
            }

            // Commit transaction if all succeed
            DB::commit();

            return redirect()->route('coordinators.index', ['tab' => 'status9'])
                ->with('success', 'Delivery request, line items and allocations updated successfully.');

        } catch (\Exception $e) {
            // Rollback on error
            DB::rollBack();

            Log::error('Failed to update allocation', ['error' => $e->getMessage()]);

            return back()->withErrors('Failed to update allocations. Please try again.');
        }
    }

    public function request($id)
    {
        $deliveryRequestTable = DeliveryRequest::resolveTableName();

        $deliveryLineItems = DeliveryRequest::join('delivery_request_line_items', $deliveryRequestTable . '.id', '=', 'delivery_request_line_items.dr_id')
            ->where($deliveryRequestTable . '.id', $id)
            ->where('delivery_request_line_items.status', '!=', 0)
            ->select($deliveryRequestTable . '.*', 'delivery_request_line_items.*', $deliveryRequestTable . '.id as request_id')
            ->get();

        if ($deliveryLineItems->isEmpty()) {
            abort(404, 'No delivery request line items found.');
        }

        $mtms = $deliveryLineItems->pluck('mtm')->unique();

        // Determine relevant trucks
        $truckIds = DeliveryRequestLineItem::whereIn('mtm', $mtms)->distinct()->pluck('truck_id');
        $trucks = Truck::all();

        $firstDeliveryLineItem = $deliveryLineItems->first();
        $company_id = $firstDeliveryLineItem->company_id ?? null;

        // Preview CVR Number (non-incremental, only for display)
        $monthlySeries = MonthlySeriesNumber::where('company_id', $company_id)->first();
        $nextCvrNumber = $monthlySeries ? $monthlySeries->series_number + 1 : 1;

        // Extract required codes for formatting
        $companyCode = DB::table('companies')->where('id', $company_id)->value('company_code') ?? '';
        $customerCode = DB::table('customers')->where('id', $firstDeliveryLineItem->customer_id)->value('name') ?? '';
        $expenseCode = DB::table('expense_types')->where('id', $firstDeliveryLineItem->expense_type_id)->value('expense_code') ?? '';

        $truckCode = Truck::where('id', $firstDeliveryLineItem->truck_id)->value('truck_code') ?? '';

        // Handle potential rollover
        $currentDate = new DateTime();
        $lastDayOfMonth = $currentDate->format('t');
        if ((int)$currentDate->format('j') === (int)$lastDayOfMonth) {
            $currentDate->modify('first day of next month');
        }
 
        $currentYear = $currentDate->format('Y');
        $currentMonth = $currentDate->format('m');
        $nextCvrNumberFormatted = sprintf('%03d', $nextCvrNumber);

        $formattedCvrNumber = "CVR-{$currentYear}-{$currentMonth}-{$nextCvrNumberFormatted}";

        // Fetch other required data
        $requestType = cvr_request_type::all();
        $employees = User::where('status', '!=', 0)->get();
        $fleetCards = FleetCard::where('status', 1)->get();
        $taxes = WithholdingTax::all();

        return view('coordinators.requestPullout', compact(
            'deliveryLineItems',
            'nextCvrNumberFormatted',
            'formattedCvrNumber',
            'requestType',
            'employees',
            'fleetCards',
            'trucks',
            'taxes'
        ));
    }

    public function storePullout(Request $request)
    {
        Log::info('Request Data:', ['data' => $request->all()]);

        try {
            $validated = $request->validate([
                'amount' => 'required|numeric',
                'request_type' => 'required',
                'requestor' => 'required',
                'mtm' => 'required',
                'remarks' => 'nullable|array',
                'remarks.*' => 'nullable|string',
                'voucher_type' => 'required|in:regular,with_tax',
                'withholding_tax' => 'nullable|exists:withholding_taxes,id',
                'tax_base_amount' => 'nullable|numeric|min:0',
            ]);
            Log::info('Validation Passed:', ['validated_data' => $validated]);

        } catch (ValidationException $e) {
            Log::error('Validation Errors:', ['errors' => $e->errors()]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        $sequence = CashVoucher::where('dr_id', $request->dr_id)
            ->where('cvr_type', $request->cvr_type)
            ->count() + 1;

        $company_id = $request->company_id;

        // Run everything in a DB transaction
        DB::transaction(function () use ($company_id, $request, $sequence) {
            // Handle potential rollover
            $currentDate = new DateTime(); // Always current date
            $yearMonth = $currentDate->format('Y-m');
            $year = $currentDate->format('Y');
            $month = $currentDate->format('m');

            // Lock and fetch the row for this company
            $monthlySeries = MonthlySeriesNumber::where('company_id', $company_id)
                ->lockForUpdate()
                ->first();

            if (!$monthlySeries) {
                // Create if not exists
                $monthlySeries = MonthlySeriesNumber::create([
                    'company_id' => $company_id,
                    'month' => $yearMonth,
                    'series_number' => 1,
                ]);
                $nextCvrNumber = 1;
                Log::info("Created MonthlySeriesNumber: company_id = $company_id, month = $yearMonth, series = 1");
            } else {
                // Check if the month has changed, and reset series number if true
                $isNewMonth = $monthlySeries->month !== $yearMonth;
                if ($isNewMonth) {
                    // Reset the series number for the new month
                    $monthlySeries->update([
                        'month' => $yearMonth,
                        'series_number' => 1,
                    ]);
                    $nextCvrNumber = 1;
                    Log::info("Reset MonthlySeriesNumber: company_id = $company_id, new month = $yearMonth, series = 1");
                } else {
                    // Normal increment
                    $monthlySeries->increment('series_number');
                    $nextCvrNumber = $monthlySeries->series_number;
                    Log::info("Incremented MonthlySeriesNumber: company_id = $company_id, series = $nextCvrNumber");
                }
            }

            // Construct the final CVR number
            $nextCvrNumberFormatted = sprintf('%03d', $nextCvrNumber); // Ensure it’s always 3 digits
            $formattedCvrNumber = "CVR-{$year}-{$month}-{$nextCvrNumberFormatted}/{$company_id}";

            // Fetch the user who created the record
            $user = Auth::user();
            $employeeCode = $user->id;

            // Save the actual cash voucher
            $cashVoucher = new CashVoucher([
                'cvr_number' => $formattedCvrNumber,
                'cvr_type' => $request->cvr_type,
                'amount' => $request->amount,
                'request_type' => $request->request_type,
                'requestor' => $request->requestor,
                'mtm' => $request->mtm,
                'status' => '1',
                'voucher_type' => $request->voucher_type,
                'withholding_tax_id' => $request->voucher_type === 'with_tax' ? $request->withholding_tax : null,
                'tax_based_amount' => $request->voucher_type === 'with_tax' ? $request->tax_base_amount : null,
                'remarks' => $request->has('remarks') ? json_encode($request->remarks) : null,
                'created_by' => $employeeCode,
                'dr_id' => $request->dr_id,
                'sequence' => $sequence,
            ]);

            Log::info('Saving Cash Voucher:', ['cash_voucher' => $cashVoucher->toArray()]);
            $cashVoucher->save();

            // Create and save the allocation
            $allocation = new Allocation([
                'dr_id' => $request->dr_id,
                'truck_id' => $request->truck_id,
                'requestor_id' => $request->requestor,
                'amount' => $request->amount,
                'fleet_card_id' => $request->fleet_card_id,
                'driver_id' => $request->driver_id,
                'helper' => $request->has('helpers') ? $request->helpers : null,
                'trip_type' => $request->trip_type,
                'created_by' => $employeeCode,
                'sequence' => $sequence,
            ]);
            $allocation->save();

            // Update DeliveryRequest status
            $deliveryRequest = DeliveryRequest::where('id', $request->dr_id)->first();
            if ($deliveryRequest && $deliveryRequest->status != 0) {
                $deliveryRequest->status = '3';
                $deliveryRequest->save();
                Log::info('Updated DeliveryRequest status to 3.');
            }

            // Update Line Items
            $lineItems = DeliveryRequestLineItem::where('dr_id', $request->dr_id)
                ->where('status', '!=', 0)
                ->get();

            foreach ($lineItems as $lineItem) {
                $lineItem->status = '1';
                $lineItem->save();
            }

            Log::info('Updated DeliveryRequestLineItems status to 1.');
        });

        return redirect()->route('coordinators.index', ['tab' => 'status4'])
            ->with('success', 'Cash Voucher created successfully and statuses updated.');
    }


    public function requestAccessorial($id)
    {
        $deliveryRequestTable = DeliveryRequest::resolveTableName();

        $deliveryLineItems = DeliveryRequest::join('delivery_request_line_items', $deliveryRequestTable . '.id', '=', 'delivery_request_line_items.dr_id')
            ->where($deliveryRequestTable . '.id', $id)
            ->where('delivery_request_line_items.status', '!=', 0)
            ->select($deliveryRequestTable . '.*', 'delivery_request_line_items.*', $deliveryRequestTable . '.id as request_id')
            ->get();

        if ($deliveryLineItems->isEmpty()) {
            abort(404, 'No delivery request line items found.');
        }

        $mtms = $deliveryLineItems->pluck('mtm')->unique();

        // Determine relevant trucks
        $truckIds = DeliveryRequestLineItem::whereIn('mtm', $mtms)->distinct()->pluck('truck_id');
        $trucks = Truck::all();

        $firstDeliveryLineItem = $deliveryLineItems->first();
        $company_id = $firstDeliveryLineItem->company_id ?? null;

        // Preview CVR Number (non-incremental, only for display)
        $monthlySeries = MonthlySeriesNumber::where('company_id', $company_id)->first();
        $nextCvrNumber = $monthlySeries ? $monthlySeries->series_number + 1 : 1;

        // Extract required codes for formatting
        $companyCode = DB::table('companies')->where('id', $company_id)->value('company_code') ?? '';
        $customerCode = DB::table('customers')->where('id', $firstDeliveryLineItem->customer_id)->value('name') ?? '';
        $expenseCode = DB::table('expense_types')->where('id', $firstDeliveryLineItem->expense_type_id)->value('expense_code') ?? '';

        $truckCode = Truck::where('id', $firstDeliveryLineItem->truck_id)->value('truck_code') ?? '';

        // Handle potential rollover
        $currentDate = new DateTime();
        $lastDayOfMonth = $currentDate->format('t');
        if ((int)$currentDate->format('j') === (int)$lastDayOfMonth) {
            $currentDate->modify('first day of next month');
        }

        $currentYear = $currentDate->format('Y');
        $currentMonth = $currentDate->format('m');
        $nextCvrNumberFormatted = sprintf('%03d', $nextCvrNumber);

        $formattedCvrNumber = "CVR-{$currentYear}-{$currentMonth}-{$nextCvrNumberFormatted}";

        // Fetch other required data
        $requestType = cvr_request_type::all();
        $employees = User::where('status', '!=', 0)->get();
        $fleetCards = FleetCard::where('status', 1)->get();
        $taxes = WithholdingTax::all();

        return view('coordinators.requestAccessorial', compact(
            'deliveryLineItems',
            'nextCvrNumberFormatted',
            'formattedCvrNumber',
            'requestType',
            'employees',
            'fleetCards',
            'trucks',
            'taxes'
        ));
    }

    public function storeAccessorial(Request $request)
    {
        Log::info('Request Data:', ['data' => $request->all()]);

        try {
            $validated = $request->validate([
                'amount' => 'required|numeric',
                'request_type' => 'required',
                'requestor' => 'required',
                'mtm' => 'required',
                'remarks' => 'nullable|array',
                'remarks.*' => 'nullable|string',
                'voucher_type' => 'required|in:regular,with_tax',
                'withholding_tax' => 'nullable|exists:withholding_taxes,id',
                'tax_base_amount' => 'nullable|numeric|min:0',
            ]);
            Log::info('Validation Passed:', ['validated_data' => $validated]);

        } catch (ValidationException $e) {
            Log::error('Validation Errors:', ['errors' => $e->errors()]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        $sequence = CashVoucher::where('dr_id', $request->dr_id)
            ->where('cvr_type', $request->trip_type)
            ->count() + 1;

        $company_id = $request->company_id;

        // Run everything in a DB transaction
        DB::transaction(function () use ($company_id, $request, $sequence) {
            // Handle potential rollover
            $currentDate = new DateTime(); // Always current date
            $yearMonth = $currentDate->format('Y-m');
            $year = $currentDate->format('Y');
            $month = $currentDate->format('m');
            $isFirstDayOfMonth = $currentDate->format('j') === '1';

            // Lock and fetch the row for this company
            $monthlySeries = MonthlySeriesNumber::where('company_id', $company_id)
                ->lockForUpdate()
                ->first();

            if (!$monthlySeries) {
                // Create if not exists
                $monthlySeries = MonthlySeriesNumber::create([
                    'company_id' => $company_id,
                    'month' => $yearMonth,
                    'series_number' => 1,
                ]);
                $nextCvrNumber = 1;
                Log::info("Created MonthlySeriesNumber: company_id = $company_id, month = $yearMonth, series = 1");
            } else {
                // Check if the month has changed, and reset series number if true
                $isNewMonth = $monthlySeries->month !== $yearMonth;
                if ($isNewMonth) {
                    // Reset the series number for the new month
                    $monthlySeries->update([
                        'month' => $yearMonth,
                        'series_number' => 1,
                    ]);
                    $nextCvrNumber = 1;
                    Log::info("Reset MonthlySeriesNumber: company_id = $company_id, new month = $yearMonth, series = 1");
                } else {
                    // Normal increment
                    $monthlySeries->increment('series_number');
                    $nextCvrNumber = $monthlySeries->series_number;
                    Log::info("Incremented MonthlySeriesNumber: company_id = $company_id, series = $nextCvrNumber");
                }
            }

            // Construct the final CVR number
            $nextCvrNumberFormatted = sprintf('%03d', $nextCvrNumber); // Ensure it’s always 3 digits
            $formattedCvrNumber = "CVR-{$year}-{$month}-{$nextCvrNumberFormatted}/{$company_id}";

            // Fetch the user who created the record
            $user = Auth::user();
            $employeeCode = $user->id;

            // Save the actual cash voucher
            $cashVoucher = new CashVoucher([
                'cvr_number' => $formattedCvrNumber,
                'cvr_type' => $request->trip_type,
                'amount' => $request->amount,
                'request_type' => $request->request_type,
                'requestor' => $request->requestor,
                'mtm' => $request->mtm,
                'status' => '1',
                'voucher_type' => $request->voucher_type,
                'withholding_tax_id' => $request->voucher_type === 'with_tax' ? $request->withholding_tax : null,
                'tax_based_amount' => $request->voucher_type === 'with_tax' ? $request->tax_base_amount : null,
                'remarks' => $request->has('remarks') ? json_encode($request->remarks) : null,
                'created_by' => $employeeCode,
                'dr_id' => $request->dr_id,
                'sequence' => $sequence,
            ]);

            Log::info('Saving Cash Voucher:', ['cash_voucher' => $cashVoucher->toArray()]);
            $cashVoucher->save();

            // Create and save the allocation
            $allocation = new Allocation([
                'dr_id' => $request->dr_id,
                'truck_id' => $request->truck_id,
                'requestor_id' => $request->requestor,
                'amount' => $request->amount,
                'fleet_card_id' => $request->fleet_card_id,
                'driver_id' => $request->driver_id,
                'helper' => $request->has('helpers') ? $request->helpers : null,
                'trip_type' => $request->trip_type,
                'created_by' => $employeeCode,
                'sequence' => $sequence,
            ]);
            $allocation->save();

            // Update DeliveryRequest status
            $deliveryRequest = DeliveryRequest::where('id', $request->dr_id)->first();
            if ($deliveryRequest && $deliveryRequest->status != 0) {
                $deliveryRequest->status = '2';
                $deliveryRequest->save();
                Log::info('Updated DeliveryRequest status to 2.');
            }

            // Update Line Items
            $lineItems = DeliveryRequestLineItem::where('dr_id', $request->dr_id)
                ->where('status', '!=', 0)
                ->get();

            foreach ($lineItems as $lineItem) {
                $lineItem->status = '1';
                $lineItem->save();
            }

            Log::info('Updated DeliveryRequestLineItems status to 1.');
        });

        return redirect()->route('coordinators.index', ['tab' => 'accessorial'])
            ->with('success', 'Cash Voucher created successfully and statuses updated.');

    }

}
