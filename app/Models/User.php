<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_TENANT_ADMIN = 'tenant_admin';

    public const ROLE_PARTNER = 'partner';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_STAFF = 'staff';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function clientsCreated(): HasMany
    {
        return $this->hasMany(Client::class, 'created_by_id');
    }

    public function clientsAsPartner(): HasMany
    {
        return $this->hasMany(Client::class, 'partner_id');
    }

    public function clientsAsManager(): HasMany
    {
        return $this->hasMany(Client::class, 'manager_id');
    }

    public function isTenantAdmin(): bool
    {
        return $this->role === self::ROLE_TENANT_ADMIN;
    }
}
