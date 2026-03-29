<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VatDetail extends Model
{
    protected $table = 'vat_details';

    protected $fillable = [
        'client_id',
        'vat_frequency_id',
        'vat_period_end',
        'next_return_due',
        'vat_bill_amount',
        'vat_bill_due',
        'latest_action_id',
        'latest_action_date',
        'records_received',
        'progress_note',
        'vat_member_state_id',
        'vat_number',
        'vat_address',
        'date_of_registration',
        'effective_date',
        'estimated_turnover',
        'applied_for_mtd',
        'mtd_ready',
        'transfer_of_going_concern',
        'involved_in_other_businesses',
        'direct_debit',
        'standard_scheme',
        'cash_accounting_scheme',
        'retail_scheme',
        'margin_scheme',
        'flat_rate',
        'flat_rate_category_id',
        'month_last_quarter_submitted',
        'box5_last_quarter_submitted',
        'general_notes',
    ];

    protected $casts = [
        'vat_period_end' => 'date',
        'next_return_due' => 'date',
        'vat_bill_amount' => 'decimal:2',
        'vat_bill_due' => 'date',
        'latest_action_date' => 'date',
        'records_received' => 'date',
        'date_of_registration' => 'date',
        'effective_date' => 'date',
        'estimated_turnover' => 'decimal:2',
        'applied_for_mtd' => 'date',
        'mtd_ready' => 'boolean',
        'transfer_of_going_concern' => 'boolean',
        'involved_in_other_businesses' => 'boolean',
        'direct_debit' => 'boolean',
        'standard_scheme' => 'boolean',
        'cash_accounting_scheme' => 'boolean',
        'retail_scheme' => 'boolean',
        'margin_scheme' => 'boolean',
        'flat_rate' => 'boolean',
        'box5_last_quarter_submitted' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function vatFrequency(): BelongsTo
    {
        return $this->belongsTo(VatFrequency::class, 'vat_frequency_id');
    }

    public function latestAction(): BelongsTo
    {
        return $this->belongsTo(ActionStatus::class, 'latest_action_id');
    }

    public function vatMemberState(): BelongsTo
    {
        return $this->belongsTo(VatMemberState::class, 'vat_member_state_id');
    }

    public function flatRateCategory(): BelongsTo
    {
        return $this->belongsTo(FlatRateCategory::class, 'flat_rate_category_id');
    }
}
