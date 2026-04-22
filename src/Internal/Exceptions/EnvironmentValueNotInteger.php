<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable\Internal\Exceptions;

use InvalidArgumentException;

final class EnvironmentValueNotInteger extends InvalidArgumentException
{
    public function __construct(private readonly string $variable)
    {
        $template = 'The value for environment variable <%s> is invalid for conversion to <integer>.';

        parent::__construct(message: sprintf($template, $this->variable));
    }
}
