<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaritalStatus extends Model
{
    protected $table = 'lkp_marital_statuses';

    protected $fillable = ['name'];
}
