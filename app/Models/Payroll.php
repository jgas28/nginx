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
        'compensation_basis',
        'base_rate',
        'gross_salary',
        'incentive_amount',
        'total_allowance',
        'sss_deduction',
        'pagibig_deduction',
        'philhealth_deduction',
        'tax_deduction',
        'cellphone_loan',
        'gasul_fund',
        'unreturn_budget',
        'cash_bond',
        'other_deduction',
        'total_deduction',
        'net_salary',
        'payroll_status',
        'created_by',
    ];

    protected $casts = [
        'cutoff_from'         => 'date',
        'cutoff_to'           => 'date',
        'base_rate'           => 'decimal:2',
        'gross_salary'        => 'decimal:2',
        'incentive_amount'    => 'decimal:2',
        'total_allowance'     => 'decimal:2',
        'sss_deduction'       => 'decimal:2',
        'pagibig_deduction'   => 'decimal:2',
        'philhealth_deduction'=> 'decimal:2',
        'tax_deduction'       => 'decimal:2',
        'cellphone_loan'      => 'decimal:2',
        'gasul_fund'          => 'decimal:2',
        'unreturn_budget'     => 'decimal:2',
        'cash_bond'           => 'decimal:2',
        'other_deduction'     => 'decimal:2',
        'total_deduction'     => 'decimal:2',
        'net_salary'          => 'decimal:2',
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
