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
use FP\PerfSuite\Utils\InitializationMonitor;

use function get_file_data;
use function wp_clear_scheduled_hook;

class Plugin
{
    private static ?ServiceContainer $container = null;
    private static bool $initialized = false;

    public static function init(): void
    {
        // Usa il monitor per prevenire inizializzazioni multiple
        if (InitializationMonitor::isInitialized() || self::$container instanceof ServiceContainer) {
            InitializationMonitor::markAsInitialized('Plugin::init (duplicate)');
            return;
        }
        
        // Marca come inizializzato usando il monitor
        InitializationMonitor::markAsInitialized('Plugin::init');
        self::$initialized = true;

        $container = new ServiceContainer();
        self::register($container);
        self::$container = $container;

        Logger::debug('Plugin initialized', ['version' => FP_PERF_SUITE_VERSION]);
        do_action('fp_perfsuite_container_ready', $container);

        $container->get(Menu::class)->boot();
        $container->get(AdminAssets::class)->boot();
        $container->get(AdminBar::class)->boot();
        AdminBar::registerActions();
        $container->get(Routes::class)->boot();

        // Carica le traduzioni il prima possibile per evitare errori
        add_action('plugins_loaded', static function () {
            load_plugin_textdomain('fp-performance-suite', false, dirname(plugin_basename(FP_PERF_SUITE_FILE)) . '/languages');
        }, 1);
        
        add_action('init', static function () use ($container) {

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
            $container->get(ThemeAssetConfiguration::class)->register();
            $container->get(CompressionManager::class)->register();
            
            // JavaScript Optimization Services (v1.4.0)
            $container->get(\FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer::class)->register();
            $container->get(\FP\PerfSuite\Services\Assets\CodeSplittingManager::class)->register();
            $container->get(\FP\PerfSuite\Services\Assets\JavaScriptTreeShaker::class)->register();
            
            // Auto Font Optimization Services (v1.5.0) - Sistema di auto-rilevamento
            if ($container->has(\FP\PerfSuite\Services\Assets\AutoFontOptimizer::class)) {
                $container->get(\FP\PerfSuite\Services\Assets\AutoFontOptimizer::class)->register();
            }
            if ($container->has(\FP\PerfSuite\Services\Assets\LighthouseFontOptimizer::class)) {
                $container->get(\FP\PerfSuite\Services\Assets\LighthouseFontOptimizer::class)->register();
            }
            
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
        
        // JavaScript Optimization Services (v1.4.0)
        $container->set(\FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer());
        $container->set(\FP\PerfSuite\Services\Assets\CodeSplittingManager::class, static fn() => new \FP\PerfSuite\Services\Assets\CodeSplittingManager());
        $container->set(\FP\PerfSuite\Services\Assets\JavaScriptTreeShaker::class, static fn() => new \FP\PerfSuite\Services\Assets\JavaScriptTreeShaker());
        
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
            $converter = new WebPConverter(
                $c->get(Fs::class),
                $c->get(RateLimiter::class),
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPImageConverter::class),
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPQueue::class),
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPAttachmentProcessor::class),
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPBatchProcessor::class),
                $c->get(\FP\PerfSuite\Services\Media\WebP\WebPPathHelper::class)
            );
            
            // Inietta automaticamente il CompatibilityManager se disponibile
            if (class_exists('FP\PerfSuite\Services\Compatibility\WebPPluginCompatibility')) {
                $converter->setCompatibilityManager($c->get(WebPPluginCompatibility::class));
            }
            
            return $converter;
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

            // Inizializza lo scheduler del database cleaner solo se le classi sono disponibili
            if (class_exists('FP\PerfSuite\Services\DB\Cleaner') && 
                class_exists('FP\PerfSuite\Utils\Env') && 
                class_exists('FP\PerfSuite\Utils\RateLimiter')) {
                
                $cleaner = new Cleaner(new Env(), new RateLimiter());
                $cleaner->primeSchedules();
                $cleaner->maybeSchedule(true);
            }

            // Verifica e crea le directory necessarie
            self::ensureRequiredDirectories();

            // Pulisci eventuali errori precedenti
            delete_option('fp_perfsuite_activation_error');

            // Log sicuro dell'attivazione
            if (class_exists('FP\PerfSuite\Utils\Logger')) {
                Logger::info('Plugin activated', ['version' => $version]);
            }

            // Trigger action hook se le funzioni WordPress sono disponibili
            if (function_exists('do_action')) {
                do_action('fp_ps_plugin_activated', $version);
            }

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

            // Tenta il recupero automatico
            if (class_exists('FP\PerfSuite\Utils\InstallationRecovery')) {
                $recovered = InstallationRecovery::attemptRecovery($errorDetails);
                if ($recovered) {
                    $errorDetails['recovery_attempted'] = true;
                    $errorDetails['recovery_successful'] = true;
                    Logger::info('Automatic recovery successful');
                } else {
                    $errorDetails['recovery_attempted'] = true;
                    $errorDetails['recovery_successful'] = false;
                }
            }

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
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            $errors[] = sprintf(
                'PHP 8.0.0 o superiore è richiesto. Versione corrente: %s',
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
        if (!empty($uploadDir['basedir']) && !is_writable($uploadDir['basedir'])) {
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
        $baseDir = $uploadDir['basedir'];
        
        if (empty($baseDir)) {
            return;
        }

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
