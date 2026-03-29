<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientType extends Model
{
    protected $table = 'lkp_client_types';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'client_type_id');
    }
}
