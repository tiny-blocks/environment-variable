<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable;

use TinyBlocks\EnvironmentVariable\Exceptions\ApplicationEnvironmentUnrecognized;
use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentVariableMissing;

/**
 * Environment the application runs in, resolved from the aliases each environment recognizes.
 *
 * <p>An alias matches regardless of casing and of surrounding whitespace, so <code>local</code>,
 * <code>LocalHost</code>, and <code>LOCALHOST</code> padded with spaces all resolve to
 * {@see ApplicationEnvironment::LOCAL}.</p>
 */
enum ApplicationEnvironment: string
{
    case LOCAL = 'local';
    case TEST = 'test';
    case DEVELOPMENT = 'development';
    case STAGING = 'staging';
    case PRODUCTION = 'production';

    /**
     * Creates an ApplicationEnvironment from the <code>APP_ENV</code> environment variable.
     *
     * <p>The variable is read through {@see EnvironmentVariable}, so the same resolution order applies.</p>
     *
     * @return ApplicationEnvironment The environment matching the value of <code>APP_ENV</code>.
     * @throws EnvironmentVariableMissing If <code>APP_ENV</code> is not set.
     * @throws ApplicationEnvironmentUnrecognized If no environment recognizes the value of <code>APP_ENV</code>.
     */
    public static function current(): ApplicationEnvironment
    {
        return ApplicationEnvironment::fromAlias(alias: EnvironmentVariable::from(name: 'APP_ENV')->toString());
    }

    /**
     * Creates an ApplicationEnvironment from one of its aliases.
     *
     * @param string $alias The alias, matched regardless of casing and of surrounding whitespace.
     * @return ApplicationEnvironment The environment that recognizes the alias.
     * @throws ApplicationEnvironmentUnrecognized If no environment recognizes the alias.
     */
    public static function fromAlias(string $alias): ApplicationEnvironment
    {
        $candidate = strtolower(trim($alias));

        $recognized = array_find(
            ApplicationEnvironment::cases(),
            static fn(ApplicationEnvironment $environment): bool => in_array($candidate, $environment->aliases(), true)
        );

        return ($recognized ?? throw new ApplicationEnvironmentUnrecognized(alias: $alias));
    }

    /**
     * Tells whether the application runs in the test environment.
     *
     * @return bool True if the environment is {@see ApplicationEnvironment::TEST}, false otherwise.
     */
    public function isTest(): bool
    {
        return $this === self::TEST;
    }

    /**
     * Returns the aliases the environment recognizes.
     *
     * @return list<string> The lowercase aliases, starting with the backing value of the case.
     */
    public function aliases(): array
    {
        return match ($this) {
            self::LOCAL       => ['local', 'localhost'],
            self::TEST        => ['test', 'testing'],
            self::DEVELOPMENT => ['development', 'dev', 'develop'],
            self::STAGING     => ['staging', 'stage', 'homolog', 'homologation'],
            self::PRODUCTION  => ['production', 'prod']
        };
    }

    /**
     * Tells whether the application runs in the local environment.
     *
     * @return bool True if the environment is {@see ApplicationEnvironment::LOCAL}, false otherwise.
     */
    public function isLocal(): bool
    {
        return $this === self::LOCAL;
    }

    /**
     * Tells whether the application runs in the staging environment.
     *
     * @return bool True if the environment is {@see ApplicationEnvironment::STAGING}, false otherwise.
     */
    public function isStaging(): bool
    {
        return $this === self::STAGING;
    }

    /**
     * Tells whether the application runs in the production environment.
     *
     * @return bool True if the environment is {@see ApplicationEnvironment::PRODUCTION}, false otherwise.
     */
    public function isProduction(): bool
    {
        return $this === self::PRODUCTION;
    }

    /**
     * Tells whether the application runs in the development environment.
     *
     * @return bool True if the environment is {@see ApplicationEnvironment::DEVELOPMENT}, false otherwise.
     */
    public function isDevelopment(): bool
    {
        return $this === self::DEVELOPMENT;
    }
}
