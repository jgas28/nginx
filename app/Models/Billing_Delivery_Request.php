<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Billing_Delivery_Request extends Model
{
    //
    use HasFactory;

    protected $table = 'billing_delivery_request';

    protected $fillable = [
        'billing_id', 'delivery_request_id', 'created_at', 'updated_at'
    ];

}
