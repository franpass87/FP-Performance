<?php

/**
 * Plugin main class file.
 *
 * @package FP\PerfSuite
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite;

use FP\PerfSuite\Admin\Assets as AdminAssets;
use FP\PerfSuite\Admin\Menu;
use FP\PerfSuite\Admin\AdminBar;
use FP\PerfSuite\Health\HealthCheck;
use FP\PerfSuite\Http\Routes;
use FP\PerfSuite\Monitoring\QueryMonitor;
use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Logs\DebugToggler;
use FP\PerfSuite\Services\Logs\RealtimeLog;
use FP\PerfSuite\Services\Media\WebPConverter;
use FP\PerfSuite\Services\Presets\Manager as PresetManager;
use FP\PerfSuite\Services\Score\Scorer;
use FP\PerfSuite\Utils\Env;
use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Htaccess;
use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\RateLimiter;
use FP\PerfSuite\Utils\Semaphore;

use function get_file_data;
use function wp_clear_scheduled_hook;

class Plugin
{
    private static ?ServiceContainer $container = null;

    public static function init(): void
    {
        if (self::$container instanceof ServiceContainer) {
            return;
        }

        $container = new ServiceContainer();
        self::register($container);
        self::$container = $container;

        Logger::debug('Plugin initialized', ['version' => FP_PERF_SUITE_VERSION]);
        do_action('fp_perfsuite_container_ready', $container);

        $container->get(Menu::class)->boot();
        $container->get(AdminAssets::class)->boot();
        $container->get(AdminBar::class)->boot();
        $container->get(Routes::class)->boot();

        add_action('init', static function () use ($container) {
            load_plugin_textdomain('fp-performance-suite', false, dirname(plugin_basename(FP_PERF_SUITE_FILE)) . '/languages');

            // Core services
            $container->get(PageCache::class)->register();
            $container->get(Headers::class)->register();
            $container->get(Optimizer::class)->register();
            $container->get(WebPConverter::class)->register();
            $container->get(Cleaner::class)->register();

            // New services (v1.1.0)
            $container->get(\FP\PerfSuite\Services\Assets\CriticalCss::class)->register();
            $container->get(\FP\PerfSuite\Services\CDN\CdnManager::class)->register();
            $container->get(\FP\PerfSuite\Services\Monitoring\PerformanceMonitor::class)->register();
            $container->get(\FP\PerfSuite\Services\Reports\ScheduledReports::class)->register();
            
            // PageSpeed optimization services (v1.2.0)
            $container->get(\FP\PerfSuite\Services\Assets\LazyLoadManager::class)->register();
            $container->get(\FP\PerfSuite\Services\Assets\FontOptimizer::class)->register();
            $container->get(\FP\PerfSuite\Services\Assets\ImageOptimizer::class)->register();
        });

        // Register WP-CLI commands
        if (defined('WP_CLI') && WP_CLI) {
            self::registerCliCommands();
        }

        // Register Site Health checks
        HealthCheck::register();

        // Register Query Monitor integration if available
        QueryMonitor::register();
    }

    /**
     * Register WP-CLI commands
     */
    private static function registerCliCommands(): void
    {
        if (!class_exists('WP_CLI')) {
            return;
        }

        require_once FP_PERF_SUITE_DIR . '/src/Cli/Commands.php';

        \WP_CLI::add_command('fp-performance cache', [Cli\Commands::class, 'cache'], [
            'shortdesc' => 'Manage page cache',
        ]);

        \WP_CLI::add_command('fp-performance db', [Cli\Commands::class, 'db'], [
            'shortdesc' => 'Database cleanup operations',
        ]);

        \WP_CLI::add_command('fp-performance webp', [Cli\Commands::class, 'webp'], [
            'shortdesc' => 'WebP conversion operations',
        ]);

        \WP_CLI::add_command('fp-performance score', [Cli\Commands::class, 'score'], [
            'shortdesc' => 'Calculate performance score',
        ]);

        \WP_CLI::add_command('fp-performance info', [Cli\Commands::class, 'info'], [
            'shortdesc' => 'Show plugin information',
        ]);

        Logger::debug('WP-CLI commands registered');
    }

    private static function register(ServiceContainer $container): void
    {
        if (!defined('FP_PERF_SUITE_FILE')) {
            define('FP_PERF_SUITE_FILE', __DIR__ . '/../fp-performance-suite.php');
        }

        $container->set(ServiceContainer::class, static fn() => $container);

        $container->set(Fs::class, static function () {
            return new Fs();
        });

        $container->set(Htaccess::class, static function (ServiceContainer $c) {
            return new Htaccess($c->get(Fs::class));
        });

        $container->set(Env::class, static fn() => new Env());
        $container->set(Semaphore::class, static fn() => new Semaphore());
        $container->set(RateLimiter::class, static fn() => new RateLimiter());

        // Asset optimization modular components
        $container->set(\FP\PerfSuite\Services\Assets\HtmlMinifier::class, static fn() => new \FP\PerfSuite\Services\Assets\HtmlMinifier());
        $container->set(\FP\PerfSuite\Services\Assets\ScriptOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\ScriptOptimizer());
        $container->set(\FP\PerfSuite\Services\Assets\WordPressOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\WordPressOptimizer());
        $container->set(\FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager::class, static fn() => new \FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager());
        $container->set(\FP\PerfSuite\Services\Assets\Combiners\DependencyResolver::class, static fn() => new \FP\PerfSuite\Services\Assets\Combiners\DependencyResolver());
        
        // PageSpeed optimization services
        $container->set(\FP\PerfSuite\Services\Assets\LazyLoadManager::class, static fn() => new \FP\PerfSuite\Services\Assets\LazyLoadManager());
        $container->set(\FP\PerfSuite\Services\Assets\FontOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\FontOptimizer());
        $container->set(\FP\PerfSuite\Services\Assets\ImageOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\ImageOptimizer());

        // WebP conversion modular components
        $container->set(\FP\PerfSuite\Services\Media\WebP\WebPPathHelper::class, static fn() => new \FP\PerfSuite\Services\Media\WebP\WebPPathHelper());
        $container->set(\FP\PerfSuite\Services\Media\WebP\WebPImageConverter::class, static fn() => new \FP\PerfSuite\Services\Media\WebP\WebPImageConverter());
        $container->set(\FP\PerfSuite\Services\Media\WebP\WebPQueue::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Services\Media\WebP\WebPQueue($c->get(RateLimiter::class)));
        $container->set(\FP\PerfSuite\Services\Media\WebP\WebPAttachmentProcessor::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Media\WebP\WebPAttachmentProcessor(
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPImageConverter::class),
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPPathHelper::class)
            );
        });
        $container->set(\FP\PerfSuite\Services\Media\WebP\WebPBatchProcessor::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Media\WebP\WebPBatchProcessor(
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPQueue::class),
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPAttachmentProcessor::class)
            );
        });

        // New services
        $container->set(\FP\PerfSuite\Services\Assets\CriticalCss::class, static fn() => new \FP\PerfSuite\Services\Assets\CriticalCss());
        $container->set(\FP\PerfSuite\Services\CDN\CdnManager::class, static fn() => new \FP\PerfSuite\Services\CDN\CdnManager());
        $container->set(\FP\PerfSuite\Services\Monitoring\PerformanceMonitor::class, static fn() => \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance());
        $container->set(\FP\PerfSuite\Services\Reports\ScheduledReports::class, static fn() => new \FP\PerfSuite\Services\Reports\ScheduledReports());
        
        // Performance Analyzer
        $container->set(\FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer(
                $c->get(PageCache::class),
                $c->get(Headers::class),
                $c->get(Optimizer::class),
                $c->get(WebPConverter::class),
                $c->get(Cleaner::class),
                $c->get(\FP\PerfSuite\Services\Monitoring\PerformanceMonitor::class)
            );
        });

        $container->set(PageCache::class, static fn(ServiceContainer $c) => new PageCache($c->get(Fs::class), $c->get(Env::class)));
        $container->set(Headers::class, static fn(ServiceContainer $c) => new Headers($c->get(Htaccess::class), $c->get(Env::class)));
        $container->set(Optimizer::class, static function (ServiceContainer $c) {
            return new Optimizer(
                $c->get(Semaphore::class),
                $c->get(\FP\PerfSuite\Services\Assets\HtmlMinifier::class),
                $c->get(\FP\PerfSuite\Services\Assets\ScriptOptimizer::class),
                $c->get(\FP\PerfSuite\Services\Assets\WordPressOptimizer::class),
                $c->get(\FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager::class),
                $c->get(\FP\PerfSuite\Services\Assets\Combiners\DependencyResolver::class)
            );
        });
        $container->set(WebPConverter::class, static function (ServiceContainer $c) {
            return new WebPConverter(
                $c->get(Fs::class),
                $c->get(RateLimiter::class),
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPImageConverter::class),
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPQueue::class),
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPAttachmentProcessor::class),
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPBatchProcessor::class),
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPPathHelper::class)
            );
        });
        $container->set(Cleaner::class, static fn(ServiceContainer $c) => new Cleaner($c->get(Env::class), $c->get(RateLimiter::class)));
        $container->set(DebugToggler::class, static fn(ServiceContainer $c) => new DebugToggler($c->get(Fs::class), $c->get(Env::class)));
        $container->set(RealtimeLog::class, static fn(ServiceContainer $c) => new RealtimeLog($c->get(DebugToggler::class)));
        $container->set(PresetManager::class, static function (ServiceContainer $c) {
            return new PresetManager(
                $c->get(PageCache::class),
                $c->get(Headers::class),
                $c->get(Optimizer::class),
                $c->get(WebPConverter::class),
                $c->get(Cleaner::class),
                $c->get(DebugToggler::class)
            );
        });
        $container->set(Scorer::class, static fn(ServiceContainer $c) => new Scorer($c->get(PageCache::class), $c->get(Headers::class), $c->get(Optimizer::class), $c->get(WebPConverter::class), $c->get(Cleaner::class), $c->get(DebugToggler::class)));

        $container->set(AdminAssets::class, static fn() => new AdminAssets());
        $container->set(Menu::class, static fn(ServiceContainer $c) => new Menu($c));
        $container->set(AdminBar::class, static fn(ServiceContainer $c) => new AdminBar($c));
        $container->set(Routes::class, static fn(ServiceContainer $c) => new Routes($c));
    }

    public static function container(): ServiceContainer
    {
        if (!self::$container instanceof ServiceContainer) {
            self::init();
        }

        return self::$container;
    }

    public static function onActivate(): void
    {
        $version = defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : '';

        if (!is_string($version) || '' === $version) {
            $data = get_file_data(FP_PERF_SUITE_FILE, ['Version' => 'Version']);
            $version = is_array($data) && !empty($data['Version']) ? (string) $data['Version'] : '1.0.0';
        }

        update_option('fp_perfsuite_version', $version);
        $cleaner = new Cleaner(new Env(), new RateLimiter());
        $cleaner->primeSchedules();
        $cleaner->maybeSchedule(true);

        Logger::info('Plugin activated', ['version' => $version]);
        do_action('fp_ps_plugin_activated', $version);
    }

    public static function onDeactivate(): void
    {
        wp_clear_scheduled_hook(Cleaner::CRON_HOOK);
        Logger::info('Plugin deactivated');
        do_action('fp_ps_plugin_deactivated');
    }
}
