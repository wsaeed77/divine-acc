<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientType extends Model
{
    public const NAME_SELF_ASSESSMENT = 'Self Assessment';

    protected $table = 'lkp_client_types';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'client_type_id');
    }

    public function isSelfAssessment(): bool
    {
        return $this->name === self::NAME_SELF_ASSESSMENT;
    }

    public static function selfAssessmentId(): ?int
    {
        return static::query()->where('name', self::NAME_SELF_ASSESSMENT)->value('id');
    }

    /**
     * Active types shown in client forms. Irish legal-form variants are omitted;
     * pass $includeId so an existing client’s current type still appears on edit.
     */
    public function scopeForClientFormSelect(Builder $query, ?int $includeId = null): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($includeId) {
                $q->where('name', 'not like', 'Irish %');
                if ($includeId !== null) {
                    $q->orWhere('id', $includeId);
                }
            });
    }
}
