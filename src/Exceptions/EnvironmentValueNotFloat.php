<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable\Exceptions;

use InvalidArgumentException;

final class EnvironmentValueNotFloat extends InvalidArgumentException
{
    public function __construct(string $variable)
    {
        $template = 'The value for environment variable <%s> is invalid for conversion to <float>.';

        parent::__construct(message: sprintf($template, $variable));
    }
}
