# Features Package

A pluggable feature system built on top of Laravel Pennant.

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

This package is designed to work with Laravel Pennant's default driver set to `'array'`. This allows features to be defined as stateless defaults that can then be overridden on a per-scope basis using the database driver.

In your `config/pennant.php`, set the default driver to `'array'`:

```php
'default' => env('PENNANT_DRIVER', 'array'),
```

This setup enables:

- **Stateless defaults**: Features defined in the array driver serve as fallback values
- **Database overrides**: Specific scopes (users, teams, etc.) can have custom values stored in the database
- **Flexible management**: Use `resetDefaults()` to remove database overrides and fall back to array defaults

## Usage

### Resetting Feature Defaults

The `FeatureRegistry` class provides a `resetDefaults` method to remove feature flags from the database, allowing them to fall back to their default values:

```php
use Inmanturbo\Features\FeatureRegistry;

// Reset all features for all scopes
FeatureRegistry::resetDefaults();

// Reset all features for a specific scope (user, team, etc.)
FeatureRegistry::resetDefaults($user);

// Reset a specific feature for all scopes
FeatureRegistry::resetDefaults(null, 'feature-name');

// Reset a specific feature for a specific scope
FeatureRegistry::resetDefaults($user, 'feature-name');
```

This is useful when you want to:
- Remove overridden feature values and revert to defaults
- Clean up test data
- Reset features during development

### Feature Retrieval

The package also provides a `retrieve` method to get feature values:

```php
use Inmanturbo\Features\FeatureRegistry;

$value = FeatureRegistry::retrieve($user, 'feature-name');
```

## License

MIT
