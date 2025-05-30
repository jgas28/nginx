<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense_Type extends Model
{
    use HasFactory;  

    protected $table = 'expense_types';
    //
    protected $fillable = [
        'expense_code', 'expense_name','type'
    ];

    public function deliveryRequestType()
    {
        return $this->belongsTo(DeliveryRequestType::class, 'type', 'id');
    }
}
