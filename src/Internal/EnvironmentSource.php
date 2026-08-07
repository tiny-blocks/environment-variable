<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable\Internal;

final class EnvironmentSource
{
    private const string REQUEST_HEADER_PREFIX = 'HTTP_';

    private function __construct()
    {
    }

    public static function lookup(string $name): ?string
    {
        $requestControlled = str_starts_with($name, self::REQUEST_HEADER_PREFIX);

        $value = (self::fromScalar(value: ($_ENV[$name] ?? null))
            ?? self::fromRequestScope(value: ($_SERVER[$name] ?? null), requestControlled: $requestControlled));

        return ($value ?? self::fromProcess(name: $name, localOnly: $requestControlled));
    }

    private static function fromScalar(mixed $value): ?string
    {
        return is_scalar($value) ? (string)$value : null;
    }

    private static function fromProcess(string $name, bool $localOnly): ?string
    {
        $value = getenv($name, $localOnly);

        return $value === false ? null : $value;
    }

    private static function fromRequestScope(mixed $value, bool $requestControlled): ?string
    {
        return $requestControlled ? null : self::fromScalar(value: $value);
    }
}
