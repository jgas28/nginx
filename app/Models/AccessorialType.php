<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessorialType extends Model
{
    use HasFactory;

    protected $table = 'accessorial_types';

    protected $fillable = [
        'accessorial_types_code', 'accessorial_types_name'
    ];

    // Relationship with DeliveryRequestLineItem model
    public function deliveryRequestLineItems()
    {
        return $this->hasMany(DeliveryRequestLineItem::class);
    }
}
