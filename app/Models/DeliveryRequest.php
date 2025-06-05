<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class DeliveryRequest extends Model
{ 
    use HasFactory;

    protected $table = 'delivery_request';

    protected $fillable = [
        'mtm', 'booking_date', 'delivery_date', 'delivery_type', 'delivery_rate', 
        'company_id', 'project_name', 'region_id', 'status', 'customer_id', 'truck_type_id','area_id', 'expense_type_id', 'delivery_request_type', 
        'created_by','delivery_status'
    ];

    // Define relationships
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    } 

    public function deliveryType()
    {
        return $this->belongsTo(DeliveryType::class, 'delivery_type', 'delivery_type_code');
    }

    public function lineItems()
    {
        return $this->hasMany(DeliveryRequestLineItem::class, 'dr_id', 'id');
    }

    public function cashVoucher()
    {
        return $this->hasMany(CashVoucher::class, 'mtm', 'mtm');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function expenseType()
    {
        return $this->belongsTo(Expense_Type::class, 'expense_type_id');
    }

    public function truckType()
    {
        return $this->belongsTo(truckType::class, 'truck_type_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function allocations()
    {
        // delivery_request.id = allocation.dr_id
        return $this->hasMany(Allocation::class, 'dr_id', 'id');
    }
}
