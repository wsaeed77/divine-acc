<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'client_id',
        'task_type_id',
        'service_id',
        'task_name',
        'status',
        'assignee_id',
        'monitor_id',
        'notify_user_id',
        'latest_action_id',
        'latest_action_date',
        'target_date',
        'target_date_manual',
        'deadline_date',
        'period_date',
        'time_estimate',
        'progress_notes',
        'description',
        'breakdown_template_id',
        'is_favourite',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'latest_action_date' => 'date',
        'target_date' => 'date',
        'target_date_manual' => 'boolean',
        'deadline_date' => 'date',
        'period_date' => 'date',
        'time_estimate' => 'decimal:2',
        'is_favourite' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'monitor_id');
    }

    public function notifyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notify_user_id');
    }

    public function latestAction(): BelongsTo
    {
        return $this->belongsTo(ActionStatus::class, 'latest_action_id');
    }

    public function breakdownTemplate(): BelongsTo
    {
        return $this->belongsTo(BreakdownTemplate::class, 'breakdown_template_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function breakdownItems(): HasMany
    {
        return $this->hasMany(TaskBreakdownItem::class)->orderBy('sort_order');
    }

    public function scopeVisibleInList($query)
    {
        return $query->whereIn('status', ['active', 'switched_off']);
    }
}
