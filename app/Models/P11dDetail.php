<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P11dDetail extends Model
{
    protected $table = 'p11d_details';

    protected $fillable = [
        'client_id',
        'next_return_due',
        'latest_submitted',
        'latest_action_id',
        'latest_action_date',
        'records_received',
        'progress_note',
    ];

    protected $casts = [
        'next_return_due' => 'date',
        'latest_submitted' => 'date',
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
