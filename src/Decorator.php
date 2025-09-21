<?php

namespace Inmanturbo\Features;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Laravel\Pennant\Drivers\Decorator as DriversDecorator;
use Laravel\Pennant\Events\FeatureRetrieved;
use Laravel\Pennant\Feature;

class Decorator extends DriversDecorator
{
    /**
     * The registered before hooks.
     *
     * @var array<string, callable(mixed):mixed>
     */
    protected $beforeHooks = [];

    /**
     * Register a feature's before hook.
     *
     * @param  string|class-string  $feature
     * @param  callable  $hook
     */
    public function before($feature, $hook): void
    {
        $this->beforeHooks[$feature] = $hook;
    }

    /**
     * Retrieve a feature flag's value.
     *
     * @internal
     *
     * @param  string  $feature
     * @param  mixed  $scope
     */
    public function get($feature, $scope): mixed
    {
        $feature = $this->resolveFeature($feature);

        $scope = $this->resolveScope($scope);

        $item = $this->cache
            ->whereStrict('scope', Feature::serializeScope($scope))
            ->whereStrict('feature', $feature)
            ->first();

        if ($item !== null) {
            Event::dispatch(new FeatureRetrieved($feature, $scope, $item['value']));

            return $item['value'];
        }

        $before = match (true) {
            isset($this->beforeHooks[$feature]) => $this->beforeHooks[$feature],
            $this->hasBeforeHook($feature) => $this->container->make($this->implementationClass($feature))->before(...),
            default => fn () => null,
        };

        $value = $this->resolveBeforeHook($feature, $scope, $before) ?? $this->driver->get($feature, $scope);

        $this->putInCache($feature, $scope, $value);

        Event::dispatch(new FeatureRetrieved($feature, $scope, $value));

        return $value;
    }

    /**
     * Get multiple feature flag values.
     *
     * @internal
     *
     * @param  string|array<int|string, mixed>  $features
     * @return array<string, array<int, mixed>>
     */
    public function getAll($features): array
    {
        $features = $this->normalizeFeaturesToLoad($features);

        if ($features->isEmpty()) {
            return [];
        }

        $hasUnresolvedFeatures = false;

        $resolvedBefore = $features->reduce(function ($resolved, $scopes, $feature) use (&$hasUnresolvedFeatures) {
            $resolved[$feature] = [];

            if (! $this->hasBeforeHook($feature) && ! isset($this->beforeHooks[$feature])) {
                $hasUnresolvedFeatures = true;

                return $resolved;
            }

            $before = $this->beforeHooks[$feature] ?? $this->container->make($this->implementationClass($feature))->before(...);

            foreach ($scopes as $index => $scope) {
                $value = $this->resolveBeforeHook($feature, $scope, $before);

                if ($value !== null) {
                    $resolved[$feature][$index] = $value;
                } else {
                    $hasUnresolvedFeatures = true;
                }
            }

            return $resolved;
        }, []);

        $results = array_replace_recursive(
            $features->all(),
            $resolvedBefore,
            $hasUnresolvedFeatures ? $this->driver->getAll($features->map(function ($scopes, $feature) use ($resolvedBefore) {
                return array_diff_key($scopes, $resolvedBefore[$feature]);
            })->all()) : [],
        );

        $features->flatMap(fn ($scopes, $key) => Collection::make($scopes)
            ->zip($results[$key])
            ->map(fn ($scopes) => $scopes->push($key)))
            ->each(fn ($value) => $this->putInCache($value[2], $value[0], $value[1]));

        return $results;
    }
}