<?php

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Inmanturbo\Features\FeatureManager;
use Laravel\Pennant\Feature;
use Laravel\Pennant\FeatureManager as PennantFeatureManager;

beforeEach(function () {

    $this->app->extend(PennantFeatureManager::class, function ($original, $app) {
        if ($original instanceof FeatureManager) {
            return $original;
        }

        return new FeatureManager($app);
    });

    $this->app['config']->set('pennant.stores.database', [
        'driver' => 'database',
        'connection' => 'testing',
    ]);

    // Clear any existing feature definitions
    Feature::flushCache();
});

it('can get features with registered before hook callback', function () {
    $queries = 0;
    DB::listen(function (QueryExecuted $event) use (&$queries) {
        $queries++;
    });

    Feature::define('feature-with-registered-before-hook-callback', fn () => 'feature-value');

    Feature::before('feature-with-registered-before-hook-callback', fn () => ['before' => 'value']);

    $value = Feature::get('feature-with-registered-before-hook-callback', null);

    expect($value)->toBe(['before' => 'value']);
    expect($queries)->toBe(0);
});
