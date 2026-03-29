<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxOffice extends Model
{
    protected $table = 'lkp_tax_offices';

    protected $fillable = ['name'];
}
