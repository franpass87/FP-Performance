<?php

namespace FP\PerfSuite\Tests\Integration\ServiceProviders;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Kernel\PluginKernel;
use FP\PerfSuite\Kernel\Container;
use Brain\Monkey\Functions;

/**
 * Test PluginKernel boot sequence
 *
 * @package FP\PerfSuite\Tests\Integration\ServiceProviders
 */
class PluginKernelTest extends TestCase
{
    protected function tearDown(): void
    {
        PluginKernel::reset();
        parent::tearDown();
    }

    public function testBoot(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        Functions\expect('do_action')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        PluginKernel::boot();

        $this->assertTrue(PluginKernel::isBooted());
    }

    public function testContainer(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        Functions\expect('do_action')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        PluginKernel::boot();
        $container = PluginKernel::container();

        $this->assertInstanceOf(Container::class, $container);
    }

    public function testReset(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        Functions\expect('do_action')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        PluginKernel::boot();
        $this->assertTrue(PluginKernel::isBooted());

        PluginKernel::reset();
        $this->assertFalse(PluginKernel::isBooted());
    }

    public function testDoubleBoot(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        Functions\expect('do_action')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        PluginKernel::boot();
        $container1 = PluginKernel::container();

        PluginKernel::boot();
        $container2 = PluginKernel::container();

        $this->assertSame($container1, $container2);
    }
}










