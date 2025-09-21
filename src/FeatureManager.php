<?php

namespace Inmanturbo\Features;

use Laravel\Pennant\FeatureManager as PennantFeatureManager;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class FeatureManager extends PennantFeatureManager
{
    /**
     * Resolve the given store.
     *
     * @param  string  $name
     * @return \Laravel\Pennant\Drivers\Decorator
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Pennant store [{$name}] is not defined.");
        }

        if (isset($this->customCreators[$config['driver']])) {
            $driver = $this->callCustomCreator($config);
        } else {
            $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

            if (method_exists($this, $driverMethod)) {
                $driver = $this->{$driverMethod}($config, $name);
            } else {
                throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
            }
        }

        return new Decorator(
            $name,
            $driver,
            $this->defaultScopeResolver($name),
            $this->container,
            new Collection
        );
    }
}