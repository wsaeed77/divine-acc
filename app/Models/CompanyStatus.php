<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyStatus extends Model
{
    protected $table = 'lkp_company_statuses';

    protected $fillable = [
        'name',
    ];

    public function companyDetails(): HasMany
    {
        return $this->hasMany(CompanyDetail::class, 'company_status_id');
    }
}
