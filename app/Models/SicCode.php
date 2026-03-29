<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SicCode extends Model
{
    protected $table = 'lkp_sic_codes';

    protected $fillable = [
        'code',
        'description',
    ];

    public function companyDetails(): HasMany
    {
        return $this->hasMany(CompanyDetail::class, 'sic_code_id');
    }
}
