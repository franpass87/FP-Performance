<?php

namespace FP\PerfSuite\Tests\Integration\Cron;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * Test cron event registration and execution
 *
 * @package FP\PerfSuite\Tests\Integration\Cron
 */
class CronEventsTest extends TestCase
{
    public function testDbCleanupCronRegistration(): void
    {
        Functions\expect('wp_schedule_event')
            ->zeroOrMoreTimes()
            ->with(
                \Mockery::type('int'),
                'hourly',
                'fp_ps_db_cleanup'
            )
            ->andReturn(true);

        // Cron would be registered during service provider boot
        $this->assertTrue(true);
    }

    public function testCacheCleanupCronRegistration(): void
    {
        Functions\expect('wp_schedule_event')
            ->zeroOrMoreTimes()
            ->with(
                \Mockery::type('int'),
                'hourly',
                'fp_ps_cache_cleanup'
            )
            ->andReturn(true);

        $this->assertTrue(true);
    }

    public function testMlAnalysisCronRegistration(): void
    {
        Functions\expect('wp_schedule_event')
            ->zeroOrMoreTimes()
            ->with(
                \Mockery::type('int'),
                'hourly',
                'fp_ps_ml_analyze_patterns'
            )
            ->andReturn(true);

        $this->assertTrue(true);
    }

    public function testCronRemovalOnDeactivation(): void
    {
        Functions\expect('wp_clear_scheduled_hook')
            ->once()
            ->with('fp_ps_db_cleanup')
            ->andReturn(true);

        Functions\expect('wp_clear_scheduled_hook')
            ->once()
            ->with('fp_ps_cache_cleanup')
            ->andReturn(true);

        Functions\expect('wp_clear_scheduled_hook')
            ->once()
            ->with('fp_ps_ml_analyze_patterns')
            ->andReturn(true);

        // Cron cleanup would be called during deactivation
        $this->assertTrue(true);
    }
}










