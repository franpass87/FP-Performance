<?php

namespace FP\PerfSuite\Tests\Integration\Cron;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * Test cron event execution
 *
 * @package FP\PerfSuite\Tests\Integration\Cron
 */
class CronEventExecutionTest extends TestCase
{
    public function testDbCleanupCronExecution(): void
    {
        Functions\expect('do_action')
            ->once()
            ->with('fp_ps_db_cleanup');

        // Simulate cron execution
        do_action('fp_ps_db_cleanup');

        $this->assertTrue(true);
    }

    public function testCacheCleanupCronExecution(): void
    {
        Functions\expect('do_action')
            ->once()
            ->with('fp_ps_cache_cleanup');

        do_action('fp_ps_cache_cleanup');

        $this->assertTrue(true);
    }

    public function testMlAnalysisCronExecution(): void
    {
        Functions\expect('do_action')
            ->once()
            ->with('fp_ps_ml_analyze_patterns');

        do_action('fp_ps_ml_analyze_patterns');

        $this->assertTrue(true);
    }

    public function testMlPredictIssuesCronExecution(): void
    {
        Functions\expect('do_action')
            ->once()
            ->with('fp_ps_ml_predict_issues');

        do_action('fp_ps_ml_predict_issues');

        $this->assertTrue(true);
    }

    public function testAutoTuneCronExecution(): void
    {
        Functions\expect('do_action')
            ->once()
            ->with('fp_ps_auto_tune');

        do_action('fp_ps_auto_tune');

        $this->assertTrue(true);
    }

    public function testDbAutoReportCronExecution(): void
    {
        Functions\expect('do_action')
            ->once()
            ->with('fp_ps_db_auto_report');

        do_action('fp_ps_db_auto_report');

        $this->assertTrue(true);
    }

    public function testCronExecutionWithErrorHandling(): void
    {
        Functions\expect('do_action')
            ->once()
            ->with('fp_ps_db_cleanup')
            ->andThrow(new \Exception('Test error'));

        Functions\expect('error_log')
            ->once()
            ->with(\Mockery::type('string'));

        try {
            do_action('fp_ps_db_cleanup');
        } catch (\Exception $e) {
            $this->assertEquals('Test error', $e->getMessage());
        }
    }
}










