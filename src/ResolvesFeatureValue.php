<?php

namespace Inmanturbo\Features;

trait ResolvesFeatureValue
{
    public static function resolve(mixed $scope, ?string $name = null, mixed $resolver = null)
    {
        $name ??= static::name();

        $resolver ??= match (true) {
            method_exists(static::class, 'resolver') => static::resolver(...),
            default => function (mixed $scope) {
                return false;
            },
        };

        return static::resolveFeatureValue($scope, $name, $resolver);
    }

    protected static function resolveFeatureValue(mixed $scope, string $name, mixed $fallback)
    {
        return FeatureRegistry::retrieve($scope, $name) ?: static::resolveFallback($fallback, $scope);
    }

    protected static function resolveFallback(mixed $fallback, mixed $scope = null)
    {
        if (is_callable($fallback)) {
            return $fallback($scope);
        }

        return $fallback;
    }
}
