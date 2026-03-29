<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    protected $fillable = [
        'tenant_id',
        'title_id',
        'first_name',
        'middle_name',
        'last_name',
        'preferred_name',
        'date_of_birth',
        'deceased_date',
        'email',
        'portal_login_email',
        'postal_address',
        'previous_address',
        'telephone_number',
        'mobile_number',
        'ni_number',
        'personal_utr',
        'companies_house_personal_code',
        'terms_signed_date',
        'photo_id_verified',
        'address_verified',
        'marital_status_id',
        'nationality_id',
        'language_id',
        'aml_check_started',
        'aml_check_date',
        'id_check_started',
        'id_check_date',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'deceased_date' => 'date',
        'terms_signed_date' => 'date',
        'photo_id_verified' => 'boolean',
        'address_verified' => 'boolean',
        'aml_check_started' => 'boolean',
        'aml_check_date' => 'date',
        'id_check_started' => 'boolean',
        'id_check_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function title(): BelongsTo
    {
        return $this->belongsTo(ContactTitle::class, 'title_id');
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_contacts')
            ->withPivot([
                'is_main_contact',
                'create_self_assessment',
                'self_assessment_fee',
                'client_does_own_sa',
            ]);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
