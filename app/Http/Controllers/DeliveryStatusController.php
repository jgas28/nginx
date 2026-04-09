<?php

namespace App\Http\Controllers;

use App\Models\DeliveryStatus;
use Illuminate\Http\Request;

class DeliveryStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        $deliveryStatus = DeliveryStatus::when($search, function ($query, $search) {
            return $query->where('status_code', 'like', '%' . $search . '%')
                        ->orWhere('status_name', 'like', '%' . $search . '%');
        })
        ->orderBy('status_name')
        ->paginate($perPage)
        ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('deliveryStatus.table', compact('deliveryStatus', 'search', 'perPage'))->render(),
                'search' => $search,
                'per_page' => $perPage,
                'total' => $deliveryStatus->total(),
            ]);
        }

        return view('deliveryStatus.index', compact('deliveryStatus', 'search', 'perPage'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('deliveryStatus.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'status_code' => 'required|unique:delivery_status,status_code',
            'status_name' => 'required',
        ]);

        // Create new Delivery Status
        $deliveryStatus = new DeliveryStatus([
            'status_code' => $request->status_code,
            'status_name' => $request->status_name,
        ]);

        $deliveryStatus->save();

        return redirect()->route('deliveryStatus.index')->with('success', 'Delivery Status created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DeliveryStatus $deliveryStatus)
    {
        return view('deliveryStatus.show', compact('deliveryStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeliveryStatus $deliveryStatus)
    {
        return view('deliveryStatus.edit', compact('deliveryStatus'));
    }    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeliveryStatus $deliveryStatus)
    {
        // Validate the request data
        $request->validate([
            'status_code' => 'required|unique:delivery_status,status_code,' . $deliveryStatus->id,
            'status_name' => 'required',
        ]);

        // Update the Delivery Status details
        $deliveryStatus->status_code = $request->status_code;
        $deliveryStatus->status_name = $request->status_name;
  
        $deliveryStatus->save();

        return redirect()->route('deliveryStatus.index')->with('success', 'Delivery Status updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeliveryStatus $deliveryStatus)
    {
        $deliveryStatus->delete();

        return redirect()->route('deliveryStatus.index')->with('success', 'Delivery Status deleted successfully.');
    }
}
