<?php

use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;

beforeEach(function () {
    // Register the defined-database driver
    $this->app->afterResolving(\Laravel\Pennant\FeatureManager::class, function ($manager) {
        $manager->extend('defined-database', function ($app, $config) {
            return new \Inmanturbo\Features\DefinedOnlyDatabaseDriver(
                $app['db'],
                $app['events'],
                $app['config'],
                $config['name'] ?? 'default',
                []
            );
        });
    });

    // Configure Pennant for testing
    $this->app['config']->set('pennant.default', 'defined-database');
    $this->app['config']->set('pennant.stores.defined-database', [
        'driver' => 'defined-database',
        'connection' => 'testing',
    ]);
    $this->app['config']->set('pennant.stores.database', [
        'driver' => 'database',
        'connection' => 'testing',
    ]);

    // Create features table directly with proper Pennant schema
    \Illuminate\Support\Facades\Schema::create('features', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->string('name');
        $table->string('scope');
        $table->json('value');
        $table->timestamp('created_at')->nullable();
        $table->timestamp('updated_at')->nullable();
        $table->unique(['name', 'scope']);
    });

    // Clear any existing feature definitions
    Feature::flushCache();
});

it('only retrieves defined features using Feature facade', function () {
    // Define a feature
    Feature::define('defined-feature', fn () => 'default-value');

    // Store both defined and undefined features in database
    DB::table('features')->insert([
        ['name' => 'defined-feature', 'scope' => 'user:1', 'value' => json_encode('defined-value'), 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'undefined-feature', 'scope' => 'user:1', 'value' => json_encode('undefined-value'), 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Test using the Feature facade which should use our defined-database driver
    expect(Feature::for('user:1')->value('defined-feature'))->toBe('defined-value');
    expect(Feature::for('user:1')->value('undefined-feature'))->toBe(false);

    // Test that undefined features are not active
    expect(Feature::for('user:1')->active('defined-feature'))->toBe(true);
    expect(Feature::for('user:1')->active('undefined-feature'))->toBe(false);
});

it('filters results to only include defined features', function () {
    // Define only one feature
    Feature::define('defined-feature', fn () => 'default');

    // Store multiple features in database
    DB::table('features')->insert([
        ['name' => 'defined-feature', 'scope' => 'user:1', 'value' => json_encode('value1'), 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'defined-feature', 'scope' => 'user:2', 'value' => json_encode('value2'), 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'undefined-feature', 'scope' => 'user:1', 'value' => json_encode('value3'), 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'undefined-feature', 'scope' => 'user:2', 'value' => json_encode('value4'), 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Test that defined feature returns database values
    expect(Feature::for('user:1')->value('defined-feature'))->toBe('value1');
    expect(Feature::for('user:2')->value('defined-feature'))->toBe('value2');

    // Test that undefined feature returns false (not database values)
    expect(Feature::for('user:1')->value('undefined-feature'))->toBe(false);
    expect(Feature::for('user:2')->value('undefined-feature'))->toBe(false);
});

it('prevents access to orphaned database features', function () {
    // Store undefined features in database (simulating orphaned data)
    DB::table('features')->insert([
        ['name' => 'orphaned-feature-1', 'scope' => 'user:1', 'value' => json_encode('orphaned-value-1'), 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'orphaned-feature-2', 'scope' => 'user:1', 'value' => json_encode('orphaned-value-2'), 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Verify data exists in database
    expect(DB::table('features')->count())->toBe(2);

    // Try to access undefined features - should not be accessible
    expect(Feature::for('user:1')->active('orphaned-feature-1'))->toBe(false);
    expect(Feature::for('user:1')->active('orphaned-feature-2'))->toBe(false);
    expect(Feature::for('user:1')->value('orphaned-feature-1'))->toBe(false);
    expect(Feature::for('user:1')->value('orphaned-feature-2'))->toBe(false);
});

it('demonstrates defined-only behavior with cache', function () {
    // Define a feature first
    Feature::define('cache-test-feature', fn () => 'default-value');

    // Store value in database
    DB::table('features')->insert([
        ['name' => 'cache-test-feature', 'scope' => 'user:1', 'value' => json_encode('stored-value'), 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Should return stored value since feature is defined
    expect(Feature::for('user:1')->active('cache-test-feature'))->toBe(true);
    expect(Feature::for('user:1')->value('cache-test-feature'))->toBe('stored-value');

    // Store undefined feature in database
    DB::table('features')->insert([
        ['name' => 'undefined-cache-feature', 'scope' => 'user:1', 'value' => json_encode('orphaned-value'), 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Should not be accessible
    expect(Feature::for('user:1')->active('undefined-cache-feature'))->toBe(false);
    expect(Feature::for('user:1')->value('undefined-cache-feature'))->toBe(false);
});

it('integrates properly with Laravel Pennant Feature facade', function () {
    // Define a feature with scope-based logic
    Feature::define('integration-feature', function ($scope) {
        return $scope === 'premium-user' ? 'premium' : 'basic';
    });

    // Test that feature works with default values
    expect(Feature::for('basic-user')->value('integration-feature'))->toBe('basic');
    expect(Feature::for('premium-user')->value('integration-feature'))->toBe('premium');

    // Store override in database for basic-user using Feature facade (ensures proper schema)
    Feature::for('basic-user')->activate('integration-feature', 'override');

    // Should return database override for basic-user
    expect(Feature::for('basic-user')->value('integration-feature'))->toBe('override');

    // Should still return default for premium-user (no database override)
    expect(Feature::for('premium-user')->value('integration-feature'))->toBe('premium');
});

it('ensures data integrity by blocking undefined features', function () {
    // This test demonstrates the key benefit of defined-database driver:
    // preventing access to undefined features even if they exist in database

    // Define one feature to test the contrast
    Feature::define('allowed-feature', fn () => 'default');

    // Store both defined and undefined features in database
    DB::table('features')->insert([
        ['name' => 'allowed-feature', 'scope' => 'user:1', 'value' => json_encode('allowed-value'), 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'blocked-feature', 'scope' => 'user:1', 'value' => json_encode('blocked-value'), 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Defined feature should be accessible
    expect(Feature::for('user:1')->active('allowed-feature'))->toBe(true);
    expect(Feature::for('user:1')->value('allowed-feature'))->toBe('allowed-value');

    // Undefined feature should be blocked despite existing in database
    expect(Feature::for('user:1')->active('blocked-feature'))->toBe(false);
    expect(Feature::for('user:1')->value('blocked-feature'))->toBe(false);
});
