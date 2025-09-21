<?php

namespace Inmanturbo\Features;

use Illuminate\Support\ServiceProvider;
use Inmanturbo\Features\Commands\FeatureInstallCommand;
use Inmanturbo\Features\Commands\FeatureResetCommand;
use Laravel\Pennant\FeatureManager as PennantFeatureManager;

class FeaturesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend(PennantFeatureManager::class, function ($original, $app) {
            if ($original instanceof FeatureManager) {
                return $original;
            }

            return new FeatureManager($app);
        });
    }

    public function boot()
    {
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
