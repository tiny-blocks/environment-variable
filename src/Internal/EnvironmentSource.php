<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable\Internal;

final readonly class EnvironmentSource
{
    public static function lookup(string $name): ?string
    {
        if (array_key_exists($name, $_ENV) && is_scalar($_ENV[$name])) {
            return (string)$_ENV[$name];
        }

        if (array_key_exists($name, $_SERVER) && is_scalar($_SERVER[$name])) {
            return (string)$_SERVER[$name];
        }

        $value = getenv($name);

        return $value === false ? null : $value;
    }
}
