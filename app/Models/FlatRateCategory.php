<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlatRateCategory extends Model
{
    protected $table = 'lkp_flat_rate_categories';

    protected $fillable = ['name', 'rate'];

    protected $casts = [
        'rate' => 'decimal:2',
    ];
}
