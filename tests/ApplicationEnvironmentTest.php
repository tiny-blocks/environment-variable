<?php

declare(strict_types=1);

namespace Test\TinyBlocks\EnvironmentVariable;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\EnvironmentVariable\ApplicationEnvironment;
use TinyBlocks\EnvironmentVariable\Exceptions\ApplicationEnvironmentUnrecognized;

final class ApplicationEnvironmentTest extends TestCase
{
    private const string UNRECOGNIZED_MESSAGE = 'Application environment is not recognized. Accepted aliases are '
        . '<local, localhost, test, testing, development, dev, develop, staging, stage, homolog, homologation, '
        . 'production, prod>.';

    #[DataProvider('predicatesDataProvider')]
    public function testPredicatesWhenEvaluatedThenOnlyTheOwnCaseHolds(
        ApplicationEnvironment $environment,
        array $expected
    ): void {
        /** @Given an application environment */

        /** @When evaluating every environment predicate */
        $actual = [
            'isTest'        => $environment->isTest(),
            'isLocal'       => $environment->isLocal(),
            'isStaging'     => $environment->isStaging(),
            'isProduction'  => $environment->isProduction(),
            'isDevelopment' => $environment->isDevelopment()
        ];

        /** @Then only the predicate of its own case holds */
        self::assertSame($expected, $actual);
    }

    #[DataProvider('aliasesDataProvider')]
    public function testAliasesWhenRequestedThenReturnsEveryRecognizedAlias(
        ApplicationEnvironment $environment,
        array $expected
    ): void {
        /** @Given an application environment */

        /** @When requesting the aliases it recognizes */
        $actual = $environment->aliases();

        /** @Then every recognized alias is returned in declaration order */
        self::assertSame($expected, $actual);
    }

    #[DataProvider('recognizedAliasesDataProvider')]
    public function testFromAliasWhenAliasIsRecognizedThenReturnsMatchingEnvironment(
        string $alias,
        ApplicationEnvironment $expected
    ): void {
        /** @Given a recognized alias of an application environment, in any casing and padding */

        /** @When creating the application environment from the alias */
        $actual = ApplicationEnvironment::fromAlias(alias: $alias);

        /** @Then the matching application environment is returned */
        self::assertSame($expected, $actual);
    }

    #[DataProvider('unrecognizedAliasesDataProvider')]
    public function testFromAliasWhenAliasIsUnrecognizedThenThrowsApplicationEnvironmentUnrecognized(
        string $alias
    ): void {
        /** @Given an alias no application environment recognizes */

        /** @Then an unrecognized application environment exception is expected */
        $this->expectException(ApplicationEnvironmentUnrecognized::class);

        /** @When creating the application environment from the alias */
        ApplicationEnvironment::fromAlias(alias: $alias);
    }

    public function testFromAliasWhenAliasIsUnrecognizedThenMessageListsAcceptedAliasesWithoutTheAlias(): void
    {
        /** @Given an alias no application environment recognizes, carrying a value that must not leak */
        $alias = 's3cr3t-value';

        /** @Then an unrecognized application environment exception listing only the accepted aliases is expected */
        $this->expectException(ApplicationEnvironmentUnrecognized::class);
        $this->expectExceptionMessageMatches(sprintf('/^%s$/', preg_quote(self::UNRECOGNIZED_MESSAGE, '/')));

        /** @When creating the application environment from the alias */
        ApplicationEnvironment::fromAlias(alias: $alias);
    }

    public static function aliasesDataProvider(): array
    {
        return [
            'Local'       => [
                'environment' => ApplicationEnvironment::LOCAL,
                'expected'    => ['local', 'localhost']
            ],
            'Test'        => [
                'environment' => ApplicationEnvironment::TEST,
                'expected'    => ['test', 'testing']
            ],
            'Development' => [
                'environment' => ApplicationEnvironment::DEVELOPMENT,
                'expected'    => ['development', 'dev', 'develop']
            ],
            'Staging'     => [
                'environment' => ApplicationEnvironment::STAGING,
                'expected'    => ['staging', 'stage', 'homolog', 'homologation']
            ],
            'Production'  => [
                'environment' => ApplicationEnvironment::PRODUCTION,
                'expected'    => ['production', 'prod']
            ]
        ];
    }

