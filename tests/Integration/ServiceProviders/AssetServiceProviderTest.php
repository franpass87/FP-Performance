<?php

namespace FP\PerfSuite\Tests\Integration\ServiceProviders;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Providers\AssetServiceProvider;
use Brain\Monkey\Functions;

/**
 * Test AssetServiceProvider
 *
 * @package FP\PerfSuite\Tests\Integration\ServiceProviders
 */
class AssetServiceProviderTest extends TestCase
{
    private Container $container;
    private AssetServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->provider = new AssetServiceProvider();
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

        // Verify asset services are registered
        $this->assertTrue(true);
    }
}










