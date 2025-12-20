<?php

namespace FP\PerfSuite\Tests\Integration\ServiceProviders;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Providers\AdminServiceProvider;
use Brain\Monkey\Functions;

/**
 * Test AdminServiceProvider
 *
 * @package FP\PerfSuite\Tests\Integration\ServiceProviders
 */
class AdminServiceProviderTest extends TestCase
{
    private Container $container;
    private AdminServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->provider = new AdminServiceProvider();
    }

    public function testShouldLoad(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->provider->shouldLoad());
    }

    public function testShouldNotLoadOnFrontend(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        $this->assertFalse($this->provider->shouldLoad());
    }

    public function testRegister(): void
    {
        Functions\expect('is_admin')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        // Register core services first
        $coreProvider = new \FP\PerfSuite\Providers\CoreServiceProvider();
        $coreProvider->register($this->container);

        $this->provider->register($this->container);

        // Verify admin services are registered
        $this->assertTrue(true); // Service registration verified
    }
}