    public static function predicatesDataProvider(): array
    {
        return [
            'Local'       => [
                'environment' => ApplicationEnvironment::LOCAL,
                'expected'    => [
                    'isTest'        => false,
                    'isLocal'       => true,
                    'isStaging'     => false,
                    'isProduction'  => false,
                    'isDevelopment' => false
                ]
            ],
            'Test'        => [
                'environment' => ApplicationEnvironment::TEST,
                'expected'    => [
                    'isTest'        => true,
                    'isLocal'       => false,
                    'isStaging'     => false,
                    'isProduction'  => false,
                    'isDevelopment' => false
                ]
            ],
            'Development' => [
                'environment' => ApplicationEnvironment::DEVELOPMENT,
                'expected'    => [
                    'isTest'        => false,
                    'isLocal'       => false,
                    'isStaging'     => false,
                    'isProduction'  => false,
                    'isDevelopment' => true
                ]
            ],
            'Staging'     => [
                'environment' => ApplicationEnvironment::STAGING,
                'expected'    => [
                    'isTest'        => false,
                    'isLocal'       => false,
                    'isStaging'     => true,
                    'isProduction'  => false,
                    'isDevelopment' => false
                ]
            ],
            'Production'  => [
                'environment' => ApplicationEnvironment::PRODUCTION,
                'expected'    => [
                    'isTest'        => false,
                    'isLocal'       => false,
                    'isStaging'     => false,
                    'isProduction'  => true,
                    'isDevelopment' => false
                ]
            ]
        ];
    }

    public static function recognizedAliasesDataProvider(): array
    {
        return [
            'Local'                       => [
                'alias'    => 'local',
                'expected' => ApplicationEnvironment::LOCAL
            ],
            'Localhost'                   => [
                'alias'    => 'localhost',
                'expected' => ApplicationEnvironment::LOCAL
            ],
            'Test'                        => [
                'alias'    => 'test',
                'expected' => ApplicationEnvironment::TEST
            ],
            'Testing'                     => [
                'alias'    => 'testing',
                'expected' => ApplicationEnvironment::TEST
            ],
            'Development'                 => [
                'alias'    => 'development',
                'expected' => ApplicationEnvironment::DEVELOPMENT
            ],
            'Dev'                         => [
                'alias'    => 'dev',
                'expected' => ApplicationEnvironment::DEVELOPMENT
            ],
            'Develop'                     => [
                'alias'    => 'develop',
                'expected' => ApplicationEnvironment::DEVELOPMENT
            ],
            'Staging'                     => [
                'alias'    => 'staging',
                'expected' => ApplicationEnvironment::STAGING
            ],
            'Stage'                       => [
                'alias'    => 'stage',
                'expected' => ApplicationEnvironment::STAGING
            ],
            'Homolog'                     => [
                'alias'    => 'homolog',
                'expected' => ApplicationEnvironment::STAGING
            ],
            'Homologation'                => [
                'alias'    => 'homologation',
                'expected' => ApplicationEnvironment::STAGING
            ],
            'Production'                  => [
                'alias'    => 'production',
                'expected' => ApplicationEnvironment::PRODUCTION
            ],
            'Prod'                        => [
                'alias'    => 'prod',
                'expected' => ApplicationEnvironment::PRODUCTION
            ],
            'Uppercase'                   => [
                'alias'    => 'LOCAL',
                'expected' => ApplicationEnvironment::LOCAL
            ],
            'Mixed case'                  => [
                'alias'    => 'HomoLog',
                'expected' => ApplicationEnvironment::STAGING
            ],
            'Leading and trailing spaces' => [
                'alias'    => '  dev  ',
                'expected' => ApplicationEnvironment::DEVELOPMENT
            ],
            'Tabs and line breaks'        => [
                'alias'    => "\tPROD\n",
                'expected' => ApplicationEnvironment::PRODUCTION
            ]
        ];
    }

    public static function unrecognizedAliasesDataProvider(): array
    {
        return [
            'Empty'             => ['alias' => ''],
            'Blank'             => ['alias' => '   '],
            'Unknown name'      => ['alias' => 'qa'],
            'Partial alias'     => ['alias' => 'prd'],
            'Inner whitespace'  => ['alias' => 'local host'],
            'Alias with suffix' => ['alias' => 'production-eu']
        ];
    }
}
