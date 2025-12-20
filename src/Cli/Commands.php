<?php

namespace FP\PerfSuite\Cli;

use FP\PerfSuite\Cli\Commands\CacheCommands;
use FP\PerfSuite\Cli\Commands\DatabaseCommands;
use FP\PerfSuite\Cli\Commands\ObjectCacheCommands;
use FP\PerfSuite\Cli\Commands\ScoreCommands;

/**
 * WP-CLI commands for FP Performance Suite
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Commands
{
    private CacheCommands $cacheCommands;
    private DatabaseCommands $databaseCommands;
    private ObjectCacheCommands $objectCacheCommands;
    private ScoreCommands $scoreCommands;

    public function __construct()
    {
        $this->cacheCommands = new CacheCommands();
        $this->databaseCommands = new DatabaseCommands();
        $this->objectCacheCommands = new ObjectCacheCommands();
        $this->scoreCommands = new ScoreCommands();
    }

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
            $this->cacheCommands->clear();
        } elseif ($subcommand === 'status') {
            $this->cacheCommands->status();
        } else {
            \WP_CLI::error("Unknown cache subcommand: {$subcommand}");
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
            $this->databaseCommands->cleanup($assoc_args);
        } elseif ($subcommand === 'status') {
            $this->databaseCommands->status();
        } elseif ($subcommand === 'optimize') {
            $this->databaseCommands->optimize($assoc_args);
        } elseif ($subcommand === 'analyze') {
            $this->databaseCommands->analyze();
        } elseif ($subcommand === 'health') {
            $this->databaseCommands->health();
        } elseif ($subcommand === 'fragmentation') {
            $this->databaseCommands->fragmentation();
        } elseif ($subcommand === 'plugin-cleanup') {
            $this->databaseCommands->pluginCleanup();
        } elseif ($subcommand === 'report') {
            $this->databaseCommands->report($assoc_args);
        } elseif ($subcommand === 'convert-engine') {
            $this->databaseCommands->convertEngine($assoc_args);
        } else {
            \WP_CLI::error("Unknown db subcommand: {$subcommand}");
        }
    }

    // Metodi dbCleanup(), dbStatus(), dbOptimize(), dbAnalyze(), dbHealth(), dbFragmentation(),
    // dbPluginCleanup(), dbReport(), dbConvertEngine() rimossi - ora gestiti da DatabaseCommands


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
        $this->scoreCommands->calculate();
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
            $this->objectCacheCommands->status();
        } elseif ($subcommand === 'enable') {
            $this->objectCacheCommands->enable();
        } elseif ($subcommand === 'disable') {
            $this->objectCacheCommands->disable();
        } elseif ($subcommand === 'flush') {
            $this->objectCacheCommands->flush();
        } else {
            \WP_CLI::error("Unknown object-cache subcommand: {$subcommand}");
        }
    }
    
    // Metodi objectCacheStatus(), objectCacheEnable(), objectCacheDisable(), objectCacheFlush()
    // rimossi - ora gestiti da ObjectCacheCommands

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
