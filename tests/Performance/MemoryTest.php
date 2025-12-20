<?php

namespace FP\PerfSuite\Tests\Performance;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Kernel\PluginKernel;
use Brain\Monkey\Functions;

/**
 * Memory usage tests
 *
 * @package FP\PerfSuite\Tests\Performance
 */
class MemoryTest extends TestCase
{
    protected function tearDown(): void
    {
        PluginKernel::reset();
        parent::tearDown();
    }

    public function testMemoryUsageOnBoot(): void
    {
        $memoryBefore = memory_get_usage(true);
        
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        Functions\expect('do_action')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        PluginKernel::boot();
        
        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        // Should use less than 10MB
        $this->assertLessThan(10 * 1024 * 1024, $memoryUsed, 'Boot should use less than 10MB');
    }

    public function testMemoryLeakPrevention(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        Functions\expect('do_action')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        $memoryBefore = memory_get_usage(true);
        
        // Boot multiple times
        for ($i = 0; $i < 5; $i++) {
            PluginKernel::reset();
            PluginKernel::boot();
        }
        
        $memoryAfter = memory_get_usage(true);
        $memoryIncrease = $memoryAfter - $memoryBefore;
        
        // Memory increase should be minimal (less than 1MB)
        $this->assertLessThan(1024 * 1024, $memoryIncrease, 'No memory leaks on multiple boots');
    }
}










