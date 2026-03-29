<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionStatus extends Model
{
    protected $table = 'lkp_action_statuses';

    protected $fillable = ['name', 'category'];
}
