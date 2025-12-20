<?php

namespace FP\PerfSuite\Tests\Performance;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Kernel\PluginKernel;
use Brain\Monkey\Functions;

/**
 * Performance profiling tests
 *
 * @package FP\PerfSuite\Tests\Performance
 */
class PerformanceTest extends TestCase
{
    public function testMemoryFootprint(): void
    {
        $memoryBefore = memory_get_usage();
        
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        Functions\expect('do_action')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        PluginKernel::boot();
        
        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        // Should use less than 10MB
        $this->assertLessThan(10 * 1024 * 1024, $memoryUsed, 'Memory usage should be less than 10MB');
    }

    public function testQueryCount(): void
    {
        global $wpdb;
        
        if (!isset($wpdb)) {
            $wpdb = $this->createMock(\stdClass::class);
            $wpdb->num_queries = 0;
        }
        
        $queriesBefore = $wpdb->num_queries ?? 0;
        
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        Functions\expect('do_action')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        PluginKernel::boot();
        
        $queriesAfter = $wpdb->num_queries ?? 0;
        $queriesUsed = $queriesAfter - $queriesBefore;
        
        // Should use less than 50 queries for boot
        $this->assertLessThan(50, $queriesUsed, 'Query count should be reasonable');
    }

    public function testLoadTime(): void
    {
        $startTime = microtime(true);
        
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        Functions\expect('do_action')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        PluginKernel::boot();
        
        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        // Should load in less than 500ms
        $this->assertLessThan(500, $loadTime, 'Load time should be less than 500ms');
    }
}










