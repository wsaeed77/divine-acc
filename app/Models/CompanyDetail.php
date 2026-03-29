<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyDetail extends Model
{
    protected $fillable = [
        'client_id',
        'company_number',
        'company_status_id',
        'incorporation_date',
        'trading_as',
        'registered_address',
        'postal_address',
        'invoice_address_type',
        'invoice_address_custom',
        'primary_email',
        'email_domain',
        'telephone',
        'turnover',
        'date_of_trading',
        'sic_code_id',
        'nature_of_business',
        'corporation_tax_office',
        'company_utr',
        'companies_house_auth_code',
    ];

    protected $casts = [
        'incorporation_date' => 'date',
        'date_of_trading' => 'date',
        'turnover' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function companyStatus(): BelongsTo
    {
        return $this->belongsTo(CompanyStatus::class, 'company_status_id');
    }

    public function sicCode(): BelongsTo
    {
        return $this->belongsTo(SicCode::class, 'sic_code_id');
    }
}
