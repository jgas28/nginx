<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'actor_name',
        'event',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'url',
        'method',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resolveAuditable(): ?Model
    {
        if (!$this->auditable_type || !$this->auditable_id || !class_exists($this->auditable_type)) {
            return null;
        }

        return $this->auditable_type::find($this->auditable_id);
    }
}
