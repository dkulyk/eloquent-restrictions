<?php

declare(strict_types=1);

namespace DKulyk\Restrictions\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Restriction.
 *
 * @property-read int $id
 * @property-read Model $entity
 * @property int $type Restriction rule type.
 * @property bool $enabled
 * @property string $restriction
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Model[]|Collection $rules
 */
class Restriction extends Model
{
    public const ALLOW = 2;
    public const DENY = 1;

    protected $table = 'restrictions';

    protected $fillable = [
        'type',
        'enabled',
        'restriction',
    ];

    protected $casts = [
        'enabled' => 'bool',
        'type' => 'int',
    ];

    protected $attributes = [
        'type' => self::DENY,
        'enabled' => true,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('entity');
    }

    /**
     * Get restrictions rules.
     *
     * @return BelongsToMany
     */
    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(
            Relation::getMorphedModel($this->restriction) ?? $this->restriction,
            'restrictions_rules',
            'restriction_id',
            'rule_id'
        );
    }
}
