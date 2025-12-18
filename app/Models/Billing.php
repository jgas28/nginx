<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'soa_number',
        'company_id',
        'withholding_tax_id',
        'billed_to',
        'billing_address',
        'billing_date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function withholdingTax()
    {
        return $this->belongsTo(WithholdingTax::class);
    }

    // Many-to-many relationship with DeliveryRequest
    public function deliveryRequests()
    {
        return $this->belongsToMany(DeliveryRequest::class, 'billing_delivery_request', 'billing_id', 'delivery_request_id')
                    ->withTimestamps();
    }

    // Many-to-many relationship with DeliveryRequestLineItem
    public function deliveryRequestLineItems()
    {
        return $this->belongsToMany(DeliveryRequestLineItem::class, 'billing_delivery_request_line_item', 'billing_id', 'delivery_request_line_item_id')
                    ->withTimestamps();
    }
}
