<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends Model
{ 
    use HasFactory;

    protected $fillable = [
        'region_code', 'region_name', 'province', 'area_id'
    ];

    // Define inverse relationship with DeliveryRequest
    public function deliveryRequests()
    {
        return $this->hasMany(DeliveryRequest::class);
    }


    public function area()
    {
        return $this->belongsTo(Area::class);
    }

}
 