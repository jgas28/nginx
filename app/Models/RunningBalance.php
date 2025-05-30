<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RunningBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'amount',
        'description',
        'approver_id',
        'created_by',
        'cvr_number'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function approver()
    {
        return $this->belongsTo(Approver::class, 'approver_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
