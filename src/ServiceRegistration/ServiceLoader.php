<?php

namespace FP\PerfSuite\ServiceRegistration;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\ServiceContainerAdapter;
use FP\PerfSuite\Utils\OptionHelper;
use FP\PerfSuite\Utils\HostingDetector;
use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Admin\NoticeManager;
use FP\PerfSuite\Plugin;

/**
 * Service Loader - Gestisce il lazy loading dei servizi basato sulle opzioni
 * 
 * Works with both ServiceContainer and ServiceContainerAdapter (kernel container).
 * 
 * @package FP\PerfSuite\ServiceRegistration
 * @author Francesco Passeri
 */
class ServiceLoader
{
    /** @var ServiceContainer|ServiceContainerAdapter */
    private $container;

    /**
     * Constructor
     * 
     * @param ServiceContainer|ServiceContainerAdapter $container Service container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Carica tutti i servizi abilitati
     */
    public function loadEnabledServices(): void
    {
        $this->loadCoreServices();
        $this->loadAssetServices();
        $this->loadCacheServices();
        $this->loadDatabaseServices();
        $this->loadCompatibilityServices();
        $this->loadMobileServices();
        $this->loadMLServices();
        $this->loadIntelligenceServices();
        $this->loadMonitoringServices();
        $this->loadAdvancedServices();
        $this->loadAlwaysActiveServices();
    }

