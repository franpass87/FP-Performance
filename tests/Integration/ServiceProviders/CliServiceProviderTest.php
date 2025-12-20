<?php

namespace FP\PerfSuite\Tests\Integration\ServiceProviders;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Providers\CliServiceProvider;
use Brain\Monkey\Functions;

/**
 * Test CliServiceProvider
 *
 * @package FP\PerfSuite\Tests\Integration\ServiceProviders
 */
class CliServiceProviderTest extends TestCase
{
    private Container $container;
    private CliServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->provider = new CliServiceProvider();
    }

    public function testShouldLoad(): void
    {
        Functions\expect('defined')
            ->once()
            ->with('WP_CLI')
            ->andReturn(true);

        Functions\expect('constant')
            ->once()
            ->with('WP_CLI')
            ->andReturn(true);

        $this->assertTrue($this->provider->shouldLoad());
    }

    public function testShouldNotLoadWithoutWpCli(): void
    {
        Functions\expect('defined')
            ->once()
            ->with('WP_CLI')
            ->andReturn(false);

        $this->assertFalse($this->provider->shouldLoad());
    }

    public function testRegister(): void
    {
        Functions\expect('defined')
            ->zeroOrMoreTimes()
            ->with('WP_CLI')
            ->andReturn(true);

        Functions\expect('constant')
            ->zeroOrMoreTimes()
            ->with('WP_CLI')
            ->andReturn(true);

        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        // Register core services first
        $coreProvider = new \FP\PerfSuite\Providers\CoreServiceProvider();
        $coreProvider->register($this->container);

        Functions\expect('add_action')
            ->atLeast()
            ->once()
            ->with('wp_cli_init', \Mockery::type('callable'));

        $this->provider->register($this->container);

        $this->assertTrue(true);
    }
}










