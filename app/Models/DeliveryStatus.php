<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryStatus extends Model
{
    use HasFactory;

    protected $table = 'delivery_status';

    protected $fillable = [
        'status_code', 'status_name'
    ];

    // Relationship with DeliveryRequestLineItem model
    public function deliveryRequestLineItems()
    {
        return $this->hasMany(DeliveryRequestLineItem::class);
    }

}
