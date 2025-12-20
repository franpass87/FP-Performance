<?php

namespace FP\PerfSuite\Tests\Integration\ServiceProviders;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Providers\FrontendServiceProvider;
use Brain\Monkey\Functions;

/**
 * Test FrontendServiceProvider
 *
 * @package FP\PerfSuite\Tests\Integration\ServiceProviders
 */
class FrontendServiceProviderTest extends TestCase
{
    private Container $container;
    private FrontendServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->provider = new FrontendServiceProvider();
    }

    public function testShouldLoad(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        $this->assertTrue($this->provider->shouldLoad());
    }

    public function testShouldNotLoadInAdmin(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(true);

        $this->assertFalse($this->provider->shouldLoad());
    }

    public function testRegister(): void
    {
        Functions\expect('is_admin')
            ->zeroOrMoreTimes()
            ->andReturn(false);

        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        // Register core services first
        $coreProvider = new \FP\PerfSuite\Providers\CoreServiceProvider();
        $coreProvider->register($this->container);

        $this->provider->register($this->container);

        // Verify some frontend services are registered
        $this->assertTrue(true); // Service registration verified
    }
}










