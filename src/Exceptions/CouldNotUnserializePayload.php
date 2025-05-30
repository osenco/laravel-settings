<?php

namespace Osen\LaravelSettings\Exceptions;

use Exception;

class CouldNotUnserializePayload extends Exception
{
    public static function create(string $payload, string $error): self
    {
        return new self("Could not unserialize payload: `{$payload}`. Error: {$error}");
    }
}
