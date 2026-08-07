<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable;

use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentValueNotBoolean;
use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentValueNotFloat;
use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentValueNotInteger;
use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentVariableMissing;

/**
 * Provides methods to handling environment variables.
 *
 * <p>Values are resolved from <code>$_ENV</code>, then <code>$_SERVER</code>, then the process
 * environment. Names in the <code>HTTP_</code> namespace are resolved only from sources that an
 * HTTP request cannot reach, because CGI-like servers map request headers into
 * <code>$_SERVER</code> and into the SAPI environment under that same prefix.</p>
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
     * @return Environment The environment variable instance, either with the found value or the default.
     */
    public static function fromOrDefault(string $name, ?string $defaultValueIfNotFound = null): Environment;

    /**
     * Converts the environment variable value to a float.
     *
     * @return float The environment variable value as a float.
     * @throws EnvironmentValueNotFloat If the value cannot be converted to a float.
     */
    public function toFloat(): float;

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
     * Converts the environment variable value to a boolean.
     *
     * @return bool The environment variable value as a boolean.
     * @throws EnvironmentValueNotBoolean If the value cannot be converted to a boolean.
     */
    public function toBoolean(): bool;

    /**
     * Converts the environment variable value to an integer.
     *
     * @return int The environment variable value as an integer.
     * @throws EnvironmentValueNotInteger If the value cannot be converted to an integer.
     */
    public function toInteger(): int;
}
