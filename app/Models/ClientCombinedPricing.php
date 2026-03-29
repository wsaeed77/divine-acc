<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientCombinedPricing extends Model
{
    protected $table = 'client_combined_pricing';

    protected $fillable = [
        'client_id',
        'annual_charge_enabled',
        'annual_charge',
        'monthly_charge_enabled',
        'monthly_charge',
    ];

    protected $casts = [
        'annual_charge_enabled' => 'boolean',
        'monthly_charge_enabled' => 'boolean',
        'annual_charge' => 'decimal:2',
        'monthly_charge' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
