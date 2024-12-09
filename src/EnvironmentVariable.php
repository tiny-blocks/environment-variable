<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable;

use TinyBlocks\EnvironmentVariable\Internal\Exceptions\EnvironmentVariableMissing;
use TinyBlocks\EnvironmentVariable\Internal\Exceptions\InvalidEnvironmentValue;

final readonly class EnvironmentVariable implements Environment
{
    private function __construct(private string $value, private string $variable)
    {
    }

    public static function from(string $name): EnvironmentVariable
    {
        $environmentVariable = getenv($name);

        return $environmentVariable === false
            ? throw new EnvironmentVariableMissing(variable: $name)
            : new EnvironmentVariable(value: $environmentVariable, variable: $name);
    }

    public static function fromOrDefault(string $name, string $defaultValueIfNotFound = null): EnvironmentVariable
    {
        $environmentVariable = getenv($name);

        return $environmentVariable === false
            ? new EnvironmentVariable(value: (string)$defaultValueIfNotFound, variable: $name)
            : new EnvironmentVariable(value: $environmentVariable, variable: $name);
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
        return is_numeric($this->value)
            ? (int)$this->value
            : throw InvalidEnvironmentValue::fromIntegerConversion(value: $this->value, variable: $this->variable);
    }

    public function toBoolean(): bool
    {
        $filteredValue = filter_var($this->value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $filteredValue !== null
            ? $filteredValue
            : throw InvalidEnvironmentValue::fromBooleanConversion(value: $this->value, variable: $this->variable);
    }
}
