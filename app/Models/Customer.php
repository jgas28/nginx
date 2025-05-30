<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //name
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function deliveryRequests()
    {
        return $this->hasMany(DeliveryRequest::class);
    }
}
 