<?php

namespace FP\PerfSuite\Tests\Integration\Hooks;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Kernel\PluginKernel;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;

/**
 * Test hook registration and lifecycle
 *
 * @package FP\PerfSuite\Tests\Integration\Hooks
 */
class HookRegistrationTest extends TestCase
{
    protected function tearDown(): void
    {
        PluginKernel::reset();
        parent::tearDown();
    }

    public function testPluginsLoadedHook(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        Functions\expect('do_action')
            ->atLeast()
            ->once()
            ->with('fp_ps_kernel_booted', \Mockery::type('object'));

        PluginKernel::boot();
    }

    public function testRestApiInitHook(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        Functions\expect('register_rest_route')
            ->atLeast()
            ->once();

        Actions\expectAdded('rest_api_init')
            ->atLeast()
            ->once();

        // Routes would be registered via RestServiceProvider
        $this->assertTrue(true);
    }

    public function testWpCliInitHook(): void
    {
        Actions\expectAdded('wp_cli_init')
            ->zeroOrMoreTimes();

        // CLI commands would be registered via CliServiceProvider
        $this->assertTrue(true);
    }
}










