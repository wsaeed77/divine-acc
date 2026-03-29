<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayeFrequency extends Model
{
    protected $table = 'lkp_paye_frequencies';

    protected $fillable = ['name'];
}
