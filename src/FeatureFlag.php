<?php

namespace Inmanturbo\Features;

interface FeatureFlag
{
    public function name(): string;

    public function envKey(): string;

    public function envValue($default = null): string;
}
