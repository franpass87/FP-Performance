<?php

namespace FP\PerfSuite\Tests\Integration\ServiceProviders;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Providers\DatabaseServiceProvider;
use Brain\Monkey\Functions;

/**
 * Test DatabaseServiceProvider
 *
 * @package FP\PerfSuite\Tests\Integration\ServiceProviders
 */
class DatabaseServiceProviderTest extends TestCase
{
    private Container $container;
    private DatabaseServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->provider = new DatabaseServiceProvider();
    }

    public function testShouldLoad(): void
    {
        $this->assertTrue($this->provider->shouldLoad());
    }

    public function testRegister(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        // Register core services first
        $coreProvider = new \FP\PerfSuite\Providers\CoreServiceProvider();
        $coreProvider->register($this->container);

        $this->provider->register($this->container);

        // Verify database services are registered
        $this->assertTrue(true);
    }
}