    /**
     * Carica servizi core
     */
    private function loadCoreServices(): void
    {
        // Page Cache
        if (OptionHelper::isEnabled('fp_ps_page_cache_settings')) {
            $this->loadService(\FP\PerfSuite\Services\Cache\PageCache::class);
        }

        // Browser Cache / Headers
        if (OptionHelper::isEnabled('fp_ps_browser_cache')) {
            $this->loadService(\FP\PerfSuite\Services\Cache\Headers::class);
        }

        // Assets Optimizer
        if (OptionHelper::isEnabled('fp_ps_assets') || get_option('fp_ps_asset_optimization_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\Optimizer::class);
        }

        // Database Cleaner (solo se schedulato)
        $dbSettings = OptionHelper::get('fp_ps_db', []);
        if (isset($dbSettings['schedule']) && $dbSettings['schedule'] !== 'manual') {
            $this->loadService(\FP\PerfSuite\Services\DB\Cleaner::class);
        }
    }

    /**
     * Carica servizi asset
     */
    private function loadAssetServices(): void
    {
        // Batch DOM Updates
        if (get_option('fp_ps_batch_dom_updates_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\BatchDOMUpdater::class);
        }

        // CSS Optimization
        if (get_option('fp_ps_css_optimization_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\CSSOptimizer::class);
        }

        // jQuery Optimization
        if (get_option('fp_ps_jquery_optimization_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\jQueryOptimizer::class);
        }

        // Predictive Prefetching
        if (OptionHelper::isEnabled('fp_ps_predictive_prefetch')) {
            $this->loadService(\FP\PerfSuite\Services\Assets\PredictivePrefetching::class);
        }

        // Third-Party Script Management
        if (OptionHelper::isEnabled('fp_ps_third_party_scripts')) {
            $this->loadService(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class);
        }

        // Third-Party Script Detector
        if (get_option('fp_ps_third_party_detector_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector::class);
        }

        // Instant Page Loader
        if (OptionHelper::isEnabled('fp_ps_instant_page')) {
            $this->loadService(\FP\PerfSuite\Services\Assets\InstantPageLoader::class);
        }

        // Embed Facades
        if (OptionHelper::isEnabled('fp_ps_embed_facades')) {
            $this->loadService(\FP\PerfSuite\Services\Assets\EmbedFacades::class);
        }

        // Delayed JavaScript
        if (OptionHelper::isEnabled('fp_ps_delay_js')) {
            $this->loadService(\FP\PerfSuite\Services\Assets\DelayedJavaScriptExecutor::class);
        }

        // Lazy Loading
        $responsiveSettings = OptionHelper::get('fp_ps_responsive_images', []);
        if (!empty($responsiveSettings['enable_lazy_loading'])) {
            Plugin::registerServiceOnce(\FP\PerfSuite\Services\Assets\LazyLoadManager::class, function() {
                $this->container->get(\FP\PerfSuite\Services\Assets\LazyLoadManager::class)->init();
            });
        }

        // Font Optimization
        $fontOptimizationEnabled = get_option('fp_ps_font_optimization_enabled', false);
        $fontSettings = OptionHelper::get('fp_ps_font_optimization', []);
        $criticalPathSettings = OptionHelper::get('fp_ps_critical_path_optimization', []);
        
        if ($fontOptimizationEnabled || !empty($fontSettings['enabled']) || !empty($criticalPathSettings['enabled'])) {
            $this->loadService(\FP\PerfSuite\Services\Assets\FontOptimizer::class);
        }

        // Image Optimization
        if (get_option('fp_ps_image_optimization_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\ImageOptimizer::class);
        }

        // Auto Font Optimization
        if (get_option('fp_ps_auto_font_optimization_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\AutoFontOptimizer::class);
        }

        // Lighthouse Font Optimization
        if (get_option('fp_ps_lighthouse_font_optimization_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\LighthouseFontOptimizer::class);
        }

        // Advanced JavaScript Optimizers
        $unusedJSSettings = OptionHelper::get('fp_ps_js_unused_optimizer', []);
        if (!empty($unusedJSSettings['enabled'])) {
            $this->loadService(\FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer::class);
        }

        $codeSplittingSettings = OptionHelper::get('fp_ps_js_code_splitter', []);
        if (!empty($codeSplittingSettings['enabled'])) {
            $this->loadService(\FP\PerfSuite\Services\Assets\CodeSplittingManager::class);
        }

        $treeShakerSettings = OptionHelper::get('fp_ps_js_tree_shaker', []);
        if (!empty($treeShakerSettings['enabled'])) {
            $this->loadService(\FP\PerfSuite\Services\Assets\JavaScriptTreeShaker::class);
        }

        // Critical Path Optimizer
        $criticalPathSettings = OptionHelper::get('fp_ps_critical_path_optimization', []);
        $assetsSettings = OptionHelper::get('fp_ps_assets', []);
        if (!empty($criticalPathSettings['enabled']) || !empty($assetsSettings['optimize_google_fonts'])) {
            $this->loadService(\FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class);
        }
    }

    /**
     * Carica servizi cache
     */
    private function loadCacheServices(): void
    {
        // Object Cache
        if (get_option('fp_ps_object_cache_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Cache\ObjectCacheManager::class);
        }

        // Edge Cache
        if (get_option('fp_ps_edge_cache_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
        }

        // External Resource Cache
        if (OptionHelper::isEnabled('fp_ps_external_cache')) {
            $this->loadService(\FP\PerfSuite\Services\Assets\ExternalResourceCacheManager::class);
        }
    }

    /**
     * Carica servizi database
     */
    private function loadDatabaseServices(): void
    {
        $dbSettings = OptionHelper::get('fp_ps_db', []);
        
        if (!empty($dbSettings['enabled'])) {
            $this->loadService(\FP\PerfSuite\Services\DB\DatabaseOptimizer::class);
            $this->loadService(\FP\PerfSuite\Services\DB\DatabaseQueryMonitor::class);
            $this->loadService(\FP\PerfSuite\Services\DB\PluginSpecificOptimizer::class);
            $this->loadService(\FP\PerfSuite\Services\DB\DatabaseReportService::class);
        }

        // Query Cache
        $queryCacheSettings = OptionHelper::get('fp_ps_query_cache', []);
        if (!empty($queryCacheSettings['enabled']) || !empty($dbSettings['query_cache_enabled'])) {
            $this->loadService(\FP\PerfSuite\Services\DB\QueryCacheManager::class);
        }
    }

    /**
     * Carica servizi compatibilità
     */
    private function loadCompatibilityServices(): void
    {
        if (get_option('fp_ps_compatibility_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Compatibility\ThemeCompatibility::class);
            $this->loadService(\FP\PerfSuite\Services\Compatibility\CompatibilityFilters::class);
            $this->loadService(\FP\PerfSuite\Services\Compatibility\SalientWPBakeryOptimizer::class);
        }

        // WooCommerce Optimizer
        if (OptionHelper::isEnabled('fp_ps_woocommerce')) {
            $this->loadService(\FP\PerfSuite\Services\Compatibility\WooCommerceOptimizer::class);
        }
    }

    /**
     * Carica servizi mobile
     */
    private function loadMobileServices(): void
    {
        if (OptionHelper::isEnabled('fp_ps_mobile_optimizer')) {
            $this->loadService(\FP\PerfSuite\Services\Mobile\MobileOptimizer::class);
        }

        if (OptionHelper::isEnabled('fp_ps_touch_optimizer')) {
            $this->loadService(\FP\PerfSuite\Services\Mobile\TouchOptimizer::class);
        }

        if (OptionHelper::isEnabled('fp_ps_mobile_cache')) {
            $this->loadService(\FP\PerfSuite\Services\Mobile\MobileCacheManager::class);
        }

        if (OptionHelper::isEnabled('fp_ps_responsive_images')) {
            $this->loadService(\FP\PerfSuite\Services\Mobile\ResponsiveImageManager::class);
        }
    }

    /**
     * Carica servizi ML
     */
    private function loadMLServices(): void
    {
        $mlSettings = OptionHelper::get('fp_ps_ml_predictor', []);
        
        if (!empty($mlSettings['enabled'])) {
            if (HostingDetector::canEnableService('MLPredictor')) {
                $this->loadService(\FP\PerfSuite\Services\ML\MLPredictor::class);
                $this->loadService(\FP\PerfSuite\Services\ML\AutoTuner::class);
            } else {
                Logger::warning('ML Services disabilitati: ambiente shared hosting rilevato. Richiede VPS/Dedicated con almeno 512MB RAM.');
                NoticeManager::warning('I servizi ML (Machine Learning) sono disabilitati automaticamente su shared hosting per evitare timeout e sovraccarichi. Per utilizzarli, passa a VPS o hosting dedicato.');
            }
        }
    }

    /**
     * Carica servizi intelligence
     */
    private function loadIntelligenceServices(): void
    {
        if (get_option('fp_ps_intelligence_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Intelligence\SmartExclusionDetector::class);
            $this->loadService(\FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator::class);
            $this->loadService(\FP\PerfSuite\Services\Intelligence\PerformanceBasedExclusionDetector::class);
            $this->loadService(\FP\PerfSuite\Services\Intelligence\CacheAutoConfigurator::class);
            $this->loadService(\FP\PerfSuite\Services\Intelligence\IntelligenceReporter::class);
            $this->loadService(\FP\PerfSuite\Services\Intelligence\AssetOptimizationIntegrator::class);
            $this->loadService(\FP\PerfSuite\Services\Intelligence\CDNExclusionSync::class);
        }
    }

    /**
     * Carica servizi monitoring
     */
    private function loadMonitoringServices(): void
    {
        if (get_option('fp_ps_monitoring_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Monitoring\PerformanceMonitor::class);
            $this->loadService(\FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor::class);
        }

        if (get_option('fp_ps_reports_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Reports\ScheduledReports::class);
        }
    }

    /**
     * Carica servizi avanzati
     */
    private function loadAdvancedServices(): void
    {
        // Backend Optimizer - SEMPRE caricato (controlla internamente se abilitato)
        // Questo permette al servizio di rispondere ai cambiamenti delle impostazioni
        $this->loadService(\FP\PerfSuite\Services\Admin\BackendOptimizer::class);

        // Compression
        if (get_option('fp_ps_compression_enabled', false) || 
            get_option('fp_ps_compression_deflate_enabled', false) || 
            get_option('fp_ps_compression_brotli_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Compression\CompressionManager::class);
        }

        // CDN
        if (OptionHelper::isEnabled('fp_ps_cdn')) {
            $this->loadService(\FP\PerfSuite\Services\CDN\CdnManager::class);
        }

        // Security
        $securitySettings = OptionHelper::get('fp_ps_htaccess_security', []);
        if (!empty($securitySettings['enabled'])) {
            if (HostingDetector::canEnableService('HtaccessSecurity')) {
                $this->loadService(\FP\PerfSuite\Services\Security\HtaccessSecurity::class);
            } else {
                Logger::warning('HtaccessSecurity disabilitato: file .htaccess non scrivibile o permessi insufficienti');
                NoticeManager::warning('Le regole di sicurezza .htaccess non possono essere applicate automaticamente. Verifica i permessi del file .htaccess o applicale manualmente.');
            }
        }

        // PWA
        if (get_option('fp_ps_pwa_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\PWA\ServiceWorkerManager::class);
        }

        // HTTP/2
        if (get_option('fp_ps_http2_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\Http2ServerPush::class);
        }

        // Critical CSS
        if (get_option('fp_ps_critical_css_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\CriticalCss::class);
            $this->loadService(\FP\PerfSuite\Services\Assets\CriticalCssAutomation::class);
        }

        // Smart Delivery
        if (get_option('fp_ps_smart_delivery_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\SmartAssetDelivery::class);
        }

        // Advanced Assets
        if (get_option('fp_ps_html_minification_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\HtmlMinifier::class);
        }

        if (get_option('fp_ps_script_optimization_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\ScriptOptimizer::class);
        }

        if (get_option('fp_ps_wordpress_optimization_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\WordPressOptimizer::class);
        }

        if (get_option('fp_ps_resource_hints_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager::class);
        }

        if (get_option('fp_ps_dependency_resolution_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Assets\Combiners\DependencyResolver::class);
        }

        // AI
        if (get_option('fp_ps_ai_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\AI\Analyzer::class);
        }

        // Presets
        if (get_option('fp_ps_presets_enabled', false)) {
            $this->loadService(\FP\PerfSuite\Services\Presets\Manager::class);
        }
    }

    /**
     * Carica servizi sempre attivi
     */
    private function loadAlwaysActiveServices(): void
    {
        // FP Plugins Integration - SEMPRE attivo
        $this->loadService(\FP\PerfSuite\Services\Compatibility\FPPluginsIntegration::class);

        // Scoring Services - Sempre attivo per calcolo score
        $this->loadService(\FP\PerfSuite\Services\Score\Scorer::class);

        // Performance Analysis Services - Sempre attivi
        $this->loadService(\FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer::class);
        $this->loadService(\FP\PerfSuite\Services\Monitoring\RecommendationApplicator::class);

        // Advanced Assets Optimization Services - Sempre attivi
        $this->loadService(\FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer::class);
        $this->loadService(\FP\PerfSuite\Services\Assets\ResponsiveImageAjaxHandler::class);
        $this->loadService(\FP\PerfSuite\Services\Assets\UnusedCSSOptimizer::class);
        $this->loadService(\FP\PerfSuite\Services\Assets\RenderBlockingOptimizer::class);
        $this->loadService(\FP\PerfSuite\Services\Assets\DOMReflowOptimizer::class);

        // Theme Services - Sempre attivi
        $this->loadService(\FP\PerfSuite\Services\Assets\ThemeAssetConfiguration::class);
        $this->loadService(\FP\PerfSuite\Services\Compatibility\ThemeDetector::class);
    }

    /**
     * Carica un servizio se non è già stato caricato
     * 
     * @param string $serviceClass Classe del servizio
     */
    private function loadService(string $serviceClass): void
    {
        Plugin::registerServiceOnce($serviceClass, function() use ($serviceClass) {
            $service = $this->container->get($serviceClass);
            // Se il servizio ha il metodo register, chiamalo
            if (method_exists($service, 'register')) {
                $service->register();
            }
        });
    }
}
















