<?php

namespace Inmanturbo\Features;

use Laravel\Pennant\Drivers\DatabaseDriver;
use Laravel\Pennant\Feature;

class DefinedOnlyDatabaseDriver extends DatabaseDriver
{
    /**
     * Get multiple feature flag values.
     *
     * @param  array<string, array<int, mixed>>  $features
     * @return array<string, array<int, mixed>>
     */
    public function getAll($features): array
    {
        $definedFeatures = array_flip($this->defined());
        $filteredFeatures = array_filter($features, function ($feature) use ($definedFeatures) {
            return array_key_exists($feature, $definedFeatures);
        }, ARRAY_FILTER_USE_KEY);

        return parent::getAll($filteredFeatures);
    }

    /**
     * Retrieve a feature flag's value.
     *
     * @param  string  $feature
     * @param  mixed  $scope
     */
    public function get($feature, $scope): mixed
    {
        if (! in_array($feature, $this->defined())) {
            return false;
        }

        return parent::get($feature, $scope);
    }

    /**
     * Retrieve the value for the given feature and scope from storage.
     *
     * @param  string  $feature
     * @param  mixed  $scope
     * @return object|null
     */
    protected function retrieve($feature, $scope)
    {
        if (! in_array($feature, $this->defined())) {
            return null;
        }

        return parent::retrieve($feature, $scope);
    }

    /**
     * Retrieve the names of all stored features.
     *
     * @return array<string>
     */
    public function stored(): array
    {
        $stored = parent::stored();
        $defined = $this->defined();

        return array_intersect($stored, $defined);
    }

    /**
     * Purge feature flags for a specific scope.
     *
     * @param  mixed  $scope
     * @param  array|null  $features
     */
    public function purgeForScope($scope, $features = null): void
    {
        $query = $this->newQuery()->where('scope', Feature::serializeScope($scope));

        if ($features) {
           $query->whereIn('name', $features);
        }
        
        $query->delete();
    }
}
