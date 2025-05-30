# laravel-settings
Tenant-aware Laravel settings package

## Installation

You can install the package via composer:

```bash
composer require osenco/laravel-settings
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-settings-config"
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-settings-migrations" && php artisan migrate
```

This is the contents of the published config file:

```php
return [
    // Define your global settings here
    'global' => [
        // 'setting_key' => 'default_value',
    ],

    // Define your tenant-specific settings here
    'tenant' => [
        // 'setting_key' => 'default_value',
    ],
];
```

## Usage

### Tenant Settings

To set a tenant-specific setting:

```php
tenant('setting_key', 'value');
```

To get a tenant-specific setting:

```php
tenant('setting_key'); // Returns 'value'
```

If a tenant-specific setting is not found, it will fall back to the global setting if available.

### Global Settings

To set a global setting:

```php
global_settings('setting_key', 'value');
```

To get a global setting:

```php
global_settings('setting_key'); // Returns 'value'
```

### Helper Function

You can also use the `settings()` helper function:

```php
// Set a tenant-specific setting
settings()->tenant()->set('setting_key', 'value');

// Get a tenant-specific setting
settings()->tenant()->get('setting_key');

// Set a global setting
settings()->global()->set('setting_key', 'value');

// Get a global setting
settings()->global()->get('setting_key');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Your Name](https://github.com/osenco)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
