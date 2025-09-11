<?php

namespace Inmanturbo\Features;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;

class FeatureRegistry
{
    use ResolvesFeatureValue;

    public static function retrieve(mixed $scope, string $name): mixed
    {
        try {
            if (Feature::driver('database')->for($scope)->active($name)) {
                return Feature::driver('database')->for($scope)->value($name);
            }

        } catch (QueryException $e) {
            //
        }

        return null;
    }

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
