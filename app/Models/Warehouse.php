<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_code', 'warehouse_name', 'warehouse_location'
    ];

    // Relationship with DeliveryRequestLineItem model
    public function deliveryRequestLineItems()
    {
        return $this->hasMany(DeliveryRequestLineItem::class);
    }

}
 