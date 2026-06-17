<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['name', 'slug', 'controller'];

    public function permissions()
    {
        return $this->hasMany(RoleModulePermission::class);
    }

    public static function forController(string $controller): ?self
    {
        return static::where('controller', $controller)->first();
    }
}
