<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VatFrequency extends Model
{
    protected $table = 'lkp_vat_frequencies';

    protected $fillable = ['name'];
}
