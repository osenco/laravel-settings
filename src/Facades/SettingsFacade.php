<?php

namespace Osen\LaravelSettings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void set(string $key, mixed $value)
 * @method static void save(string $key, mixed $value) // Example of a combined set & save
 * @method static array all()
 * @method static bool has(string $key)
 * @method static Osen\LaravelSettings\SettingsManager load(string $group = 'default')
 * @method static Osen\LaravelSettings\SettingsManager forTenant(string|int|null $tenantId)
 *
 * @see \Osen\LaravelSettings\SettingsManager
 */
class SettingsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'settings'; // Renamed accessor
    }
}
