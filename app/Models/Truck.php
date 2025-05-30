<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Truck extends Model
{ 
    use HasFactory;

    protected $fillable = [
        'truck_code', 'truck_name', 'plate_no', 'truck_type'
    ];

    // Relationship with DeliveryRequestLineItem model
    public function deliveryRequestLineItems()
    {
        return $this->hasMany(DeliveryRequestLineItem::class);
    }

}
 