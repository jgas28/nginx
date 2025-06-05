<?php

namespace App\Http\Controllers;

use App\Models\AccessorialType;
use App\Models\DeliveryRequest;
use App\Models\DeliveryRequestLineItem;
use App\Models\DeliveryStatus;
use App\Models\DeliveryType;
use App\Models\Company;
use App\Models\Region;
use App\Models\Warehouse;
use App\Models\AddOnRate;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Truck;
use App\Models\DistanceType;
use App\Models\Expense_Type;
use App\Models\TruckType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class DeliveryRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the search term if it exists
        $search = $request->input('search');

        // Query the Delivery Request table
        $deliveryRequests = DeliveryRequest::with([
            'company',
            'region',
            'truckType',
            'area',
            'lineItems' => function ($query) {
                $query->where('status', '!=', 0); // Filter out line items with status = 0
            },
            'lineItems.deliveryStatus',
            'lineItems.addOnRate',
        ])
        ->when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('mtm', 'like', '%' . $search . '%')
                    ->orWhere('customer_id', 'like', '%' . $search . '%')
                    ->orWhere('booking_date', 'like', '%' . $search . '%')
                    ->orWhere('delivery_date', 'like', '%' . $search . '%')
                    ->orWhere('delivery_type', 'like', '%' . $search . '%')
                    ->orWhere('delivery_rate', 'like', '%' . $search . '%')
                    ->orWhereHas('company', function ($companyQuery) use ($search) {
                        $companyQuery->where('company_code', 'like', '%' . $search . '%');
                    })
                    ->orWhere('project_name', 'like', '%' . $search . '%')
                    ->orWhereHas('region', function ($companyQuery) use ($search) {
                        $companyQuery->where('region_code', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('area', function ($companyQuery) use ($search) {
                        $companyQuery->where('area_code', 'like', '%' . $search . '%');
                    })
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        })
        ->where('status', '!=', 0)
        ->paginate(10);
               

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('deliveryRequest.table', compact('deliveryRequests'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('deliveryRequest.index', compact('deliveryRequests', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
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
        return view('deliveryRequest.create', compact('companies', 'regions', 'deliveryTypes', 'warehouses', 'AddOnRates_multiDrops', 'deliveryStatuses', 'trucks', 'distances', 'AddOnRates_multiPickUps', 'accessorialTypes', 'customers', 'truckTypes','areas', 'expenseTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $employeeCode = $user->id;

        Log::debug('Delivery Type:', ['delivery_type' => $request->delivery_type]);

        // Validate the request data
        $validationRules = [
            // 'mtm' => 'required|unique:delivery_request,mtm',
            'mtm' => [
                'required',
                Rule::unique('delivery_request', 'mtm')->where(function ($query) {
                    return $query->where('status', '!=', 0); // Ensure it is unique among active records
                }),
            ],
            'customer_id' => 'required',
            'booking_date' => 'required',
            'delivery_date' => 'required',
            'delivery_rate' => 'required',
            'truck_type_id' => 'required',
            'company_id' => 'required',
            'project_name' => 'required',
            'region_id' => 'required',
            'area_id' => 'required',
            'delivery_type' => 'required', // This is always required
            'expense_type_id' => 'required',
            'delivery_status' => 'required',
        ];

        // Add specific validation for delivery types
        if ($request->delivery_type == 'Regular') {
            $validationRules['regular'] = 'nullable|array';
            // $validationRules['regular.*.warehouse_id'] = 'nullable|string';
            $validationRules['regular.*.site_name'] = 'nullable|string';
            $validationRules['regular.*.delivery_number'] = 'nullable|string';
            $validationRules['regular.*.delivery_address'] = 'nullable|string';
            $validationRules['regular.*.distance_type'] = 'nullable|string';
            $validationRules['regular.*.accessorial_type'] = 'nullable|string';
            $validationRules['regular.*.accessorial_rate'] = 'nullable|string';
            // $validationRules['regular.*.add_on_rate'] = 'nullable|string';
        }

        if ($request->delivery_type == 'Multi-Drop') {
            $validationRules['multi_drop'] = 'nullable|array';
            $validationRules['multi_drop.*.warehouse_id'] = 'nullable|string';
            // $validationRules['multi_drop.*.site_name'] = 'nullable|string';
            $validationRules['multi_drop.*.delivery_number'] = 'nullable|string';
            // $validationRules['multi_drop.*.delivery_address'] = 'nullable|string';
            $validationRules['multi_drop.*.distance_type'] = 'nullable|string';
            $validationRules['multi_drop.*.accessorial_type'] = 'nullable|string';
            $validationRules['multi_drop.*.accessorial_rate'] = 'nullable|string';
            // $validationRules['multi_drop.*.add_on_rate'] = 'nullable|string';
        }

        if ($request->delivery_type == 'Multi Pick-Up') {
            $validationRules['multi_pickup'] = 'nullable|array';
            $validationRules['multi_pickup.*.warehouse_id'] = 'nullable|string';
            $validationRules['multi_pickup.*.site_name'] = 'nullable|string';
            $validationRules['multi_pickup.*.delivery_address'] = 'nullable|string';
            $validationRules['multi_pickup.*.distance_type'] = 'nullable|string';
            $validationRules['multi_pickup.*.add_on_rate'] = 'nullable|string';
            $validationRules['multi_pickup.*.accessorial_type'] = 'nullable|string';
            $validationRules['multi_pickup.*.accessorial_rate'] = 'nullable|string';
        }

        // Perform the validation
        $request->validate($validationRules);

        Log::debug('Validated data:', $request->all());

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
                'delivery_status' => $request->delivery_status,
                'created_by' => $employeeCode,
            ]);
            $deliveryRequest->save();

            $deliveryRequestId = $deliveryRequest->id;

            Log::debug('Get DR ID:', ['delivery_request_id' => $deliveryRequestId]);

            // Conditionally save line items based on delivery type
            switch ($request->delivery_type) {
                case 'Regular':
                    if ($request->has('regular') && count($request->regular) > 0) {
                        Log::debug('Saving Regular Line Items:', $request->regular);
                        $this->saveLineItems($request->regular, $request->mtm, $deliveryRequestId, $employeeCode);
                    } else {
                        Log::debug('No Regular Line Items to save');
                    }
                    break;

                case 'Multi-Drop':
                    if ($request->has('multi_drop') && count($request->multi_drop) > 0) {
                        Log::debug('Saving Multi-Drop Line Items:', $request->multi_drop);
    
                        // Create an updated multi-drop array with missing fields filled
                        $updatedMultiDropItems = [];
                        foreach ($request->multi_drop as $index => $lineItem) {
                            // If some fields are missing in multi_drop[n], use values from multi_drop[0]
                            if ($index > 0) {
                                $lineItem['warehouse_id'] = $lineItem['warehouse_id'] ?? $request->multi_drop[0]['warehouse_id'];
                                $lineItem['add_on_rate'] = $lineItem['add_on_rate'] ?? $request->multi_drop[0]['add_on_rate'];
                            }
    
                            $updatedMultiDropItems[] = $lineItem;
                        }
    
                        // Now save the updated multi-drop items
                        $this->saveLineItems($updatedMultiDropItems, $request->mtm, $deliveryRequestId, $employeeCode);
    
                    } else {
                        Log::debug('No Multi-Drop Line Items to save');
                    }
                    break;

                    case 'Multi Pick-Up':
                        if ($request->has('multi_pickup') && count($request->multi_pickup) > 0) {
                            Log::debug('Saving Multi Pick-Up Line Items:', $request->multi_pickup);
        
                            // Create an updated multi-pickup array with missing common fields filled
                            $updatedMultiPickupItems = [];
                            foreach ($request->multi_pickup as $index => $lineItem) {
                                // Fill common fields like site_name, delivery_address, and add_on_rate if missing
                                $lineItem['site_name'] = $lineItem['site_name'] ?? $request->multi_pickup[0]['site_name'];
                                $lineItem['delivery_address'] = $lineItem['delivery_address'] ?? $request->multi_pickup[0]['delivery_address'];
                                $lineItem['add_on_rate'] = $lineItem['add_on_rate'] ?? $request->multi_pickup[0]['add_on_rate'];
        
                                // Add the updated line item to the array
                                $updatedMultiPickupItems[] = $lineItem;
                            }
        
                            // Now save the updated multi-pickup items
                            $this->saveLineItems($updatedMultiPickupItems, $request->mtm, $deliveryRequestId, $employeeCode);
        
                        } else {
                            Log::debug('No Multi Pick-Up Line Items to save');
                        }
                    break;

                default:
                    Log::debug('Unknown Delivery Type');
                    break;
            }

            // Commit the transaction if everything is successful
            DB::commit();

            return redirect()->route('coordinators.index')->with('success', 'Delivery Request created successfully.');
        } catch (\Exception $e) {
            // Rollback if there is an error
            DB::rollBack();
            Log::error('Error saving delivery request and line items: ' . $e->getMessage());
            return redirect()->route('allocations.index')->with('error', 'Failed to create Delivery Request.');
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(DeliveryRequest $deliveryRequest)
    {
        return view('deliveryRequest.show', compact('deliveryRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeliveryRequest $deliveryRequest)
    {
        // Fetch related delivery line items by joining with the correct table name
        $deliveryLineItems = DeliveryRequestLineItem::join('delivery_request', 'delivery_request.mtm', '=', 'delivery_request_line_items.mtm')
        ->where('delivery_request.id', $deliveryRequest->id)
        ->where('delivery_request_line_items.status', '!=', 0)
        ->select('delivery_request_line_items.*', 'delivery_request.id as request_id')
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

        return view('deliveryRequest.edit', compact(
            'companies', 'regions', 'warehouses', 'AddOnRates_multiDrops', 
            'AddOnRates_multiPickUps', 'deliveryStatuses', 'trucks', 'distances', 'deliveryLineItems', 
            'deliveryRequest', 'deliveryTypes', 'accessorialTypes', 'customers', 'truckTypes', 'areas', 'expenseTypes'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeliveryRequest $deliveryRequest)
    {
        Log::debug('Delivery Type:', ['delivery_type' => $request->delivery_type]);


        // Validate the request data
        $validationRules = [
            'mtm' => 'required|unique:delivery_request,mtm,' . $deliveryRequest->id,
            'customer_id' => 'required',
            'booking_date' => 'required',
            'delivery_date' => 'required',
            'delivery_rate' => 'required',
            'truck_type_id' => 'required',
            'company_id' => 'required',
            'project_name' => 'required',
            'region_id' => 'required',
            'area_id' => 'required',
            'delivery_type' => 'required', // This is always required
            'expense_type_id' => 'required',    
            'delivery_status' => 'required',
        ];

        // Add specific validation for delivery types
        if ($request->delivery_type == 'Regular') {
            $validationRules['regular'] = 'nullable|array';
            $validationRules['regular.*.site_name'] = 'nullable|string';
            $validationRules['regular.*.delivery_number'] = 'nullable|string';
            $validationRules['regular.*.delivery_address'] = 'nullable|string';
            $validationRules['regular.*.distance_type'] = 'nullable|string';

            $validationRules['regular.*.accessorial_type'] = 'nullable|string';
            $validationRules['regular.*.accessorial_rate'] = 'nullable|string';
        }

        if ($request->delivery_type == 'Multi-Drop') {
            $validationRules['multi_drop'] = 'nullable|array';
            $validationRules['multi_drop.*.warehouse_id'] = 'nullable|string';
            $validationRules['multi_drop.*.delivery_number'] = 'nullable|string';
            $validationRules['multi_drop.*.distance_type'] = 'nullable|string';

            $validationRules['multi_drop.*.accessorial_type'] = 'nullable|string';
            $validationRules['multi_drop.*.accessorial_rate'] = 'nullable|string';
        }

        if ($request->delivery_type == 'Multi Pick-Up') {
            $validationRules['multi_pickup'] = 'nullable|array';
            $validationRules['multi_pickup.*.warehouse_id'] = 'nullable|string';
            $validationRules['multi_pickup.*.site_name'] = 'nullable|string';
            $validationRules['multi_pickup.*.delivery_address'] = 'nullable|string';
            $validationRules['multi_pickup.*.distance_type'] = 'nullable|string';
            $validationRules['multi_pickup.*.add_on_rate'] = 'nullable|string';

            $validationRules['multi_pickup.*.accessorial_type'] = 'nullable|string';
            $validationRules['multi_pickup.*.accessorial_rate'] = 'nullable|string';
        }

        Log::debug('Starting Validation...', $request->all());

        // Perform the validation
        $request->validate($validationRules);
        
        Log::debug('Validated data:', $request->all());
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
            $deliveryRequest->status = '1';
            $deliveryRequest->delivery_status = $request->delivery_status;

            $deliveryRequest->update();
            Log::debug('DeliveryRequest saved:', $deliveryRequest->toArray());
            // Conditionally update line items based on delivery type
            switch ($request->delivery_type) {
                case 'Regular':
                    if ($request->has('regular') && count($request->regular) > 0) {
                        Log::debug('Saving Regular Line Items:', $request->regular);
                        $this->updateLineItems($request->regular, $request->mtm);
                    } else {
                        Log::debug('No Regular Line Items to save');
                    }
                    break;

                case 'Multi-Drop':
                    if ($request->has('multi_drop') && count($request->multi_drop) > 0) {
                        Log::debug('Saving Multi-Drop Line Items:', $request->multi_drop);
                        
                        // Create an updated multi-drop array with missing fields filled
                        $updatedMultiDropItems = [];
                        foreach ($request->multi_drop as $index => $lineItem) {
                            // If some fields are missing in multi_drop[n], use values from multi_drop[0]
                            if ($index > 0) {
                                $lineItem['warehouse_id'] = $lineItem['warehouse_id'] ?? $request->multi_drop[0]['warehouse_id'];
                                $lineItem['add_on_rate'] = $lineItem['add_on_rate'] ?? $request->multi_drop[0]['add_on_rate'];
                            }
                            $updatedMultiDropItems[] = $lineItem;
                        }

                        // Now save the updated multi-drop items
                        $this->updateLineItems($updatedMultiDropItems, $request->mtm);

                    } else {
                        Log::debug('No Multi-Drop Line Items to save');
                    }
                    break;

                case 'Multi Pick-Up':
                    if ($request->has('multi_pickup') && count($request->multi_pickup) > 0) {
                        Log::debug('Saving Multi Pick-Up Line Items:', $request->multi_pickup);

                        // Create an updated multi-pickup array with missing common fields filled
                        $updatedMultiPickupItems = [];
                        foreach ($request->multi_pickup as $index => $lineItem) {
                            // Fill common fields like site_name, delivery_address, and add_on_rate if missing
                            $lineItem['site_name'] = $lineItem['site_name'] ?? $request->multi_pickup[0]['site_name'];
                            $lineItem['delivery_address'] = $lineItem['delivery_address'] ?? $request->multi_pickup[0]['delivery_address'];
                            $lineItem['add_on_rate'] = $lineItem['add_on_rate'] ?? $request->multi_pickup[0]['add_on_rate'];

                            // Add the updated line item to the array
                            $updatedMultiPickupItems[] = $lineItem;
                        }

                        // Now save the updated multi-pickup items
                        $this->updateLineItems($updatedMultiPickupItems, $request->mtm);

                    } else {
                        Log::debug('No Multi Pick-Up Line Items to save');
                    }
                    break;

                default:
                    Log::debug('Unknown Delivery Type');
                    break;
            }

            // Commit the transaction if everything is successful
            DB::commit();

            return redirect()->route('deliveryRequest.index')->with('success', 'Delivery Request updated successfully.');
        } catch (\Exception $e) {
            // Rollback if there is an error
            DB::rollBack();
            Log::error('Error updating delivery request and line items: ' . $e->getMessage());
            return redirect()->route('deliveryRequest.index')->with('error', 'Failed to update Delivery Request.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeliveryRequest $deliveryRequest)
    {
        // Change the status of the DeliveryRequest to 0
        $deliveryRequest->status = 0;
        $deliveryRequest->update();

        // Change the status of all related DeliveryRequestLineItems to 0
        $deliveryRequest->lineItems()->update(['status' => 0]);

        return redirect()->route('deliveryRequest.index')->with('success', 'Delivery Request deleted successfully.');
    }

    // Helper function to save line items
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
                
                // You can validate the data here if necessary, for example:
                // $deliveryRequestLineItem->validate();
                
                $deliveryRequestLineItem->save();
                Log::debug('Line Item saved successfully', ['mtm' => $mtm, 'lineItem' => $lineItem]);
            } catch (\Exception $e) {
                // Log more detailed error information including the data being saved
                Log::error('Error saving delivery request line item: ' . $e->getMessage(), [
                    'mtm' => $mtm,
                    'lineItem' => $lineItem
                ]);
            }
        }
    }

    private function updateLineItems($lineItems, $mtm)
    {
        // Start a database transaction
        DB::beginTransaction();
        try {
            foreach ($lineItems as $key => $lineItem) {
                // Ensure that 'id' is present for existing items, or handle the new item
                if (empty($lineItem['id'])) {
                    // Handle new line items (those without an ID)
                    $this->saveLineItems([$lineItem], $mtm);
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
                $lineItem['accessorial_rate'] = empty($lineItem['accessorial_rate']) ? null : $lineItem['accessorial_rate'];
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
                    $this->saveLineItems([$lineItem], $mtm);
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
        // Fetch related delivery line items by joining with the correct table name
        $deliveryLineItems = DeliveryRequestLineItem::join('delivery_request', 'delivery_request.mtm', '=', 'delivery_request_line_items.mtm')
        ->where('delivery_request.id', $deliveryRequest->id)
        ->where('delivery_request_line_items.status', '!=', 0)
        ->select('delivery_request_line_items.*', 'delivery_request.id as request_id')
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

        return view('deliveryRequest.splitView', compact(
            'companies', 'regions', 'warehouses', 'AddOnRates_multiDrops', 
            'AddOnRates_multiPickUps', 'deliveryStatuses', 'trucks', 'distances', 'deliveryLineItems', 
            'deliveryRequest', 'deliveryTypes', 'accessorialTypes', 'customers', 'truckTypes' ,'areas'
        ));
    }

    public function showSplitForm($id, Request $request)
    {
        $requestId = $request->query('request_id');

        $deliveryLineItems = DeliveryRequestLineItem::join('delivery_request', 'delivery_request.mtm', '=', 'delivery_request_line_items.mtm')
        ->where('delivery_request_line_items.id', $id)
        ->where('delivery_request_line_items.status', '!=', 0)
        ->select('delivery_request_line_items.*', 'delivery_request.id as request_id')
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

        return view('deliveryRequest.split', compact(
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

        // Step 3: Update the MTM by appending '-1' to the existing MTM
        // $updatedMtm = $deliveryRequest->mtm . '-1';
        // $deliveryRequest->mtm = $updatedMtm;
        // $deliveryRequest->save(); // Save the updated DeliveryRequest

        // Step 4: Initialize the lineItemIds array
        $lineItemIds = [];

        // Step 5: Update line items and track lineItemIds for selected ones
        if (isset($data['regular'])) {
            foreach ($data['regular'] as $index => $lineItemData) {
                // Extract the line item ID
                $lineItemId = $lineItemData['id'];
        
                // Store the line item ID in the array for later use
                $lineItemIds[] = $lineItemId;

                // Find the corresponding DeliveryLineItem
                $lineItem = DeliveryRequestLineItem::find($lineItemId);

                // Check if the line item exists
                if ($lineItem) {
                    // Update MTM to append '-2' to line items that have been split
                    $lineItem->mtm = $lineItem->mtm . '-1';
                    $lineItem->status = 0; // Set the status to 0 for items that have been split
                    $lineItem->save();
                }
            }
        }

        // Step 6: Update the DeliveryRequestLineItems that have not been selected
        // $allLineItems = DeliveryRequestLineItem::where('mtm', $deliveryRequest->mtm)->get();
        // foreach ($allLineItems as $lineItem) {
        //     // Check if the line item ID is not in the selected list (those that were not selected)
        //     if (!in_array($lineItem->id, $lineItemIds)) {
        //         // Update the MTM to append '-2' for those not selected
        //         $lineItem->mtm = $lineItem->mtm . '-1';
        //         $lineItem->save();
        //     }
        // }

        // Step 7: Create the new DeliveryRequest with the provided data
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
        $deliveryRequest  = DeliveryRequest::create($deliveryRequestData);

        $newlyCreatedId = $deliveryRequest->id;

        // Step 8: Handle DeliveryLineItems for the new request
        if (isset($data['regular'])) {
            foreach ($data['regular'] as $index => $lineItemData) {
                // Create the new line item associated with the new delivery request
                $lineItem = [
                    'mtm' => $data['mtm'],
                    'warehouse_id' => $lineItemData['warehouse_id'],
                    'delivery_number' => $lineItemData['delivery_number'],
                    'site_name' => $lineItemData['site_name'],
                    'delivery_address' => $lineItemData['delivery_address'],
                    'status' => 1,
                    'created_by' => $employeeCode,
                    'dr_id' =>  $newlyCreatedId,
                ];

                // Create the DeliveryLineItem in the database
                DeliveryRequestLineItem::create($lineItem);
            }
        }

        // Step 9: Redirect or show a success message
        return redirect()->route('deliveryRequest.index')->with('success', 'Delivery request created and line items updated successfully!');
    }

}
