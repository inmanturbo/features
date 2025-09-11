<?php

namespace Inmanturbo\Features;

use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class FeaturesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/features.php', 'features'
        );
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/features.php' => config_path('features.php'),
        ], 'features-config');

        $this->bootFeatures();
    }

    protected function bootFeatures()
    {
        foreach (config('plugins.enabled') as $pluginName => $pluginValue) {
            Feature::driver('array')->define($pluginName, function (mixed $scope) use ($pluginValue, $pluginName) {
                return FeatureRegistry::resolve($scope, $pluginName, config('plugins.resolvers.'.$pluginName, $pluginValue));
            });
        }
    }
}
