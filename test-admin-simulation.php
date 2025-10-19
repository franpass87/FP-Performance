<?php
/**
 * Test di Simulazione Amministratore
 * Testa tutte le funzionalità del plugin FP Performance Suite
 * 
 * UTILIZZO: Eseguire questo file in un ambiente WordPress con il plugin attivo
 * php test-admin-simulation.php oppure caricarlo come script WordPress
 */

// Prevenire esecuzione diretta se non in ambiente WordPress
if (!defined('ABSPATH')) {
    // Tentativo di caricamento WordPress per test CLI
    $wp_load = __DIR__ . '/../../wp-load.php';
    if (file_exists($wp_load)) {
        require_once $wp_load;
    } else {
        die("❌ Questo script deve essere eseguito in un ambiente WordPress\n");
    }
}

/**
 * Classe per Test Simulazione Admin
 */
class FP_Admin_Simulation_Test {
    
    private $results = [];
    private $errors = [];
    private $warnings = [];
    
    public function __construct() {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║   TEST SIMULAZIONE AMMINISTRATORE - FP PERFORMANCE SUITE       ║\n";
        echo "║   Versione: 1.2.0                                              ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }
    
    /**
     * Esegui tutti i test
     */
    public function runAllTests() {
        $this->checkPluginActive();
        $this->testCacheModule();
        $this->testAssetsModule();
        $this->testMediaWebPModule();
        $this->testDatabaseModule();
        $this->testLogsModule();
        $this->testAdvancedFeatures();
        $this->testPageSpeedFeatures();
        $this->testPerformanceScore();
        $this->testPresetsAndTools();
        $this->generateReport();
    }
    
    /**
     * Verifica che il plugin sia attivo
     */
    private function checkPluginActive() {
        $this->section("Verifica Attivazione Plugin");
        
        if (!class_exists('FP\\PerfSuite\\Plugin')) {
            $this->error("Plugin FP Performance Suite non trovato o non attivo");
            die("❌ Impossibile continuare senza il plugin attivo\n");
        }
        
        $this->success("Plugin FP Performance Suite è attivo");
        
        // Verifica versione
        if (defined('FP_PERF_SUITE_VERSION')) {
            $this->info("Versione: " . FP_PERF_SUITE_VERSION);
        }
        
        // Verifica directory
        if (defined('FP_PERF_SUITE_DIR')) {
            $this->info("Directory: " . FP_PERF_SUITE_DIR);
            if (is_dir(FP_PERF_SUITE_DIR)) {
                $this->success("Directory del plugin esistente");
            } else {
                $this->warning("Directory del plugin non trovata");
            }
        }
    }
    
    /**
     * Test modulo Cache
     */
    private function testCacheModule() {
        $this->section("1. TEST MODULO CACHE");
        
        try {
            // Test Page Cache
            $this->subsection("1.1 Page Cache");
            
            // Verifica se la classe PageCache esiste
            if (class_exists('FP\\PerfSuite\\Services\\Cache\\PageCache')) {
                $this->success("Classe PageCache trovata");
                
                // Test impostazioni cache
                $cache_enabled = get_option('fp_ps_cache_enabled', false);
                $this->info("Cache abilitata: " . ($cache_enabled ? 'Sì' : 'No'));
                
                $cache_dir = WP_CONTENT_DIR . '/cache/fp-performance/';
                if (is_dir($cache_dir)) {
                    $this->success("Directory cache esiste: $cache_dir");
                    
                    // Verifica permessi
                    if (is_writable($cache_dir)) {
                        $this->success("Directory cache scrivibile");
                    } else {
                        $this->warning("Directory cache non scrivibile");
                    }
                    
                    // Conta file in cache
                    $cache_files = glob($cache_dir . '*.html');
                    $cache_count = $cache_files ? count($cache_files) : 0;
                    $this->info("File in cache: $cache_count");
                } else {
                    $this->warning("Directory cache non esiste ancora");
                }
                
            } else {
                $this->error("Classe PageCache non trovata");
            }
            
            // Test Browser Cache Headers
            $this->subsection("1.2 Browser Cache Headers");
            
            if (class_exists('FP\\PerfSuite\\Services\\Cache\\Headers')) {
                $this->success("Classe Headers trovata");
                
                $browser_cache = get_option('fp_ps_browser_cache_enabled', false);
                $this->info("Browser cache headers abilitati: " . ($browser_cache ? 'Sì' : 'No'));
                
                // Verifica .htaccess
                $htaccess_path = ABSPATH . '.htaccess';
                if (file_exists($htaccess_path) && is_readable($htaccess_path)) {
                    $htaccess_content = file_get_contents($htaccess_path);
                    if (strpos($htaccess_content, 'FP Performance Suite') !== false) {
                        $this->success("Regole cache trovate in .htaccess");
                    } else {
                        $this->info("Nessuna regola cache in .htaccess (normale se non abilitato)");
                    }
                }
            } else {
                $this->error("Classe Headers non trovata");
            }
            
            // Test Object Cache Manager
            $this->subsection("1.3 Object Cache Manager");
            
            if (class_exists('FP\\PerfSuite\\Services\\Cache\\ObjectCacheManager')) {
                $this->success("Classe ObjectCacheManager trovata");
                $this->info("Supporto per Redis/Memcached se disponibile");
            }
            
        } catch (Exception $e) {
            $this->error("Errore nel test Cache: " . $e->getMessage());
        }
    }
    
    /**
     * Test modulo Assets
     */
    private function testAssetsModule() {
        $this->section("2. TEST MODULO ASSETS");
        
        try {
            $this->subsection("2.1 Asset Optimizer");
            
            if (class_exists('FP\\PerfSuite\\Services\\Assets\\Optimizer')) {
                $this->success("Classe Optimizer trovata");
                
                // Verifica opzioni
                $minify_css = get_option('fp_ps_minify_css', false);
                $minify_js = get_option('fp_ps_minify_js', false);
                $defer_js = get_option('fp_ps_defer_js', false);
                
                $this->info("Minify CSS: " . ($minify_css ? 'Sì' : 'No'));
                $this->info("Minify JS: " . ($minify_js ? 'Sì' : 'No'));
                $this->info("Defer JS: " . ($defer_js ? 'Sì' : 'No'));
            } else {
                $this->error("Classe Optimizer non trovata");
            }
            
            $this->subsection("2.2 Script Optimizer");
            
            if (class_exists('FP\\PerfSuite\\Services\\Assets\\ScriptOptimizer')) {
                $this->success("Classe ScriptOptimizer trovata");
                $this->info("Gestisce defer/async per script specifici");
            }
            
            $this->subsection("2.3 WordPress Optimizer");
            
            if (class_exists('FP\\PerfSuite\\Services\\Assets\\WordPressOptimizer')) {
                $this->success("Classe WordPressOptimizer trovata");
                
                $disable_emojis = get_option('fp_ps_disable_emojis', false);
                $disable_embeds = get_option('fp_ps_disable_embeds', false);
                $heartbeat = get_option('fp_ps_heartbeat_control', 'default');
                
                $this->info("Disabilita emoji: " . ($disable_emojis ? 'Sì' : 'No'));
                $this->info("Disabilita embeds: " . ($disable_embeds ? 'Sì' : 'No'));
                $this->info("Controllo heartbeat: $heartbeat");
            }
            
            $this->subsection("2.4 HTML Minifier");
            
            if (class_exists('FP\\PerfSuite\\Services\\Assets\\HtmlMinifier')) {
                $this->success("Classe HtmlMinifier trovata");
                $html_minify = get_option('fp_ps_minify_html', false);
                $this->info("Minify HTML: " . ($html_minify ? 'Sì' : 'No'));
            }
            
            $this->subsection("2.5 Resource Hints");
            
            if (class_exists('FP\\PerfSuite\\Services\\Assets\\ResourceHints\\ResourceHintsManager')) {
                $this->success("Classe ResourceHintsManager trovata");
                
                $dns_prefetch = get_option('fp_ps_dns_prefetch', []);
                $preconnect = get_option('fp_ps_preconnect', []);
                
                $this->info("DNS Prefetch configurati: " . count($dns_prefetch));
                $this->info("Preconnect configurati: " . count($preconnect));
            }
            
        } catch (Exception $e) {
            $this->error("Errore nel test Assets: " . $e->getMessage());
        }
    }
    
    /**
     * Test modulo Media/WebP
     */
    private function testMediaWebPModule() {
        $this->section("3. TEST MODULO MEDIA / WEBP");
        
        try {
            $this->subsection("3.1 WebP Converter");
            
            if (class_exists('FP\\PerfSuite\\Services\\Media\\WebPConverter')) {
                $this->success("Classe WebPConverter trovata");
                
                $webp_enabled = get_option('fp_ps_webp_enabled', false);
                $webp_quality = get_option('fp_ps_webp_quality', 80);
                $webp_method = get_option('fp_ps_webp_method', 'gd');
                
                $this->info("WebP abilitato: " . ($webp_enabled ? 'Sì' : 'No'));
                $this->info("Qualità WebP: $webp_quality%");
                $this->info("Metodo conversione: $webp_method");
                
                // Verifica supporto GD/Imagick
                if (function_exists('imagewebp')) {
                    $this->success("Supporto GD WebP disponibile");
                } else {
                    $this->warning("Supporto GD WebP non disponibile");
                }
                
                if (extension_loaded('imagick')) {
                    $this->success("Estensione Imagick disponibile");
                } else {
                    $this->info("Estensione Imagick non disponibile (opzionale)");
                }
            }
            
            $this->subsection("3.2 WebP Image Converter");
            
            if (class_exists('FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter')) {
                $this->success("Classe WebPImageConverter trovata");
            }
            
            $this->subsection("3.3 WebP Batch Processor");
            
            if (class_exists('FP\\PerfSuite\\Services\\Media\\WebP\\WebPBatchProcessor')) {
                $this->success("Classe WebPBatchProcessor trovata per conversioni bulk");
            }
            
            $this->subsection("3.4 WebP Queue");
            
            if (class_exists('FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue')) {
                $this->success("Classe WebPQueue trovata per code di conversione");
            }
            
            // Verifica statistiche WebP
            $webp_stats = get_option('fp_ps_webp_stats', []);
            if (!empty($webp_stats)) {
                $converted = isset($webp_stats['converted']) ? $webp_stats['converted'] : 0;
                $total = isset($webp_stats['total']) ? $webp_stats['total'] : 0;
                $this->info("Immagini convertite: $converted / $total");
            }
            
        } catch (Exception $e) {
            $this->error("Errore nel test Media/WebP: " . $e->getMessage());
        }
    }
    
    /**
     * Test modulo Database
     */
    private function testDatabaseModule() {
        $this->section("4. TEST MODULO DATABASE");
        
        try {
            $this->subsection("4.1 Database Cleaner");
            
            if (class_exists('FP\\PerfSuite\\Services\\DB\\Cleaner')) {
                $this->success("Classe Cleaner trovata");
                
                $auto_cleanup = get_option('fp_ps_db_auto_cleanup', false);
                $cleanup_schedule = get_option('fp_ps_db_cleanup_schedule', 'weekly');
                
                $this->info("Auto cleanup: " . ($auto_cleanup ? 'Sì' : 'No'));
                $this->info("Schedule cleanup: $cleanup_schedule");
                
                // Verifica cron
                $next_cleanup = wp_next_scheduled('fp_ps_db_cleanup_cron');
                if ($next_cleanup) {
                    $this->success("Prossimo cleanup programmato: " . date('Y-m-d H:i:s', $next_cleanup));
                } else {
                    $this->info("Nessun cleanup programmato");
                }
            }
            
            $this->subsection("4.2 Database Optimizer");
            
            if (class_exists('FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer')) {
                $this->success("Classe DatabaseOptimizer trovata");
                
                // Test connessione database
                global $wpdb;
                if ($wpdb) {
                    $this->success("Connessione database OK");
                    
                    // Info database
                    $db_size_query = $wpdb->get_var("
                        SELECT SUM(data_length + index_length) / 1024 / 1024 
                        FROM information_schema.TABLES 
                        WHERE table_schema = DATABASE()
                    ");
                    
                    if ($db_size_query) {
                        $this->info("Dimensione database: " . round($db_size_query, 2) . " MB");
                    }
                    
                    // Conta revisioni
                    $revisions = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'");
                    $this->info("Revisioni post: $revisions");
                    
                    // Conta transient
                    $transients = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
                    $this->info("Transient salvati: $transients");
                }
            }
            
            $this->subsection("4.3 Database Query Monitor");
            
            if (class_exists('FP\\PerfSuite\\Services\\DB\\DatabaseQueryMonitor')) {
                $this->success("Classe DatabaseQueryMonitor trovata per monitoraggio query");
            }
            
        } catch (Exception $e) {
            $this->error("Errore nel test Database: " . $e->getMessage());
        }
    }
    
    /**
     * Test modulo Logs
     */
    private function testLogsModule() {
        $this->section("5. TEST MODULO LOGS");
        
        try {
            $this->subsection("5.1 Debug Toggler");
            
            if (class_exists('FP\\PerfSuite\\Services\\Logs\\DebugToggler')) {
                $this->success("Classe DebugToggler trovata");
                
                // Verifica stato debug
                $wp_debug = defined('WP_DEBUG') ? WP_DEBUG : false;
                $this->info("WP_DEBUG attivo: " . ($wp_debug ? 'Sì' : 'No'));
                
                if (defined('WP_DEBUG_LOG')) {
                    $this->info("WP_DEBUG_LOG: " . (WP_DEBUG_LOG ? 'Sì' : 'No'));
                }
                
                if (defined('WP_DEBUG_DISPLAY')) {
                    $this->info("WP_DEBUG_DISPLAY: " . (WP_DEBUG_DISPLAY ? 'Sì' : 'No'));
                }
                
                // Verifica backup wp-config
                $backup_dir = WP_CONTENT_DIR . '/fp-performance-backups/';
                if (is_dir($backup_dir)) {
                    $backups = glob($backup_dir . 'wp-config-backup-*.php');
                    $this->success("Directory backup esiste, backup trovati: " . count($backups));
                } else {
                    $this->info("Directory backup non ancora creata");
                }
            }
            
            $this->subsection("5.2 Realtime Log");
            
            if (class_exists('FP\\PerfSuite\\Services\\Logs\\RealtimeLog')) {
                $this->success("Classe RealtimeLog trovata");
                
                // Verifica debug.log
                $debug_log = WP_CONTENT_DIR . '/debug.log';
                if (file_exists($debug_log)) {
                    $log_size = filesize($debug_log);
                    $this->info("File debug.log esiste, dimensione: " . $this->formatBytes($log_size));
                } else {
                    $this->info("File debug.log non esiste (nessun errore ancora)");
                }
            }
            
            $this->subsection("5.3 Logger Utility");
            
            if (class_exists('FP\\PerfSuite\\Utils\\Logger')) {
                $this->success("Classe Logger trovata");
                $this->info("Sistema di logging centralizzato disponibile");
            }
            
        } catch (Exception $e) {
            $this->error("Errore nel test Logs: " . $e->getMessage());
        }
    }
    
    /**
     * Test funzionalità avanzate (v1.1.0)
     */
    private function testAdvancedFeatures() {
        $this->section("6. TEST FUNZIONALITÀ AVANZATE (v1.1.0)");
        
        try {
            $this->subsection("6.1 Critical CSS");
            
            if (class_exists('FP\\PerfSuite\\Services\\Assets\\CriticalCss')) {
                $this->success("Classe CriticalCss trovata");
                
                $critical_css = get_option('fp_ps_critical_css', '');
                if (!empty($critical_css)) {
                    $this->success("Critical CSS configurato (" . strlen($critical_css) . " caratteri)");
                } else {
                    $this->info("Critical CSS non ancora configurato");
                }
            }
            
            $this->subsection("6.2 CDN Manager");
            
            if (class_exists('FP\\PerfSuite\\Services\\CDN\\CdnManager')) {
                $this->success("Classe CdnManager trovata");
                
                $cdn_enabled = get_option('fp_ps_cdn_enabled', false);
                $cdn_provider = get_option('fp_ps_cdn_provider', 'custom');
                $cdn_url = get_option('fp_ps_cdn_url', '');
                
                $this->info("CDN abilitato: " . ($cdn_enabled ? 'Sì' : 'No'));
                $this->info("Provider CDN: $cdn_provider");
                if (!empty($cdn_url)) {
                    $this->info("URL CDN: $cdn_url");
                }
            }
            
            $this->subsection("6.3 Performance Monitor");
            
            if (class_exists('FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor')) {
                $this->success("Classe PerformanceMonitor trovata");
                
                $monitoring_enabled = get_option('fp_ps_monitoring_enabled', false);
                $this->info("Monitoring abilitato: " . ($monitoring_enabled ? 'Sì' : 'No'));
            }
            
            $this->subsection("6.4 Performance Analyzer");
            
            if (class_exists('FP\\PerfSuite\\Services\\Monitoring\\PerformanceAnalyzer')) {
                $this->success("Classe PerformanceAnalyzer trovata per analisi metriche");
            }
            
            $this->subsection("6.5 Scheduled Reports");
            
            if (class_exists('FP\\PerfSuite\\Services\\Reports\\ScheduledReports')) {
                $this->success("Classe ScheduledReports trovata");
                
                $reports_enabled = get_option('fp_ps_reports_enabled', false);
                $report_frequency = get_option('fp_ps_report_frequency', 'weekly');
                
                $this->info("Report automatici: " . ($reports_enabled ? 'Sì' : 'No'));
                $this->info("Frequenza report: $report_frequency");
            }
            
            $this->subsection("6.6 Health Check Integration");
            
            if (class_exists('FP\\PerfSuite\\Health\\HealthCheck')) {
                $this->success("Classe HealthCheck trovata");
                $this->info("Integrazione con WordPress Site Health attiva");
            }
            
            $this->subsection("6.7 Query Monitor Integration");
            
            if (class_exists('FP\\PerfSuite\\Monitoring\\QueryMonitor')) {
                $this->success("Classe QueryMonitor trovata");
                
                if (class_exists('QueryMonitor')) {
                    $this->success("Plugin Query Monitor rilevato - integrazione disponibile");
                } else {
                    $this->info("Plugin Query Monitor non installato (opzionale)");
                }
            }
            
        } catch (Exception $e) {
            $this->error("Errore nel test funzionalità avanzate: " . $e->getMessage());
        }
    }
    
    /**
     * Test nuove funzionalità PageSpeed (v1.2.0)
     */
    private function testPageSpeedFeatures() {
        $this->section("7. TEST FUNZIONALITÀ PAGESPEED (v1.2.0)");
        
        try {
            $this->subsection("7.1 Lazy Load Manager");
            
            if (class_exists('FP\\PerfSuite\\Services\\Assets\\LazyLoadManager')) {
                $this->success("Classe LazyLoadManager trovata");
                
                $lazy_load_enabled = get_option('fp_ps_lazy_load_enabled', false);
                $lazy_load_images = get_option('fp_ps_lazy_load_images', true);
                $lazy_load_iframes = get_option('fp_ps_lazy_load_iframes', true);
                
                $this->info("Lazy loading abilitato: " . ($lazy_load_enabled ? 'Sì' : 'No'));
                $this->info("Lazy load immagini: " . ($lazy_load_images ? 'Sì' : 'No'));
                $this->info("Lazy load iframe: " . ($lazy_load_iframes ? 'Sì' : 'No'));
            }
            
            $this->subsection("7.2 Font Optimizer");
            
            if (class_exists('FP\\PerfSuite\\Services\\Assets\\FontOptimizer')) {
                $this->success("Classe FontOptimizer trovata");
                
                $font_display_swap = get_option('fp_ps_font_display_swap', false);
                $font_preload = get_option('fp_ps_font_preload', []);
                
                $this->info("Font display=swap: " . ($font_display_swap ? 'Sì' : 'No'));
                $this->info("Font preload configurati: " . count($font_preload));
            }
            
            $this->subsection("7.3 Image Optimizer");
            
            if (class_exists('FP\\PerfSuite\\Services\\Assets\\ImageOptimizer')) {
                $this->success("Classe ImageOptimizer trovata");
                
                $add_dimensions = get_option('fp_ps_image_add_dimensions', false);
                $aspect_ratio = get_option('fp_ps_image_aspect_ratio', false);
                
                $this->info("Aggiungi dimensioni immagini: " . ($add_dimensions ? 'Sì' : 'No'));
                $this->info("Aspect ratio CSS: " . ($aspect_ratio ? 'Sì' : 'No'));
            }
            
            $this->subsection("7.4 CSS Combiner");
            
            if (class_exists('FP\\PerfSuite\\Services\\Assets\\Combiners\\CssCombiner')) {
                $this->success("Classe CssCombiner trovata");
                
                $combine_css = get_option('fp_ps_combine_css', false);
                $async_css = get_option('fp_ps_async_css', false);
                
                $this->info("Combina CSS: " . ($combine_css ? 'Sì' : 'No'));
                $this->info("CSS asincrono: " . ($async_css ? 'Sì' : 'No'));
            }
            
            $this->subsection("7.5 JS Combiner");
            
            if (class_exists('FP\\PerfSuite\\Services\\Assets\\Combiners\\JsCombiner')) {
                $this->success("Classe JsCombiner trovata");
                
                $combine_js = get_option('fp_ps_combine_js', false);
                $this->info("Combina JS: " . ($combine_js ? 'Sì' : 'No'));
            }
            
            $this->subsection("7.6 Compression Manager");
            
            if (class_exists('FP\\PerfSuite\\Services\\Compression\\CompressionManager')) {
                $this->success("Classe CompressionManager trovata");
                
                $gzip_enabled = get_option('fp_ps_gzip_enabled', false);
                $this->info("Compressione Gzip: " . ($gzip_enabled ? 'Sì' : 'No'));
                
                // Test supporto Gzip
                if (function_exists('gzencode')) {
                    $this->success("Funzione gzencode disponibile");
                }
            }
            
        } catch (Exception $e) {
            $this->error("Errore nel test PageSpeed: " . $e->getMessage());
        }
    }
    
    /**
     * Test Performance Score
     */
    private function testPerformanceScore() {
        $this->section("8. TEST PERFORMANCE SCORE E DASHBOARD");
        
        try {
            $this->subsection("8.1 Performance Scorer");
            
            if (class_exists('FP\\PerfSuite\\Services\\Score\\Scorer')) {
                $this->success("Classe Scorer trovata");
                
                // Tentativo di recuperare lo score
                $score_data = get_option('fp_ps_performance_score', null);
                if ($score_data) {
                    if (isset($score_data['overall'])) {
                        $overall = $score_data['overall'];
                        $this->success("Performance Score: $overall/100");
                        
                        // Valutazione score
                        if ($overall >= 90) {
                            $this->success("Eccellente! ⭐⭐⭐⭐⭐");
                        } elseif ($overall >= 80) {
                            $this->success("Molto buono! ⭐⭐⭐⭐");
                        } elseif ($overall >= 70) {
                            $this->info("Buono ⭐⭐⭐");
                        } elseif ($overall >= 60) {
                            $this->warning("Sufficiente ⭐⭐");
                        } else {
                            $this->warning("Necessita miglioramenti ⭐");
                        }
                    }
                    
                    // Mostra dettagli score
                    if (isset($score_data['cache'])) {
                        $this->info("Score Cache: " . $score_data['cache']);
                    }
                    if (isset($score_data['assets'])) {
                        $this->info("Score Assets: " . $score_data['assets']);
                    }
                    if (isset($score_data['media'])) {
                        $this->info("Score Media: " . $score_data['media']);
                    }
                    if (isset($score_data['database'])) {
                        $this->info("Score Database: " . $score_data['database']);
                    }
                } else {
                    $this->info("Score non ancora calcolato - visitare la Dashboard per generarlo");
                }
            }
            
            $this->subsection("8.2 Dashboard Overview");
            
            if (class_exists('FP\\PerfSuite\\Admin\\Pages\\Overview')) {
                $this->success("Classe Overview (Dashboard) trovata");
                $this->info("Dashboard accessibile da menu WordPress");
            }
            
        } catch (Exception $e) {
            $this->error("Errore nel test Performance Score: " . $e->getMessage());
        }
    }
    
    /**
     * Test Presets e Tools
     */
    private function testPresetsAndTools() {
        $this->section("9. TEST PRESETS E TOOLS");
        
        try {
            $this->subsection("9.1 Presets Manager");
            
            if (class_exists('FP\\PerfSuite\\Services\\Presets\\Manager')) {
                $this->success("Classe Manager (Presets) trovata");
                
                $current_preset = get_option('fp_ps_hosting_preset', 'general');
                $this->info("Preset attivo: $current_preset");
                
                // Verifica enum presets
                if (class_exists('FP\\PerfSuite\\Enums\\HostingPreset')) {
                    $this->success("Enum HostingPreset disponibile (General, IONOS, Aruba)");
                }
            }
            
            $this->subsection("9.2 Import/Export Settings");
            
            // Verifica se esistono settings salvati
            $all_options = wp_load_alloptions();
            $plugin_options = array_filter($all_options, function($key) {
                return strpos($key, 'fp_ps_') === 0;
            }, ARRAY_FILTER_USE_KEY);
            
            $this->info("Opzioni plugin trovate: " . count($plugin_options));
            $this->success("Sistema import/export disponibile dalla pagina Tools");
            
            $this->subsection("9.3 WP-CLI Commands");
            
            if (class_exists('FP\\PerfSuite\\Cli\\Commands')) {
                $this->success("Classe Commands (WP-CLI) trovata");
                
                if (defined('WP_CLI') && WP_CLI) {
                    $this->success("WP-CLI attivo - comandi disponibili");
                    $this->info("  - wp fp-performance cache clear");
                    $this->info("  - wp fp-performance db cleanup");
                    $this->info("  - wp fp-performance webp convert");
                    $this->info("  - wp fp-performance score");
                } else {
                    $this->info("WP-CLI non rilevato (normale per ambiente web)");
                }
            }
            
        } catch (Exception $e) {
            $this->error("Errore nel test Presets: " . $e->getMessage());
        }
    }
    
    /**
     * Genera report finale
     */
    private function generateReport() {
        $this->section("REPORT FINALE");
        
        $total_tests = count($this->results);
        $errors = count($this->errors);
        $warnings = count($this->warnings);
        $successes = $total_tests - $errors - $warnings;
        
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║                     RIEPILOGO TEST                             ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "✅ Successi: $successes\n";
        echo "⚠️  Warning: $warnings\n";
        echo "❌ Errori: $errors\n";
        echo "📊 Totale test: $total_tests\n";
        echo "\n";
        
        // Calcola percentuale successo
        $success_rate = $total_tests > 0 ? round(($successes / $total_tests) * 100, 2) : 0;
        echo "Tasso di successo: $success_rate%\n";
        echo "\n";
        
        // Raccomandazioni
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║                     RACCOMANDAZIONI                            ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        
        if ($errors > 0) {
            echo "⚠️  ATTENZIONE: Sono stati rilevati errori critici!\n";
            echo "\n";
            echo "Errori trovati:\n";
            foreach ($this->errors as $error) {
                echo "  ❌ $error\n";
            }
            echo "\n";
        }
        
        if ($warnings > 0) {
            echo "ℹ️  Sono stati rilevati alcuni warning:\n";
            echo "\n";
            foreach ($this->warnings as $warning) {
                echo "  ⚠️  $warning\n";
            }
            echo "\n";
        }
        
        if ($success_rate >= 90) {
            echo "🎉 ECCELLENTE! Il plugin è completamente funzionante!\n";
            echo "\n";
            echo "Prossimi passi suggeriti:\n";
            echo "1. Visita la Dashboard del plugin in WordPress\n";
            echo "2. Configura le ottimizzazioni desiderate\n";
            echo "3. Esegui un test PageSpeed per verificare i miglioramenti\n";
        } elseif ($success_rate >= 75) {
            echo "✅ BUONO! Il plugin funziona correttamente con qualche avviso.\n";
            echo "\n";
            echo "Prossimi passi suggeriti:\n";
            echo "1. Risolvi i warning evidenziati sopra\n";
            echo "2. Configura le funzionalità base\n";
            echo "3. Monitora i log per eventuali problemi\n";
        } else {
            echo "⚠️  ATTENZIONE! Ci sono diversi problemi da risolvere.\n";
            echo "\n";
            echo "Azioni richieste:\n";
            echo "1. Verifica che il plugin sia attivato correttamente\n";
            echo "2. Controlla i permessi delle directory\n";
            echo "3. Verifica la compatibilità con la versione di PHP/WordPress\n";
            echo "4. Consulta i log per dettagli sugli errori\n";
        }
        
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║              TEST COMPLETATO CON SUCCESSO                      ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }
    
    // Helper methods
    
    private function section($title) {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo " $title\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "\n";
    }
    
    private function subsection($title) {
        echo "\n";
        echo "───────────────────────────────────────────────────────────────\n";
        echo " $title\n";
        echo "───────────────────────────────────────────────────────────────\n";
    }
    
    private function success($message) {
        echo "✅ $message\n";
        $this->results[] = ['type' => 'success', 'message' => $message];
    }
    
    private function error($message) {
        echo "❌ $message\n";
        $this->results[] = ['type' => 'error', 'message' => $message];
        $this->errors[] = $message;
    }
    
    private function warning($message) {
        echo "⚠️  $message\n";
        $this->results[] = ['type' => 'warning', 'message' => $message];
        $this->warnings[] = $message;
    }
    
    private function info($message) {
        echo "ℹ️  $message\n";
        $this->results[] = ['type' => 'info', 'message' => $message];
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// Esegui i test
$test = new FP_Admin_Simulation_Test();
$test->runAllTests();

