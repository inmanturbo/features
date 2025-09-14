<?php

namespace Inmanturbo\Features;

use Illuminate\Support\ServiceProvider;
use Inmanturbo\Features\Commands\FeatureResetCommand;
use Inmanturbo\Features\Commands\FeatureInstallCommand;
use Laravel\Pennant\FeatureManager;

class FeaturesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/pennant.php' => config_path('pennant.php'),
        ], 'pennant-config-defined');

        if ($this->app->runningInConsole()) {
            $this->commands([
                FeatureResetCommand::class,
                FeatureInstallCommand::class,
            ]);
        }

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
