<?php

use Inmanturbo\Features\FeaturesServiceProvider;
use Orchestra\Testbench\TestCase;

uses(TestCase::class)->in(__DIR__);

function getPackageProviders($app)
{
    return [
        FeaturesServiceProvider::class,
    ];
}

function defineEnvironment($app)
{
    $app['config']->set('database.default', 'testing');
    $app['config']->set('database.connections.testing', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);

    $app['config']->set('pennant.default', 'defined-database');
    $app['config']->set('pennant.stores.defined-database', [
        'driver' => 'defined-database',
        'connection' => null,
    ]);

    // Force registration of commands for testing
    $app->resolving('Illuminate\Contracts\Console\Kernel', function ($console) {
        $console->registerCommand(new \Inmanturbo\Features\Commands\FeatureResetCommand());
    });
}

function defineDatabaseMigrations()
{
    return [
        __DIR__.'/migrations/2024_01_01_000000_create_features_table.php',
    ];
}
