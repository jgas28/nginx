<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddOnRate extends Model
{
    use HasFactory;

    protected $table = 'add_on_rates';

    protected $fillable = [
        'add_on_rate_type_code', 'add_on_rate_type_name', 'rate', 'percent_rate', 'delivery_type'
    ];

    // Relationship with DeliveryRequestLineItem model
    public function deliveryRequestLineItems()
    {
        return $this->hasMany(DeliveryRequestLineItem::class);
    }
}