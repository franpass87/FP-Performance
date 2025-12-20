<?php

namespace FP\PerfSuite\Cli\Commands;

use FP\PerfSuite\Plugin;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\DB\DatabaseOptimizer;
use FP\PerfSuite\Services\DB\PluginSpecificOptimizer;
use FP\PerfSuite\Services\DB\DatabaseReportService;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor;

/**
 * Comandi WP-CLI per la gestione del database
 * 
 * @package FP\PerfSuite\Cli\Commands
 * @author Francesco Passeri
 */
class DatabaseCommands
{
    /**
     * Run database cleanup
     */
    public function cleanup(array $assoc_args): void
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
    public function status(): void
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
    public function optimize(array $assoc_args): void
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
    public function analyze(): void
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
    public function health(): void
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
                    } else {
                        $color = '%G';
                    }
                    \WP_CLI::log(\WP_CLI::colorize("{$color}• {$issue['title']}%n"));
                    \WP_CLI::log("  {$issue['description']}");
                }
            } else {
                \WP_CLI::success('No issues found!');
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to run health check: ' . $e->getMessage());
        }
    }
    
    /**
     * Analyze database fragmentation
     */
    public function fragmentation(): void
    {
        try {
            $container = Plugin::container();
            $optimizer = $container->get(DatabaseOptimizer::class);
            
            \WP_CLI::log('Analyzing database fragmentation...');
            
            $fragmentation = $optimizer->analyzeFragmentation();
            
            \WP_CLI::log("\n" . \WP_CLI::colorize('%G=== Fragmentation Analysis ===%n'));
            
            if (!empty($fragmentation['tables'])) {
                foreach ($fragmentation['tables'] as $table => $data) {
                    $fragPercent = round($data['fragmentation'], 2);
                    $color = $fragPercent > 30 ? '%R' : ($fragPercent > 10 ? '%Y' : '%G');
                    \WP_CLI::log(\WP_CLI::colorize("{$color}{$table}: {$fragPercent}% fragmented%n"));
                }
                
                \WP_CLI::log("\nTotal fragmentation: " . round($fragmentation['total'], 2) . '%');
            } else {
                \WP_CLI::success('No fragmentation detected!');
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to analyze fragmentation: ' . $e->getMessage());
        }
    }
    
    /**
     * Analyze plugin-specific cleanup opportunities
     */
    public function pluginCleanup(): void
    {
        try {
            $pluginOptimizer = new PluginSpecificOptimizer();
            
            \WP_CLI::log('Analyzing plugin-specific cleanup opportunities...');
            
            $opportunities = $pluginOptimizer->analyzeInstalledPlugins();
            
            \WP_CLI::log("\n" . \WP_CLI::colorize('%G=== Plugin Cleanup Opportunities ===%n'));
            
            if (!empty($opportunities['opportunities'])) {
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
            } else {
                \WP_CLI::log('No supported plugins found for cleanup.');
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to analyze plugin cleanup: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate and export database report
     */
    public function report(array $assoc_args): void
    {
        try {
            $reportService = new DatabaseReportService();
            
            $format = $assoc_args['format'] ?? 'json';
            $output = $assoc_args['output'] ?? null;
            
            \WP_CLI::log('Generating database report...');
            
            $report = $reportService->generateCompleteReport();
            
            if ($format === 'json') {
                $content = $reportService->exportJSON($report);
                $filename = $output ?: 'fp-db-report-' . date('Y-m-d-His') . '.json';
                file_put_contents($filename, $content);
                \WP_CLI::success("Report saved to: {$filename}");
            } elseif ($format === 'csv') {
                $content = $reportService->exportCSV($report);
                $filename = $output ?: 'fp-db-report-' . date('Y-m-d-His') . '.csv';
                file_put_contents($filename, $content);
                \WP_CLI::success("Report saved to: {$filename}");
            } else {
                // Display summary
                \WP_CLI::log("\n" . \WP_CLI::colorize('%G=== Database Report ===%n'));
                \WP_CLI::log('Generated: ' . ($report['generated_at_formatted'] ?? date('Y-m-d H:i:s')));
                \WP_CLI::log('Database size: ' . ($report['current_snapshot']['size']['total_mb'] ?? 'N/A') . ' MB');
                \WP_CLI::log("\n" . \WP_CLI::colorize('%CUse --format=json or --format=csv for detailed reports.%n'));
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to generate report: ' . $e->getMessage());
        }
    }
    
    /**
     * Convert MyISAM tables to InnoDB
     */
    public function convertEngine(array $assoc_args): void
    {
        try {
            global $wpdb;
            $container = Plugin::container();
            $optimizer = $container->get(DatabaseOptimizer::class);
            
            $table = $assoc_args['table'] ?? null;
            $all = isset($assoc_args['all']);
            
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
                    $tableName = $tbl['name'];
                    \WP_CLI::log("Converting {$tableName}...");
                    
                    $result = $wpdb->query("ALTER TABLE `{$tableName}` ENGINE=InnoDB");
                    
                    if ($result !== false) {
                        $converted++;
                    } else {
                        $failed++;
                        \WP_CLI::warning("Failed to convert {$tableName}: " . $wpdb->last_error);
                    }
                }
                
                \WP_CLI::success("Converted {$converted} tables. Failed: {$failed}");
            } else {
                \WP_CLI::log("Converting {$table} to InnoDB...");
                $result = $wpdb->query("ALTER TABLE `{$table}` ENGINE=InnoDB");
                
                if ($result !== false) {
                    \WP_CLI::success("Table {$table} converted successfully!");
                } else {
                    \WP_CLI::error("Failed to convert: " . $wpdb->last_error);
                }
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to convert storage engine: ' . $e->getMessage());
        }
    }
}

