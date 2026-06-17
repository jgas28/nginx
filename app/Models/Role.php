<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Define the relationship to users
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function modulePermissions()
    {
        return $this->hasMany(RoleModulePermission::class);
    }

    public function canOnModule(string $moduleSlug, string $action): bool
    {
        return $this->modulePermissions()
            ->whereHas('module', fn ($query) => $query->where('slug', $moduleSlug))
            ->where("can_{$action}", true)
            ->exists();
    }
}
