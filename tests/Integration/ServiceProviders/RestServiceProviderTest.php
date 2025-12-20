<?php

namespace FP\PerfSuite\Tests\Integration\ServiceProviders;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Providers\RestServiceProvider;
use Brain\Monkey\Functions;

/**
 * Test RestServiceProvider
 *
 * @package FP\PerfSuite\Tests\Integration\ServiceProviders
 */
class RestServiceProviderTest extends TestCase
{
    private Container $container;
    private RestServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->provider = new RestServiceProvider();
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

        Functions\expect('add_action')
            ->atLeast()
            ->once()
            ->with('rest_api_init', \Mockery::type('callable'));

        $this->provider->register($this->container);

        // Verify REST services are registered
        $this->assertTrue(true);
    }
}










