<?php

namespace Inmanturbo\Features;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;

class FeatureRegistry
{

    public static function resetDefaults(mixed $scope = null, ?string $name = null)
    {
        $query = DB::connection(config('pennant.stores.database.connection'))
            ->table('features');

        if ($scope) {
            $query->where('scope', Feature::serializeScope($scope));
        }

        if ($name) {
            $query->where('name', $name);
        }

        $query->delete();
    }
}
