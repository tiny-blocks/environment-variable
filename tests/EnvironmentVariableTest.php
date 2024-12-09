<?php

declare(strict_types=1);

namespace TinyBlocks\EnvironmentVariable;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\EnvironmentVariable\Internal\Exceptions\EnvironmentVariableMissing;
use TinyBlocks\EnvironmentVariable\Internal\Exceptions\InvalidEnvironmentValue;

final class EnvironmentVariableTest extends TestCase
{
    #[DataProvider('stringConversionDataProvider')]
    public function testConvertToString(mixed $value, string $variable, string $expected): void
    {
        /** @Given the environment variable is set with the given string value */
        putenv(sprintf('%s=%s', $variable, $value));

        /** @When I try to convert the environment variable value to a string */
        $actual = EnvironmentVariable::from(name: $variable)->toString();

        /** @Then the result should match the expected string value */
        self::assertEquals($expected, $actual);
    }

    #[DataProvider('integerConversionDataProvider')]
    public function testConvertToInteger(mixed $value, string $variable, int $expected): void
    {
        /** @Given the environment variable is set with the given integer value */
        putenv(sprintf('%s=%s', $variable, $value));

        /** @When I try to convert the environment variable value to an integer */
        $actual = EnvironmentVariable::from(name: $variable)->toInteger();

        /** @Then the result should match the expected integer value */
        self::assertEquals($expected, $actual);
    }

    #[DataProvider('booleanConversionDataProvider')]
    public function testConvertToBoolean(mixed $value, string $variable, bool $expected): void
    {
        /** @Given the environment variable is set with the given boolean value */
        putenv(sprintf('%s=%s', $variable, $value));

        /** @When I try to convert the environment variable value to a boolean */
        $actual = EnvironmentVariable::from(name: $variable)->toBoolean();

        /** @Then the result should match the expected boolean value */
        self::assertEquals($expected, $actual);
    }

    public function testFromOrDefaultWithDefaultValue(): void
    {
        /** @Given that the environment variable 'NON_EXISTENT_MY_VAR' does not exist */
        $variable = 'NON_EXISTENT_MY_VAR';

        /** @When I try to get the value of the environment variable with a default value */
        $actual = EnvironmentVariable::fromOrDefault(name: $variable, defaultValueIfNotFound: '0');

        /** @Then the result should match the default value */
        self::assertEquals(0, $actual->toInteger());
    }

    public function testFromOrDefaultWithExistingVariable(): void
    {
        /** @Given that the environment variable 'MY_VAR' exists with the value 'existing_value' */
        putenv(sprintf('%s=%s', 'MY_VAR', 'existing_value'));

        /** @When I try to get the value of the environment variable */
        $actual = EnvironmentVariable::fromOrDefault(name: 'MY_VAR', defaultValueIfNotFound: 'default_value');

        /** @Then the result should match the existing value */
        self::assertEquals('existing_value', $actual->toString());
    }

    public function testFromOrDefaultWhenVariableIsMissingAndNoDefault(): void
    {
        /** @Given that the environment variable 'NON_EXISTENT_VAR' does not exist */
        $variable = 'NON_EXISTENT_VAR';

        /** @When I try to get the value of the missing environment variable without a default value */
        $actual = EnvironmentVariable::fromOrDefault(name: $variable);

        /** @Then the result should be no value */
        self::assertEmpty($actual->toString());
        self::assertFalse($actual->hasValue());
    }

    #[DataProvider('hasValueDataProvider')]
    public function testHasValue(mixed $value, string $variable): void
    {
        /** @Given the environment variable is set with the given value */
        putenv(sprintf('%s=%s', $variable, $value));

        /** @When I check if the environment variable has a value */
        $actual = EnvironmentVariable::from(name: $variable)->hasValue();

        /** @Then the result should be true (has value) */
        self::assertTrue($actual);
    }

    #[DataProvider('hasNoValueDataProvider')]
    public function testHasNoValue(mixed $value, string $variable): void
    {
        /** @Given the environment variable is set with the given value */
        putenv(sprintf('%s=%s', $variable, $value));

        /** @When I check if the environment variable has a value */
        $actual = EnvironmentVariable::from(name: $variable)->hasValue();

        /** @Then the result should be false (no value) */
        self::assertFalse($actual);
    }

    public function testExceptionWhenVariableIsMissing(): void
    {
        /** @Given that the environment variable 'NON_EXISTENT' does not exist */
        $variable = 'NON_EXISTENT';

        /** @Then an error indicating the variable is missing should occur */
        $this->expectException(EnvironmentVariableMissing::class);
        $this->expectExceptionMessage('Environment variable <NON_EXISTENT> is missing.');

        /** @When I try to get the value of the missing environment variable */
        EnvironmentVariable::from(name: $variable);
    }

