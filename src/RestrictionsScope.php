<?php

declare(strict_types=1);

namespace DKulyk\Restrictions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Arrayable;
use DKulyk\Restrictions\Entities\Restriction;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class RestrictionsScope implements Scope
{
    /**
     * @var array
     */
    private $allowedRestrictions;

    /**
     * RestrictionScope constructor.
     *
     * @param  string[] $allowedRestrictions Allowed restrictions class names.
     */
    public function __construct(array $allowedRestrictions)
    {
        $this->allowedRestrictions = $allowedRestrictions;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $this->applyRestrictions($builder, $model);
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    public function extend(Builder $builder)
    {
        $scope = $this;
        $builder->macro('whereRestrictions', function (Builder $builder, array $restrictions) use ($scope) {
            return $scope->applyRestrictions($builder->withoutGlobalScope($scope), $builder->getModel(), $restrictions);
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array|null $restrictions
     */
    private function applyRestrictions(Builder $builder, Model $model, array $restrictions = null)
    {
        $restrictions = $restrictions ?? Restrictions::getRestrictions();

        $restrictions = array_filter($restrictions, function ($key) {
            return in_array($key, $this->allowedRestrictions);
        }, ARRAY_FILTER_USE_KEY);

        $builder->where(function (Builder $builder) use ($model, $restrictions) {
            foreach ($restrictions as $restriction => $keys) {
                /* @var Builder $this */
                if ($keys instanceof Model) {
                    $keys = $keys->getKey();
                } elseif ($keys instanceof EloquentCollection) {
                    $keys = $keys->modelKeys();
                } elseif ($keys instanceof Arrayable) {
                    $keys = $keys->toArray();
                }

                /* @var \Illuminate\Database\Eloquent\Relations\MorphMany $relation */
                $relation = Relation::noConstraints(function () use ($model) {
                    return $model->morphMany(Restriction::class, 'entity');
                });

                $restrictionNames = array_filter([
                    $restriction,
                    Relation::getMorphedModel($restriction),
                ]);

                //Deny query
                $hasQuery = $relation->getRelationExistenceQuery($relation->getRelated()->newQuery(), $builder);

                $hasQuery->getModel()->restriction = $restriction;

                $hasQuery
                    ->whereIn('restriction', $restrictionNames)
                    ->where([
                        'type' => Restriction::DENY,
                        'enabled' => true,
                    ])
                    ->whereHas('rules', function (Builder $query) use ($keys) {
                        $query->whereKey($keys);
                    });

                $builder->addHasWhere($hasQuery, $relation, '<', 1, 'and');

                //Allow query
                $hasQuery = $relation->getRelationExistenceQuery($relation->getRelated()->newQuery(), $builder);

                $hasQuery->getModel()->restriction = $restriction;

                $hasQuery
                    ->whereIn('restriction', $restrictionNames)
                    ->where([
                        'type' => Restriction::ALLOW,
                        'enabled' => true,
                    ])
                    ->whereDoesntHave('rules', function (Builder $query) use ($keys) {
                        $query->whereKey($keys);
                    });

                $builder->addHasWhere($hasQuery, $relation, '<', 1, 'and');
            }
        });
    }
}
