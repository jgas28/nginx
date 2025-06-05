<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Allocation extends Model
{
    //
    use HasFactory;

     protected $fillable = [
        'dr_id',
        'line_item_id',
        'truck_id',
        'amount',
        'fleet_card_id',
        'driver_id',
        'helper',
        'created_by',
        'requestor_id',
        'trip_type'
    ];

    // Automatically casts the `helper` column to an array
    protected $casts = [
        'helper' => 'array',
    ];

    public function deliveryRequest()
    {
        // allocation.dr_id = delivery_request.id
        return $this->belongsTo(DeliveryRequest::class, 'dr_id', 'id');
    } 

    public function truck()
    {
        return $this->belongsTo(Truck::class, 'truck_id', 'id');
    }
}
