<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'area_code', 'area_name'
    ];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }
}
