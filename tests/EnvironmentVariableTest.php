<?php

declare(strict_types=1);

namespace Test\TinyBlocks\EnvironmentVariable;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\EnvironmentVariable\EnvironmentVariable;
use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentValueNotBoolean;
use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentValueNotFloat;
use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentValueNotInteger;
use TinyBlocks\EnvironmentVariable\Exceptions\EnvironmentVariableMissing;

final class EnvironmentVariableTest extends TestCase
{
    private const array MANAGED_VARIABLES = [
        'MY_VAR',
        'VALID_INT',
        'INVALID_INT',
        'VALID_FLOAT',
        'INVALID_BOOL',
        'NON_EXISTENT',
        'NULL_VALUE',
        'HTTP_PROXY',
        'EMPTY_STRING',
        'VALID_STRING',
        'NEGATIVE_INT',
        'NUMERIC_TRUE',
        'BOOLEAN_TRUE',
        'STRING_VALUE',
        'INTEGER_ZERO',
        'NUMERIC_FALSE',
        'BOOLEAN_FALSE',
        'INVALID_FLOAT',
        'NON_SCALAR_ENV',
        'NUMERIC_STRING',
        'NEGATIVE_FLOAT',
        'ENV_AND_PROCESS',
        'NON_EXISTENT_VAR',
        'INTEGER_POSITIVE',
        'INTEGER_NEGATIVE',
        'INTEGER_AS_FLOAT',
        'SCIENTIFIC_FLOAT',
        'NON_SCALAR_SERVER',
        'BOTH_SUPERGLOBALS',
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

    #[DataProvider('floatConversionDataProvider')]
    public function testToFloatWhenValueIsNumericThenReturnsExpectedFloat(
        string $value,
        string $variable,
        float $expected
    ): void {
        /** @Given the environment variable is set with a numeric string */
        putenv(sprintf('%s=%s', $variable, $value));

        /** @When converting the environment variable to float */
        $actual = EnvironmentVariable::from(name: $variable)->toFloat();

        /** @Then the returned float matches the expected value */
        self::assertSame($expected, $actual);
    }

    #[DataProvider('stringConversionDataProvider')]
    public function testToStringWhenValuePresentThenReturnsExpectedString(
        string|bool $value,
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

    public function testFromOrDefaultWhenVariableMissingThenReturnsDefault(): void
    {
        /** @Given the environment variable does not exist */
        $variable = 'NON_EXISTENT_MY_VAR';

        /** @When requesting the variable with a default value */
        $actual = EnvironmentVariable::fromOrDefault(name: $variable, defaultValueIfNotFound: '0');

        /** @Then the returned instance exposes the default value */
        self::assertSame(0, $actual->toInteger());
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

    public function testFromWhenNonScalarInServerSuperglobalThenThrowsMissing(): void
    {
        /** @Given a non-scalar entry in $_SERVER */
        $_SERVER['NON_SCALAR_SERVER'] = ['nested' => 'value'];

        /** @Then a missing environment variable exception is expected */
        $this->expectException(EnvironmentVariableMissing::class);

        /** @When reading the environment variable */
        EnvironmentVariable::from(name: 'NON_SCALAR_SERVER');
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

    public function testFromOrDefaultWhenVariableExistsThenReturnsExistingValue(): void
    {
        /** @Given the environment variable exists with an existing value */
        putenv(sprintf('%s=%s', 'MY_VAR', 'existing_value'));

        /** @When requesting the variable with a default value */
        $actual = EnvironmentVariable::fromOrDefault(name: 'MY_VAR', defaultValueIfNotFound: 'default_value');

        /** @Then the returned instance exposes the existing value */
        self::assertSame('existing_value', $actual->toString());
    }

    public function testFromWhenPresentInBothSuperglobalsThenEnvSuperglobalWins(): void
    {
        /** @Given a value available in $_ENV */
        $_ENV['BOTH_SUPERGLOBALS'] = 'from-env';

        /** @And a different value available in $_SERVER under the same name */
        $_SERVER['BOTH_SUPERGLOBALS'] = 'from-server';

        /** @When reading the environment variable */
        $actual = EnvironmentVariable::from(name: 'BOTH_SUPERGLOBALS')->toString();

        /** @Then the value from $_ENV takes precedence */
        self::assertSame('from-env', $actual);
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

    public function testFromWhenScalarPresentInServerSuperglobalThenValueIsCoerced(): void
    {
        /** @Given a non-string scalar available only in $_SERVER */
        $_SERVER['FROM_SERVER_SUPERGLOBAL'] = 7;

        /** @When reading the environment variable */
        $actual = EnvironmentVariable::from(name: 'FROM_SERVER_SUPERGLOBAL')->toString();

        /** @Then the value from $_SERVER is coerced to string */
        self::assertSame('7', $actual);
    }

    public function testFromWhenRequestScopedNameIsSetInProcessThenProcessValueWins(): void
    {
        /** @Given a request header mapped into $_SERVER under the HTTP_ namespace */
        $_SERVER['HTTP_PROXY'] = 'http://attacker.example.com';

        /** @And the same name present in the process environment */
        putenv(sprintf('%s=%s', 'HTTP_PROXY', 'http://proxy.internal'));

        /** @When reading the environment variable */
        $actual = EnvironmentVariable::from(name: 'HTTP_PROXY')->toString();

        /** @Then the process value is returned and the request header is ignored */
        self::assertSame('http://proxy.internal', $actual);
    }

    public function testFromWhenPresentInEnvSuperglobalAndProcessThenSuperglobalWins(): void
    {
        /** @Given a value available in $_ENV */
        $_ENV['ENV_AND_PROCESS'] = 'from-env';

        /** @And a different value present in the process environment */
        putenv(sprintf('%s=%s', 'ENV_AND_PROCESS', 'from-process'));

        /** @When reading the environment variable */
        $actual = EnvironmentVariable::from(name: 'ENV_AND_PROCESS')->toString();

        /** @Then the value from $_ENV takes precedence */
        self::assertSame('from-env', $actual);
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

    public function testToFloatWhenValueIsNotNumericThenThrowsEnvironmentValueNotFloat(): void
    {
        /** @Given the environment variable holds a non-numeric value */
        putenv(sprintf('%s=%s', 'INVALID_FLOAT', 'invalid-value'));

        /** @Then an invalid float conversion exception is expected */
        $this->expectException(EnvironmentValueNotFloat::class);
        $this->expectExceptionMessage(
            'The value for environment variable <INVALID_FLOAT> is invalid for conversion to <float>.'
        );

        /** @When converting the environment variable to float */
        EnvironmentVariable::from(name: 'INVALID_FLOAT')->toFloat();
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

    public function testFromOrDefaultWhenVariableMissingAndNoDefaultThenToStringIsEmpty(): void
    {
        /** @Given the environment variable does not exist */
        $variable = 'NON_EXISTENT_VAR';

        /** @When requesting the variable without a default value */
        $actual = EnvironmentVariable::fromOrDefault(name: $variable);

        /** @Then the returned instance exposes an empty string */
        self::assertSame('', $actual->toString());
    }

    public function testFromWhenRequestScopedNameOnlyInServerSuperglobalThenThrowsMissing(): void
    {
        /** @Given a request header mapped into $_SERVER under the HTTP_ namespace */
        $_SERVER['HTTP_PROXY'] = 'http://attacker.example.com';

        /** @Then a missing environment variable exception is expected */
        $this->expectException(EnvironmentVariableMissing::class);

        /** @When reading the environment variable */
        EnvironmentVariable::from(name: 'HTTP_PROXY');
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

    public static function floatConversionDataProvider(): array
    {
        return [
            'Float value'         => [
                'value'    => '1.5',
                'variable' => 'VALID_FLOAT',
                'expected' => 1.5
            ],
            'Integer value'       => [
                'value'    => '2',
                'variable' => 'INTEGER_AS_FLOAT',
                'expected' => 2.0
            ],
            'Negative float'      => [
                'value'    => '-0.5',
                'variable' => 'NEGATIVE_FLOAT',
                'expected' => -0.5
            ],
            'Scientific notation' => [
                'value'    => '1e3',
                'variable' => 'SCIENTIFIC_FLOAT',
                'expected' => 1000.0
            ]
        ];
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
}
