<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientRegistration extends Model
{
    protected $table = 'registration';

    protected $fillable = [
        'client_id',
        'terms_signed_fee_paid',
        'registration_fee',
        'letter_of_engagement_signed',
        'money_laundering_complete',
        'sixty_four_eight_registration',
    ];

    protected $casts = [
        'terms_signed_fee_paid' => 'boolean',
        'registration_fee' => 'decimal:2',
        'letter_of_engagement_signed' => 'date',
        'money_laundering_complete' => 'boolean',
        'sixty_four_eight_registration' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
