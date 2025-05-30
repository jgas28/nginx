<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
 
class DeliveryType extends Model
{
    use HasFactory;

    protected $table = 'delivery_types';

    protected $fillable = [
        'delivery_type_code', 'delivery_type_name'
    ];

    // Define inverse relationship with DeliveryRequest
    public function deliveryRequests()
    {
        return $this->hasMany(DeliveryRequest::class);
    }
}
