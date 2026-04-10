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
        'commenced_trading',
        'ceased_trading',
        'registered_for_sa',
        'turnover',
        'nature_of_business',
        'mtd_qualifying_year',
        'utr',
        'telephone',
        'email',
    ];

    protected $casts = [
        'commenced_trading' => 'date',
        'ceased_trading' => 'date',
        'registered_for_sa' => 'date',
        'turnover' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
