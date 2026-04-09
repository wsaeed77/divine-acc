<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoEnrolment extends Model
{
    protected $table = 'auto_enrolment';

    protected $fillable = [
        'client_id',
        'latest_action_id',
        'latest_action_date',
        'records_received',
        'progress_note',
        'missing_records',
        'staging_date',
        'postponement_date',
        'pensions_regulator_opt_out_date',
        're_enrolment_date',
        'pension_provider',
        'pension_id',
        'declaration_of_compliance_due',
        'declaration_of_compliance_submission',
    ];

    protected $casts = [
        'latest_action_date' => 'date',
        'records_received' => 'date',
        'staging_date' => 'date',
        'postponement_date' => 'date',
        'pensions_regulator_opt_out_date' => 'date',
        're_enrolment_date' => 'date',
        'declaration_of_compliance_due' => 'date',
        'declaration_of_compliance_submission' => 'date',
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
