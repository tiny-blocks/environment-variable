<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable\Exceptions;

use InvalidArgumentException;
use TinyBlocks\EnvironmentVariable\ApplicationEnvironment;

/**
 * Raised when no application environment recognizes an alias.
 */
final class ApplicationEnvironmentUnrecognized extends InvalidArgumentException
{
    public function __construct(string $alias)
    {
        $template = 'Application environment <%s> is not recognized. Accepted aliases are <%s>.';
        $acceptedAliases = implode(', ', array_map(
            static fn(ApplicationEnvironment $environment): string => implode(', ', $environment->aliases()),
            ApplicationEnvironment::cases()
        ));

        parent::__construct(message: sprintf($template, $alias, $acceptedAliases));
    }
}
