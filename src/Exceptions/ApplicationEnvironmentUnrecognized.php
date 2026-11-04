<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable\Exceptions;

use InvalidArgumentException;
use TinyBlocks\EnvironmentVariable\ApplicationEnvironment;

/**
 * Raised when no application environment recognizes an alias.
 *
 * <p>The message names the accepted aliases, never the value received, so a secret read by mistake does not leak into
 * logs or stack traces.</p>
 */
final class ApplicationEnvironmentUnrecognized extends InvalidArgumentException
{
    public function __construct()
    {
        $template = 'Application environment is not recognized. Accepted aliases are <%s>.';
        $acceptedAliases = implode(', ', array_map(
            static fn(ApplicationEnvironment $environment): string => implode(', ', $environment->aliases()),
            ApplicationEnvironment::cases()
        ));

        parent::__construct(message: sprintf($template, $acceptedAliases));
    }
}
