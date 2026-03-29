<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Client extends Model
{
    protected $fillable = [
        'tenant_id',
        'internal_reference',
        'name',
        'client_type_id',
        'partner_id',
        'manager_id',
        'credit_check_completed',
        'credit_check_date',
        'created_by_id',
        'is_active',
    ];

    protected $casts = [
        'credit_check_completed' => 'boolean',
        'credit_check_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function clientType(): BelongsTo
    {
        return $this->belongsTo(ClientType::class, 'client_type_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function companyDetail(): HasOne
    {
        return $this->hasOne(CompanyDetail::class);
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'client_contacts')
            ->withPivot([
                'is_main_contact',
                'create_self_assessment',
                'self_assessment_fee',
                'client_does_own_sa',
            ]);
    }

    public function clientServices(): HasMany
    {
        return $this->hasMany(ClientService::class);
    }

    public function combinedPricing(): HasOne
    {
        return $this->hasOne(ClientCombinedPricing::class);
    }

    public function accountsReturn(): HasOne
    {
        return $this->hasOne(AccountsReturn::class);
    }

    public function confirmationStatement(): HasOne
    {
        return $this->hasOne(ConfirmationStatement::class);
    }

    public function vatDetail(): HasOne
    {
        return $this->hasOne(VatDetail::class);
    }

    public function payeDetail(): HasOne
    {
        return $this->hasOne(PayeDetail::class);
    }

    public function cisDetail(): HasOne
    {
        return $this->hasOne(CisDetail::class);
    }

    public function autoEnrolment(): HasOne
    {
        return $this->hasOne(AutoEnrolment::class);
    }

    public function p11dDetail(): HasOne
    {
        return $this->hasOne(P11dDetail::class);
    }

    public function clientRegistration(): HasOne
    {
        return $this->hasOne(ClientRegistration::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function generateInternalReference(int $tenantId): string
    {
        $tenant = Tenant::query()->findOrFail($tenantId);
        $prefix = 'T'.$tenantId.'-';

        do {
            $seq = str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            $ref = $prefix.$seq;
        } while (static::query()->where('internal_reference', $ref)->exists());

        return $ref;
    }
}
