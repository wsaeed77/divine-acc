<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CisDetail extends Model
{
    protected $table = 'cis_details';

    protected $fillable = [
        'client_id',
        'is_contractor',
        'is_subcontractor',
        'cis_date',
        'cis_deadline',
        'latest_action_id',
        'latest_action_date',
        'records_received',
        'progress_note',
    ];

    protected $casts = [
        'is_contractor' => 'boolean',
        'is_subcontractor' => 'boolean',
        'cis_date' => 'date',
        'cis_deadline' => 'date',
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
