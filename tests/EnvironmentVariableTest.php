<?php

declare(strict_types=1);

namespace Test\TinyBlocks\EnvironmentVariable;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\EnvironmentVariable\EnvironmentVariable;
use TinyBlocks\EnvironmentVariable\Internal\Exceptions\EnvironmentValueNotBoolean;
use TinyBlocks\EnvironmentVariable\Internal\Exceptions\EnvironmentValueNotInteger;
use TinyBlocks\EnvironmentVariable\Internal\Exceptions\EnvironmentVariableMissing;

final class EnvironmentVariableTest extends TestCase
{
    private const array MANAGED_VARIABLES = [
        'MY_VAR',
        'VALID_INT',
        'INVALID_INT',
        'INVALID_BOOL',
        'NON_EXISTENT',
        'NULL_VALUE',
        'EMPTY_STRING',
        'VALID_STRING',
        'NEGATIVE_INT',
        'NUMERIC_TRUE',
        'BOOLEAN_TRUE',
        'STRING_VALUE',
        'INTEGER_ZERO',
        'NUMERIC_FALSE',
        'BOOLEAN_FALSE',
        'NON_SCALAR_ENV',
        'NUMERIC_STRING',
        'NON_EXISTENT_VAR',
        'INTEGER_POSITIVE',
        'INTEGER_NEGATIVE',
        'NON_SCALAR_SERVER',
        'STRING_WITH_SPACES',
        'FROM_ENV_SUPERGLOBAL',
        'NON_EXISTENT_MY_VAR',
        'FROM_SERVER_SUPERGLOBAL'
    ];

    protected function tearDown(): void
    {
        foreach (self::MANAGED_VARIABLES as $variable) {
            putenv($variable);
            unset($_ENV[$variable], $_SERVER[$variable]);
        }
    }

    #[DataProvider('stringConversionDataProvider')]
    public function testToStringWhenValuePresentThenReturnsExpectedString(
        mixed $value,
        string $variable,
        string $expected
    ): void {
        /** @Given the environment variable is set with the given raw value */
        putenv(sprintf('%s=%s', $variable, $value));

        /** @When converting the environment variable to string */
        $actual = EnvironmentVariable::from(name: $variable)->toString();

        /** @Then the returned string matches the expected representation */
        self::assertSame($expected, $actual);
    }

    #[DataProvider('integerConversionDataProvider')]
    public function testToIntegerWhenValueIsNumericThenReturnsExpectedInteger(
        string $value,
        string $variable,
        int $expected
    ): void {
        /** @Given the environment variable is set with a numeric string */
        putenv(sprintf('%s=%s', $variable, $value));

        /** @When converting the environment variable to integer */
        $actual = EnvironmentVariable::from(name: $variable)->toInteger();

        /** @Then the returned integer matches the expected value */
        self::assertSame($expected, $actual);
    }

    #[DataProvider('booleanConversionDataProvider')]
    public function testToBooleanWhenValueIsBooleanLikeThenReturnsExpectedBoolean(
        string $value,
        string $variable,
        bool $expected
    ): void {
        /** @Given the environment variable is set with a boolean-like value */
        putenv(sprintf('%s=%s', $variable, $value));

        /** @When converting the environment variable to boolean */
        $actual = EnvironmentVariable::from(name: $variable)->toBoolean();

        /** @Then the returned boolean matches the expected value */
        self::assertSame($expected, $actual);
    }

    public function testFromOrDefaultWhenVariableMissingThenReturnsDefault(): void
    {
        /** @Given the environment variable does not exist */
        $variable = 'NON_EXISTENT_MY_VAR';

        /** @When requesting the variable with a default value */
        $actual = EnvironmentVariable::fromOrDefault(name: $variable, defaultValueIfNotFound: '0');

        /** @Then the returned instance exposes the default value */
        self::assertSame(0, $actual->toInteger());
    }

    public function testFromOrDefaultWhenVariableExistsThenReturnsExistingValue(): void
    {
        /** @Given the environment variable exists with an existing value */
        putenv(sprintf('%s=%s', 'MY_VAR', 'existing_value'));

        /** @When requesting the variable with a default value */
        $actual = EnvironmentVariable::fromOrDefault(name: 'MY_VAR', defaultValueIfNotFound: 'default_value');

        /** @Then the returned instance exposes the existing value */
        self::assertSame('existing_value', $actual->toString());
    }

    public function testFromOrDefaultWhenVariableMissingAndNoDefaultThenToStringIsEmpty(): void
    {
        /** @Given the environment variable does not exist */
        $variable = 'NON_EXISTENT_VAR';

        /** @When requesting the variable without a default value */
        $actual = EnvironmentVariable::fromOrDefault(name: $variable);

        /** @Then the returned instance exposes an empty string */
        self::assertSame('', $actual->toString());
    }

    public function testFromOrDefaultWhenVariableMissingAndNoDefaultThenHasValueIsFalse(): void
    {
        /** @Given the environment variable does not exist */
        $variable = 'NON_EXISTENT_VAR';

        /** @When requesting the variable without a default value */
        $actual = EnvironmentVariable::fromOrDefault(name: $variable);

        /** @Then the returned instance reports no value */
        self::assertFalse($actual->hasValue());
    }

