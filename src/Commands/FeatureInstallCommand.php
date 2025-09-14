<?php

namespace Inmanturbo\Features\Commands;

use Illuminate\Console\Command;

class FeatureInstallCommand extends Command
{
    protected $signature = 'feature:install {--force : Force the operation to run when in production}';

    protected $description = 'Install Laravel Pennant service provider and publish configuration';

    public function handle()
    {
        $force = $this->option('force');

        $this->info('Installing Laravel Pennant...');

        // Publish Laravel Pennant service provider
        $this->call('vendor:publish', [
            '--provider' => 'Laravel\\Pennant\\PennantServiceProvider',
            '--force' => $force,
        ]);

        // Publish the pennant config defined by this package
        $this->call('vendor:publish', [
            '--tag' => 'pennant-config-defined',
            '--force' => $force,
        ]);

        $this->info('Laravel Pennant installation completed successfully!');

        return 0;
    }
}