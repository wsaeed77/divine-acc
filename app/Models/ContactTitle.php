<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactTitle extends Model
{
    protected $table = 'lkp_titles';

    protected $fillable = ['name'];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'title_id');
    }
}
