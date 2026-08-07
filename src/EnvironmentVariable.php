<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable;

use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentVariableMissing;
use TinyBlocks\EnvironmentVariable\Internal\EnvironmentSource;
use TinyBlocks\EnvironmentVariable\Internal\EnvironmentValue;

final readonly class EnvironmentVariable implements Environment
{
    private function __construct(private EnvironmentValue $environmentValue)
    {
    }

    public static function from(string $name): EnvironmentVariable
    {
        $value = EnvironmentSource::lookup(name: $name);

        return is_null($value)
            ? throw new EnvironmentVariableMissing(variable: $name)
            : new EnvironmentVariable(environmentValue: EnvironmentValue::from(value: $value, variable: $name));
    }

    public static function fromOrDefault(string $name, ?string $defaultValueIfNotFound = null): EnvironmentVariable
    {
        $value = (EnvironmentSource::lookup(name: $name) ?? $defaultValueIfNotFound ?? '');

        return new EnvironmentVariable(environmentValue: EnvironmentValue::from(value: $value, variable: $name));
    }

    public function toFloat(): float
    {
        return $this->environmentValue->toFloat();
    }

    public function hasValue(): bool
    {
        return $this->environmentValue->hasValue();
    }

    public function toString(): string
    {
        return $this->environmentValue->toString();
    }

    public function toBoolean(): bool
    {
        return $this->environmentValue->toBoolean();
    }

    public function toInteger(): int
    {
        return $this->environmentValue->toInteger();
    }
}
