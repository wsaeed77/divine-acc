<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakdownTemplateItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'template_id',
        'sort_order',
        'description',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(BreakdownTemplate::class, 'template_id');
    }
}
