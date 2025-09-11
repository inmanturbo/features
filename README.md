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

This package uses a custom `defined-database` driver that only stores and retrieves features that have been explicitly defined. This prevents undefined features from being stored in the database and ensures better data integrity.

The published Pennant config sets this as the default driver:

```php
'default' => env('PENNANT_DRIVER', 'defined-database'),
```

Benefits of the `defined-database` driver:

- **Defined features only**: Only explicitly defined features are stored in the database
- **Data integrity**: Prevents orphaned feature flags from undefined features
- **Clean database**: No stale or undefined feature data accumulates

## Usage

### Registering Features


```php
use Laravel\Pennant\Feature;

Feature::define('my-feature', function (mixed $scope) {
    return 'default-value';
});
```

Since the package uses the `defined-database` driver, manually defined features will be stored and retrieved from the database only when explicitly defined.

### Resetting Feature Defaults

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
