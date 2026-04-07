<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soa extends Model
{
    use HasFactory;

    protected $fillable = [
        'soa_number',
        'company_id',
        'customer_id',
        'billing_period_from',
        'billing_period_to',
        'statement_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'outstanding_amount',
        'status',
        'notes',
        'created_by',
        'delivery_request_line_item_ids', // JSON array of delivery request line item IDs included in this SOA
    ];

    protected $casts = [
        'billing_period_from' => 'date',
        'billing_period_to' => 'date',
        'statement_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'delivery_request_line_item_ids' => 'array',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deliveryRequestLineItems()
    {
        return $this->belongsToMany(\App\Models\DeliveryRequestLineItem::class, 'soa_delivery_line_items', 'soa_id', 'delivery_request_line_item_id');
    }
}
