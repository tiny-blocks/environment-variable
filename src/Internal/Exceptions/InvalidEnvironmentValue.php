<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable\Internal\Exceptions;

use InvalidArgumentException;

final class InvalidEnvironmentValue extends InvalidArgumentException
{
    public function __construct(string $value, string $variable, string $conversionType)
    {
        $template = 'The value <%s> for environment variable <%s> is invalid for conversion to <%s>.';

        parent::__construct(message: sprintf($template, $value, $variable, $conversionType));
    }

    public static function fromIntegerConversion(string $value, string $variable): InvalidEnvironmentValue
    {
        return new InvalidEnvironmentValue(value: $value, variable: $variable, conversionType: 'integer');
    }

    public static function fromBooleanConversion(string $value, string $variable): InvalidEnvironmentValue
    {
        return new InvalidEnvironmentValue(value: $value, variable: $variable, conversionType: 'boolean');
    }
}
