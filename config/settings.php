<?php

// use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository; // Comment out or remove Spatie reference

return [
    /*
     * The name of the column used to store the tenant identifier.
     * This column will be added to the settings table.
     */
    'tenant_column' => 'tenant_id',

    /*
     * A closure that returns the current tenant ID.
     * If null, settings are considered global (tenant_id will be null).
     * Example: fn() => auth()->check() ? auth()->id() : null
     * Or for a dedicated tenancy package: fn() => app(SomeTenantManager::class)->getCurrentTenantId()
     */
    'current_tenant_resolver' => null,

    /*
     * Each settings class used in your application must be registered.
     */
    'settings' => [
        // App\Settings\GeneralSettings::class,
    ],

    /*
     * The path where settings classes will be created (e.g., by a make command).
     */
    'setting_class_path' => app_path('Settings'),

    /*
     * In these directories, settings migrations will be stored and run.
     */
    'migrations_paths' => [
        database_path('settings_migrations'), // Changed from 'settings' to avoid conflict if spatie/laravel-settings is also used
    ],

    /*
     * The default repository to use for loading and saving settings.
     * Options: 'database', or custom ones you define.
     */
    'default_repository' => 'database',

    /*
     * Configuration for available repositories.
     */
    'repositories' => [
        'database' => [
            'type' => Osen\LaravelSettings\Repositories\DatabaseTenantSettingsRepository::class, // This class handles tenant resolution internally
            'model' => null, 
            'table' => 'settings', // Renamed table name
            'connection' => null, 
        ],
        // You could add other repository types here, e.g., 'redis'
    ],

    /*
     * Encoder and decoder for storing settings.
     * Default: json_encode and json_decode.
     */
    'encoder' => null,
    'decoder' => null,

    /*
     * Cache configuration for settings.
     */
    'cache' => [
        'enabled' => env('SETTINGS_CACHE_ENABLED', false), // Renamed env variable
        'store' => null, // Laravel cache store (null for default)
        'prefix' => 'settings', // Renamed cache prefix
        'ttl' => null, // Cache TTL in seconds (null for forever)
    ],

    /*
     * Global casts for properties.
     */
    'global_casts' => [
        // DateTimeInterface::class => Osen\LaravelSettings\SettingsCasts\DateTimeInterfaceCast::class,
    ],

    /*
     * Paths for auto-discovering settings classes.
     */
    'auto_discover_settings' => [
        // app_path('Settings'),
    ],

    /*
     * Cache path for discovered settings classes.
     */
    'discovered_settings_cache_path' => base_path('bootstrap/cache'),
];
