<?php

use Illuminate\Support\Facades\DB;
use Inmanturbo\Features\Commands\FeatureResetCommand;

beforeEach(function () {
    // Create features table directly
    \Illuminate\Support\Facades\Schema::create('features', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->string('name');
        $table->string('scope');
        $table->json('value');
        $table->timestamp('created_at')->nullable();
        $table->unique(['name', 'scope']);
    });

    // Manually register the command
    $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand(
        new FeatureResetCommand()
    );

    // Insert test data
    DB::table('features')->insert([
        ['name' => 'test-feature-1', 'scope' => 'user:1', 'value' => json_encode(true), 'created_at' => now()],
        ['name' => 'test-feature-1', 'scope' => 'user:2', 'value' => json_encode(false), 'created_at' => now()],
        ['name' => 'test-feature-2', 'scope' => 'user:1', 'value' => json_encode('premium'), 'created_at' => now()],
        ['name' => 'test-feature-2', 'scope' => 'team:1', 'value' => json_encode('basic'), 'created_at' => now()],
    ]);
});

it('can reset all when no data exists', function () {
    // Clear all test data first
    DB::table('features')->delete();
    
    // Should work without prompting when no data exists
    $this->artisan('feature:reset', [])
        ->expectsOutput('Successfully reset all features for all scopes to default values.')
        ->assertExitCode(0);

    expect(DB::table('features')->count())->toBe(0);
});

it('can reset using FeatureRegistry directly', function () {
    // Test the underlying FeatureRegistry functionality that the command uses
    expect(DB::table('features')->count())->toBe(4);
    
    // Test resetting all features for a specific scope
    \Inmanturbo\Features\FeatureRegistry::resetDefaults('user:1', null);
    expect(DB::table('features')->where('scope', 'user:1')->count())->toBe(0);
    expect(DB::table('features')->count())->toBe(2);
    
    // Reset the test data
    DB::table('features')->insert([
        ['name' => 'test-feature-1', 'scope' => 'user:1', 'value' => json_encode(true), 'created_at' => now()],
        ['name' => 'test-feature-2', 'scope' => 'user:1', 'value' => json_encode('premium'), 'created_at' => now()],
    ]);
    
    // Test resetting a specific feature for all scopes
    \Inmanturbo\Features\FeatureRegistry::resetDefaults(null, 'test-feature-1');
    expect(DB::table('features')->where('name', 'test-feature-1')->count())->toBe(0);
    expect(DB::table('features')->count())->toBe(2);
});

it('tests command with explicit feature and scope options', function () {
    // This tests the command functionality that definitely works
    expect(DB::table('features')->count())->toBe(4);
    
    // Test with both options provided - this avoids all prompting
    $this->artisan('feature:reset', ['--scope' => 'user:2', '--feature' => 'test-feature-1'])
        ->expectsOutput("Successfully reset feature 'test-feature-1' for scope 'user:2' to default values.")
        ->assertExitCode(0);

    // Should only remove that specific combination
    expect(DB::table('features')->count())->toBe(3);
    expect(DB::table('features')->where('scope', 'user:2')->where('name', 'test-feature-1')->count())->toBe(0);
});

it('can reset a specific feature for a specific scope', function () {
    // Verify initial data
    expect(DB::table('features')->count())->toBe(4);

    $this->artisan('feature:reset', ['--scope' => 'user:1', '--feature' => 'test-feature-1'])
        ->expectsOutput("Successfully reset feature 'test-feature-1' for scope 'user:1' to default values.")
        ->assertExitCode(0);

    // Verify only the specific combination is removed
    expect(DB::table('features')->count())->toBe(3);
    expect(DB::table('features')->where('name', 'test-feature-1')->where('scope', 'user:1')->count())->toBe(0);
    expect(DB::table('features')->where('name', 'test-feature-1')->where('scope', 'user:2')->count())->toBe(1);
    expect(DB::table('features')->where('name', 'test-feature-2')->count())->toBe(2);
});

it('handles empty database gracefully', function () {
    // Clear all data
    DB::table('features')->delete();

    $this->artisan('feature:reset')
        ->expectsOutput('Successfully reset all features for all scopes to default values.')
        ->assertExitCode(0);

    expect(DB::table('features')->count())->toBe(0);
});

it('handles non-existent scope gracefully', function () {
    // Use both options to avoid prompting
    $this->artisan('feature:reset', ['--scope' => 'non-existent', '--feature' => 'any-feature'])
        ->expectsOutput("Successfully reset feature 'any-feature' for scope 'non-existent' to default values.")
        ->assertExitCode(0);

    // Original data should remain
    expect(DB::table('features')->count())->toBe(4);
});

it('handles non-existent feature gracefully', function () {
    // Use both options to avoid prompting  
    $this->artisan('feature:reset', ['--scope' => 'any-scope', '--feature' => 'non-existent'])
        ->expectsOutput("Successfully reset feature 'non-existent' for scope 'any-scope' to default values.")
        ->assertExitCode(0);

    // Original data should remain
    expect(DB::table('features')->count())->toBe(4);
});
