<?php

namespace App\Models;

use App\Http\Controllers\TruckController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'cvr_type', 'cvr_number', 'amount', 'line_item_id', 'request_type', 'requestor', 'mtm', 'driver', 'fleet_card','helpers', 'status',
        'withholding_tax_id', 'voucher_type', 'remarks', 'tax_based_amount', 'company_id', 'expense_type_id', 'supplier_id', 'description', 
        'amount_details', 'dr_id', 'created_by', 'reject_remarks', 'truck_id'
    ];

    // Conditional logic to store line_item_id only for accessorial CVRs
    public function setLineItemIdAttribute($value)
    {
        if ($this->cvr_type === 'accesorial') {
            $this->attributes['line_item_id'] = $value;
        } else {
            $this->attributes['line_item_id'] = null; // For regular CVRs, line_item_id is null
        }
    }

    // Define relationships
    public function lineItem()
    {
        return $this->belongsTo(DeliveryRequestLineItem::class, 'line_item_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'requestor', 'id');
    }

    public function cvrApprovals()
    {
        return $this->hasMany(cvr_approval::class, 'cvr_id'); // This assumes 'cvr_number' is the foreign key in the cvr_approvals table
    }

    public function liquidations()
    {
        return $this->hasMany(Liquidation::class, 'cvr_number', 'cvr_number');
    }

    public function cvrTypes()
    {
        return $this->belongsTo(cvr_request_type::class, 'request_type');
    }

    public function voucherType()
    {
        return $this->belongsTo(WithholdingTax::class, 'tax_rate');
    }

    public function suppliers()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    
    public function expenseTypes()
    {
        return $this->belongsTo(Expense_Type::class, 'expense_type_id');
    }

    public function deliveryRequest()
    {
        return $this->belongsTo(DeliveryRequest::class, 'dr_id', 'id');
    }

     public function trucks()
    {
        return $this->belongsTo(Truck::class, 'truck_id');
    }

    public function withholdingTax()
    {
        return $this->belongsTo(WithholdingTax::class, 'withholding_tax_id');
    }

}
