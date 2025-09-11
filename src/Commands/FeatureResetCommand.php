<?php

namespace Inmanturbo\Features\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Inmanturbo\Features\FeatureRegistry;

use function Laravel\Prompts\select;

class FeatureResetCommand extends Command
{
    protected $signature = 'feature:reset {--scope= : The scope to reset features for} {--feature= : The specific feature to reset}';

    protected $description = 'Reset feature flags to their default values by removing database overrides';

    public function handle()
    {
        $scopeOption = $this->option('scope');
        $featureOption = $this->option('feature');

        // Get available scopes and features from database
        $availableScopes = $this->getAvailableScopes();
        $availableFeatures = $this->getAvailableFeatures();

        // Prompt for scope if not provided
        $scope = null;
        if ($scopeOption) {
            $scope = $scopeOption;
        } elseif (! empty($availableScopes)) {
            $scopeChoices = array_merge(['[All Scopes]'], $availableScopes);
            $selectedScope = select(
                'Select a scope to reset (or choose "All Scopes" to reset all):',
                $scopeChoices
            );

            if ($selectedScope !== '[All Scopes]') {
                $scope = $selectedScope;
            }
        }

        // Prompt for feature if not provided
        $feature = null;
        if ($featureOption) {
            $feature = $featureOption;
        } elseif (! empty($availableFeatures)) {
            $featureChoices = array_merge(['[All Features]'], $availableFeatures);
            $selectedFeature = select(
                'Select a feature to reset (or choose "All Features" to reset all):',
                $featureChoices
            );

            if ($selectedFeature !== '[All Features]') {
                $feature = $selectedFeature;
            }
        }

        // Perform the reset
        try {
            FeatureRegistry::resetDefaults($scope, $feature);

            $message = 'Successfully reset ';
            if ($feature && $scope) {
                $message .= "feature '{$feature}' for scope '{$scope}'";
            } elseif ($feature) {
                $message .= "feature '{$feature}' for all scopes";
            } elseif ($scope) {
                $message .= "all features for scope '{$scope}'";
            } else {
                $message .= 'all features for all scopes';
            }

            $this->info($message.' to default values.');

        } catch (\Exception $e) {
            $this->error('Failed to reset features: '.$e->getMessage());

            return 1;
        }

        return 0;
    }

    protected function getAvailableScopes(): array
    {
        try {
            return DB::connection(config('pennant.stores.database.connection'))
                ->table('features')
                ->distinct()
                ->pluck('scope')
                ->filter()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getAvailableFeatures(): array
    {
        try {
            return DB::connection(config('pennant.stores.database.connection'))
                ->table('features')
                ->distinct()
                ->pluck('name')
                ->filter()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
