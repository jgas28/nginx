<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cvr_approval extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'payment_type', 'payment_name', 'reference_number', 'amount', 'receiver','source', 'charge', 'cvr_number', 'status', 'created_by', 'cvr_id', 'reject_remarks' 
    ];

    public function deliveryRequests() 
    {
        return $this->hasMany(DeliveryRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(Approver::class, 'source');
    }

    public function liquidations()
    {
        return $this->hasMany(Liquidation::class, 'cvr_approval_id');
    }

    public function cashVoucher()
    {
        return $this->belongsTo(CashVoucher::class, 'cvr_id', 'id');
    }

}
