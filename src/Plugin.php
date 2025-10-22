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
use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Cache\ObjectCacheManager;
use FP\PerfSuite\Services\Cache\EdgeCacheManager;
use FP\PerfSuite\Services\Compression\CompressionManager;
use FP\PerfSuite\Services\Compatibility\ThemeCompatibility;
use FP\PerfSuite\Services\Compatibility\ThemeDetector;
use FP\PerfSuite\Services\Compatibility\CompatibilityFilters;
use FP\PerfSuite\Services\Compatibility\WebPPluginCompatibility;
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

        // Solo admin e routes - NIENTE altro durante init
        // I servizi verranno caricati lazy quando servono
        if (is_admin()) {
            $container->get(Menu::class)->boot();
            $container->get(AdminAssets::class)->boot();
            $container->get(AdminBar::class)->boot();
            AdminBar::registerActions();
        }
        
        $container->get(Routes::class)->boot();

        add_action('init', static function () use ($container) {
            load_plugin_textdomain('fp-performance-suite', false, dirname(plugin_basename(FP_PERF_SUITE_FILE)) . '/languages');

            // CARICAMENTO LAZY - Solo servizi essenziali per ridurre memory footprint
            // Gli altri servizi si registrano solo se le loro opzioni sono abilitate
            
            // Core services (sempre attivi)
            $container->get(PageCache::class)->register();
            $container->get(Headers::class)->register();
            
            // Optimizer e WebP solo se abilitati nelle opzioni
            if (get_option('fp_ps_asset_optimization_enabled', false)) {
                $container->get(Optimizer::class)->register();
            }
            if (get_option('fp_ps_webp_enabled', false)) {
                $container->get(WebPConverter::class)->register();
            }
            
            // Database cleaner solo se schedulato
            $dbSettings = get_option('fp_ps_db', []);
            if (isset($dbSettings['schedule']) && $dbSettings['schedule'] !== 'manual') {
                $container->get(Cleaner::class)->register();
            }
            
            // Theme Compatibility (essenziale per funzionamento)
            $container->get(ThemeCompatibility::class)->register();
            $container->get(CompatibilityFilters::class)->register();
            
            // Ottimizzatori Assets Avanzati (Ripristinato 21 Ott 2025 - FASE 2)
            // Registrati solo se le loro opzioni sono abilitate
            if (get_option('fp_ps_batch_dom_updates_enabled', false)) {
                $container->get(\FP\PerfSuite\Services\Assets\BatchDOMUpdater::class)->register();
            }
            if (get_option('fp_ps_css_optimization_enabled', false)) {
                $container->get(\FP\PerfSuite\Services\Assets\CSSOptimizer::class)->register();
            }
            if (get_option('fp_ps_jquery_optimization_enabled', false)) {
                $container->get(\FP\PerfSuite\Services\Assets\jQueryOptimizer::class)->register();
            }
        });
        
        // Handler AJAX (Ripristinato 21 Ott 2025 - FASE 2)
        // Registrati solo durante richieste AJAX per ottimizzare performance
        if (defined('DOING_AJAX') && DOING_AJAX) {
            add_action('init', static function () use ($container) {
                $container->get(\FP\PerfSuite\Http\Ajax\RecommendationsAjax::class)->register();
                $container->get(\FP\PerfSuite\Http\Ajax\WebPAjax::class)->register();
                $container->get(\FP\PerfSuite\Http\Ajax\CriticalCssAjax::class)->register();
                $container->get(\FP\PerfSuite\Http\Ajax\AIConfigAjax::class)->register();
            }, 5);
        }

        // Register WP-CLI commands
        if (defined('WP_CLI') && WP_CLI) {
            self::registerCliCommands();
        }

        // Register Site Health checks
        HealthCheck::register();

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
            define('FP_PERF_SUITE_FILE', dirname(__DIR__) . '/fp-performance-suite.php');
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
        
        // Auto Font Optimization Services (v1.5.0) - Sistema di auto-rilevamento
        $container->set(\FP\PerfSuite\Services\Assets\AutoFontOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\AutoFontOptimizer());
        $container->set(\FP\PerfSuite\Services\Assets\LighthouseFontOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\LighthouseFontOptimizer());
        
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
        
        // Recommendation Applicator
        $container->set(\FP\PerfSuite\Services\Monitoring\RecommendationApplicator::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Monitoring\RecommendationApplicator(
                $c->get(PageCache::class),
                $c->get(Headers::class),
                $c->get(Optimizer::class),
                $c->get(Cleaner::class)
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
        
        // Third-Party Script Detector (AI Auto-detect)
        $container->set(\FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector(
            $c->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class)
        ));
        
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
        
        // Responsive Image Optimizer (Ripristinato 21 Ott 2025)
        $container->set(\FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer());
        $container->set(\FP\PerfSuite\Services\Assets\ResponsiveImageAjaxHandler::class, static fn() => new \FP\PerfSuite\Services\Assets\ResponsiveImageAjaxHandler());
        
        // Unused CSS Optimizer (Ripristinato 21 Ott 2025)
        $container->set(\FP\PerfSuite\Services\Assets\UnusedCSSOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\UnusedCSSOptimizer());
        
        // Render Blocking Optimizer (Ripristinato 21 Ott 2025)
        $container->set(\FP\PerfSuite\Services\Assets\RenderBlockingOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\RenderBlockingOptimizer());
        
        // Critical Path Optimizer (Ripristinato 21 Ott 2025)
        $container->set(\FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\CriticalPathOptimizer());
        
        // DOM Reflow Optimizer (Ripristinato 21 Ott 2025)
        $container->set(\FP\PerfSuite\Services\Assets\DOMReflowOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\DOMReflowOptimizer());
        
        // AI Analyzer (Ripristinato 21 Ott 2025)
        $container->set(\FP\PerfSuite\Services\AI\Analyzer::class, static fn() => new \FP\PerfSuite\Services\AI\Analyzer());
        
        // Ottimizzatori Assets Avanzati (Ripristinato 21 Ott 2025 - FASE 2)
        $container->set(\FP\PerfSuite\Services\Assets\BatchDOMUpdater::class, static fn() => new \FP\PerfSuite\Services\Assets\BatchDOMUpdater());
        $container->set(\FP\PerfSuite\Services\Assets\CSSOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\CSSOptimizer());
        $container->set(\FP\PerfSuite\Services\Assets\jQueryOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\jQueryOptimizer());
        
        // Handler AJAX (Ripristinato 21 Ott 2025 - FASE 2)
        $container->set(\FP\PerfSuite\Http\Ajax\RecommendationsAjax::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Http\Ajax\RecommendationsAjax($c));
        $container->set(\FP\PerfSuite\Http\Ajax\WebPAjax::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Http\Ajax\WebPAjax($c));
        $container->set(\FP\PerfSuite\Http\Ajax\CriticalCssAjax::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Http\Ajax\CriticalCssAjax($c));
        $container->set(\FP\PerfSuite\Http\Ajax\AIConfigAjax::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Http\Ajax\AIConfigAjax($c));
        
        // EdgeCache Providers (Ripristinato 21 Ott 2025 - FASE 2) - Architettura modulare SOLID
        $container->set(\FP\PerfSuite\Services\Cache\EdgeCache\CloudflareProvider::class, static function (ServiceContainer $c) {
            $settings = $c->get(EdgeCacheManager::class)->settings()['cloudflare'] ?? [];
            return new \FP\PerfSuite\Services\Cache\EdgeCache\CloudflareProvider(
                $settings['api_token'] ?? '',
                $settings['zone_id'] ?? '',
                $settings['email'] ?? ''
            );
        });
        $container->set(\FP\PerfSuite\Services\Cache\EdgeCache\CloudFrontProvider::class, static function (ServiceContainer $c) {
            $settings = $c->get(EdgeCacheManager::class)->settings()['cloudfront'] ?? [];
            return new \FP\PerfSuite\Services\Cache\EdgeCache\CloudFrontProvider(
                $settings['access_key_id'] ?? '',
                $settings['secret_access_key'] ?? '',
                $settings['distribution_id'] ?? '',
                $settings['region'] ?? 'us-east-1'
            );
        });
        $container->set(\FP\PerfSuite\Services\Cache\EdgeCache\FastlyProvider::class, static function (ServiceContainer $c) {
            $settings = $c->get(EdgeCacheManager::class)->settings()['fastly'] ?? [];
            return new \FP\PerfSuite\Services\Cache\EdgeCache\FastlyProvider(
                $settings['api_key'] ?? '',
                $settings['service_id'] ?? ''
            );
        });
        
        // Theme Asset Configuration (gestisce asset specifici per tema/builder)
        $container->set(ThemeAssetConfiguration::class, static fn(ServiceContainer $c) => new ThemeAssetConfiguration($c->get(ThemeDetector::class)));
        
        // Theme Compatibility
        $container->set(ThemeDetector::class, static fn() => new ThemeDetector());
        $container->set(CompatibilityFilters::class, static fn(ServiceContainer $c) => new CompatibilityFilters($c->get(ThemeDetector::class)));
        $container->set(ThemeCompatibility::class, static fn(ServiceContainer $c) => new ThemeCompatibility($c, $c->get(ThemeDetector::class)));
        
        // WebP Plugin Compatibility
        $container->set(WebPPluginCompatibility::class, static fn() => new WebPPluginCompatibility());
        
        // Smart Intelligence Services
        $container->set(SmartExclusionDetector::class, static fn() => new SmartExclusionDetector());
        $container->set(\FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator(
            $c->get(SmartExclusionDetector::class)
        ));
        
        // Backend Optimizer
        $container->set(BackendOptimizer::class, static fn() => new BackendOptimizer());
        
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
        // Aumenta memory limit temporaneamente per l'attivazione
        @ini_set('memory_limit', '768M');
        
        // Attivazione minimale - solo operazioni essenziali
        try {
            // Salva versione
            $version = defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : '1.5.0';
            update_option('fp_perfsuite_version', $version, false);
            
            // Pulisci errori precedenti
            delete_option('fp_perfsuite_activation_error');
            
            // Crea directory (non bloccare se fallisce)
            try {
                self::ensureRequiredDirectories();
            } catch (\Throwable $e) {
                // Ignora errori directory
            }
            
            // Trigger hook
            do_action('fp_ps_plugin_activated', $version);

        } catch (\Throwable $e) {
            // Salva errore silenziosamente
            update_option('fp_perfsuite_activation_error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'time' => time(),
            ], false);
            
            // NON bloccare l'attivazione
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

        // Verifica permessi di scrittura
        $uploadDir = wp_upload_dir();
        if (is_array($uploadDir) && !empty($uploadDir['basedir']) && !is_writable($uploadDir['basedir'])) {
            $errors[] = sprintf(
                'Directory uploads non scrivibile: %s. Verifica i permessi.',
                $uploadDir['basedir']
            );
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
        $uploadDir = wp_upload_dir();
        
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
                wp_mkdir_p($dir);
                
                // Crea file .htaccess per proteggere le directory
                $htaccessFile = $dir . '/.htaccess';
                if (!file_exists($htaccessFile)) {
                    file_put_contents($htaccessFile, "Order deny,allow\nDeny from all\n");
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
