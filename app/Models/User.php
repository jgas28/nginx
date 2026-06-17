<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    private const DASHBOARD_ROLE_VIEW_MAP = [
        37 => 'dashboards.coordinator',
        38 => 'dashboards.admin',
        39 => 'dashboards.allocation',
        40 => 'dashboards.owner1',
        41 => 'dashboards.owner2',
    ];

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_code',
        'fname',
        'lname',
        'email',
        'position',
        'role_id',
        'password',
        'status',
        'employment_status',
        'daily_rate',
        'monthly_salary',
        'sss_no',
        'philhealth_no',
        'tin_no',
        'company_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'daily_rate' => 'decimal:2',
            'monthly_salary' => 'decimal:2',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRoleId([1, 2]);
    }

    public function hasRole($roleName)
    {
        return $this->roles->contains('name', $roleName);
    }

    // Check if user has a role by ID
    public function hasRoleId(int $roleId): bool
    {
        return $this->roles->contains('id', $roleId);
    }

    // Check if user has any role in array of IDs
    public function hasAnyRoleId(array $roleIds): bool
    {
        return $this->roles->whereIn('id', $roleIds)->isNotEmpty();
    }

    public function modulePermissions()
    {
        return $this->hasMany(UserModulePermission::class);
    }

    public function canOnModule(string $moduleSlug, string $action): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $override = $this->modulePermissions()
            ->whereHas('module', fn ($query) => $query->where('slug', $moduleSlug))
            ->first();

        if ($override) {
            return (bool) $override->{"can_{$action}"};
        }

        return $this->roles->contains(fn ($role) => $role->canOnModule($moduleSlug, $action));
    }

    public function hasDashboardAccess(): bool
    {
        return $this->hasAnyRoleId(array_keys(self::DASHBOARD_ROLE_VIEW_MAP));
    }

    public function getAssignedDashboardView(): ?string
    {
        foreach (self::DASHBOARD_ROLE_VIEW_MAP as $roleId => $view) {
            if ($this->hasRoleId($roleId)) {
                return $view;
            }
        }

        return null;
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
    
}
