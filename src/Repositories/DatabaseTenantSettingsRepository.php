<?php

namespace Osen\LaravelSettings\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Osen\LaravelSettings\Contracts\SettingsRepositoryContract; // Implement local contract
use Osen\LaravelSettings\Exceptions\CouldNotUnserializePayload; // Custom exception

class DatabaseTenantSettingsRepository implements SettingsRepositoryContract
{
    protected ?string $connection;
    protected ?string $table;
    protected ?string $tenantColumn;
    protected $tenantIdResolver;
    protected $encoder;
    protected $decoder;

    public function __construct(array $config)
    {
        $this->connection = Arr::get($config, 'connection');
        $this->table = Arr::get($config, 'table');
        $this->tenantColumn = Arr::get($config, 'tenant_column', 'tenant_id');
        $this->tenantIdResolver = Arr::get($config, 'current_tenant_resolver');
        $this->encoder = Arr::get($config, 'encoder', 'json_encode');
        $this->decoder = Arr::get($config, 'decoder', 'json_decode');
    }

    protected function getCurrentTenantId(): string|int|null
    {
        if (is_callable($this->tenantIdResolver)) {
            return call_user_func($this->tenantIdResolver);
        }
        return null; // Or throw an exception if tenant context is always required
    }

    public function getPropertiesInGroup(string $group): array
    {
        $tenantId = $this->getCurrentTenantId();

        $properties = $this->getQuery()
            ->where('group', $group)
            ->where($this->tenantColumn, $tenantId) // Filter by current tenant
            ->get()
            ->mapWithKeys(function ($property) {
                return [$property->name => $this->decode($property->payload)];
            })
            ->toArray();
        
        return $properties;
    }

    public function checkIfPropertyExists(string $group, string $name): bool
    {
        $tenantId = $this->getCurrentTenantId();
        return $this->getQuery()
            ->where('group', $group)
            ->where('name', $name)
            ->where($this->tenantColumn, $tenantId)
            ->exists();
    }

    public function getPropertyPayload(string $group, string $name)
    {
        $tenantId = $this->getCurrentTenantId();
        $property = $this->getQuery()
            ->where('group', $group)
            ->where('name', $name)
            ->where($this->tenantColumn, $tenantId)
            ->first();

        return $property ? $this->decode($property->payload) : null;
    }

    public function createProperty(string $group, string $name, $payload): void
    {
        $tenantId = $this->getCurrentTenantId();
        $this->getQuery()->insert([
            $this->tenantColumn => $tenantId,
            'group' => $group,
            'name' => $name,
            'payload' => $this->encode($payload),
            'locked' => false, // Assuming default is not locked
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function updatePropertiesPayload(string $group, array $properties): void
    {
        $tenantId = $this->getCurrentTenantId();
        foreach ($properties as $name => $payload) {
            $this->getQuery()
                ->where('group', $group)
                ->where('name', $name)
                ->where($this->tenantColumn, $tenantId)
                ->update([
                    'payload' => $this->encode($payload),
                    'updated_at' => now(),
                ]);
        }
    }

    public function deleteProperty(string $group, string $name): void
    {
        $tenantId = $this->getCurrentTenantId();
        $this->getQuery()
            ->where('group', $group)
            ->where('name', $name)
            ->where($this->tenantColumn, $tenantId)
            ->delete();
    }

    public function lockProperties(string $group, array $properties): void
    {
        $tenantId = $this->getCurrentTenantId();
        $this->getQuery()
            ->where('group', $group)
            ->whereIn('name', $properties)
            ->where($this->tenantColumn, $tenantId)
            ->update(['locked' => true, 'updated_at' => now()]);
    }

    public function unlockProperties(string $group, array $properties): void
    {
        $tenantId = $this->getCurrentTenantId();
        $this->getQuery()
            ->where('group', $group)
            ->whereIn('name', $properties)
            ->where($this->tenantColumn, $tenantId)
            ->update(['locked' => false, 'updated_at' => now()]);
    }

    public function getLockedProperties(string $group): array
    {
        $tenantId = $this->getCurrentTenantId();
        return $this->getQuery()
            ->where('group', $group)
            ->where('locked', true)
            ->where($this->tenantColumn, $tenantId)
            ->pluck('name')
            ->toArray();
    }

    protected function getQuery()
    {
        return DB::connection($this->connection)->table($this->table);
    }

    protected function encode($payload): string
    {
        return call_user_func($this->encoder, $payload);
    }

    protected function decode(?string $payload)
    {
        if ($payload === null) {
            return null;
        }
        
        $value = call_user_func_array($this->decoder, [$payload, true]);

        if($value === null && json_last_error() !== JSON_ERROR_NONE){
            throw CouldNotUnserializePayload::create($payload, json_last_error_msg());
        }

        return $value;
    }
}
