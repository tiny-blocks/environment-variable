<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable;

use TinyBlocks\EnvironmentVariable\Internal\EnvironmentSource;
use TinyBlocks\EnvironmentVariable\Internal\Exceptions\EnvironmentValueNotBoolean;
use TinyBlocks\EnvironmentVariable\Internal\Exceptions\EnvironmentValueNotInteger;
use TinyBlocks\EnvironmentVariable\Internal\Exceptions\EnvironmentVariableMissing;

final readonly class EnvironmentVariable implements Environment
{
    private function __construct(private string $value, private string $variable)
    {
    }

    public static function from(string $name): EnvironmentVariable
    {
        $environmentVariable = EnvironmentSource::lookup(name: $name);

        return is_null($environmentVariable)
            ? throw new EnvironmentVariableMissing(variable: $name)
            : new EnvironmentVariable(value: $environmentVariable, variable: $name);
    }

    public static function fromOrDefault(string $name, ?string $defaultValueIfNotFound = null): EnvironmentVariable
    {
        $environmentVariable = EnvironmentSource::lookup(name: $name) ?? $defaultValueIfNotFound ?? '';

        return new EnvironmentVariable(value: $environmentVariable, variable: $name);
    }

    public function hasValue(): bool
    {
        return match (strtolower(trim($this->value))) {
            '', 'null' => false,
            default    => true
        };
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function toInteger(): int
    {
        $filteredValue = filter_var($this->value, FILTER_VALIDATE_INT);

        return $filteredValue !== false
            ? $filteredValue
            : throw new EnvironmentValueNotInteger(variable: $this->variable);
    }

    public function toBoolean(): bool
    {
        $filteredValue = filter_var($this->value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $filteredValue !== null
            ? $filteredValue
            : throw new EnvironmentValueNotBoolean(variable: $this->variable);
    }
}
