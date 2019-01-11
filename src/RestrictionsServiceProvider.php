<?php

declare(strict_types=1);

namespace DKulyk\Restrictions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;

final class RestrictionsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        //Fallback for models without restrictions.
        Builder::macro('whereRestrictions', function (Builder $builder, array $restrictions) {
            return $builder;
        });

        $this->registerMigrations();
    }

    /**
     * Register the package's migrations.
     *
     * @return void
     */
    private function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(dirname(__DIR__).'/database/migrations');
        }
    }
}
