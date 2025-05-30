<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class cvr_request_type extends Model
{
    //
    use HasFactory;

    protected $table = 'cvr_request_type';

    protected $fillable = [
        'request_code', 'request_type', 'group_type'
    ];

    public function deliveryRequests()
    {
        return $this->hasMany(DeliveryRequest::class);
    }
}

