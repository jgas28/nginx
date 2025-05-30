<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TruckType extends Model
{
    //
    use HasFactory;

    protected $table = 'truck_types';

    protected $fillable = [
        'truck_code', 'truck_type'
    ];
}
