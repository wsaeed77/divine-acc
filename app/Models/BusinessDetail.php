<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessDetail extends Model
{
    protected $table = 'business_details';

    protected $fillable = [
        'client_id',
        'trading_name',
        'business_address',
        'nature_of_business',
        'utr',
        'telephone',
        'email',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
