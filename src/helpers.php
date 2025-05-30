<?php

namespace Osen\\LaravelSettings\\Helpers;

use Osen\\LaravelSettings\\Facades\\SettingsFacade; // Renamed facade

if (!function_exists('Osen\\LaravelSettings\\Helpers\\settings')) {
    /**
     * Access the tenant settings manager or get/set a specific setting value.
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return \\Osen\\LaravelSettings\\SettingsManager|mixed
     */
    function settings($key = null, $default = null)
    {
        $manager = SettingsFacade::getFacadeRoot(); // Renamed facade

        if (is_null($key)) {
            return $manager;
        }

        if (is_array($key)) {
            // If $key is an array, assume we are setting multiple values
            // e.g., settings(['key1' => 'value1', 'key2' => 'value2'])
            foreach ($key as $k => $v) {
                $manager->set($k, $v);
            }
            // Optionally, you might want to auto-save here or require explicit save
            // $manager->save(); 
            return $manager; // Or return void, or true, depending on desired behavior
        }

        // If $default is not null and $key is a string, it implies a set operation
        // However, typical helper usage for get is settings('key', 'default_value_if_not_found')
        // To set a value, one might expect settings(['key' => 'value']) or settings()->set('key', 'value')
        // For clarity, let's stick to get if $default is provided in a typical get manner.
        // If you want `settings('key', 'value')` to be a set operation, you'd adjust this logic.
        return $manager->get($key, $default);
    }
}
