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
