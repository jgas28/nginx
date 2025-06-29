<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryRequest;
use App\Models\DeliveryRequestLineItem;

class DetailsController extends Controller
{
    public function index()
    {
        $deliveryRequests = \App\Models\DeliveryRequest::with([
            'lineItems',
            'cashVouchers',
            'cvrApprovals', 
            'liquidations',
        ])->get();

        return view('details.index', compact('deliveryRequests'));
    }
}
