<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payroll';

    protected $fillable = [
        'payroll_no',
        'user_id',
        'cutoff_from',
        'cutoff_to',
        'total_days_worked',
        'total_hours_worked',
        'total_late_minutes',
        'total_undertime_minutes',
        'total_overtime_hours',
        'gross_salary',
        'total_allowance',
        'total_deduction',
        'net_salary',
        'payroll_status',
        'created_by',
    ];

    protected $casts = [
        'cutoff_from' => 'date',
        'cutoff_to' => 'date',
    ];

    /**
     * Get the user associated with this payroll
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created this payroll
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
