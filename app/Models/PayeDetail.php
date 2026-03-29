<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayeDetail extends Model
{
    protected $table = 'paye_details';

    protected $fillable = [
        'client_id',
        'employers_reference',
        'accounts_office_reference',
        'years_required',
        'paye_frequency_id',
        'irregular_monthly_pay',
        'nil_eps',
        'no_of_employees',
        'salary_details',
        'first_pay_date',
        'rti_deadline',
        'paye_scheme_ceased',
        'latest_action_id',
        'latest_action_date',
        'records_received',
        'progress_note',
        'general_notes',
    ];

    protected $casts = [
        'irregular_monthly_pay' => 'boolean',
        'nil_eps' => 'boolean',
        'first_pay_date' => 'date',
        'rti_deadline' => 'date',
        'paye_scheme_ceased' => 'date',
        'latest_action_date' => 'date',
        'records_received' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function payeFrequency(): BelongsTo
    {
        return $this->belongsTo(PayeFrequency::class, 'paye_frequency_id');
    }

    public function latestAction(): BelongsTo
    {
        return $this->belongsTo(ActionStatus::class, 'latest_action_id');
    }
}
