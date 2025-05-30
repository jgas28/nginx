<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FleetCard extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'account',
        'account_name',
        'account_number',
    ];

}
