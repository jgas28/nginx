<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\User;
use App\Models\FleetCard;
use App\Models\Truck;
use App\Models\Company;
use App\Models\cvr_request_type;
use App\Models\Expense_Type;
use App\Models\CashVoucher;
use App\Models\DeliveryRequestLineItem;
use App\Models\MonthlySeriesNumber;
use App\Models\DeliveryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Allocation;

class AllocationController extends Controller
{
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
                $query->where('status', '!=', 0)
                    ->where('delivery_status', '"8"');
            },
            'lineItems.deliveryStatus',
            'lineItems.addOnRate',
        ])
        ->whereHas('lineItems', function ($query) {
            $query->where('status', '!=', 0)
                  ->where('delivery_status', '"8"');
        })
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('mtm', 'like', '%' . $search . '%')
                ->orWhere('region', 'like', '%' . $search . '%')
                ->orWhere('province', 'like', '%' . $search . '%')
                ->orWhere('delivery_date', 'like', '%' . $search . '%');
            });
        })
        ->where('status', '!=', 0)
        ->paginate(10);
               

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json(view('allocations.table', compact('deliveryRequests'))->render());
        }

        // For non-AJAX requests, just return the view
        return view('allocations.index', compact('deliveryRequests', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'truck' => 'required|exists:trucks,id',
            'driver_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'helpers' => 'nullable|array',
            'delivery_request_ids' => 'required|array',
        ]);

        $truckId = $request->truck;
        $driverId = $request->driver_id;
        $amount = $request->amount;
        $user = Auth::user();
        $employeeCode = $user->id;
        $helpers = $request->helpers ?? [];

        Log::info('Starting the allocation process.', [
            'user_id' => $employeeCode,
            'truck_id' => $truckId,
            'driver_id' => $driverId,
            'amount' => $amount,
            'helpers' => $helpers,
            'delivery_request_ids' => $request->delivery_request_ids
        ]);

        $firstDr = true;

        foreach ($request->delivery_request_ids as $drId) {
            Log::info('Processing delivery request.', ['dr_id' => $drId]);

            $lineItems = DeliveryRequestLineItem::where('dr_id', $drId)
                ->where('status', '!=', 0)
                ->where('delivery_status', '=', '"8"') // fix: remove extra quotes
                ->get();

            $currentAmount = $firstDr ? $amount : 0;
            $firstDr = false; // only true for the first DR

            foreach ($lineItems as $lineItem) {
                // Find existing allocation
                $allocation = Allocation::where('dr_id', $drId)
                    ->first();

                if ($allocation) {
                    $allocation->update([
                        'amount' => $currentAmount,
                        'helper' => $helpers,
                        'created_by' => $employeeCode,
                    ]);
                    Log::info('Updated existing allocation.', ['allocation_id' => $allocation->id]);
                } else {
                    $allocation = Allocation::create([
                        'dr_id' => $drId,
                        'line_item_id' => $lineItem->id,
                        'truck_id' => $truckId,
                        'driver_id' => $driverId,
                        'helper' => $helpers,
                        'amount' => $currentAmount,
                        'created_by' => $employeeCode,
                    ]);
                    Log::info('Created new allocation.', ['allocation_id' => $allocation->id]);
                }

                // Update delivery status
                $lineItem->update(['delivery_status' => '14']);

                Log::info('Updated delivery status of line item.', [
                    'line_item_id' => $lineItem->id,
                    'new_status' => '14',
                ]);
            }
        }

        Log::info('Cash voucher allocations processed successfully.', [
            'user_id' => $employeeCode,
            'delivery_request_ids' => $request->delivery_request_ids,
        ]);

        return redirect()->route('allocations.index')->with('success', 'Cash voucher allocations processed successfully.');
    }

    public function allocate(Request $request)
    {
        $ids = explode(',', $request->query('ids'));
        $deliveryRequests = DeliveryRequest::whereIn('id', $ids)->with([
            'company',
            'region',
            'truckType',
            'area',
            'region',
            'lineItems.deliveryStatus',
            'lineItems.addOnRate',
        ])->get();

        // Flatten all line items into one collection
        $deliveryLineItems = $deliveryRequests->flatMap->lineItems;

        // Generate dummy CVR numbers (you’ll replace this with your logic)
        $formattedCvrNumbers = [];
        foreach ($deliveryRequests as $request) {
            $formattedCvrNumbers[$request->id] = 'CVR-2025-05-00' . $request->id; // Example format
        }

        // Pass supporting data (you need to load these from DB or services)
        $employees = User::all(); // or however you're fetching
        $fleetCards = FleetCard::all();
        $trucks = Truck::all();
        $requestType = cvr_request_type::all();

        return view('allocations.allocate', compact(
            'ids',
            'deliveryRequests', 
            'deliveryLineItems',
            'formattedCvrNumbers',
            'employees',
            'fleetCards',
            'trucks',
            'requestType',
        ));
    }

}