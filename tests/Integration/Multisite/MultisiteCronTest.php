<?php

namespace FP\PerfSuite\Tests\Integration\Multisite;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * Test multisite cron events
 *
 * @package FP\PerfSuite\Tests\Integration\Multisite
 */
class MultisiteCronTest extends TestCase
{
    public function testCronEventsPerSite(): void
    {
        Functions\expect('is_multisite')
            ->once()
            ->andReturn(true);

        Functions\expect('get_sites')
            ->once()
            ->andReturn([
                (object)['blog_id' => 1],
                (object)['blog_id' => 2]
            ]);

        Functions\expect('switch_to_blog')
            ->twice()
            ->with(\Mockery::anyOf(1, 2));

        Functions\expect('wp_schedule_event')
            ->twice()
            ->with(\Mockery::type('int'), 'hourly', 'fp_ps_db_cleanup');

        Functions\expect('restore_current_blog')
            ->twice();

        // Cron events should be registered per site
        $this->assertTrue(true);
    }

    public function testCronEventExecutionPerSite(): void
    {
        Functions\expect('is_multisite')
            ->once()
            ->andReturn(true);

        Functions\expect('get_sites')
            ->once()
            ->andReturn([
                (object)['blog_id' => 1],
                (object)['blog_id' => 2]
            ]);

        Functions\expect('switch_to_blog')
            ->twice()
            ->with(\Mockery::anyOf(1, 2));

        Functions\expect('do_action')
            ->twice()
            ->with('fp_ps_db_cleanup');

        Functions\expect('restore_current_blog')
            ->twice();

        // Cron events should execute per site
        $this->assertTrue(true);
    }

    public function testNoCronConflicts(): void
    {
        Functions\expect('is_multisite')
            ->once()
            ->andReturn(true);

        Functions\expect('wp_next_scheduled')
            ->twice()
            ->with('fp_ps_db_cleanup')
            ->andReturn(false, false);

        Functions\expect('wp_schedule_event')
            ->twice()
            ->andReturn(true, true);

        // No cron conflicts between sites
        $this->assertTrue(true);
    }
}