    #[DataProvider('hasValueDataProvider')]
    public function testHasValueWhenValueIsMeaningfulThenReturnsTrue(string $value, string $variable): void
    {
        /** @Given the environment variable is set with a meaningful value */
        putenv(sprintf('%s=%s', $variable, $value));

        /** @When checking if the environment variable has a value */
        $actual = EnvironmentVariable::from(name: $variable)->hasValue();

        /** @Then the check reports the presence of a value */
        self::assertTrue($actual);
    }

    #[DataProvider('hasNoValueDataProvider')]
    public function testHasValueWhenValueIsAbsentOrNullLikeThenReturnsFalse(?string $value, string $variable): void
    {
        /** @Given the environment variable is set with a null-like value */
        putenv(sprintf('%s=%s', $variable, $value));

        /** @When checking if the environment variable has a value */
        $actual = EnvironmentVariable::from(name: $variable)->hasValue();

        /** @Then the check reports the absence of a value */
        self::assertFalse($actual);
    }

    public function testFromWhenVariableIsMissingThenThrowsEnvironmentVariableMissing(): void
    {
        /** @Given the environment variable does not exist */
        $variable = 'NON_EXISTENT';

        /** @Then a missing environment variable exception is expected */
        $this->expectException(EnvironmentVariableMissing::class);
        $this->expectExceptionMessage('Environment variable <NON_EXISTENT> is missing.');

        /** @When requesting the missing environment variable */
        EnvironmentVariable::from(name: $variable);
    }

    public function testFromWhenScalarPresentInEnvSuperglobalThenValueIsCoerced(): void
    {
        /** @Given a non-string scalar available only in $_ENV */
        $_ENV['FROM_ENV_SUPERGLOBAL'] = 42;

        /** @When reading the environment variable */
        $actual = EnvironmentVariable::from(name: 'FROM_ENV_SUPERGLOBAL')->toString();

        /** @Then the value from $_ENV is coerced to string */
        self::assertSame('42', $actual);
    }

    public function testFromWhenScalarPresentInServerSuperglobalThenValueIsCoerced(): void
    {
        /** @Given a non-string scalar available only in $_SERVER */
        $_SERVER['FROM_SERVER_SUPERGLOBAL'] = 7;

        /** @When reading the environment variable */
        $actual = EnvironmentVariable::from(name: 'FROM_SERVER_SUPERGLOBAL')->toString();

        /** @Then the value from $_SERVER is coerced to string */
        self::assertSame('7', $actual);
    }

    public function testFromWhenNonScalarInEnvSuperglobalThenThrowsMissing(): void
    {
        /** @Given a non-scalar entry in $_ENV */
        $_ENV['NON_SCALAR_ENV'] = ['nested' => 'value'];

        /** @Then a missing environment variable exception is expected */
        $this->expectException(EnvironmentVariableMissing::class);

        /** @When reading the environment variable */
        EnvironmentVariable::from(name: 'NON_SCALAR_ENV');
    }

    public function testFromWhenNonScalarInServerSuperglobalThenThrowsMissing(): void
    {
        /** @Given a non-scalar entry in $_SERVER */
        $_SERVER['NON_SCALAR_SERVER'] = ['nested' => 'value'];

        /** @Then a missing environment variable exception is expected */
        $this->expectException(EnvironmentVariableMissing::class);

        /** @When reading the environment variable */
        EnvironmentVariable::from(name: 'NON_SCALAR_SERVER');
    }

    public function testToIntegerWhenValueIsNotNumericThenThrowsEnvironmentValueNotInteger(): void
    {
        /** @Given the environment variable holds a non-numeric value */
        putenv(sprintf('%s=%s', 'INVALID_INT', 'invalid-value'));

        /** @Then an invalid integer conversion exception is expected */
        $this->expectException(EnvironmentValueNotInteger::class);
        $this->expectExceptionMessage(
            'The value for environment variable <INVALID_INT> is invalid for conversion to <integer>.'
        );

        /** @When converting the environment variable to integer */
        EnvironmentVariable::from(name: 'INVALID_INT')->toInteger();
    }

    public function testToBooleanWhenValueIsNotBooleanLikeThenThrowsEnvironmentValueNotBoolean(): void
    {
        /** @Given the environment variable holds a non-boolean-like value */
        putenv(sprintf('%s=%s', 'INVALID_BOOL', 'invalid-value'));

        /** @Then an invalid boolean conversion exception is expected */
        $this->expectException(EnvironmentValueNotBoolean::class);
        $this->expectExceptionMessage(
            'The value for environment variable <INVALID_BOOL> is invalid for conversion to <boolean>.'
        );

        /** @When converting the environment variable to boolean */
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
            'Integer value'    => [
                'value'    => '123',
                'variable' => 'VALID_INT',
                'expected' => 123
            ],
            'Numeric string'   => [
                'value'    => '42',
                'variable' => 'NUMERIC_STRING',
                'expected' => 42
            ],
            'Negative integer' => [
                'value'    => '-7',
                'variable' => 'NEGATIVE_INT',
                'expected' => -7
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

