<?php

namespace FP\PerfSuite\Cli;

use FP\PerfSuite\Plugin;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Media\WebPConverter;
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
     * @when after_wp_load
     */
    public function db($args, $assoc_args)
    {
        $subcommand = $args[0] ?? 'cleanup';
        
        if ($subcommand === 'cleanup') {
            $this->dbCleanup($assoc_args);
        } elseif ($subcommand === 'status') {
            $this->dbStatus();
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
            $status = $cleaner->status();
            
            \WP_CLI::log('Database Status:');
            \WP_CLI::log('  Overhead: ' . $status['overhead_mb'] . ' MB');
            \WP_CLI::log('  Schedule: ' . $status['schedule']);
            
            if ($status['last_run'] > 0) {
                \WP_CLI::log('  Last run: ' . date('Y-m-d H:i:s', $status['last_run']));
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to get database status: ' . $e->getMessage());
        }
    }

    /**
     * WebP conversion operations
     *
     * ## OPTIONS
     *
     * [--limit=<limit>]
     * : Number of images to convert
     * ---
     * default: 20
     * ---
     *
     * ## EXAMPLES
     *
     *     # Convert 50 images to WebP
     *     wp fp-performance webp convert --limit=50
     *
     * @when after_wp_load
     */
    public function webp($args, $assoc_args)
    {
        $subcommand = $args[0] ?? 'convert';
        
        if ($subcommand === 'convert') {
            $this->webpConvert($assoc_args);
        } elseif ($subcommand === 'status') {
            $this->webpStatus();
        } else {
            \WP_CLI::error("Unknown webp subcommand: {$subcommand}");
        }
    }

    /**
     * Convert images to WebP
     */
    private function webpConvert(array $assoc_args): void
    {
        try {
            $container = Plugin::container();
            $converter = $container->get(WebPConverter::class);
            
            $limit = isset($assoc_args['limit']) ? (int)$assoc_args['limit'] : 20;
            
            \WP_CLI::log("Starting WebP conversion (limit: {$limit})...");
            
            $result = $converter->bulkConvert($limit);
            
            if (isset($result['error'])) {
                \WP_CLI::error($result['error']);
                return;
            }
            
            if ($result['queued']) {
                \WP_CLI::log("Queued {$result['total']} images for conversion");
                \WP_CLI::log('Processing in background...');
                
                // Wait and check progress
                $maxWait = 60; // seconds
                $waited = 0;
                while ($waited < $maxWait) {
                    sleep(2);
                    $waited += 2;
                    
                    $status = $converter->status();
                    \WP_CLI::log("Progress: {$status['coverage']}% coverage");
                    
                    if ($status['coverage'] >= 100) {
                        break;
                    }
                }
                
                \WP_CLI::success('WebP conversion completed!');
            } else {
                \WP_CLI::log('No images to convert');
            }
            
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to convert images: ' . $e->getMessage());
        }
    }

    /**
     * Show WebP status
     */
    private function webpStatus(): void
    {
        try {
            $container = Plugin::container();
            $converter = $container->get(WebPConverter::class);
            $status = $converter->status();
            
            \WP_CLI::log('WebP Status:');
            \WP_CLI::log('  Enabled: ' . ($status['enabled'] ? 'Yes' : 'No'));
            \WP_CLI::log('  Quality: ' . $status['quality']);
            \WP_CLI::log('  Coverage: ' . round($status['coverage'], 2) . '%');
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to get WebP status: ' . $e->getMessage());
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
        \WP_CLI::log('  cache clear       - Clear page cache');
        \WP_CLI::log('  cache status      - Show cache status');
        \WP_CLI::log('  db cleanup        - Run database cleanup');
        \WP_CLI::log('  db status         - Show database status');
        \WP_CLI::log('  webp convert      - Convert images to WebP');
        \WP_CLI::log('  webp status       - Show WebP status');
        \WP_CLI::log('  score             - Calculate performance score');
        \WP_CLI::log('  info              - Show this information');
    }
}
