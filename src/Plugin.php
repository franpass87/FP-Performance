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
use FP\PerfSuite\Services\Assets\ExternalResourceCacheManager;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Cache\ObjectCacheManager;
use FP\PerfSuite\Services\Cache\EdgeCacheManager;
use FP\PerfSuite\Services\Compression\CompressionManager;
use FP\PerfSuite\Services\Compatibility\ThemeCompatibility;
use FP\PerfSuite\Services\Compatibility\ThemeDetector;
use FP\PerfSuite\Services\Compatibility\CompatibilityFilters;
use FP\PerfSuite\Services\Compatibility\SalientWPBakeryOptimizer;
use FP\PerfSuite\Services\Compatibility\FPPluginsIntegration;
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
use FP\PerfSuite\Utils\HostingDetector;

use function get_file_data;
use function wp_clear_scheduled_hook;

class Plugin
{
    private static ?ServiceContainer $container = null;
    private static array $registeredServices = [];

    public static function init(): void
    {
        // FIX RACE CONDITION: Usa SOLO il container come flag atomico
        // Se già inizializzato, esci immediatamente
        if (self::$container !== null) {
            return;
        }
        
        // Aumenta temporaneamente i limiti per l'inizializzazione
        // Usa limiti dinamici basati sul tipo di hosting
        $original_memory_limit = ini_get('memory_limit');
        $original_time_limit = ini_get('max_execution_time');
        
        // Limiti raccomandati in base all'ambiente
        $recommended_memory = HostingDetector::getRecommendedMemoryLimit();
        $recommended_time = HostingDetector::getRecommendedTimeLimit();
        
        try {
            @ini_set('memory_limit', $recommended_memory);
            @ini_set('max_execution_time', (string) $recommended_time);
            
            // Crea container temporaneo
            $container = new ServiceContainer();
            
            // Registra tutti i servizi
            self::register($container);
            
            // ATOMICO: Assegna il container SOLO dopo che è completamente inizializzato
            // Questo previene race conditions
            self::$container = $container;
            
        } finally {
            // Ripristina i limiti originali
            if ($original_memory_limit) {
                @ini_set('memory_limit', $original_memory_limit);
            }
            if ($original_time_limit) {
                @ini_set('max_execution_time', $original_time_limit);
            }
        }

        // Carica servizi admin se siamo nell'admin
        if (is_admin()) {
            try {
                $container->get(Menu::class)->boot();
                $container->get(AdminAssets::class)->boot();
                $container->get(AdminBar::class)->boot();
                AdminBar::registerActions();
            } catch (\Throwable $e) {
                if (defined('FP_PERF_DEBUG') && FP_PERF_DEBUG) {
                    error_log("[FP-PerfSuite] ERROR in admin services: " . $e->getMessage());
                }
                Logger::error("Error in admin mode: " . $e->getMessage());
            }
        }
        
        // Routes: carica SOLO se admin o API request
        if (is_admin() || (function_exists('wp_is_json_request') && wp_is_json_request())) {
            $container->get(Routes::class)->boot();
        }
        
        // Shortcodes: carica SOLO in frontend
        if (!is_admin()) {
            $container->get(Shortcodes::class)->boot();
        }

        add_action('init', static function () use ($container) {
            load_plugin_textdomain('fp-performance-suite', false, dirname(plugin_basename(FP_PERF_SUITE_FILE)) . '/languages');
            
            // Inizializza opzioni di default per utenti esistenti (solo se non esistono)
            self::ensureDefaultOptionsExist();
            
            // Fix critico per pagina mobile - Forza inizializzazione se necessario
            self::forceMobileOptionsInitialization();
            
            // Aggiungi cron schedules personalizzati per ML
            add_filter('cron_schedules', function($schedules) {
                $schedules['fp_ps_6hourly'] = [
                    'interval' => 6 * HOUR_IN_SECONDS,
                    'display' => __('Every 6 Hours (FP Performance ML)', 'fp-performance-suite'),
                ];
                return $schedules;
            });

            // LOGGING AMPIO PER DEBUG - Identificare causa pagine vuote
            Logger::debug("=== PLUGIN INIT DEBUG ===");
            Logger::debug("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
            Logger::debug("WP_ADMIN defined: " . (defined('WP_ADMIN') ? 'YES' : 'NO'));
            Logger::debug("WP_ADMIN value: " . (defined('WP_ADMIN') ? (WP_ADMIN ? 'TRUE' : 'FALSE') : 'undefined'));
            Logger::debug("is_admin(): " . (is_admin() ? 'TRUE' : 'FALSE'));
            Logger::debug("is_ajax(): " . (function_exists('wp_doing_ajax') ? (wp_doing_ajax() ? 'TRUE' : 'FALSE') : 'function not exists'));
            Logger::debug("DOING_AJAX: " . (defined('DOING_AJAX') ? (DOING_AJAX ? 'TRUE' : 'FALSE') : 'undefined'));
            Logger::debug("SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'unknown'));
            Logger::debug("HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'unknown'));
            Logger::debug("=== END PLUGIN INIT DEBUG ===");
            
            // FIX CRITICO: Prevenire attivazione servizi frontend nell'admin
            $is_admin_request = (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-admin/') !== false) || 
                               (defined('WP_ADMIN') && WP_ADMIN) || 
                               is_admin();
            
            if ($is_admin_request) {
                // Disabilita tutti i servizi che potrebbero interferire con l'admin
                add_filter('fp_ps_disable_frontend_services', '__return_true');
                Logger::debug("Frontend services disabled in admin - REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
            } else {
                Logger::debug("Frontend services ENABLED - NOT in admin");
            }

            // CARICAMENTO LAZY - Solo servizi essenziali per ridurre memory footprint
            // Gli altri servizi si registrano solo se le loro opzioni sono abilitate
            
            // Core services - Solo se abilitati esplicitamente
            $pageCacheSettings = get_option('fp_ps_page_cache', []);
            if (!empty($pageCacheSettings['enabled'])) {
                self::registerServiceOnce(PageCache::class, function() use ($container) {
                    $container->get(PageCache::class)->register();
                });
            }
            
            $headersSettings = get_option('fp_ps_browser_cache', []);
            if (!empty($headersSettings['enabled'])) {
                self::registerServiceOnce(Headers::class, function() use ($container) {
                    $container->get(Headers::class)->register();
                });
            }
            
            // Optimizer solo se abilitato nelle opzioni
            $assetSettings = get_option('fp_ps_assets', []);
            if (!empty($assetSettings['enabled']) || get_option('fp_ps_asset_optimization_enabled', false)) {
                self::registerServiceOnce(Optimizer::class, function() use ($container) {
                    $container->get(Optimizer::class)->register();
                });
            }
            
            // Database cleaner solo se schedulato
            $dbSettings = get_option('fp_ps_db', []);
            if (isset($dbSettings['schedule']) && $dbSettings['schedule'] !== 'manual') {
                self::registerServiceOnce(Cleaner::class, function() use ($container) {
                    $container->get(Cleaner::class)->register();
                });
            }
            
            // FP Plugins Integration - SEMPRE attivo per escludere automaticamente dalla cache
            self::registerServiceOnce(FPPluginsIntegration::class, function() use ($container) {
                $container->get(FPPluginsIntegration::class)->register();
            });
            
            // Theme Compatibility - Solo se abilitato esplicitamente
            if (get_option('fp_ps_compatibility_enabled', false)) {
                self::registerServiceOnce(ThemeCompatibility::class, function() use ($container) {
                    $container->get(ThemeCompatibility::class)->register();
                });
                self::registerServiceOnce(CompatibilityFilters::class, function() use ($container) {
                    $container->get(CompatibilityFilters::class)->register();
                });
                // Salient + WPBakery Optimizer - Si auto-attiva se rileva tema/builder
                self::registerServiceOnce(SalientWPBakeryOptimizer::class, function() use ($container) {
                    $container->get(SalientWPBakeryOptimizer::class)->register();
                });
            }
            
            // Ottimizzatori Assets Avanzati (Ripristinato 21 Ott 2025 - FASE 2)
            // Registrati solo se le loro opzioni sono abilitate
            if (get_option('fp_ps_batch_dom_updates_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\BatchDOMUpdater::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\BatchDOMUpdater::class)->register();
                });
            }
            if (get_option('fp_ps_css_optimization_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\CSSOptimizer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\CSSOptimizer::class)->register();
                });
            }
            if (get_option('fp_ps_jquery_optimization_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\jQueryOptimizer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\jQueryOptimizer::class)->register();
                });
            }
            
            // Predictive Prefetching - Cache predittiva intelligente
            $prefetchSettings = get_option('fp_ps_predictive_prefetch', []);
            if (!empty($prefetchSettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\PredictivePrefetching::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\PredictivePrefetching::class)->register();
                });
            }
            
            // Third-Party Script Management
            $thirdPartySettings = get_option('fp_ps_third_party_scripts', []);
            if (!empty($thirdPartySettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class)->register();
                });
            }
            
            // Third-Party Script Detector - Solo se abilitato esplicitamente
            if (get_option('fp_ps_third_party_detector_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector::class)->register();
                });
            }
            
