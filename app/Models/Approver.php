<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Approver extends Model
{
    //
    use HasFactory;

    protected $table = 'cvr_approver';

    protected $fillable = [
        'name',
        'site',
    ];

    public function cvrApprovals()
    {
        return $this->hasMany(cvr_approval::class, 'source');  // 'source' is the foreign key
    }
}
 