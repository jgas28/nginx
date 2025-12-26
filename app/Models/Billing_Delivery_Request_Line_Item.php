<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Billing_Delivery_Request_Line_Item extends Model
{
    //
    use HasFactory;

    protected $table = 'billing_delivery_request_line_item';

    protected $fillable = [
        'billing_id', 'delivery_request_line_item_id', 'created_at', 'updated_at'
    ];

}
