<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VatMemberState extends Model
{
    protected $table = 'lkp_vat_member_states';

    protected $fillable = ['name', 'code'];
}
