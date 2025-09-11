<?php

namespace Inmanturbo\Features;

class Feature implements FeatureFlag
{
    public function __construct(
        public readonly string $name,
        public readonly string $envKey,
        public array $options = [],
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function envKey(): string
    {
        return $this->envKey;
    }

    public function envValue($default = null): string
    {
        return env($this->envKey(), $default);
    }
}
