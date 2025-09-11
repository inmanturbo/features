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

Publish the configuration file:

```bash
php artisan vendor:publish --tag=features-config
```

This will create a `config/features.php` file where you can configure your features.

### Pennant Configuration

This package is designed to work with [Laravel Pennant's](https://github.com/laravel/pennant) default driver set to `'array'`. This allows features to be defined as stateless defaults that can then be overridden on a per-scope basis using the database driver.

In your `config/pennant.php`, set the default driver to `'array'`:

```php
'default' => env('PENNANT_DRIVER', 'array'),
```

This setup enables:

- **Stateless defaults**: Features defined in the array driver serve as fallback values
- **Database overrides**: Specific scopes (users, teams, etc.) can have custom values stored in the database
- **Flexible management**: Use `resetDefaults()` to remove database overrides and fall back to array defaults

## Usage

### Registering Features

Features are automatically registered from your `config/features.php` configuration. The package reads the `enabled` array and registers each feature using the array driver.

#### Using the Configuration

Define your features in the `enabled` array:

```php
// config/features.php
return [
    'enabled' => [
        'new-dashboard' => true,
        'beta-feature' => env('BETA_FEATURES', false),
        'premium-widgets' => 'premium',
    ],

    'resolvers' => [
        'premium-widgets' => function (mixed $scope) {
            return $scope?->subscription?->isPremium() ? 'premium' : 'basic';
        },
    ],
];
```

#### Manual Registration

You can also manually register features using the same pattern as the service provider:

```php
use Laravel\Pennant\Feature;
use Inmanturbo\Features\FeatureRegistry;

Feature::driver('array')->define('my-feature', function (mixed $scope) {
    return FeatureRegistry::resolve($scope, 'my-feature', 'default-value');
});
```

The `resolve` method handles:

- Checking for database overrides first
- Falling back to the provided default value
- Supporting both simple values and closure resolvers

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
