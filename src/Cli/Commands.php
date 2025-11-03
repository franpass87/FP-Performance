<?php

namespace FP\PerfSuite\Cli;

use FP\PerfSuite\Plugin;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Cache\ObjectCacheManager;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor;
use FP\PerfSuite\Services\DB\DatabaseOptimizer;
use FP\PerfSuite\Services\DB\PluginSpecificOptimizer;
use FP\PerfSuite\Services\DB\DatabaseReportService;
use FP\PerfSuite\Services\Score\Scorer;
use FP\PerfSuite\Utils\Logger;

/**
 * WP-CLI commands for FP Performance Suite
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Commands
{
    /**
     * Clear all caches
     *
     * ## EXAMPLES
     *
     *     # Clear page cache
     *     wp fp-performance cache clear
     *
     * @when after_wp_load
     */
    public function cache($args, $assoc_args)
    {
        $subcommand = $args[0] ?? 'clear';

        if ($subcommand === 'clear') {
            $this->cacheClear();
        } elseif ($subcommand === 'status') {
            $this->cacheStatus();
        } else {
            \WP_CLI::error("Unknown cache subcommand: {$subcommand}");
        }
    }

    /**
     * Clear page cache
     */
    private function cacheClear(): void
    {
        try {
            $container = Plugin::container();
            $pageCache = $container->get(PageCache::class);

            \WP_CLI::log('Clearing page cache...');
            $pageCache->clear();

            \WP_CLI::success('Page cache cleared successfully!');
            Logger::info('Page cache cleared via WP-CLI');
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Show cache status
     */
    private function cacheStatus(): void
    {
        try {
            $container = Plugin::container();
            $pageCache = $container->get(PageCache::class);
            $status = $pageCache->status();

            \WP_CLI::log('Cache Status:');
            \WP_CLI::log('  Enabled: ' . ($status['enabled'] ? 'Yes' : 'No'));
            \WP_CLI::log('  Cached files: ' . $status['files']);
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to get cache status: ' . $e->getMessage());
        }
    }

    /**
     * Database cleanup operations
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Run in dry-run mode (no actual deletions)
     *
     * [--scope=<scope>]
     * : Comma-separated list of cleanup tasks
     * ---
     * default: revisions,auto_drafts,trash_posts,spam_comments,expired_transients
     * ---
     *
     * ## EXAMPLES
     *
     *     # Dry run cleanup
     *     wp fp-performance db cleanup --dry-run
     *
     *     # Actually cleanup revisions only
     *     wp fp-performance db cleanup --scope=revisions
     *
     *     # Optimize all database tables
     *     wp fp-performance db optimize
     *
     *     # Analyze database and get recommendations
     *     wp fp-performance db analyze
     *
     * @when after_wp_load
     */
    public function db($args, $assoc_args)
    {
        $subcommand = $args[0] ?? 'cleanup';

        if ($subcommand === 'cleanup') {
            $this->dbCleanup($assoc_args);
        } elseif ($subcommand === 'status') {
            $this->dbStatus();
        } elseif ($subcommand === 'optimize') {
            $this->dbOptimize($assoc_args);
        } elseif ($subcommand === 'analyze') {
            $this->dbAnalyze();
        } elseif ($subcommand === 'health') {
            $this->dbHealth();
        } elseif ($subcommand === 'fragmentation') {
            $this->dbFragmentation();
        } elseif ($subcommand === 'plugin-cleanup') {
            $this->dbPluginCleanup();
        } elseif ($subcommand === 'report') {
            $this->dbReport($assoc_args);
        } elseif ($subcommand === 'convert-engine') {
            $this->dbConvertEngine($assoc_args);
        } else {
            \WP_CLI::error("Unknown db subcommand: {$subcommand}");
        }
    }

    /**
     * Run database cleanup
     */
    private function dbCleanup(array $assoc_args): void
    {
        try {
            $container = Plugin::container();
            $cleaner = $container->get(Cleaner::class);

            $dryRun = isset($assoc_args['dry-run']);
            $scope = isset($assoc_args['scope'])
                ? explode(',', $assoc_args['scope'])
                : ['revisions', 'auto_drafts', 'trash_posts', 'spam_comments', 'expired_transients'];

            $mode = $dryRun ? 'DRY RUN' : 'LIVE';
            \WP_CLI::log("Running database cleanup ({$mode})...");
            \WP_CLI::log('Scope: ' . implode(', ', $scope));

            $results = $cleaner->cleanup($scope, $dryRun);

            \WP_CLI::log("\nResults:");
            foreach ($results as $task => $result) {
                if (is_array($result) && isset($result['found'])) {
                    $found = $result['found'];
                    $deleted = $result['deleted'] ?? 0;
                    \WP_CLI::log("  {$task}: Found {$found}, Deleted {$deleted}");
                }
            }

            $total = array_sum(array_column($results, 'deleted'));
            \WP_CLI::success("Cleanup complete! Total items removed: {$total}");
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to run cleanup: ' . $e->getMessage());
        }
    }

    /**
     * Show database status
     */
    private function dbStatus(): void
    {
        try {
            $container = Plugin::container();
            $cleaner = $container->get(Cleaner::class);
            $optimizer = $container->get(DatabaseOptimizer::class);
            
            $status = $cleaner->status();
            $dbSize = $optimizer->getDatabaseSize();

            \WP_CLI::log('Database Status:');
            \WP_CLI::log('  Size: ' . $dbSize['total_mb'] . ' MB');
            \WP_CLI::log('  Overhead: ' . $status['overhead_mb'] . ' MB');
            \WP_CLI::log('  Schedule: ' . $status['schedule']);

            if ($status['last_run'] > 0) {
                \WP_CLI::log('  Last cleanup: ' . date('Y-m-d H:i:s', $status['last_run']));
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to get database status: ' . $e->getMessage());
        }
    }
    
    /**
     * Optimize database tables
     */
    private function dbOptimize(array $assoc_args): void
    {
        try {
            $container = Plugin::container();
            $optimizer = $container->get(DatabaseOptimizer::class);

            \WP_CLI::log('Optimizing all database tables...');
            
            $results = $optimizer->optimizeAllTables();
            
            if ($results['success']) {
                \WP_CLI::success("Optimized {$results['total']} tables successfully!");
            } else {
                \WP_CLI::warning("Optimized with errors. Check logs for details.");
            }
            
            if (!empty($results['errors'])) {
                \WP_CLI::log("\nErrors:");
                foreach ($results['errors'] as $table => $error) {
                    \WP_CLI::log("  {$table}: {$error}");
                }
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to optimize database: ' . $e->getMessage());
        }
    }
    
    /**
     * Analyze database and get recommendations
     */
    private function dbAnalyze(): void
    {
        try {
            $container = Plugin::container();
            $optimizer = $container->get(DatabaseOptimizer::class);

            \WP_CLI::log('Analyzing database...');
            
            $analysis = $optimizer->analyze();
            
            \WP_CLI::log("\n" . \WP_CLI::colorize('%G=== Database Analysis ===%n'));
            \WP_CLI::log('Size: ' . $analysis['database_size']['total_mb'] . ' MB');
            \WP_CLI::log('Tables: ' . $analysis['table_analysis']['total_tables']);
            \WP_CLI::log('Overhead: ' . $analysis['table_analysis']['total_overhead_mb'] . ' MB');
            
            if (!empty($analysis['recommendations'])) {
                \WP_CLI::log("\n" . \WP_CLI::colorize('%Y=== Recommendations ===%n'));
                foreach ($analysis['recommendations'] as $rec) {
                    // BUGFIX PHP 7.4 COMPATIBILITY: match() è PHP 8.0+, usa if-else
                    if ($rec['type'] === 'critical') {
                        $color = '%R';
                    } elseif ($rec['type'] === 'warning') {
                        $color = '%Y';
                    } elseif ($rec['type'] === 'info') {
                        $color = '%C';
                    } else {
                        $color = '%G';
                    }
                    \WP_CLI::log(\WP_CLI::colorize("{$color}• {$rec['title']}%n"));
                    \WP_CLI::log("  {$rec['message']}");
                }
            } else {
                \WP_CLI::success('Database is well optimized!');
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to analyze database: ' . $e->getMessage());
        }
    }
    
    /**
     * Advanced database health check
     */
    private function dbHealth(): void
    {
        try {
            $reportService = new DatabaseReportService();
            
            \WP_CLI::log('Running database health check...');
            
            $health = $reportService->getHealthScore();
            
            \WP_CLI::log("\n" . \WP_CLI::colorize('%G=== Database Health Score ===%n'));
            \WP_CLI::log('Score: ' . $health['score'] . '%');
            \WP_CLI::log('Grade: ' . $health['grade']);
            \WP_CLI::log('Status: ' . ucfirst($health['status']));
            
            if (!empty($health['issues'])) {
                \WP_CLI::log("\n" . \WP_CLI::colorize('%Y=== Issues Found ===%n'));
                foreach ($health['issues'] as $issue) {
                    // BUGFIX PHP 7.4 COMPATIBILITY: match() è PHP 8.0+, usa if-else
                    if ($issue['severity'] === 'high') {
                        $color = '%R';
                    } elseif ($issue['severity'] === 'medium') {
                        $color = '%Y';
                    } elseif ($issue['severity'] === 'low') {
                        $color = '%C';
                    } else {
                        $color = '%G';
                    }
                    \WP_CLI::log(\WP_CLI::colorize("{$color}• {$issue['issue']} (penalty: {$issue['penalty']})%n"));
                }
                
                \WP_CLI::log("\n" . \WP_CLI::colorize('%C=== Recommendations ===%n'));
                foreach ($health['recommendations'] as $rec) {
                    \WP_CLI::log("  {$rec}");
                }
            } else {
                \WP_CLI::success('Database is in excellent health!');
            }
            
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to check database health: ' . $e->getMessage());
        }
    }
    
    /**
     * Analyze fragmentation
     */
    private function dbFragmentation(): void
    {
        try {
            $container = Plugin::container();
            $optimizer = $container->get(DatabaseOptimizer::class);
            
            \WP_CLI::log('Analyzing database fragmentation...');
            
            $fragmentation = $optimizer->analyzeFragmentation();
            
            \WP_CLI::log("\n" . \WP_CLI::colorize('%G=== Fragmentation Analysis ===%n'));
            \WP_CLI::log('Fragmented tables: ' . $fragmentation['total_fragmented']);
            \WP_CLI::log('Total wasted space: ' . $fragmentation['total_wasted_mb'] . ' MB');
            
            if (!empty($fragmentation['fragmented_tables'])) {
                \WP_CLI::log("\n" . \WP_CLI::colorize('%Y=== Top Fragmented Tables ===%n'));
                
                $table = new \cli\Table();
                $table->setHeaders(['Table', 'Fragmentation %', 'Wasted MB', 'Severity']);
                
                foreach (array_slice($fragmentation['fragmented_tables'], 0, 10) as $tbl) {
                    $table->addRow([
                        $tbl['table'],
                        number_format($tbl['fragmentation_pct'], 2),
                        number_format($tbl['wasted_mb'], 2),
                        strtoupper($tbl['severity'])
                    ]);
                }
                
                $table->display();
                
                \WP_CLI::log("\n" . \WP_CLI::colorize('%CRun "wp fp db optimize" to defragment tables.%n'));
            } else {
                \WP_CLI::success('No significant fragmentation detected!');
            }
            
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to analyze fragmentation: ' . $e->getMessage());
        }
    }
    
    /**
     * Analyze plugin-specific cleanup opportunities
     */
    private function dbPluginCleanup(): void
    {
        try {
            $pluginOptimizer = new PluginSpecificOptimizer();
            
            \WP_CLI::log('Analyzing plugin-specific cleanup opportunities...');
            
            $opportunities = $pluginOptimizer->analyzeInstalledPlugins();
            
            if (empty($opportunities['opportunities'])) {
                \WP_CLI::log('No supported plugins found for cleanup.');
                return;
            }
            
            \WP_CLI::log("\n" . \WP_CLI::colorize('%G=== Plugin Cleanup Opportunities ===%n'));
            \WP_CLI::log('Total potential savings: ' . number_format($opportunities['total_potential_savings_mb'], 2) . ' MB');
            \WP_CLI::log('Total items to clean: ' . number_format($opportunities['total_items_to_clean']));
            
            foreach ($opportunities['opportunities'] as $plugin => $data) {
                \WP_CLI::log("\n" . \WP_CLI::colorize("%Y{$data['plugin_name']}:%n"));
                \WP_CLI::log('  Items: ' . number_format($data['total_items']));
                \WP_CLI::log('  Potential savings: ~' . number_format($data['potential_savings_mb'], 2) . ' MB');
                
                if (!empty($data['recommendations'])) {
                    foreach ($data['recommendations'] as $rec) {
                        \WP_CLI::log('  ' . \WP_CLI::colorize('%C→%n ') . $rec['message']);
                    }
                }
            }
            
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to analyze plugin cleanup: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate and export database report
     */
    private function dbReport(array $assoc_args): void
    {
        try {
            $reportService = new DatabaseReportService();
            $format = $assoc_args['format'] ?? 'text';
            
            \WP_CLI::log('Generating database report...');
            
            $report = $reportService->generateCompleteReport();
            
            switch ($format) {
                case 'json':
                    $output = $reportService->exportJSON($report);
                    $filename = 'fp-db-report-' . date('Y-m-d-His') . '.json';
                    file_put_contents($filename, $output);
                    \WP_CLI::success("Report saved to: {$filename}");
                    break;
                    
                case 'csv':
                    $output = $reportService->exportCSV($report);
                    $filename = 'fp-db-report-' . date('Y-m-d-His') . '.csv';
                    file_put_contents($filename, $output);
                    \WP_CLI::success("Report saved to: {$filename}");
                    break;
                    
                case 'text':
                default:
                    // Display summary
                    \WP_CLI::log("\n" . \WP_CLI::colorize('%G=== Database Report ===%n'));
                    \WP_CLI::log('Generated: ' . $report['generated_at_formatted']);
                    \WP_CLI::log('Database size: ' . $report['current_snapshot']['size']['total_mb'] . ' MB');
                    \WP_CLI::log('Overhead: ' . $report['current_snapshot']['size']['free_mb'] . ' MB');
                    
                    // Health score
                    $health = $reportService->getHealthScore();
                    \WP_CLI::log("\nHealth Score: " . $health['score'] . '% (Grade: ' . $health['grade'] . ')');
                    
                    // Trends
                    if (isset($report['trends']['size'])) {
                        \WP_CLI::log("\nGrowth rate: " . number_format($report['trends']['size']['growth_per_day_mb'], 2) . ' MB/day');
                        \WP_CLI::log('30-day projection: ' . number_format($report['trends']['projection_30_days'], 2) . ' MB');
                    }
                    
                    \WP_CLI::log("\n" . \WP_CLI::colorize('%CUse --format=json or --format=csv for detailed reports.%n'));
                    break;
            }
            
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to generate report: ' . $e->getMessage());
        }
    }
    
    /**
     * Convert MyISAM tables to InnoDB
     */
    private function dbConvertEngine(array $assoc_args): void
    {
        try {
            $container = Plugin::container();
            $optimizer = $container->get(DatabaseOptimizer::class);
            
            $table = $assoc_args['table'] ?? null;
            $all = $assoc_args['all'] ?? false;
            
            if (!$table && !$all) {
                \WP_CLI::error('Please specify --table=<name> or --all');
                return;
            }
            
            if ($all) {
                $engines = $optimizer->analyzeStorageEngines();
                
                if (empty($engines['myisam_tables'])) {
                    \WP_CLI::success('No MyISAM tables found!');
                    return;
                }
                
                \WP_CLI::log('Found ' . count($engines['myisam_tables']) . ' MyISAM tables.');
                \WP_CLI::confirm('Convert all to InnoDB?');
                
                $converted = 0;
                $failed = 0;
                
                foreach ($engines['myisam_tables'] as $tbl) {
                    \WP_CLI::log("Converting {$tbl['table']}...");
                    $result = $optimizer->convertToInnoDB($tbl['table']);
                    
                    if ($result['success']) {
                        $converted++;
                    } else {
                        $failed++;
                        \WP_CLI::warning($result['message']);
                    }
                }
                
                \WP_CLI::success("Converted {$converted} tables. Failed: {$failed}");
            } else {
                \WP_CLI::log("Converting {$table} to InnoDB...");
                $result = $optimizer->convertToInnoDB($table);
                
                if ($result['success']) {
                    \WP_CLI::success($result['message']);
                } else {
                    \WP_CLI::error($result['message']);
                }
            }
            
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to convert storage engine: ' . $e->getMessage());
        }
    }


    /**
     * Calculate performance score
     *
     * ## EXAMPLES
     *
     *     # Show performance score
     *     wp fp-performance score
     *
     * @when after_wp_load
     */
    public function score($args, $assoc_args)
    {
        try {
            $container = Plugin::container();
            $scorer = $container->get(Scorer::class);

            \WP_CLI::log('Calculating performance score...');
            $score = $scorer->calculate();

            \WP_CLI::log("\n" . \WP_CLI::colorize('%G=== Performance Score ===%n'));
            \WP_CLI::log(\WP_CLI::colorize('%YTotal Score: %G' . $score['total'] . '%n'));

            \WP_CLI::log("\n" . \WP_CLI::colorize('%Y=== Breakdown ===%n'));
            foreach ($score['breakdown'] as $label => $points) {
                $color = $points >= 10 ? '%G' : ($points >= 5 ? '%Y' : '%R');
                \WP_CLI::log("  {$label}: " . \WP_CLI::colorize("{$color}{$points}%n"));
            }

            if (!empty($score['suggestions'])) {
                \WP_CLI::log("\n" . \WP_CLI::colorize('%Y=== Suggestions ===%n'));
                foreach ($score['suggestions'] as $suggestion) {
                    \WP_CLI::log("  - {$suggestion}");
                }
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to calculate score: ' . $e->getMessage());
        }
    }

    /**
     * Object cache management
     *
     * ## EXAMPLES
     *
     *     # Check object cache status
     *     wp fp-performance object-cache status
     *
     *     # Enable object cache
     *     wp fp-performance object-cache enable
     *
     *     # Disable object cache
     *     wp fp-performance object-cache disable
     *
     *     # Flush object cache
     *     wp fp-performance object-cache flush
     *
     * @when after_wp_load
     */
    public function objectCache($args, $assoc_args)
    {
        $subcommand = $args[0] ?? 'status';

        if ($subcommand === 'status') {
            $this->objectCacheStatus();
        } elseif ($subcommand === 'enable') {
            $this->objectCacheEnable();
        } elseif ($subcommand === 'disable') {
            $this->objectCacheDisable();
        } elseif ($subcommand === 'flush') {
            $this->objectCacheFlush();
        } else {
            \WP_CLI::error("Unknown object-cache subcommand: {$subcommand}");
        }
    }
    
    /**
     * Show object cache status
     */
    private function objectCacheStatus(): void
    {
        try {
            $container = Plugin::container();
            $objectCache = $container->get(ObjectCacheManager::class);
            
            $info = $objectCache->getBackendInfo();
            $stats = $objectCache->getStatistics();
            
            \WP_CLI::log(\WP_CLI::colorize('%G=== Object Cache Status ===%n'));
            
            if ($info['available']) {
                \WP_CLI::log('Backend: ' . $info['name']);
                \WP_CLI::log('Available: Yes');
                \WP_CLI::log('Enabled: ' . ($info['enabled'] ? 'Yes' : 'No'));
                
                if ($stats['enabled']) {
                    \WP_CLI::log("\n" . \WP_CLI::colorize('%Y=== Statistics ===%n'));
                    \WP_CLI::log('Cache Hits: ' . number_format($stats['hits'] ?? 0));
                    \WP_CLI::log('Cache Misses: ' . number_format($stats['misses'] ?? 0));
                    \WP_CLI::log('Hit Ratio: ' . round($stats['ratio'] ?? 0, 2) . '%');
                }
            } else {
                \WP_CLI::warning('No object cache backend available (Redis, Memcached, APCu)');
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to get object cache status: ' . $e->getMessage());
        }
    }
    
    /**
     * Enable object cache
     */
    private function objectCacheEnable(): void
    {
        try {
            $container = Plugin::container();
            $objectCache = $container->get(ObjectCacheManager::class);
            
            \WP_CLI::log('Enabling object cache...');
            
            $result = $objectCache->install();
            
            if ($result['success']) {
                \WP_CLI::success($result['message']);
            } else {
                \WP_CLI::error($result['message']);
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to enable object cache: ' . $e->getMessage());
        }
    }
    
    /**
     * Disable object cache
     */
    private function objectCacheDisable(): void
    {
        try {
            $container = Plugin::container();
            $objectCache = $container->get(ObjectCacheManager::class);
            
            \WP_CLI::log('Disabling object cache...');
            
            $result = $objectCache->uninstall();
            
            if ($result['success']) {
                \WP_CLI::success($result['message']);
            } else {
                \WP_CLI::error($result['message']);
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to disable object cache: ' . $e->getMessage());
        }
    }
    
    /**
     * Flush object cache
     */
    private function objectCacheFlush(): void
    {
        try {
            $container = Plugin::container();
            $objectCache = $container->get(ObjectCacheManager::class);
            
            \WP_CLI::log('Flushing object cache...');
            
            if ($objectCache->flush()) {
                \WP_CLI::success('Object cache flushed successfully!');
            } else {
                \WP_CLI::error('Failed to flush object cache');
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to flush object cache: ' . $e->getMessage());
        }
    }

    /**
     * Plugin information and status
     *
     * ## EXAMPLES
     *
     *     # Show plugin info
     *     wp fp-performance info
     *
     * @when after_wp_load
     */
    public function info($args, $assoc_args)
    {
        \WP_CLI::log(\WP_CLI::colorize('%G=== FP Performance Suite ===%n'));
        \WP_CLI::log('Version: ' . (defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : 'Unknown'));
        \WP_CLI::log('Author: Francesco Passeri');
        \WP_CLI::log('Website: https://francescopasseri.com');

        \WP_CLI::log("\n" . \WP_CLI::colorize('%Y=== Available Commands ===%n'));
        \WP_CLI::log('  cache clear          - Clear page cache');
        \WP_CLI::log('  cache status         - Show cache status');
        \WP_CLI::log("\n" . \WP_CLI::colorize('%C=== Database Commands ===%n'));
        \WP_CLI::log('  db cleanup           - Run database cleanup');
        \WP_CLI::log('  db status            - Show database status');
        \WP_CLI::log('  db optimize          - Optimize database tables');
        \WP_CLI::log('  db analyze           - Analyze database and get recommendations');
        \WP_CLI::log('  db health            - Advanced database health check');
        \WP_CLI::log('  db fragmentation     - Analyze database fragmentation');
        \WP_CLI::log('  db plugin-cleanup    - Analyze plugin-specific cleanup opportunities');
        \WP_CLI::log('  db report            - Generate and export database report');
        \WP_CLI::log('  db convert-engine    - Convert MyISAM tables to InnoDB');
        \WP_CLI::log("\n" . \WP_CLI::colorize('%C=== Cache Commands ===%n'));
        \WP_CLI::log('  object-cache status  - Show object cache status');
        \WP_CLI::log('  object-cache enable  - Enable object cache');
        \WP_CLI::log('  object-cache disable - Disable object cache');
        \WP_CLI::log('  object-cache flush   - Flush object cache');
        \WP_CLI::log("\n" . \WP_CLI::colorize('%C=== Media Commands ===%n'));
        \WP_CLI::log("\n" . \WP_CLI::colorize('%C=== Other Commands ===%n'));
        \WP_CLI::log('  score                - Calculate performance score');
        \WP_CLI::log('  info                 - Show this information');
    }
}
