# Features Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/inmanturbo/features.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/features)
[![Total Downloads](https://img.shields.io/packagist/dt/inmanturbo/features.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/features)
[![License](https://img.shields.io/packagist/l/inmanturbo/features.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/features)

A pluggable feature system built on top of [Laravel Pennant](https://github.com/laravel/pennant).

## Installation

Install the package via Composer:

```bash
composer require inmanturbo/features
```

The service provider will be automatically registered thanks to Laravel's package auto-discovery.

## Configuration

Publish the configuration files:

```bash

# Publish the Pennant configuration (optional, but recommended)
php artisan vendor:publish --tag=pennant-config
```

This will create:
- `config/pennant.php` - Pennant configuration with `defined-database` driver

### Defined-Database Driver

This package uses a custom `defined-database` driver that only retrieves features that have been explicitly defined. This allows removing features without losing any data, fetures can be redefined later with their data still intact.

The published Pennant config sets this as the default driver:

```php
'default' => env('PENNANT_DRIVER', 'defined-database'),
```

## Usage

### Registering Features


```php
use Laravel\Pennant\Feature;

Feature::define('my-feature', function (mixed $scope) {
    return 'default-value';
});
```

Since the package uses the `defined-database` driver, features will show as active only when explicitly defined.

### Resetting Feature Defaults

#### Using the Artisan Command

The package provides a convenient `feature:reset` Artisan command to reset features interactively or via command line options.

##### Interactive Mode

Run the command without options for an interactive experience:

```bash
php artisan feature:reset
```

When features exist in your database, the command will:
1. **Auto-discover** available scopes and features from your database
2. **Present interactive menus** to select:
   - A specific scope or "[All Scopes]" 
   - A specific feature or "[All Features]"
3. **Safely reset** only the selected combination

If no features exist in the database, it will immediately reset (no-op) without prompting.

##### Command Line Options

Skip the interactive prompts by providing explicit options:

```bash
# Reset all features for all scopes (when database is empty)
php artisan feature:reset

# Reset all features for a specific scope
php artisan feature:reset --scope="user:1"

# Reset a specific feature for all scopes  
php artisan feature:reset --feature="new-dashboard"

# Reset a specific feature for a specific scope
php artisan feature:reset --scope="user:1" --feature="new-dashboard"

# Reset non-existent features (safe no-op)
php artisan feature:reset --scope="nonexistent" --feature="missing"
```

##### Use Cases

The `feature:reset` command is useful for:

- **Development**: Quickly reset feature flags during testing
- **Debugging**: Remove problematic feature overrides to fall back to defaults  
- **Data cleanup**: Clear old or unused feature flag data
- **User management**: Reset features for specific users or teams
- **Feature rollback**: Revert features to their default state after experiments

#### Using the FeatureRegistry Class

The `FeatureRegistry` class provides a `resetDefaults` method to remove feature flags from the database, allowing them to fall back to their default values:

```php
use Inmanturbo\Features\FeatureRegistry;

// Reset all features for all scopes
FeatureRegistry::resetDefaults();

// Reset all features for a specific scope (user, team, etc.)
FeatureRegistry::resetDefaults(scope: $user);

// Reset a specific feature for all scopes
FeatureRegistry::resetDefaults(name: 'feature-name');

// Reset a specific feature for a specific scope
FeatureRegistry::resetDefaults(scope: $user, name: 'feature-name');
```

This is useful when you want to:

- Remove overridden feature values and revert to defaults
- Clean up test data
- Reset features during development

## Credits

This package is built on top of [Laravel Pennant](https://github.com/laravel/pennant), Laravel's official feature flag package.

## License

MIT
