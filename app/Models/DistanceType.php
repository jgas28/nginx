<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DistanceType extends Model
{
    use HasFactory;

    protected $table = 'distance_types';

    protected $fillable = [
        'distance_type_code', 'distance_type_name'
    ];

     // Relationship with DeliveryRequestLineItem model
     public function deliveryRequestLineItems()
     {
         return $this->hasMany(DeliveryRequestLineItem::class);
     }
}