    public function testExceptionWhenInvalidIntegerConversion(): void
    {
        /** @Given that the environment variable 'INVALID_INT' has an invalid integer value */
        putenv(sprintf('%s=%s', 'INVALID_INT', 'invalid-value'));

        /** @Then an error indicating the value cannot be converted to an integer should occur */
        $this->expectException(InvalidEnvironmentValue::class);
        $this->expectExceptionMessage(
            'The value <invalid-value> for environment variable <INVALID_INT> is invalid for conversion to <integer>.'
        );

        /** @When I try to convert the invalid value to an integer */
        EnvironmentVariable::from(name: 'INVALID_INT')->toInteger();
    }

    public function testExceptionWhenInvalidBooleanConversion(): void
    {
        /** @Given that the environment variable 'INVALID_BOOL' has an invalid boolean value */
        putenv(sprintf('%s=%s', 'INVALID_BOOL', 'invalid-value'));

        /** @Then an error indicating the value cannot be converted to a boolean should occur */
        $this->expectException(InvalidEnvironmentValue::class);
        $this->expectExceptionMessage(
            'The value <invalid-value> for environment variable <INVALID_BOOL> is invalid for conversion to <boolean>.'
        );

        /** @When I try to convert the invalid value to a boolean */
        EnvironmentVariable::from(name: 'INVALID_BOOL')->toBoolean();
    }

    public static function stringConversionDataProvider(): array
    {
        return [
            'String value'        => [
                'value'    => 'Hello, world!',
                'variable' => 'VALID_STRING',
                'expected' => 'Hello, world!'
            ],
            'Numeric string'      => [
                'value'    => '123',
                'variable' => 'NUMERIC_STRING',
                'expected' => '123'
            ],
            'Boolean true value'  => [
                'value'    => true,
                'variable' => 'BOOLEAN_TRUE',
                'expected' => '1'
            ],
            'Boolean false value' => [
                'value'    => false,
                'variable' => 'BOOLEAN_FALSE',
                'expected' => ''
            ]
        ];
    }

    public static function integerConversionDataProvider(): array
    {
        return [
            'Float value'    => [
                'value'    => '99.99',
                'variable' => 'FLOAT_VALUE',
                'expected' => 99
            ],
            'Integer value'  => [
                'value'    => '123',
                'variable' => 'VALID_INT',
                'expected' => 123
            ],
            'Numeric string' => [
                'value'    => '42',
                'variable' => 'NUMERIC_STRING',
                'expected' => 42
            ]
        ];
    }

    public static function booleanConversionDataProvider(): array
    {
        return [
            'Numeric value one as string'   => [
                'value'    => '1',
                'variable' => 'NUMERIC_TRUE',
                'expected' => true
            ],
            'Numeric value zero as string'  => [
                'value'    => '0',
                'variable' => 'NUMERIC_FALSE',
                'expected' => false
            ],
            'Boolean true value as string'  => [
                'value'    => 'true',
                'variable' => 'BOOLEAN_TRUE',
                'expected' => true
            ],
            'Boolean false value as string' => [
                'value'    => 'false',
                'variable' => 'BOOLEAN_FALSE',
                'expected' => false
            ]
        ];
    }

    public static function hasValueDataProvider(): array
    {
        return [
            'String value'           => [
                'value'    => 'Hello, World!',
                'variable' => 'STRING_VALUE'
            ],
            'Integer value 0'        => [
                'value'    => '0',
                'variable' => 'INTEGER_ZERO'
            ],
            'Boolean value true'     => [
                'value'    => 'true',
                'variable' => 'BOOLEAN_TRUE'
            ],
            'Boolean value false'    => [
                'value'    => 'false',
                'variable' => 'BOOLEAN_FALSE'
            ],
            'Integer value positive' => [
                'value'    => '123',
                'variable' => 'INTEGER_POSITIVE'
            ],
            'Integer value negative' => [
                'value'    => '-1',
                'variable' => 'INTEGER_NEGATIVE'
            ]
        ];
    }

    public static function hasNoValueDataProvider(): array
    {
        return [
            'Null value'              => [
                'value'    => null,
                'variable' => 'NULL_VALUE'
            ],
            'Empty string'            => [
                'value'    => '',
                'variable' => 'EMPTY_STRING'
            ],
            'String null value'       => [
                'value'    => 'NULL',
                'variable' => 'NULL_VALUE'
            ],
            'String with only spaces' => [
                'value'    => '    ',
                'variable' => 'STRING_WITH_SPACES'
            ]
        ];
    }
}
