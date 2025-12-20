<?php

namespace FP\PerfSuite\Tests\Unit\Services;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Services\DB\DatabaseOptimizer;
use FP\PerfSuite\Core\Logging\LoggerInterface;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;

/**
 * Test DatabaseOptimizer
 *
 * @package FP\PerfSuite\Tests\Unit\Services
 */
class DatabaseOptimizerTest extends TestCase
{
    private $logger;
    private $optimizer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->logger = \Mockery::mock(LoggerInterface::class);
        $this->optimizer = new DatabaseOptimizer(true, true, $this->logger);
    }

    public function testInitWithAutoOptimize(): void
    {
        Actions\expectAdded('wp_scheduled_delete')
            ->once()
            ->with(\Mockery::type('array'));

        Actions\expectAdded('fp_optimize_database')
            ->once()
            ->with(\Mockery::type('array'));

        Actions\expectAdded('init')
            ->once()
            ->with(\Mockery::type('array'));

        $this->optimizer->init();
    }

    public function testInitWithoutAutoOptimize(): void
    {
        $optimizer = new DatabaseOptimizer(false, true, $this->logger);

        Actions\expectAdded('wp_scheduled_delete')
            ->never();

        Actions\expectAdded('init')
            ->once();

        $optimizer->init();
    }

    public function testInitWithoutQueryCache(): void
    {
        $optimizer = new DatabaseOptimizer(true, false, $this->logger);

        Actions\expectAdded('wp_scheduled_delete')
            ->once();

        Actions\expectAdded('init')
            ->never();

        $optimizer->init();
    }

    public function testOptimizeDatabaseRateLimit(): void
    {
        Functions\expect('get_transient')
            ->once()
            ->with('fp_ps_db_optimize_last_run')
            ->andReturn(time() - 100); // Recent run

        Functions\expect('HOUR_IN_SECONDS')
            ->andReturn(3600);

        // Mock HostingDetector
        Functions\expect('HostingDetector::isSharedHosting')
            ->once()
            ->andReturn(true);

        $this->logger->shouldReceive('info')
            ->once()
            ->with(\Mockery::type('string'));

        $result = $this->optimizer->optimizeDatabase();

        $this->assertEquals(0, $result);
    }

    public function testOptimizeDatabaseExecutes(): void
    {
        global $wpdb;
        
        $wpdb = \Mockery::mock('wpdb');
        $wpdb->prefix = 'wp_';
        
        Functions\expect('get_transient')
            ->once()
            ->with('fp_ps_db_optimize_last_run')
            ->andReturn(false);

        Functions\expect('HostingDetector::isSharedHosting')
            ->once()
            ->andReturn(false);

        Functions\expect('set_transient')
            ->once()
            ->andReturn(true);

        Functions\expect('HOUR_IN_SECONDS')
            ->andReturn(3600);

        Functions\expect('MINUTE_IN_SECONDS')
            ->andReturn(60);

        $wpdb->shouldReceive('get_results')
            ->with('SHOW TABLES', ARRAY_N)
            ->andReturn([
                ['wp_posts'],
                ['wp_options']
            ]);

        $wpdb->shouldReceive('get_var')
            ->zeroOrMoreTimes()
            ->andReturn(0);

        $wpdb->shouldReceive('query')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        $this->logger->shouldReceive('info')
            ->atLeast()
            ->once();

        $result = $this->optimizer->optimizeDatabase();

        $this->assertIsInt($result);
    }
}