            // Mobile Optimization Services (v1.6.0) - SEMPRE protetti
            $mobileSettings = get_option('fp_ps_mobile_optimizer', []);
            if (!empty($mobileSettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Mobile\MobileOptimizer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Mobile\MobileOptimizer::class)->register();
                });
            }
            
            // Touch Optimizer - SEMPRE protetto
            $touchSettings = get_option('fp_ps_touch_optimizer', []);
            if (!empty($touchSettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Mobile\TouchOptimizer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Mobile\TouchOptimizer::class)->register();
                });
            }
            
            // Mobile Cache Manager - SEMPRE protetto
            $mobileCacheSettings = get_option('fp_ps_mobile_cache', []);
            if (!empty($mobileCacheSettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Mobile\MobileCacheManager::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Mobile\MobileCacheManager::class)->register();
                });
            }
            
            // Responsive Image Manager - SEMPRE protetto
            $responsiveSettings = get_option('fp_ps_responsive_images', []);
            if (!empty($responsiveSettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Mobile\ResponsiveImageManager::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Mobile\ResponsiveImageManager::class)->register();
                });
            }
            
            // NEW FEATURES v1.7.0 - Critical Performance Enhancements
            
            // Instant Page Loader - Prefetch on hover/viewport
            $instantPageSettings = get_option('fp_ps_instant_page', []);
            if (!empty($instantPageSettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\InstantPageLoader::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\InstantPageLoader::class)->register();
                });
            }
            
            // Embed Facades - YouTube/Vimeo/Maps lazy facades
            $facadesSettings = get_option('fp_ps_embed_facades', []);
            if (!empty($facadesSettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\EmbedFacades::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\EmbedFacades::class)->register();
                });
            }
            
            // Delayed JavaScript Executor - Delay JS until user interaction
            $delayJsSettings = get_option('fp_ps_delay_js', []);
            if (!empty($delayJsSettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\DelayedJavaScriptExecutor::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\DelayedJavaScriptExecutor::class)->register();
                });
            }
            
            // WooCommerce Optimizer - Specific optimizations for WooCommerce
            $wooSettings = get_option('fp_ps_woocommerce', []);
            if (!empty($wooSettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Compatibility\WooCommerceOptimizer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Compatibility\WooCommerceOptimizer::class)->register();
                });
            }
            
            // Machine Learning Services (v1.6.0)
            // PROTEZIONE SHARED HOSTING: Disabilita ML su shared hosting
            $mlSettings = get_option('fp_ps_ml_predictor', []);
            if (!empty($mlSettings['enabled'])) {
                // Verifica se l'ambiente può gestire ML (richiede risorse elevate)
                if (HostingDetector::canEnableService('MLPredictor')) {
                    self::registerServiceOnce(\FP\PerfSuite\Services\ML\MLPredictor::class, function() use ($container) {
                        $container->get(\FP\PerfSuite\Services\ML\MLPredictor::class)->register();
                    });
                    self::registerServiceOnce(\FP\PerfSuite\Services\ML\AutoTuner::class, function() use ($container) {
                        $container->get(\FP\PerfSuite\Services\ML\AutoTuner::class)->register();
                    });
                } else {
                    // Log warning per shared hosting
                    Logger::warning('ML Services disabilitati: ambiente shared hosting rilevato. Richiede VPS/Dedicated con almeno 512MB RAM.');
                    
                    // Mostra avviso in admin
                    if (is_admin() && current_user_can('manage_options')) {
                        add_action('admin_notices', function() {
                            echo '<div class="notice notice-warning is-dismissible">
                                <p><strong>FP Performance Suite:</strong> I servizi ML (Machine Learning) sono disabilitati automaticamente su shared hosting per evitare timeout e sovraccarichi. Per utilizzarli, passa a VPS o hosting dedicato.</p>
                            </div>';
                        });
                    }
                }
            }
            
            // Backend Optimization Services
            $backendSettings = get_option('fp_ps_backend_optimizer', []);
            if (!empty($backendSettings['enabled'])) {
                self::registerServiceOnce(BackendOptimizer::class, function() use ($container) {
                    $container->get(BackendOptimizer::class)->register();
                });
            }
            
            // Database Optimization Services
            $dbSettings = get_option('fp_ps_db', []);
            if (!empty($dbSettings['enabled'])) {
                self::registerServiceOnce(DatabaseOptimizer::class, function() use ($container) {
                    $container->get(DatabaseOptimizer::class)->register();
                });
                self::registerServiceOnce(DatabaseQueryMonitor::class, function() use ($container) {
                    $container->get(DatabaseQueryMonitor::class)->register();
                });
                self::registerServiceOnce(PluginSpecificOptimizer::class, function() use ($container) {
                    $container->get(PluginSpecificOptimizer::class)->register();
                });
                self::registerServiceOnce(DatabaseReportService::class, function() use ($container) {
                    $container->get(DatabaseReportService::class)->register();
                });
            }
            
            // Query Cache Manager - Può essere abilitato indipendentemente
            $queryCacheSettings = get_option('fp_ps_query_cache', []);
            if (!empty($queryCacheSettings['enabled']) || !empty($dbSettings['query_cache_enabled'])) {
                self::registerServiceOnce(QueryCacheManager::class, function() use ($container) {
                    $container->get(QueryCacheManager::class)->register();
                });
            }
            
            // Security Services - FIX CRITICO
            // PROTEZIONE SHARED HOSTING: Verifica permessi .htaccess
            $securitySettings = get_option('fp_ps_htaccess_security', []);
            if (!empty($securitySettings['enabled'])) {
                // Verifica se può modificare .htaccess
                if (HostingDetector::canEnableService('HtaccessSecurity')) {
                    self::registerServiceOnce(HtaccessSecurity::class, function() use ($container) {
                        $container->get(HtaccessSecurity::class)->register();
                    });
                } else {
                    Logger::warning('HtaccessSecurity disabilitato: file .htaccess non scrivibile o permessi insufficienti');
                    
                    if (is_admin() && current_user_can('manage_options')) {
                        add_action('admin_notices', function() {
                            echo '<div class="notice notice-warning is-dismissible">
                                <p><strong>FP Performance Suite:</strong> Le regole di sicurezza .htaccess non possono essere applicate automaticamente. Verifica i permessi del file .htaccess o applicale manualmente.</p>
                            </div>';
                        });
                    }
                }
            }
            
            // External Resource Cache Services - NUOVO
            $externalCacheSettings = get_option('fp_ps_external_cache', []);
            if (!empty($externalCacheSettings['enabled'])) {
                self::registerServiceOnce(ExternalResourceCacheManager::class, function() use ($container) {
                    $container->get(ExternalResourceCacheManager::class)->register();
                });
            }
            
            // Compression Services - FIX CRITICO
            if (get_option('fp_ps_compression_enabled', false) || get_option('fp_ps_compression_deflate_enabled', false) || get_option('fp_ps_compression_brotli_enabled', false)) {
                self::registerServiceOnce(CompressionManager::class, function() use ($container) {
                    $container->get(CompressionManager::class)->register();
                });
            }
            
            // CDN Services - FIX CRITICO
            $cdnSettings = get_option('fp_ps_cdn', []);
            if (!empty($cdnSettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\CDN\CdnManager::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\CDN\CdnManager::class)->register();
                });
            }
            
            // Object Cache Services - FIX CRITICO
            if (get_option('fp_ps_object_cache_enabled', false)) {
                self::registerServiceOnce(ObjectCacheManager::class, function() use ($container) {
                    $container->get(ObjectCacheManager::class)->register();
                });
            }
            
            // Edge Cache Services - FIX CRITICO
            if (get_option('fp_ps_edge_cache_enabled', false)) {
                self::registerServiceOnce(EdgeCacheManager::class, function() use ($container) {
                    $container->get(EdgeCacheManager::class)->register();
                });
            }
            
            // Monitoring Services - FIX CRITICO
            if (get_option('fp_ps_monitoring_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Monitoring\PerformanceMonitor::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Monitoring\PerformanceMonitor::class)->register();
                });
                self::registerServiceOnce(\FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor::class)->register();
                });
            }
            
            // Reports Services - FIX CRITICO
            if (get_option('fp_ps_reports_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Reports\ScheduledReports::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Reports\ScheduledReports::class)->register();
                });
            }
            
            // Scoring Services - Sempre attivo per calcolo score (FIX CRITICO)
            self::registerServiceOnce(Scorer::class, function() use ($container) {
                $container->get(Scorer::class)->register();
            });
            
            // Preset Services - Solo se abilitato esplicitamente
            if (get_option('fp_ps_presets_enabled', false)) {
                self::registerServiceOnce(PresetManager::class, function() use ($container) {
                    $container->get(PresetManager::class)->register();
                });
            }
            
            // AI Services - FIX CRITICO
            if (get_option('fp_ps_ai_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\AI\Analyzer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\AI\Analyzer::class)->register();
                });
            }
            
            // Intelligence Services - Solo se abilitati esplicitamente
            if (get_option('fp_ps_intelligence_enabled', false)) {
                self::registerServiceOnce(SmartExclusionDetector::class, function() use ($container) {
                    $container->get(SmartExclusionDetector::class)->register();
                });
                self::registerServiceOnce(\FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator::class)->register();
                });
                self::registerServiceOnce(\FP\PerfSuite\Services\Intelligence\PerformanceBasedExclusionDetector::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Intelligence\PerformanceBasedExclusionDetector::class)->register();
                });
                self::registerServiceOnce(\FP\PerfSuite\Services\Intelligence\CacheAutoConfigurator::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Intelligence\CacheAutoConfigurator::class)->register();
                });
                self::registerServiceOnce(\FP\PerfSuite\Services\Intelligence\IntelligenceReporter::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Intelligence\IntelligenceReporter::class)->register();
                });
                self::registerServiceOnce(\FP\PerfSuite\Services\Intelligence\AssetOptimizationIntegrator::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Intelligence\AssetOptimizationIntegrator::class)->register();
                });
                self::registerServiceOnce(\FP\PerfSuite\Services\Intelligence\CDNExclusionSync::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Intelligence\CDNExclusionSync::class)->register();
                });
            }
            
            // PWA Services - FIX CRITICO
            if (get_option('fp_ps_pwa_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\PWA\ServiceWorkerManager::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\PWA\ServiceWorkerManager::class)->register();
                });
            }
            
            // HTTP/2 Services - FIX CRITICO
            if (get_option('fp_ps_http2_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\Http2ServerPush::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\Http2ServerPush::class)->register();
                });
            }
            
            // Advanced Assets Services - FIX CRITICO
            if (get_option('fp_ps_critical_css_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\CriticalCss::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\CriticalCss::class)->register();
                });
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\CriticalCssAutomation::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\CriticalCssAutomation::class)->register();
                });
            }
            
            if (get_option('fp_ps_smart_delivery_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\SmartAssetDelivery::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\SmartAssetDelivery::class)->register();
                });
            }
            
            // Advanced Assets Optimization Services - FIX CRITICO
            if (get_option('fp_ps_html_minification_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\HtmlMinifier::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\HtmlMinifier::class)->register();
                });
            }
            
            if (get_option('fp_ps_script_optimization_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\ScriptOptimizer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\ScriptOptimizer::class)->register();
                });
            }
            
            if (get_option('fp_ps_wordpress_optimization_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\WordPressOptimizer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\WordPressOptimizer::class)->register();
                });
            }
            
            if (get_option('fp_ps_resource_hints_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager::class)->register();
                });
            }
            
            if (get_option('fp_ps_dependency_resolution_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\Combiners\DependencyResolver::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\Combiners\DependencyResolver::class)->register();
                });
            }
            
            if (get_option('fp_ps_lazy_loading_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\LazyLoadManager::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\LazyLoadManager::class)->register();
                });
            }
            
            // Font Optimizer - Controlla sia le opzioni vecchie che nuove
            $fontOptimizationEnabled = get_option('fp_ps_font_optimization_enabled', false);
            $fontSettings = get_option('fp_ps_font_optimization', []);
            $criticalPathSettings = get_option('fp_ps_critical_path_optimization', []);
            
            if ($fontOptimizationEnabled || !empty($fontSettings['enabled']) || !empty($criticalPathSettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\FontOptimizer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\FontOptimizer::class)->register();
                });
            }
            
            if (get_option('fp_ps_image_optimization_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\ImageOptimizer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\ImageOptimizer::class)->register();
                });
            }
            
            if (get_option('fp_ps_auto_font_optimization_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\AutoFontOptimizer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\AutoFontOptimizer::class)->register();
                });
            }
            
            if (get_option('fp_ps_lighthouse_font_optimization_enabled', false)) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\LighthouseFontOptimizer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\LighthouseFontOptimizer::class)->register();
                });
            }
            
            
            // Performance Analysis Services - FIX CRITICO (sempre attivi per analisi performance)
            self::registerServiceOnce(\FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer::class, function() use ($container) {
                $container->get(\FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer::class)->register();
            });
            self::registerServiceOnce(\FP\PerfSuite\Services\Monitoring\RecommendationApplicator::class, function() use ($container) {
                $container->get(\FP\PerfSuite\Services\Monitoring\RecommendationApplicator::class)->register();
            });
            
            // Advanced Assets Optimization Services - FIX CRITICO (sempre attivi per ottimizzazioni avanzate)
            self::registerServiceOnce(\FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer::class, function() use ($container) {
                $container->get(\FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer::class)->register();
            });
            self::registerServiceOnce(\FP\PerfSuite\Services\Assets\ResponsiveImageAjaxHandler::class, function() use ($container) {
                $container->get(\FP\PerfSuite\Services\Assets\ResponsiveImageAjaxHandler::class)->register();
            });
            self::registerServiceOnce(\FP\PerfSuite\Services\Assets\UnusedCSSOptimizer::class, function() use ($container) {
                $container->get(\FP\PerfSuite\Services\Assets\UnusedCSSOptimizer::class)->register();
            });
            self::registerServiceOnce(\FP\PerfSuite\Services\Assets\RenderBlockingOptimizer::class, function() use ($container) {
                $container->get(\FP\PerfSuite\Services\Assets\RenderBlockingOptimizer::class)->register();
            });
            // Critical Path Optimizer - Solo se abilitato
            $criticalPathSettings = get_option('fp_ps_critical_path_optimization', []);
            if (!empty($criticalPathSettings['enabled'])) {
                self::registerServiceOnce(\FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class)->register();
                });
            }
            self::registerServiceOnce(\FP\PerfSuite\Services\Assets\DOMReflowOptimizer::class, function() use ($container) {
                $container->get(\FP\PerfSuite\Services\Assets\DOMReflowOptimizer::class)->register();
            });
            
            
            // Theme Services - FIX CRITICO (sempre attivi per gestione tema)
            self::registerServiceOnce(ThemeAssetConfiguration::class, function() use ($container) {
                $container->get(ThemeAssetConfiguration::class)->register();
            });
            self::registerServiceOnce(ThemeDetector::class, function() use ($container) {
                $container->get(ThemeDetector::class)->register();
            });
        });
        
        // Handler AJAX (Ripristinato 21 Ott 2025 - FASE 2)
        // Registrati solo durante richieste AJAX per ottimizzare performance
        if (defined('DOING_AJAX') && DOING_AJAX) {
            add_action('init', static function () use ($container) {
                self::registerServiceOnce(\FP\PerfSuite\Http\Ajax\RecommendationsAjax::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Http\Ajax\RecommendationsAjax::class)->register();
                });
                self::registerServiceOnce(\FP\PerfSuite\Http\Ajax\CriticalCssAjax::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Http\Ajax\CriticalCssAjax::class)->register();
                });
                self::registerServiceOnce(\FP\PerfSuite\Http\Ajax\AIConfigAjax::class, function() use ($container) {
                    $container->get(\FP\PerfSuite\Http\Ajax\AIConfigAjax::class)->register();
                });
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
        
        // External Resource Cache Manager - NUOVO
        $container->set(ExternalResourceCacheManager::class, static fn() => new ExternalResourceCacheManager());
        
        // PageSpeed optimization services
        $container->set(\FP\PerfSuite\Services\Assets\LazyLoadManager::class, static fn() => new \FP\PerfSuite\Services\Assets\LazyLoadManager());
        $container->set(\FP\PerfSuite\Services\Assets\FontOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\FontOptimizer());
        $container->set(\FP\PerfSuite\Services\Assets\ImageOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\ImageOptimizer());
        
        // Auto Font Optimization Services (v1.5.0) - Sistema di auto-rilevamento
        $container->set(\FP\PerfSuite\Services\Assets\AutoFontOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\AutoFontOptimizer());
        $container->set(\FP\PerfSuite\Services\Assets\LighthouseFontOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\LighthouseFontOptimizer());
        
        // Compression service
        $container->set(CompressionManager::class, static fn() => new CompressionManager());


        // v1.1.0 Services
        $container->set(\FP\PerfSuite\Services\Assets\CriticalCss::class, static fn() => new \FP\PerfSuite\Services\Assets\CriticalCss());
        $container->set(\FP\PerfSuite\Services\CDN\CdnManager::class, static fn() => new \FP\PerfSuite\Services\CDN\CdnManager());
        $container->set(\FP\PerfSuite\Services\Monitoring\PerformanceMonitor::class, static fn() => \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance());
        $container->set(\FP\PerfSuite\Services\Monitoring\SystemMonitor::class, static fn() => \FP\PerfSuite\Services\Monitoring\SystemMonitor::instance());
        $container->set(\FP\PerfSuite\Services\Reports\ScheduledReports::class, static fn() => new \FP\PerfSuite\Services\Reports\ScheduledReports());
        
        // Performance Analyzer
        $container->set(\FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer(
                $c->get(PageCache::class),
                $c->get(Headers::class),
                $c->get(Optimizer::class),
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
        
        // Browser Cache
        $container->set(\FP\PerfSuite\Services\Cache\BrowserCache::class, static fn() => new \FP\PerfSuite\Services\Cache\BrowserCache());
        
        // Edge Cache Providers
        $container->set(EdgeCacheManager::class, static fn() => new EdgeCacheManager());
        
        
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
        
        // Smart Asset Delivery
        $container->set(\FP\PerfSuite\Services\Assets\SmartAssetDelivery::class, static fn() => new \FP\PerfSuite\Services\Assets\SmartAssetDelivery());
        
        // Service Worker / PWA
        $container->set(\FP\PerfSuite\Services\PWA\ServiceWorkerManager::class, static fn() => new \FP\PerfSuite\Services\PWA\ServiceWorkerManager());
        
        // Core Web Vitals Monitor
        $container->set(\FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor::class, static fn() => new \FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor());
        
        // Predictive Prefetching
        $container->set(\FP\PerfSuite\Services\Assets\PredictivePrefetching::class, static fn() => new \FP\PerfSuite\Services\Assets\PredictivePrefetching());
        
        // NEW FEATURES v1.7.0 - Critical Performance Enhancements
        
        // Instant Page Loader (Prefetch on hover/viewport)
        $container->set(\FP\PerfSuite\Services\Assets\InstantPageLoader::class, static fn() => new \FP\PerfSuite\Services\Assets\InstantPageLoader());
        
        // Embed Facades (YouTube/Vimeo/Maps lazy load)
        $container->set(\FP\PerfSuite\Services\Assets\EmbedFacades::class, static fn() => new \FP\PerfSuite\Services\Assets\EmbedFacades());
        
        // Delayed JavaScript Executor
        $container->set(\FP\PerfSuite\Services\Assets\DelayedJavaScriptExecutor::class, static fn() => new \FP\PerfSuite\Services\Assets\DelayedJavaScriptExecutor());
        
        // WooCommerce Optimizer
        $container->set(\FP\PerfSuite\Services\Compatibility\WooCommerceOptimizer::class, static fn() => new \FP\PerfSuite\Services\Compatibility\WooCommerceOptimizer());
        
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
        
        // Ottimizzatori JavaScript Avanzati
        $container->set(\FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer());
        $container->set(\FP\PerfSuite\Services\Assets\CodeSplittingManager::class, static fn() => new \FP\PerfSuite\Services\Assets\CodeSplittingManager());
        $container->set(\FP\PerfSuite\Services\Assets\JavaScriptTreeShaker::class, static fn() => new \FP\PerfSuite\Services\Assets\JavaScriptTreeShaker());
        
        // Servizi Media
        $container->set(\FP\PerfSuite\Services\Media\LazyLoadManager::class, static fn() => new \FP\PerfSuite\Services\Media\LazyLoadManager());
        
        
        // Handler AJAX (Ripristinato 21 Ott 2025 - FASE 2)
        $container->set(\FP\PerfSuite\Http\Ajax\RecommendationsAjax::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Http\Ajax\RecommendationsAjax($c));
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
        $container->set(CompatibilityFilters::class, static fn(ServiceContainer $c) => new CompatibilityFilters($c, $c->get(ThemeDetector::class)));
        $container->set(ThemeCompatibility::class, static fn(ServiceContainer $c) => new ThemeCompatibility($c, $c->get(ThemeDetector::class)));
        $container->set(SalientWPBakeryOptimizer::class, static fn(ServiceContainer $c) => new SalientWPBakeryOptimizer($c, $c->get(ThemeDetector::class)));
        $container->set(FPPluginsIntegration::class, static fn() => new FPPluginsIntegration());
        
        
        // Smart Intelligence Services
        $container->set(SmartExclusionDetector::class, static fn() => new SmartExclusionDetector());
        $container->set(\FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator(
            $c->get(SmartExclusionDetector::class)
        ));
        $container->set(\FP\PerfSuite\Services\Intelligence\PerformanceBasedExclusionDetector::class, static fn() => new \FP\PerfSuite\Services\Intelligence\PerformanceBasedExclusionDetector());
        $container->set(\FP\PerfSuite\Services\Intelligence\CacheAutoConfigurator::class, static fn() => new \FP\PerfSuite\Services\Intelligence\CacheAutoConfigurator());
        $container->set(\FP\PerfSuite\Services\Intelligence\IntelligenceReporter::class, static fn() => new \FP\PerfSuite\Services\Intelligence\IntelligenceReporter());
        $container->set(\FP\PerfSuite\Services\Intelligence\AssetOptimizationIntegrator::class, static fn() => new \FP\PerfSuite\Services\Intelligence\AssetOptimizationIntegrator());
        $container->set(\FP\PerfSuite\Services\Intelligence\CDNExclusionSync::class, static fn() => new \FP\PerfSuite\Services\Intelligence\CDNExclusionSync());
        
        // Backend Optimizer
        $container->set(BackendOptimizer::class, static fn() => new BackendOptimizer());
        
        // Security Services
        $container->set(HtaccessSecurity::class, static fn(ServiceContainer $c) => new HtaccessSecurity($c->get(Htaccess::class), $c->get(Env::class)));

        $container->set(PageCache::class, static fn(ServiceContainer $c) => new PageCache());
        $container->set(Headers::class, static fn(ServiceContainer $c) => new Headers());
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
        $container->set(Cleaner::class, static fn(ServiceContainer $c) => new Cleaner());
        
        // Backend Optimization Service
        $container->set(BackendOptimizer::class, static fn() => new BackendOptimizer());
        
        // Database Optimization Services (v1.4.0)
        $container->set(DatabaseOptimizer::class, static fn() => new DatabaseOptimizer());
        $container->set(DatabaseQueryMonitor::class, static fn() => new DatabaseQueryMonitor());
        $container->set(PluginSpecificOptimizer::class, static fn() => new PluginSpecificOptimizer());
        $container->set(DatabaseReportService::class, static fn() => new DatabaseReportService());
        $container->set(DebugToggler::class, static fn(ServiceContainer $c) => new DebugToggler($c->get(Fs::class), $c->get(Env::class)));
        $container->set(RealtimeLog::class, static fn(ServiceContainer $c) => new RealtimeLog($c->get(DebugToggler::class)));
        $container->set(PresetManager::class, static function (ServiceContainer $c) {
            return new PresetManager(
                $c->get(PageCache::class),
                $c->get(Headers::class),
                $c->get(Optimizer::class),
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

        // Mobile Optimization Services (v1.6.0)
        $container->set(\FP\PerfSuite\Services\Mobile\TouchOptimizer::class, static fn() => new \FP\PerfSuite\Services\Mobile\TouchOptimizer());
        $container->set(\FP\PerfSuite\Services\Mobile\MobileCacheManager::class, static fn() => new \FP\PerfSuite\Services\Mobile\MobileCacheManager());
        $container->set(\FP\PerfSuite\Services\Mobile\ResponsiveImageManager::class, static fn() => new \FP\PerfSuite\Services\Mobile\ResponsiveImageManager());
        $container->set(\FP\PerfSuite\Services\Mobile\MobileOptimizer::class, static fn() => new \FP\PerfSuite\Services\Mobile\MobileOptimizer());

        // Machine Learning Services (v1.6.0)
        $container->set(\FP\PerfSuite\Services\ML\PatternLearner::class, static fn() => new \FP\PerfSuite\Services\ML\PatternLearner());
        $container->set(\FP\PerfSuite\Services\ML\AnomalyDetector::class, static fn() => new \FP\PerfSuite\Services\ML\AnomalyDetector());
        $container->set(\FP\PerfSuite\Services\ML\MLPredictor::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\ML\MLPredictor(
                $c,
                $c->get(\FP\PerfSuite\Services\ML\PatternLearner::class),
                $c->get(\FP\PerfSuite\Services\ML\AnomalyDetector::class)
            );
        });
        $container->set(\FP\PerfSuite\Services\ML\AutoTuner::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\ML\AutoTuner(
                $c,
                $c->get(\FP\PerfSuite\Services\ML\MLPredictor::class),
                $c->get(\FP\PerfSuite\Services\ML\PatternLearner::class)
            );
        });

        $container->set(AdminAssets::class, static fn() => new AdminAssets());
        $container->set(Menu::class, static fn(ServiceContainer $c) => new Menu($c));
        $container->set(AdminBar::class, static fn(ServiceContainer $c) => new AdminBar($c));
        $container->set(Routes::class, static fn(ServiceContainer $c) => new Routes($c));
        $container->set(Shortcodes::class, static fn() => new Shortcodes());
    }

    public static function container(): ServiceContainer
    {
        if (!self::$container instanceof ServiceContainer) {
            self::init();
        }

        return self::$container;
    }
    
    /**
     * Resetta lo stato di inizializzazione (per debug/recupero errori)
     * ATTENZIONE: Usare solo in contesti di test/debug
     */
    public static function reset(): void
    {
        self::$container = null;
        self::$registeredServices = [];
    }
    
    /**
     * Verifica se il plugin è stato inizializzato
     */
    public static function isInitialized(): bool
    {
        return self::$container !== null;
    }
    
    /**
     * Registra un servizio solo se non è già stato registrato
     */
    public static function registerServiceOnce(string $serviceClass, callable $registerCallback): bool
    {
        if (isset(self::$registeredServices[$serviceClass])) {
            return false; // Già registrato
        }
        
        try {
            $registerCallback();
            self::$registeredServices[$serviceClass] = true;
            return true;
        } catch (\Throwable $e) {
            if (defined('FP_PERF_DEBUG') && FP_PERF_DEBUG) {
                error_log("[FP-PerfSuite] ERROR registering service " . $serviceClass . ": " . $e->getMessage());
            }
            Logger::error('Failed to register service: ' . $serviceClass, ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Verifica se un servizio è già stato registrato
     */
    public static function isServiceRegistered(string $serviceClass): bool
    {
        return isset(self::$registeredServices[$serviceClass]);
    }
    
    /**
     * Verifica se i servizi frontend devono essere disabilitati
     */
    public static function shouldDisableFrontendServices(): bool
    {
        return apply_filters('fp_ps_disable_frontend_services', is_admin());
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
            
            // Inizializza opzioni di default per i nuovi servizi (v1.6.0)
            self::initializeDefaultOptions();
            
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
     * Inizializza le opzioni di default per i nuovi servizi
     */
    private static function initializeDefaultOptions(): void
    {
        self::ensureDefaultOptionsExist();
    }

    /**
     * Assicura che le opzioni di default esistano (per utenti esistenti e nuovi)
     */
    private static function ensureDefaultOptionsExist(): void
    {
        // Mobile Optimization Services (v1.6.0) - INIZIALIZZATI ma DISABILITATI di default
        if (!get_option('fp_ps_mobile_optimizer')) {
            update_option('fp_ps_mobile_optimizer', [
                'enabled' => false,
                'disable_animations' => false,
                'remove_unnecessary_scripts' => false,
                'optimize_touch_targets' => false,
                'enable_responsive_images' => false
            ], false);
        }
        
        // Touch Optimizer - INIZIALIZZATO ma DISABILITATO di default
        if (!get_option('fp_ps_touch_optimizer')) {
            update_option('fp_ps_touch_optimizer', [
                'enabled' => false,
                'disable_hover_effects' => false,
                'improve_touch_targets' => false,
                'optimize_scroll' => false,
                'prevent_zoom' => false
            ], false);
        }
        
        // Responsive Images - INIZIALIZZATO ma DISABILITATO di default
        if (!get_option('fp_ps_responsive_images')) {
            update_option('fp_ps_responsive_images', [
                'enabled' => false,
                'enable_lazy_loading' => false,
                'optimize_srcset' => false,
                'max_mobile_width' => 768,
                'max_content_image_width' => '100%'
            ], false);
        }
        
        // Mobile Cache Manager - INIZIALIZZATO ma DISABILITATO di default
        if (!get_option('fp_ps_mobile_cache')) {
            update_option('fp_ps_mobile_cache', [
                'enabled' => false,
                'enable_mobile_cache_headers' => false,
                'enable_resource_caching' => false,
                'cache_mobile_css' => false,
                'cache_mobile_js' => false,
                'html_cache_duration' => 300,
                'css_cache_duration' => 3600,
                'js_cache_duration' => 3600
            ], false);
        }
        
        // Machine Learning Services (v1.6.0) - DISATTIVATI di default
        if (!get_option('fp_ps_ml_predictor')) {
            update_option('fp_ps_ml_predictor', [
                'enabled' => false, // Disabilitato di default per sicurezza
                'data_retention_days' => 30,
                'prediction_threshold' => 0.7,
                'anomaly_threshold' => 0.8,
                'pattern_confidence_threshold' => 0.8
            ], false);
        }
        
        // Pattern Learner - DISATTIVATO di default
        if (!get_option('fp_ps_pattern_learner')) {
            update_option('fp_ps_pattern_learner', [
                'enabled' => false,
                'min_data_points' => 10,
                'confidence_threshold' => 0.7,
                'learning_rate' => 0.1,
                'max_patterns' => 100
            ], false);
        }
        
        // Anomaly Detector - DISATTIVATO di default
        if (!get_option('fp_ps_anomaly_detector')) {
            update_option('fp_ps_anomaly_detector', [
                'enabled' => false,
                'confidence_threshold' => 0.7,
                'z_score_threshold' => 2.0,
                'window_size' => 24,
                'sensitivity' => 'medium'
            ], false);
        }
        
        // Auto Tuner - DISATTIVATO di default
        if (!get_option('fp_ps_auto_tuner')) {
            update_option('fp_ps_auto_tuner', [
                'enabled' => false,
                'tuning_frequency' => '6hourly',
                'aggressive_mode' => false,
                'performance_threshold' => 0.8,
                'auto_rollback' => true
            ], false);
        }
        
        // Security Services - DISATTIVATI di default
        if (!get_option('fp_ps_htaccess_security')) {
            update_option('fp_ps_htaccess_security', [
                'enabled' => false,
                'ssl_redirect' => [
                    'enabled' => false,
                    'force_https' => false,
                    'force_www' => false,
                    'domain' => ''
                ],
                'security_headers' => [
                    'enabled' => false,
                    'hsts' => false,
                    'hsts_max_age' => 31536000,
                    'hsts_subdomains' => false,
                    'hsts_preload' => false,
                    'x_content_type_options' => false,
                    'x_frame_options' => 'SAMEORIGIN',
                    'referrer_policy' => 'strict-origin-when-cross-origin',
                    'permissions_policy' => 'camera=(), microphone=(), geolocation=()'
                ],
                'cache_rules' => [
                    'enabled' => false,
                    'html_cache' => false,
                    'fonts_cache' => false,
                    'fonts_max_age' => 31536000,
                    'images_max_age' => 31536000,
                    'css_js_max_age' => 2592000
                ],
                'cors' => [
                    'enabled' => false,
                    'fonts_origin' => '*',
                    'svg_origin' => '*'
                ],
                'file_protection' => [
                    'enabled' => false,
                    'protect_hidden_files' => false,
                    'protect_wp_config' => false,
                    'protect_htaccess' => false,
                    'protect_readme' => false
                ]
            ], false);
        }
        
        // Media Optimization - DISATTIVATA di default
        if (!get_option('fp_ps_media_optimizer')) {
            update_option('fp_ps_media_optimizer', [
                'enabled' => false,
                'generate_sizes' => false,
                'js_detection' => false,
                'lazy_loading' => false,
                'quality' => 75,
                'lossy' => true
            ], false);
        }
        
        // Compression - DISATTIVATA di default
        if (!get_option('fp_ps_compression_deflate_enabled')) {
            update_option('fp_ps_compression_deflate_enabled', false, false);
        }
        
        // Backend Optimization - DISATTIVATA di default
        if (!get_option('fp_ps_backend_optimizer')) {
            update_option('fp_ps_backend_optimizer', [
                'enabled' => false,
                'admin_bar' => [
                    'disable_frontend' => false,
                    'disable_wordpress_logo' => false,
                    'disable_updates' => false,
                    'disable_comments' => false,
                    'disable_new' => false,
                    'disable_customize' => false,
                ],
                'dashboard' => [
                    'disable_welcome' => false,
                    'disable_quick_press' => false,
                    'disable_activity' => false,
                    'disable_primary' => false,
                    'disable_secondary' => false,
                    'disable_site_health' => false,
                    'disable_php_update' => false,
                ],
                'heartbeat' => [
                    'dashboard' => 'default',
                    'editor' => 'default',
                    'frontend' => 'default',
                ],
                'heartbeat_interval' => 60,
                'admin_ajax' => [
                    'disable_heartbeat' => false,
                    'disable_autosave' => false,
                    'disable_post_lock' => false,
                ]
            ], false);
        }
        
        // Database Optimization - DISATTIVATA di default
        if (!get_option('fp_ps_db')) {
            update_option('fp_ps_db', [
                'enabled' => false,
                'schedule' => 'manual',
                'batch' => 200,
                'cleanup_scope' => [],
                'query_monitor' => [
                    'enabled' => false,
                    'log_queries' => false,
                    'log_slow_queries' => false,
                    'slow_query_threshold' => 0.5
                ],
                'optimization' => [
                    'auto_optimize' => false,
                    'optimize_on_cleanup' => false,
                    'repair_tables' => false
                ]
            ], false);
        }
        
        // CDN Services - DISATTIVATI di default
        if (!get_option('fp_ps_cdn')) {
            update_option('fp_ps_cdn', [
                'enabled' => false,
                'url' => '',
                'provider' => 'custom'
            ], false);
        }
        
        // Object Cache - DISATTIVATO di default
        if (!get_option('fp_ps_object_cache_enabled')) {
            update_option('fp_ps_object_cache_enabled', false, false);
        }
        
        // Edge Cache - DISATTIVATO di default
        if (!get_option('fp_ps_edge_cache_enabled')) {
            update_option('fp_ps_edge_cache_enabled', false, false);
        }
        
        // Monitoring Services - DISATTIVATI di default
        if (!get_option('fp_ps_monitoring_enabled')) {
            update_option('fp_ps_monitoring_enabled', false, false);
        }
        
        // Reports Services - DISATTIVATI di default
        if (!get_option('fp_ps_reports_enabled')) {
            update_option('fp_ps_reports_enabled', false, false);
        }
        
        // Core Services - DISATTIVATI di default
        if (!get_option('fp_ps_page_cache')) {
            update_option('fp_ps_page_cache', [
                'enabled' => false,
                'ttl' => 3600
            ], false);
        }
        
        if (!get_option('fp_ps_browser_cache')) {
            update_option('fp_ps_browser_cache', [
                'enabled' => false
            ], false);
        }
        
        // Third-Party Script Detector - DISATTIVATO di default
        if (!get_option('fp_ps_third_party_detector_enabled')) {
            update_option('fp_ps_third_party_detector_enabled', false, false);
        }
        
        // Scoring Services - ABILITATI di default (necessari per Overview)
        if (!get_option('fp_ps_scoring_enabled')) {
            update_option('fp_ps_scoring_enabled', true, false);
        }
        
        // Preset Services - DISATTIVATI di default
        if (!get_option('fp_ps_presets_enabled')) {
            update_option('fp_ps_presets_enabled', false, false);
        }
        
        // Intelligence Services - DISATTIVATI di default
        if (!get_option('fp_ps_intelligence_enabled')) {
            update_option('fp_ps_intelligence_enabled', false, false);
        }
        
        // Compatibility Services - DISATTIVATI di default
        if (!get_option('fp_ps_compatibility_enabled')) {
            update_option('fp_ps_compatibility_enabled', false, false);
        }
        
        // AI Services - DISATTIVATI di default
        if (!get_option('fp_ps_ai_enabled')) {
            update_option('fp_ps_ai_enabled', false, false);
        }
        
        // PWA Services - DISATTIVATI di default
        if (!get_option('fp_ps_pwa_enabled')) {
            update_option('fp_ps_pwa_enabled', false, false);
        }
        
        // HTTP/2 Services - DISATTIVATI di default
        if (!get_option('fp_ps_http2_enabled')) {
            update_option('fp_ps_http2_enabled', false, false);
        }
        
        // Advanced Assets Services - DISATTIVATI di default
        if (!get_option('fp_ps_critical_css_enabled')) {
            update_option('fp_ps_critical_css_enabled', false, false);
        }
        
        if (!get_option('fp_ps_smart_delivery_enabled')) {
            update_option('fp_ps_smart_delivery_enabled', false, false);
        }
        
        // Advanced Assets Optimization Services - DISATTIVATI di default
        if (!get_option('fp_ps_html_minification_enabled')) {
            update_option('fp_ps_html_minification_enabled', false, false);
        }
        
        if (!get_option('fp_ps_script_optimization_enabled')) {
            update_option('fp_ps_script_optimization_enabled', false, false);
        }
        
        if (!get_option('fp_ps_wordpress_optimization_enabled')) {
            update_option('fp_ps_wordpress_optimization_enabled', false, false);
        }
        
        if (!get_option('fp_ps_resource_hints_enabled')) {
            update_option('fp_ps_resource_hints_enabled', false, false);
        }
        
        if (!get_option('fp_ps_dependency_resolution_enabled')) {
            update_option('fp_ps_dependency_resolution_enabled', false, false);
        }
        
        if (!get_option('fp_ps_lazy_loading_enabled')) {
            update_option('fp_ps_lazy_loading_enabled', false, false);
        }
        
        if (!get_option('fp_ps_font_optimization_enabled')) {
            update_option('fp_ps_font_optimization_enabled', false, false);
        }
        
        if (!get_option('fp_ps_image_optimization_enabled')) {
            update_option('fp_ps_image_optimization_enabled', false, false);
        }
        
        if (!get_option('fp_ps_auto_font_optimization_enabled')) {
            update_option('fp_ps_auto_font_optimization_enabled', false, false);
        }
        
        if (!get_option('fp_ps_lighthouse_font_optimization_enabled')) {
            update_option('fp_ps_lighthouse_font_optimization_enabled', false, false);
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

    /**
     * Forza l'inizializzazione delle opzioni mobile per risolvere il problema della pagina vuota
     * Metodo pubblico per essere chiamato da script esterni o admin
     */
    public static function forceMobileOptionsInitialization(): bool
    {
        try {
            // Controlla se almeno una opzione mobile esiste
            $has_mobile_options = get_option('fp_ps_mobile_optimizer') || 
                                 get_option('fp_ps_touch_optimizer') || 
                                 get_option('fp_ps_mobile_cache') || 
                                 get_option('fp_ps_responsive_images');
            
            // Se nessuna opzione mobile esiste, forza l'inizializzazione
            if (!$has_mobile_options) {
                self::ensureDefaultOptionsExist();
            }
            
            // Le opzioni vengono inizializzate ma rimangono disabilitate di default
            // L'utente può abilitarle manualmente dalla pagina admin
            
            return true;
        } catch (\Exception $e) {
            if (defined('FP_PERF_DEBUG') && FP_PERF_DEBUG) {
                error_log('[FP Performance Suite] Errore durante inizializzazione opzioni mobile: ' . $e->getMessage());
            }
            return false;
        }
    }


    public static function onDeactivate(): void
    {
        // === RIMUOVI TUTTI I CRON JOBS DEL PLUGIN ===
        wp_clear_scheduled_hook(Cleaner::CRON_HOOK);
        wp_clear_scheduled_hook('fp_ps_ml_analyze_patterns');
        wp_clear_scheduled_hook('fp_ps_ml_predict_issues');
        wp_clear_scheduled_hook('fp_ps_auto_tune');
        wp_clear_scheduled_hook('fp_ps_db_auto_report');
        
        // ScheduledReports cron (se esiste)
        if (class_exists('FP\\PerfSuite\\Services\\Reports\\ScheduledReports')) {
            wp_clear_scheduled_hook(\FP\PerfSuite\Services\Reports\ScheduledReports::CRON_HOOK);
        }
        
        Logger::info('Plugin deactivated - all cron jobs cleared');
        do_action('fp_ps_plugin_deactivated');
    }
}
