<?php

namespace Osen\LaravelSettings;

use Illuminate\Contracts\Foundation\Application;
use Osen\LaravelSettings\Contracts\SettingsRepositoryContract; // Use local contract
use Illuminate\Support\Str;
use Osen\LaravelSettings\Exceptions\SettingNotFoundException;

class SettingsManager
{
    protected Application $app;
    // Update type hint to local contract
    protected SettingsRepositoryContract $repository;
    protected array $config;
    protected ?string $currentTenantId = null;
    protected array $cachedSettings = [];
    protected string $activeGroup = 'default'; // Default group

    // Update type hint in constructor
    public function __construct(Application $app, SettingsRepositoryContract $repository, array $config)
    {
        $this->app = $app;
        $this->repository = $repository; // This is the DatabaseTenantSettingsRepository instance
        $this->config = $config;
        $this->resolveCurrentTenantId(); // Resolve tenant ID upon instantiation
    }

    protected function resolveCurrentTenantId(): void
    {
        $resolver = $this->config['current_tenant_resolver'];
        if (is_callable($resolver)) {
            $this->currentTenantId = call_user_func($resolver);
        }
        // If no resolver or resolver returns null, settings are considered global for this instance
        // or until forTenant() is called.
    }

    /**
     * Set the tenant context for the current operation chain.
     *
     * @param string|int|null $tenantId
     * @return $this
     */
    public function forTenant(string|int|null $tenantId): self
    {
        // This is a bit tricky with how Spatie's original works with specific Settings classes.
        // For a simple key-value helper, this approach is more direct.
        // If you were to replicate Settings classes, the tenant context would need to be managed
        // more deeply, perhaps by having the repository itself be tenant-aware via its constructor
        // or a setter method that the manager calls.
        
        // For now, let's assume the repository is already tenant-aware via its config
        // or that this method re-initializes/re-configures the repository or a new instance.
        // The DatabaseTenantSettingsRepository is designed to use the resolver from config.
        // This method could override that for a specific chain of calls if the repo supports it.

        // A simple approach for a direct manager:
        $this->currentTenantId = $tenantId;
        $this->cachedSettings = []; // Clear cache when tenant changes
        
        // If your repository needs explicit tenant setting:
        if (method_exists($this->repository, 'setTenantId')) {
            $this->repository->setTenantId($tenantId);
        }
        return $this;
    }

    /**
     * Set the settings group for subsequent operations.
     *
     * @param string $group
     * @return $this
     */
    public function group(string $group): self
    {
        $this->activeGroup = $group;
        // Cache should probably be group-specific too
        // $this->cachedSettings = []; // Or a more sophisticated multi-level cache
        return $this;
    }

    public function get(string $key, $default = null)
    {
        $group = $this->activeGroup;
        $cacheKey = $this->getCacheKey($group, $key);

        if (array_key_exists($cacheKey, $this->cachedSettings)) {
            return $this->cachedSettings[$cacheKey];
        }

        if ($this->repository->checkIfPropertyExists($group, $key)) {
            $value = $this->repository->getPropertyPayload($group, $key);
            $this->cachedSettings[$cacheKey] = $value;
            return $value;
        }

        return $default;
    }

    public function set(string $key, $value): void
    {
        $group = $this->activeGroup;
        $cacheKey = $this->getCacheKey($group, $key);

        // For a simple key-value store, we might just update or create.
        // Spatie's approach involves Settings classes and migrations for structure.
        // This simplified version directly writes.

        if ($this->repository->checkIfPropertyExists($group, $key)) {
            $this->repository->updatePropertiesPayload($group, [$key => $value]);
        } else {
            $this->repository->createProperty($group, $key, $value);
        }
        $this->cachedSettings[$cacheKey] = $value;
    }

    /**
     * Persist a setting value (alias for set, assuming set also saves).
     */
    public function save(string $key, $value): void
    {
        $this->set($key, $value);
        // In a more complex system, set might only stage changes, and save would persist them.
        // Here, set already persists.
    }

    public function all(string $group = null): array
    {
        $group = $group ?? $this->activeGroup;
        // This should ideally cache the whole group if fetched
        return $this->repository->getPropertiesInGroup($group);
    }

    public function has(string $key): bool
    {
        $group = $this->activeGroup;
        $cacheKey = $this->getCacheKey($group, $key);

        if (array_key_exists($cacheKey, $this->cachedSettings)) {
            return true;
        }

        return $this->repository->checkIfPropertyExists($group, $key);
    }

    protected function getCacheKey(string $group, string $name): string
    {
        // Include tenant ID in cache key to prevent collisions if manager is used for multiple tenants
        $tenantPart = $this->currentTenantId ?? 'global';
        return "{$tenantPart}.{$group}.{$name}";
    }

    // --- Methods to mimic Spatie's Settings class interaction (more advanced) ---
    // These would require a more significant rewrite and understanding of how you want
    // to manage settings classes (discovery, registration, casting, etc.)

    /**
     * Load a specific settings class.
     *
     * This is a placeholder for how one might begin to replicate Spatie's Settings class functionality.
     * It would involve instantiating the settings class, loading its properties from the repository,
     * handling casts, default values, etc.
     *
     * @param string $settingsClass The ::class string of the settings class
     * @return object An instance of the settings class, populated with values.
     */
    public function load(string $settingsClass): object
    {
        if (!class_exists($settingsClass)) {
            throw new \Exception("Settings class {$settingsClass} not found.");
        }

        // $group = $settingsClass::group(); // Static method on settings class
        // $properties = $this->repository->getPropertiesInGroup($group);
        // $settingsInstance = new $settingsClass(...$properties); // Simplified; need to handle casts, defaults etc.
        // return $settingsInstance;
        
        // For now, this is a conceptual placeholder.
        // The actual implementation would be much more involved to match Spatie's features.
        throw new \BadMethodCallException('Loading full settings classes is not yet fully implemented in this simplified manager.');
    }
}
