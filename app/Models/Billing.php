<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Billing extends Model
{
    use HasFactory;

     protected $fillable = [
        'soa_number',
        'company_id',
        'withholding_tax_id',
        'billed_to',
        'billing_address',
        'total_price',
        'billing_date',
        'status',
        'created_by',
        'received_by',
        'prepared_by ',
        'billing_type',
    ];

     public function withholdingTax()
    {
        return $this->belongsTo(WithholdingTax::class, 'withholding_tax_id');
    }
    
    public function employee()
    {
        return $this->belongsTo(User::class, 'requestor', 'id');
    }

    public function deliveryRequests()
    {
        return $this->hasMany(DeliveryRequest::class, 'billing_id');
    }


}
