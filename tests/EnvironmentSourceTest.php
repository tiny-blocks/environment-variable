<?php

declare(strict_types=1);

namespace Test\TinyBlocks\EnvironmentVariable;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyBlocks\EnvironmentVariable\Internal\EnvironmentSource;

final class EnvironmentSourceTest extends TestCase
{
    public function testConstructorWhenInvokedThroughReflectionThenRemainsPrivate(): void
    {
        /** @Given the constructor of the source, which no public path can reach */
        $constructor = new ReflectionMethod(EnvironmentSource::class, '__construct');

        /** @When it is invoked through reflection */
        $constructor->invoke(new ReflectionClass(EnvironmentSource::class)->newInstanceWithoutConstructor());

        /** @Then it stays private, so the source exposes only static lookups */
        self::assertTrue($constructor->isPrivate());
    }
}
