<?php

declare(strict_types=1);

namespace DKulyk\Restrictions;

use ReflectionMethod;
use Illuminate\Contracts\Support\Arrayable;
use DKulyk\Restrictions\Entities\Restriction;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\{Model, Scope, Builder, Collection};
use Illuminate\Database\Query\Builder as Query;

/**
 * Class RestrictionsScope.
 */
class RestrictionsScope implements Scope
{
    /**
     * @var array
     */
    private $allowedRestrictions;

    /**
     * RestrictionScope constructor.
     *
     * @param  string[]  $allowedRestrictions  Allowed restrictions class names.
     */
    public function __construct(array $allowedRestrictions = [])
    {
        $this->allowedRestrictions = array_map(function ($restriction) {
            return Relation::getMorphedModel($restriction) ?? $restriction;
        }, $allowedRestrictions);
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
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
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
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
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array|null  $restrictions
     */
    private function applyRestrictions(Builder $builder, Model $model, array $restrictions = null)
    {
        $restrictions = $restrictions ?? Restrictions::getRestrictions();

        $morphMap = array_flip(Relation::$morphMap);

        $addHasWhere = new ReflectionMethod(Builder::class, 'addHasWhere');
        $addHasWhere->setAccessible(true);

        $builder->where(function (Builder $builder) use ($model, $restrictions, $morphMap, $addHasWhere) {
            foreach ($restrictions as $restriction => $keys) {

                $restrictionModel = Relation::getMorphedModel($restriction) ?? $restriction;
                if (! in_array($restrictionModel, $this->allowedRestrictions)) {
                    continue;
                }

                $restriction = $morphMap[$restrictionModel] ?? $restrictionModel;

                /* @var Builder $this */
                if ($keys instanceof Model) {
                    $keys = $keys->getKey();
                } elseif ($keys instanceof Collection) {
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

                $rules = $hasQuery->getModel()->rules();
                $hasQuery
                    ->where('restriction', $restrictionModel)
                    ->where('type', Restriction::DENY)
                    ->where('enabled', true)
                    ->whereExists(function(Query $query) use ($rules,$keys){
                        $query->from($rules->getTable())
                            ->whereColumn($rules->getQualifiedParentKeyName(), '=', $rules->getQualifiedForeignPivotKeyName())
                            ->wherein($rules->getQualifiedRelatedPivotKeyName(), (array)$keys);
                    });

                $addHasWhere->invoke($builder, $hasQuery, $relation, '<', 1, 'and');

                //Allow query
                $hasQuery = $relation->getRelationExistenceQuery($relation->getRelated()->newQuery(), $builder);

                $hasQuery->getModel()->restriction = $restriction;

                $hasQuery
                    ->where('restriction', $restrictionModel)
                    ->where('type', Restriction::ALLOW)
                    ->where('enabled', true)
                    ->whereNotExists(function(Query $query) use ($rules,$keys){
                        $query->from($rules->getTable())
                            ->whereColumn($rules->getQualifiedParentKeyName(), '=', $rules->getQualifiedForeignPivotKeyName())
                            ->wherein($rules->getQualifiedRelatedPivotKeyName(), (array)$keys);
                    });

                $addHasWhere->invoke($builder, $hasQuery, $relation, '<', 1, 'and');
            }
        });
    }

    /**
     * @return array
     */
    public function getAllowedRestrictions(): array
    {
        return $this->allowedRestrictions;
    }
}
