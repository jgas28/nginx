<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Liquidation extends Model
{
    use HasFactory;

    protected $fillable = [
        // Expense Fields
        'allowance',
        'manpower',
        'hauling',
        'right_of_way',
        'roro_expense',
        'cash_charge',

        // JSON Fields
        'gasoline', // Replaces gasoline_cash/card/type/etc
        'rfid',     // Replaces rfid_autosweep_* and rfid_easytrip_*

        // Approval Workflow
        'prepared_by',
        'noted_by',
        'validated_by',
        'collected_by',
        'approved_by',
        'collector_id',

        // Metadata
        'others',
        'status',
        'cvr_approval_id',
        'cvr_number',
        'cvr_id',
        'mtm',
        'remarks',
    ];

    protected $casts = [
        'others'   => 'array', // dynamic others
        'gasoline' => 'array', // JSON structure for multiple entries
        'rfid'     => 'array', // JSON structure for multiple entries
    ];

    public function cvrApproval()
    {
        return $this->belongsTo(cvr_approval::class, 'cvr_approval_id');
    }

    public function cashVoucher()
    {
        return $this->belongsTo(CashVoucher::class, 'cvr_number', 'cvr_number');
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function notedBy()
    {
        return $this->belongsTo(User::class, 'noted_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

     public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function runningBalances()
    {
        return $this->hasMany(RunningBalance::class, 'cvr_number', 'cvr_number');
    }
}
