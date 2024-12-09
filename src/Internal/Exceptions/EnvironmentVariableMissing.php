<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable\Internal\Exceptions;

use InvalidArgumentException;

final class EnvironmentVariableMissing extends InvalidArgumentException
{
    public function __construct(string $variable)
    {
        $template = 'Environment variable <%s> is missing.';

        parent::__construct(message: sprintf($template, $variable));
    }
}
