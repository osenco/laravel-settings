<?php

namespace Osen\LaravelSettings\Exceptions;

use Exception;

class SettingNotFoundException extends Exception
{
    public static function forKey(string $key, string $group, ?string $tenantId):
    self
    {
        $tenantMessage = $tenantId ? " for tenant '{$tenantId}'" : " (global)";
        return new self("Setting with key '{$key}' in group '{$group}'{$tenantMessage} not found.");
    }
}
