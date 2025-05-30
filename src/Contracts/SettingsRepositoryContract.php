<?php

namespace Osen\LaravelSettings\Contracts;

interface SettingsRepositoryContract
{
    public function getPropertiesInGroup(string $group): array;

    public function checkIfPropertyExists(string $group, string $name): bool;

    public function getPropertyPayload(string $group, string $name);

    public function createProperty(string $group, string $name, $payload): void;

    public function updatePropertiesPayload(string $group, array $properties): void;

    public function deleteProperty(string $group, string $name): void;

    public function lockProperties(string $group, array $properties): void;

    public function unlockProperties(string $group, array $properties): void;

    public function getLockedProperties(string $group): array;
}
