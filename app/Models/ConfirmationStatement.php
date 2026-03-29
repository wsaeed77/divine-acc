<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfirmationStatement extends Model
{
    protected $fillable = [
        'client_id',
        'statement_date',
        'statement_due',
        'latest_action_id',
        'latest_action_date',
        'records_received',
        'progress_note',
        'officers',
        'share_capital',
        'shareholders',
        'people_with_significant_control',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'statement_due' => 'date',
        'latest_action_date' => 'date',
        'records_received' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function latestAction(): BelongsTo
    {
        return $this->belongsTo(ActionStatus::class, 'latest_action_id');
    }
}
