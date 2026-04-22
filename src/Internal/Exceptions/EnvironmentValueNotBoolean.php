<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable\Internal\Exceptions;

use InvalidArgumentException;

final class EnvironmentValueNotBoolean extends InvalidArgumentException
{
    public function __construct(private readonly string $variable)
    {
        $template = 'The value for environment variable <%s> is invalid for conversion to <boolean>.';

        parent::__construct(message: sprintf($template, $this->variable));
    }
}
