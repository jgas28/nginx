<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlySeriesNumber extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'monthly_series_numbers';

    // Specify the fillable attributes (optional, for mass assignment protection)
    protected $fillable = ['company_id','month', 'series_number'];

    // Disable the timestamps if you don't want them automatically handled (optional)
    public $timestamps = true;

    // Define the default value for the series number if needed
    protected $attributes = [
        'series_number' => 0,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
