<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountsReturn extends Model
{
    protected $table = 'accounts_returns';

    protected $fillable = [
        'client_id',
        'accounts_period_end',
        'ch_year_end',
        'hmrc_year_end',
        'ch_accounts_next_due',
        'ct600_due',
        'corporation_tax_amount_due',
        'tax_due_hmrc_year_end',
        'ct_payment_reference',
        'tax_office_id',
        'ch_email_reminder',
        'latest_action_id',
        'latest_action_date',
        'records_received',
        'progress_note',
        'sa_income_overview',
        'sa_notes',
        'sa_tax_year',
        'sa_tax_amount_due_1',
        'sa_tax_amount_due_2',
        'sa_tax_amount_due_3',
        'sa_missing_records',
    ];

    protected $casts = [
        'accounts_period_end' => 'date',
        'ch_year_end' => 'date',
        'hmrc_year_end' => 'date',
        'ch_accounts_next_due' => 'date',
        'ct600_due' => 'date',
        'corporation_tax_amount_due' => 'decimal:2',
        'tax_due_hmrc_year_end' => 'date',
        'ch_email_reminder' => 'boolean',
        'latest_action_date' => 'date',
        'records_received' => 'date',
        'sa_tax_amount_due_1' => 'decimal:2',
        'sa_tax_amount_due_2' => 'decimal:2',
        'sa_tax_amount_due_3' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function taxOffice(): BelongsTo
    {
        return $this->belongsTo(TaxOffice::class, 'tax_office_id');
    }

    public function latestAction(): BelongsTo
    {
        return $this->belongsTo(ActionStatus::class, 'latest_action_id');
    }
}
