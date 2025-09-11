<?php

namespace Inmanturbo\Features;

use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use Laravel\Pennant\FeatureManager;

class FeaturesServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->app->afterResolving(FeatureManager::class, function (FeatureManager $manager) {
            $manager->extend('defined-database', function ($app, $config) {
                return new DefinedOnlyDatabaseDriver(
                    $app['db'],
                    $app['events'],
                    $app['config'],
                    $config['name'] ?? 'default',
                    []
                );
            });
        });
    }
}
