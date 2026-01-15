<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable;

use TinyBlocks\EnvironmentVariable\Internal\Exceptions\EnvironmentVariableMissing;
use TinyBlocks\EnvironmentVariable\Internal\Exceptions\InvalidEnvironmentValue;

/**
 * Provides methods to handling environment variables.
 */
interface Environment
{
    /**
     * Retrieves an instance of the environment variable.
     *
     * @param string $name The name of the environment variable.
     * @return Environment The environment variable instance.
     * @throws EnvironmentVariableMissing If the variable does not exist.
     */
    public static function from(string $name): Environment;

    /**
     * Retrieves an instance of the environment variable or uses a default value if not found.
     *
     * @param string $name The name of the environment variable.
     * @param string|null $defaultValueIfNotFound The default value to use if the environment variable is not found.
     * @return EnvironmentVariable The environment variable instance, either with the found value or the default.
     */
    public static function fromOrDefault(string $name, ?string $defaultValueIfNotFound = null): EnvironmentVariable;

    /**
     * Checks if the environment variable has a value. Values like `false`, `0`, and `-1` are valid and non-empty.
     *
     * @return bool True if the environment variable has a valid value, false otherwise.
     */
    public function hasValue(): bool;

    /**
     * Converts the environment variable value to a string.
     *
     * @return string The environment variable value as a string.
     */
    public function toString(): string;

    /**
     * Converts the environment variable value to an integer.
     *
     * @return int The environment variable value as an integer.
     * @throws InvalidEnvironmentValue If the value cannot be converted to an integer.
     */
    public function toInteger(): int;

    /**
     * Converts the environment variable value to a boolean.
     *
     * @return bool The environment variable value as a boolean.
     * @throws InvalidEnvironmentValue If the value cannot be converted to a boolean.
     */
    public function toBoolean(): bool;
}
