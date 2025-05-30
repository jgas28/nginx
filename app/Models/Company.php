<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_code', 'company_name', 'company_location'
    ];

    public function deliveryRequests()
    {
        return $this->hasMany(DeliveryRequest::class);
    }

    public function monthlySeriesNumbers()
{
    return $this->hasMany(MonthlySeriesNumber::class);
}
}
 