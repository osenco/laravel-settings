<?php

namespace Osen\LaravelSettings;

use Illuminate\Support\ServiceProvider;
use Osen\LaravelSettings\Repositories\DatabaseTenantSettingsRepository; // Adjust if you have a factory or different structure

class SettingsServiceProvider extends ServiceProvider // Renamed class
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/settings.php', 'settings' // Renamed config key and file
        );

        $this->app->singleton('settings', function ($app) { // Renamed singleton
            // This is a simplified version. You\'d likely have a factory to create the repository
            // based on the config (\'default_repository\' and \'repositories\' settings).
            $config = $app['config']['settings']; // Renamed config key
            $repositoryConfig = $config['repositories'][$config['default_repository']];
            
            // Pass the specific repository config and the global tenant_column and resolver
            $repositoryConfig['tenant_column'] = $config['tenant_column'];
            $repositoryConfig['current_tenant_resolver'] = $config['current_tenant_resolver'];
            $repositoryConfig['encoder'] = $config['encoder'];
            $repositoryConfig['decoder'] = $config['decoder'];

            $repository = new DatabaseTenantSettingsRepository($repositoryConfig); // Or your factory
            
            // The SettingsManager would be similar to Spatie\'s, but adapted for tenancy
            // It would use the resolved repository.
            return new SettingsManager($app, $repository, $config); 
        });

        // Register the helper file
        $this->registerHelpers();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/settings.php' => config_path('settings.php'), // Renamed config file
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations/create_settings_table.php.stub' => $this->getMigrationFileName('create_settings_table.php'), // Renamed migration file
            ], 'migrations');

            // You might want to add commands here, similar to spatie/laravel-settings
            // e.g., for making settings classes or migrations
            // $this->commands([
            //     MakeSettingsMigrationCommand::class,
            //     MakeSettingClassCommand::class, 
            // ]);
        }
    }

    protected function registerHelpers()
    {
        if (file_exists($file = __DIR__.'/helpers.php')) {
            require_once $file;
        }
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');
        $filesystem = $this->app->make(\Illuminate\Filesystem\Filesystem::class);

        // Check if a migration with this name (ignoring timestamp) already exists
        // This is a basic check; you might need a more robust way if you allow multiple migrations
        // with similar names or if you want to update existing ones.
        $existingMigrations = $filesystem->glob(database_path("migrations/*_" . $migrationFileName));
        if ($existingMigrations) {
            return $existingMigrations[0]; // Return the first existing one
        }

        return database_path("migrations/{$timestamp}_{$migrationFileName}");
    }
}
