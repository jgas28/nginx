<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRequestLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'mtm',
        'warehouse_id',
        'site_name',
        'delivery_number',
        'truck_id',
        'status',
        'delivery_status',
        'delivery_address',
        'distance_type',
        'add_on_rate',
        'accessorial_type',
        'accessorial_rate',
        'dr_id',
        'created_by'
    ];

    protected $casts = [
        'warehouse_id' => 'array',
        'site_name' => 'array',
        'delivery_number' => 'array',
        'delivery_address' => 'array',
        'delivery_status' => 'array',
        'accessorial_type' => 'array',
        'accessorial_rate' => 'array',
    ];

    // Relationship with DeliveryRequest model (assuming the relationship exists in DeliveryRequest model)
    public function deliveryRequest()
    {
        return $this->belongsTo(DeliveryRequest::class, 'id', 'dr_id');
    }

    public function deliveryStatus()
    {
        return $this->belongsTo(DeliveryStatus::class, 'delivery_status');
    }

    public function distanceType()
    {
        return $this->belongsTo(DistanceType::class, 'distance_type');
    }

    public function addOnRate()
    {
        return $this->belongsTo(AddOnRate::class, 'add_on_rate');
    }

    public function accessorialType()
    {
        return $this->belongsTo(AccessorialType::class, 'accessorial_type');
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'delivery_request_line_item_warehouse', 'line_item_id', 'warehouse_id');
    }
}
