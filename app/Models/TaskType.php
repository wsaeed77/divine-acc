<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskType extends Model
{
    protected $table = 'lkp_task_types';

    protected $fillable = [
        'name',
        'slug',
        'naming_pattern',
        'service_id',
        'recurrence',
        'deadline_source',
        'deadline_manual',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'deadline_manual' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_type_id');
    }
}
