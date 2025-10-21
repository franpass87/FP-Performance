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
use FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer;
use FP\PerfSuite\Services\Assets\ResponsiveImageAjaxHandler;
use FP\PerfSuite\Services\Assets\RenderBlockingOptimizer;
use FP\PerfSuite\Services\Assets\CSSOptimizer;
use FP\PerfSuite\Services\Assets\UnusedCSSOptimizer;
use FP\PerfSuite\Services\Assets\DOMReflowOptimizer;
use FP\PerfSuite\Services\Assets\jQueryOptimizer;
use FP\PerfSuite\Services\Assets\BatchDOMUpdater;
use FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer;
use FP\PerfSuite\Services\Assets\CodeSplittingManager;
use FP\PerfSuite\Services\Assets\JavaScriptTreeShaker;
use FP\PerfSuite\Services\Assets\CriticalPathOptimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Cache\ObjectCacheManager;
use FP\PerfSuite\Services\Cache\EdgeCacheManager;
use FP\PerfSuite\Services\Compression\CompressionManager;
use FP\PerfSuite\Services\Compatibility\ThemeCompatibility;
use FP\PerfSuite\Services\Compatibility\ThemeDetector;
use FP\PerfSuite\Services\Compatibility\CompatibilityFilters;
use FP\PerfSuite\Services\Assets\ThemeAssetConfiguration;
use FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;
use FP\PerfSuite\Services\Security\HtaccessSecurity;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\DB\QueryCacheManager;
use FP\PerfSuite\Services\DB\DatabaseOptimizer;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor;
use FP\PerfSuite\Services\DB\PluginSpecificOptimizer;
use FP\PerfSuite\Services\DB\DatabaseReportService;
use FP\PerfSuite\Services\Logs\DebugToggler;
use FP\PerfSuite\Services\Logs\RealtimeLog;
use FP\PerfSuite\Services\Media\WebPConverter;
use FP\PerfSuite\Services\Media\AVIFConverter;
use FP\PerfSuite\Services\Presets\Manager as PresetManager;
use FP\PerfSuite\Services\Score\Scorer;
use FP\PerfSuite\Services\Admin\BackendOptimizer;
use FP\PerfSuite\Utils\Env;
use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Htaccess;
use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\RateLimiter;
use FP\PerfSuite\Utils\Semaphore;
use FP\PerfSuite\Utils\InstallationRecovery;

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
        $container->get(\FP\PerfSuite\Admin\Assets::class)->boot();
        $container->get(AdminBar::class)->boot();
        $container->get(Routes::class)->boot();

        add_action('init', static function () use ($container) {
            load_plugin_textdomain('fp-performance-suite', false, dirname(plugin_basename(FP_PERF_SUITE_FILE)) . '/languages');

            // Inizializza lo scheduler se necessario (primo caricamento dopo attivazione)
            if (get_option('fp_perfsuite_needs_scheduler_init') === '1') {
                if (class_exists('FP\PerfSuite\Services\DB\Cleaner')) {
                    try {
                        $cleanerInstance = $container->get(Cleaner::class);
                        $cleanerInstance->primeSchedules();
                        $cleanerInstance->maybeSchedule(true);
                        delete_option('fp_perfsuite_needs_scheduler_init');
                        error_log('[FP Performance Suite] Scheduler initialized successfully');
                        
                        // Ora è sicuro triggerare l'action hook (siamo dentro 'init')
                        $version = get_option('fp_perfsuite_version', FP_PERF_SUITE_VERSION);
                        do_action('fp_ps_plugin_activated', $version);
                    } catch (\Throwable $e) {
                        error_log('[FP Performance Suite] Failed to initialize scheduler: ' . $e->getMessage());
                    }
                }
            }

            // Core services
            $container->get(PageCache::class)->register();
            $container->get(Headers::class)->register();
            $container->get(Optimizer::class)->register();
            $container->get(WebPConverter::class)->register();
            $container->get(Cleaner::class)->register();
            
            // Database Optimization Services (v1.4.0) - Carica solo se disponibili
            if ($container->has(DatabaseOptimizer::class)) {
                $container->get(DatabaseOptimizer::class)->register();
            }
            if ($container->has(DatabaseQueryMonitor::class)) {
                $container->get(DatabaseQueryMonitor::class)->register();
            }
            
            // Security services
            $container->get(HtaccessSecurity::class)->register();

            // Cache services (v1.1.0)
            $container->get(\FP\PerfSuite\Services\Assets\CriticalCss::class)->register();
            $container->get(\FP\PerfSuite\Services\CDN\CdnManager::class)->register();
            $container->get(\FP\PerfSuite\Services\Monitoring\PerformanceMonitor::class)->register();
            $container->get(\FP\PerfSuite\Services\Reports\ScheduledReports::class)->register();
            
            // PageSpeed optimization services (v1.2.0)
            $container->get(\FP\PerfSuite\Services\Assets\LazyLoadManager::class)->register();
            $container->get(\FP\PerfSuite\Services\Assets\FontOptimizer::class)->register();
            $container->get(\FP\PerfSuite\Services\Assets\ImageOptimizer::class)->register();
            $container->get(CriticalPathOptimizer::class)->register();
            $container->get(ResponsiveImageOptimizer::class)->register();
            $container->get(ResponsiveImageAjaxHandler::class)->register();
            $container->get(RenderBlockingOptimizer::class)->register();
            $container->get(CSSOptimizer::class)->register();
            $container->get(UnusedCSSOptimizer::class)->register();
            $container->get(ThemeAssetConfiguration::class)->register();
            $container->get(CompressionManager::class)->register();
            
            // Advanced Performance Services (v1.3.0)
            $container->get(ObjectCacheManager::class)->register();
            $container->get(EdgeCacheManager::class)->register();
            $container->get(AVIFConverter::class)->register();
            $container->get(\FP\PerfSuite\Services\Assets\Http2ServerPush::class)->register();
            $container->get(\FP\PerfSuite\Services\Assets\CriticalCssAutomation::class)->register();
            $container->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class)->register();
            $container->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector::class)->register();
            $container->get(\FP\PerfSuite\Services\PWA\ServiceWorkerManager::class)->register();
            $container->get(\FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor::class)->register();
            $container->get(QueryCacheManager::class)->register();
            $container->get(\FP\PerfSuite\Services\Assets\PredictivePrefetching::class)->register();
            $container->get(\FP\PerfSuite\Services\Assets\SmartAssetDelivery::class)->register();
            
            // DOM Reflow Optimization Services (v1.4.1)
            $container->get(DOMReflowOptimizer::class)->register();
            $container->get(jQueryOptimizer::class)->register();
            $container->get(BatchDOMUpdater::class)->register();
            
            // JavaScript Optimization Services (v1.4.2)
            $container->get(UnusedJavaScriptOptimizer::class)->register();
            $container->get(CodeSplittingManager::class)->register();
            $container->get(JavaScriptTreeShaker::class)->register();
            
            // Theme Compatibility (v1.3.0)
            $container->get(ThemeCompatibility::class)->register();
            $container->get(CompatibilityFilters::class)->register();
            
            // Backend Optimizer
            $container->get(BackendOptimizer::class)->init();
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
        $container->set(ResponsiveImageOptimizer::class, static fn() => new ResponsiveImageOptimizer());
        $container->set(ResponsiveImageAjaxHandler::class, static fn() => new ResponsiveImageAjaxHandler());
        $container->set(RenderBlockingOptimizer::class, static fn() => new RenderBlockingOptimizer());
        $container->set(CSSOptimizer::class, static fn() => new CSSOptimizer());
        $container->set(UnusedCSSOptimizer::class, static fn() => new UnusedCSSOptimizer());
        
        // Compression service
        $container->set(CompressionManager::class, static fn(ServiceContainer $c) => new CompressionManager($c->get(Htaccess::class)));

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

        // v1.1.0 Services
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
        
        // v1.3.0 Advanced Performance Services
        
        // Object Cache (Redis/Memcached)
        $container->set(ObjectCacheManager::class, static fn() => new ObjectCacheManager());
        
        // Edge Cache Providers
        $container->set(EdgeCacheManager::class, static fn() => new EdgeCacheManager());
        
        // AVIF Image Converter
        $container->set(\FP\PerfSuite\Services\Media\AVIF\AVIFImageConverter::class, static fn() => new \FP\PerfSuite\Services\Media\AVIF\AVIFImageConverter());
        $container->set(\FP\PerfSuite\Services\Media\AVIF\AVIFPathHelper::class, static fn() => new \FP\PerfSuite\Services\Media\AVIF\AVIFPathHelper());
        $container->set(AVIFConverter::class, static function (ServiceContainer $c) {
            return new AVIFConverter(
                $c->get(\FP\PerfSuite\Services\Media\AVIF\AVIFImageConverter::class),
                $c->get(\FP\PerfSuite\Services\Media\AVIF\AVIFPathHelper::class)
            );
        });
        
        // HTTP/2 Server Push
        $container->set(\FP\PerfSuite\Services\Assets\Http2ServerPush::class, static fn() => new \FP\PerfSuite\Services\Assets\Http2ServerPush());
        
        // Critical CSS Automation
        $container->set(\FP\PerfSuite\Services\Assets\CriticalCssAutomation::class, static fn() => new \FP\PerfSuite\Services\Assets\CriticalCssAutomation());
        
        // Third-Party Script Manager
        $container->set(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class, static fn() => new \FP\PerfSuite\Services\Assets\ThirdPartyScriptManager());
        
        // JavaScript Optimization Services
        $container->set(UnusedJavaScriptOptimizer::class, static fn() => new UnusedJavaScriptOptimizer());
        $container->set(CodeSplittingManager::class, static fn() => new CodeSplittingManager());
        $container->set(JavaScriptTreeShaker::class, static fn() => new JavaScriptTreeShaker());
        
        // Third-Party Script Detector (AI Auto-detect)
        $container->set(\FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector(
            $c->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class)
        ));
        
        // AI Analyzer for Auto-Configuration
        $container->set(\FP\PerfSuite\Services\AI\Analyzer::class, static fn() => new \FP\PerfSuite\Services\AI\Analyzer());
        
        // Service Worker / PWA
        $container->set(\FP\PerfSuite\Services\PWA\ServiceWorkerManager::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Services\PWA\ServiceWorkerManager($c->get(Fs::class)));
        
        // Core Web Vitals Monitor
        $container->set(\FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor::class, static fn() => new \FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor());
        
        // Database Query Cache
        $container->set(QueryCacheManager::class, static fn() => new QueryCacheManager());
        
        // Predictive Prefetching
        $container->set(\FP\PerfSuite\Services\Assets\PredictivePrefetching::class, static fn() => new \FP\PerfSuite\Services\Assets\PredictivePrefetching());
        
        // Smart Asset Delivery
        $container->set(\FP\PerfSuite\Services\Assets\SmartAssetDelivery::class, static fn() => new \FP\PerfSuite\Services\Assets\SmartAssetDelivery());
        
        // Theme Asset Configuration (gestisce asset specifici per tema/builder)
        $container->set(ThemeAssetConfiguration::class, static fn(ServiceContainer $c) => new ThemeAssetConfiguration($c->get(ThemeDetector::class)));
        
        // Theme Compatibility
        $container->set(ThemeDetector::class, static fn() => new ThemeDetector());
        $container->set(CompatibilityFilters::class, static fn(ServiceContainer $c) => new CompatibilityFilters($c->get(ThemeDetector::class)));
        $container->set(ThemeCompatibility::class, static fn(ServiceContainer $c) => new ThemeCompatibility($c, $c->get(ThemeDetector::class)));
        
        // Smart Intelligence Services
        $container->set(SmartExclusionDetector::class, static fn() => new SmartExclusionDetector());
        $container->set(\FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator(
            $c->get(SmartExclusionDetector::class)
        ));
        
        // Backend Optimizer
        $container->set(BackendOptimizer::class, static fn() => new BackendOptimizer());
        
        // DOM Reflow Optimization Services (v1.4.1)
        $container->set(DOMReflowOptimizer::class, static fn() => new DOMReflowOptimizer());
        $container->set(jQueryOptimizer::class, static fn() => new jQueryOptimizer());
        $container->set(BatchDOMUpdater::class, static fn() => new BatchDOMUpdater());
        
        // Security Services
        $container->set(HtaccessSecurity::class, static fn(ServiceContainer $c) => new HtaccessSecurity($c->get(Htaccess::class), $c->get(Env::class)));

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
        $container->set(CriticalPathOptimizer::class, static fn() => new CriticalPathOptimizer());
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
        
        // Database Optimization Services (v1.4.0) - Registra solo se le classi esistono
        if (class_exists('FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer')) {
            $container->set(DatabaseOptimizer::class, static fn() => new DatabaseOptimizer());
        }
        if (class_exists('FP\\PerfSuite\\Services\\DB\\DatabaseQueryMonitor')) {
            $container->set(DatabaseQueryMonitor::class, static fn() => new DatabaseQueryMonitor());
        }
        if (class_exists('FP\\PerfSuite\\Services\\DB\\PluginSpecificOptimizer')) {
            $container->set(PluginSpecificOptimizer::class, static fn() => new PluginSpecificOptimizer());
        }
        if (class_exists('FP\\PerfSuite\\Services\\DB\\DatabaseReportService')) {
            $container->set(DatabaseReportService::class, static fn() => new DatabaseReportService());
        }
        $container->set(DebugToggler::class, static fn(ServiceContainer $c) => new DebugToggler($c->get(Fs::class), $c->get(Env::class)));
        $container->set(RealtimeLog::class, static fn(ServiceContainer $c) => new RealtimeLog($c->get(DebugToggler::class)));
        $container->set(PresetManager::class, static function (ServiceContainer $c) {
            return new PresetManager(
                $c->get(PageCache::class),
                $c->get(Headers::class),
                $c->get(Optimizer::class),
                $c->get(WebPConverter::class),
                $c->get(Cleaner::class),
                $c->get(DebugToggler::class),
                $c->get(\FP\PerfSuite\Services\Assets\LazyLoadManager::class)
            );
        });
        $container->set(Scorer::class, static function (ServiceContainer $c) {
            return new Scorer(
                $c->get(PageCache::class),
                $c->get(Headers::class),
                $c->get(Optimizer::class),
                $c->get(WebPConverter::class),
                $c->get(Cleaner::class),
                $c->get(DebugToggler::class),
                $c->get(\FP\PerfSuite\Services\Assets\LazyLoadManager::class),
                $c->get(\FP\PerfSuite\Services\Assets\FontOptimizer::class),
                $c->get(\FP\PerfSuite\Services\Assets\ImageOptimizer::class),
                $c->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class),
                $c->get(ObjectCacheManager::class),
                $c->get(\FP\PerfSuite\Services\CDN\CdnManager::class),
                $c->get(\FP\PerfSuite\Services\Assets\CriticalCss::class),
                $c->get(CompressionManager::class)
            );
        });

        $container->set(\FP\PerfSuite\Admin\Assets::class, static fn() => new \FP\PerfSuite\Admin\Assets());
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
        try {
            // Controlli preliminari di sistema
            self::performSystemChecks();

            // Determina la versione del plugin
            $version = defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : '';

            if (!is_string($version) || '' === $version) {
                if (!defined('FP_PERF_SUITE_FILE')) {
                    define('FP_PERF_SUITE_FILE', __DIR__ . '/../fp-performance-suite.php');
                }
                $data = get_file_data(FP_PERF_SUITE_FILE, ['Version' => 'Version']);
                $version = is_array($data) && !empty($data['Version']) ? (string) $data['Version'] : '1.0.0';
            }

            // Salva la versione nelle opzioni
            update_option('fp_perfsuite_version', $version, false);

            // NON inizializziamo lo scheduler durante l'attivazione per evitare caricamento textdomain
            // Cleaner::primeSchedules() registra filtri con __() che caricano textdomain troppo presto
            // Lo scheduler verrà inizializzato automaticamente al primo caricamento del plugin
            // Salviamo solo un flag per indicare che è la prima attivazione
            update_option('fp_perfsuite_needs_scheduler_init', '1', false);

            // Verifica e crea le directory necessarie
            self::ensureRequiredDirectories();

            // Pulisci eventuali errori precedenti
            delete_option('fp_perfsuite_activation_error');

            // Salva log attivazione in option invece che error_log per evitare conflitti
            update_option('fp_perfsuite_activation_log', [
                'version' => $version,
                'timestamp' => time(),
                'status' => 'success'
            ], false);

            // NON usare do_action durante l'attivazione per evitare conflitti con altri plugin
            // L'action hook verrà triggerato al primo caricamento del plugin

        } catch (\Throwable $e) {
            // Gestione sicura degli errori per prevenire white screen
            $errorDetails = self::formatActivationError($e);
            
            if (function_exists('error_log')) {
                error_log(sprintf(
                    '[FP Performance Suite] ERRORE CRITICO durante l\'attivazione: %s in %s:%d',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ));
            }

            // NON tentiamo il recupero automatico durante l'attivazione per evitare conflitti
            // InstallationRecovery usa Logger che chiama do_action(), causando problemi con altri plugin
            // Il recupero avverrà automaticamente al primo caricamento del plugin se necessario
            $errorDetails['recovery_attempted'] = false;
            $errorDetails['recovery_successful'] = false;

            // Salva l'errore nelle opzioni per il debug
            if (function_exists('update_option')) {
                update_option('fp_perfsuite_activation_error', $errorDetails, false);
            }

            // Non rilanciare l'eccezione per evitare white screen
            // Il plugin si attiverà comunque ma senza la configurazione iniziale dello scheduler
        }
    }

    /**
     * Esegue controlli preliminari di sistema prima dell'attivazione
     * 
     * @throws \RuntimeException se i requisiti minimi non sono soddisfatti
     */
    private static function performSystemChecks(): void
    {
        $errors = [];

        // Verifica versione PHP minima
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $errors[] = sprintf(
                'PHP 7.4.0 o superiore è richiesto. Versione corrente: %s',
                PHP_VERSION
            );
        }

        // Verifica estensioni PHP richieste
        $requiredExtensions = ['json', 'mbstring', 'fileinfo'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $errors[] = sprintf('Estensione PHP richiesta non trovata: %s', $ext);
            }
        }

        // Verifica permessi di scrittura (solo se la funzione è disponibile)
        // wp_upload_dir() può triggerare hook che causano problemi durante l'attivazione
        if (function_exists('wp_upload_dir')) {
            $uploadDir = @wp_upload_dir(null, false); // false = non creare directory
            if (is_array($uploadDir) && !empty($uploadDir['basedir']) && file_exists($uploadDir['basedir']) && !is_writable($uploadDir['basedir'])) {
                // Solo warning, non blocchiamo l'attivazione per i permessi
                error_log('[FP Performance Suite] WARNING: Directory uploads non scrivibile: ' . $uploadDir['basedir']);
            }
        }

        // Verifica disponibilità funzioni WordPress critiche
        $requiredFunctions = ['wp_upload_dir', 'update_option', 'add_action', 'get_option'];
        foreach ($requiredFunctions as $func) {
            if (!function_exists($func)) {
                $errors[] = sprintf('Funzione WordPress richiesta non disponibile: %s', $func);
            }
        }

        // Verifica disponibilità classe WP_Filesystem
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if (!empty($errors)) {
            throw new \RuntimeException(
                'Requisiti di sistema non soddisfatti: ' . implode('; ', $errors)
            );
        }
    }

    /**
     * Assicura che le directory necessarie esistano e siano scrivibili
     */
    private static function ensureRequiredDirectories(): void
    {
        // Usa @ per sopprimere warning e false per non triggerare hook di creazione
        if (!function_exists('wp_upload_dir')) {
            return;
        }
        
        $uploadDir = @wp_upload_dir(null, false);
        if (!is_array($uploadDir) || empty($uploadDir['basedir'])) {
            return;
        }
        
        $baseDir = $uploadDir['basedir'];

        $requiredDirs = [
            $baseDir . '/fp-performance-suite',
            $baseDir . '/fp-performance-suite/cache',
            $baseDir . '/fp-performance-suite/logs',
        ];

        foreach ($requiredDirs as $dir) {
            if (!file_exists($dir)) {
                // Usa @ per sopprimere eventuali warning
                @wp_mkdir_p($dir);
                
                // Crea file .htaccess per proteggere le directory
                $htaccessFile = $dir . '/.htaccess';
                if (!file_exists($htaccessFile)) {
                    @file_put_contents($htaccessFile, "Order deny,allow\nDeny from all\n");
                }
            }
        }
    }

    /**
     * Formatta i dettagli dell'errore di attivazione
     * 
     * @param \Throwable $e L'eccezione catturata
     * @return array Dettagli dell'errore formattati
     */
    private static function formatActivationError(\Throwable $e): array
    {
        $errorType = 'unknown';
        $solution = 'Contatta il supporto con i dettagli dell\'errore.';

        // Identifica il tipo di errore e suggerisci una soluzione
        $message = $e->getMessage();
        
        if (strpos($message, 'PHP') !== false && strpos($message, 'version') !== false) {
            $errorType = 'php_version';
            $solution = 'Aggiorna PHP alla versione 7.4 o superiore tramite il pannello di hosting.';
        } elseif (strpos($message, 'extension') !== false || strpos($message, 'Estensione') !== false) {
            $errorType = 'php_extension';
            $solution = 'Abilita le estensioni PHP richieste (json, mbstring, fileinfo) tramite il pannello di hosting.';
        } elseif (strpos($message, 'permission') !== false || strpos($message, 'scrivibile') !== false) {
            $errorType = 'permissions';
            $solution = 'Verifica i permessi delle directory. La directory wp-content/uploads deve essere scrivibile (chmod 755 o 775).';
        } elseif (strpos($message, 'Class') !== false && strpos($message, 'not found') !== false) {
            $errorType = 'missing_class';
            $solution = 'Reinstalla il plugin assicurandoti che tutti i file siano stati caricati correttamente.';
        } elseif (strpos($message, 'memory') !== false) {
            $errorType = 'memory_limit';
            $solution = 'Aumenta il limite di memoria PHP (memory_limit) a almeno 128MB nel file php.ini.';
        }

        return [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'time' => time(),
            'type' => $errorType,
            'solution' => $solution,
            'trace' => $e->getTraceAsString(),
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
        ];
    }

    public static function onDeactivate(): void
    {
        wp_clear_scheduled_hook(Cleaner::CRON_HOOK);
        Logger::info('Plugin deactivated');
        do_action('fp_ps_plugin_deactivated');
    }
}
