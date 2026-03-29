<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'lkp_languages';

    protected $fillable = ['name'];
}
