<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable\Internal;

use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentValueNotBoolean;
use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentValueNotFloat;
use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentValueNotInteger;

final readonly class EnvironmentValue
{
    private function __construct(private string $value, private string $variable)
    {
    }

    public static function from(string $value, string $variable): EnvironmentValue
    {
        return new EnvironmentValue(value: $value, variable: $variable);
    }

    public function toFloat(): float
    {
        $filteredValue = filter_var($this->value, FILTER_VALIDATE_FLOAT);

        return $filteredValue !== false
            ? $filteredValue
            : throw new EnvironmentValueNotFloat(variable: $this->variable);
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

    public function toBoolean(): bool
    {
        $filteredValue = filter_var($this->value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return ($filteredValue ?? throw new EnvironmentValueNotBoolean(variable: $this->variable));
    }

    public function toInteger(): int
    {
        $filteredValue = filter_var($this->value, FILTER_VALIDATE_INT);

        return $filteredValue !== false
            ? $filteredValue
            : throw new EnvironmentValueNotInteger(variable: $this->variable);
    }
}
