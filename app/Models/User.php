<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_MEMBER = 'member';

    public const ROLE_MANAGER = 'manager';

    public const ROLES = [
        self::ROLE_MEMBER,
        self::ROLE_MANAGER,
    ];

    protected $fillable = [
        'name',
        'email',
        'organization_id',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function setRoleAttribute(string $role): void
    {
        if (! in_array($role, self::ROLES, true)) {
            throw new InvalidArgumentException("Unsupported user role [{$role}].");
        }

        $this->attributes['role'] = $role;
    }
}
