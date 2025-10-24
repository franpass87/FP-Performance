<?php
/**
 * Test Salvataggio Opzioni FP Performance Suite
 * 
 * Verifica che ogni opzione si salvi correttamente quando modificata
 * 
 * @package FP\PerfSuite\Tests
 * @author Francesco Passeri
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class OptionsSavingTest
{
    private $results = [];
    private $testedOptions = [];

    public function __construct()
    {
        $this->runSavingTests();
    }

    /**
     * Esegue test di salvataggio
     */
    public function runSavingTests(): void
    {
        echo "<h1>💾 Test Salvataggio Opzioni FP Performance Suite</h1>\n";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
        
        $this->testCacheOptionsSaving();
        $this->testAssetsOptionsSaving();
        $this->testDatabaseOptionsSaving();
        $this->testBackendOptionsSaving();
        $this->testMobileOptionsSaving();
        $this->testCompressionOptionsSaving();
        $this->testCdnOptionsSaving();
        $this->testSecurityOptionsSaving();
        $this->testMonitoringOptionsSaving();
        $this->testJavaScriptOptimizationSaving();
        
        $this->displayResults();
        echo "</div>\n";
    }

    /**
     * Test salvataggio opzioni Cache
     */
    private function testCacheOptionsSaving(): void
    {
        echo "<h2>🚀 Test Salvataggio Opzioni Cache</h2>\n";
        
        // Test Page Cache
        $this->testOptionSaving('page_cache_enabled', 'Page Cache', 'FP\\PerfSuite\\Services\\Cache\\PageCache', [
            'enabled' => true,
            'ttl' => 3600
        ]);
        
        // Test Predictive Prefetching
        $this->testOptionSaving('prefetch_enabled', 'Predictive Prefetching', 'FP\\PerfSuite\\Services\\Assets\\PredictivePrefetching', [
            'enabled' => true,
            'strategy' => 'hover',
            'hover_delay' => 100,
            'prefetch_limit' => 5
        ]);
        
        // Test Cache Rules
        $this->testOptionSaving('cache_rules_enabled', 'Cache Rules', 'FP\\PerfSuite\\Services\\Cache\\Headers', [
            'cache_rules' => [
                'enabled' => true,
                'html_cache' => true,
                'fonts_cache' => true
            ]
        ]);
        
        // Test Browser Cache
        $this->testOptionSaving('browser_cache_enabled', 'Browser Cache', 'FP\\PerfSuite\\Services\\Cache\\Headers', [
            'enabled' => true,
            'headers' => true,
            'expires_ttl' => 2592000
        ]);
        
        // Test PWA
        $this->testOptionSaving('pwa_enabled', 'PWA Service Worker', 'FP\\PerfSuite\\Services\\PWA\\ServiceWorkerManager', [
            'enabled' => true,
            'cache_strategy' => 'cache_first',
            'cache_duration' => 86400,
            'offline_fallback' => true
        ]);
        
        // Test Edge Cache
        $this->testOptionSaving('edge_cache_enabled', 'Edge Cache', 'FP\\PerfSuite\\Services\\Cache\\EdgeCacheManager', [
            'enabled' => true,
            'provider' => 'cloudflare',
            'api_key' => 'test_key',
            'zone_id' => 'test_zone'
        ]);
    }

    /**
     * Test salvataggio opzioni Assets
     */
    private function testAssetsOptionsSaving(): void
    {
        echo "<h2>📦 Test Salvataggio Opzioni Assets</h2>\n";
        
        // Test Font Optimization
        $this->testOptionSaving('font_optimization', 'Font Optimization', 'FP\\PerfSuite\\Services\\Assets\\FontOptimizer', [
            'enabled' => true,
            'preload_critical' => true,
            'display_swap' => true
        ]);
        
        // Test Image Optimization
        $this->testOptionSaving('image_optimization', 'Image Optimization', 'FP\\PerfSuite\\Services\\Assets\\ImageOptimizer', [
            'enabled' => true,
            'lazy_loading' => true,
            'webp_conversion' => true
        ]);
        
        // Test Lazy Loading
        $this->testOptionSaving('lazy_loading', 'Lazy Loading', 'FP\\PerfSuite\\Services\\Assets\\LazyLoadManager', [
            'enabled' => true,
            'images' => true,
            'videos' => true,
            'iframes' => true
        ]);
    }

    /**
     * Test salvataggio opzioni Database
     */
    private function testDatabaseOptionsSaving(): void
    {
        echo "<h2>💾 Test Salvataggio Opzioni Database</h2>\n";
        
        // Test Database Cleaner
        $this->testOptionSaving('database_cleaner', 'Database Cleaner', 'FP\\PerfSuite\\Services\\DB\\Cleaner', [
            'enabled' => true,
            'revisions' => true,
            'spam' => true,
            'trash' => true
        ]);
        
        // Test Database Optimizer
        $this->testOptionSaving('database_optimizer', 'Database Optimizer', 'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer', [
            'enabled' => true,
            'auto_optimize' => true,
            'query_cache' => true
        ]);
        
        // Test Query Cache Manager
        $this->testOptionSaving('query_cache_manager', 'Query Cache Manager', 'FP\\PerfSuite\\Services\\DB\\QueryCacheManager', [
            'enabled' => true,
            'ttl' => 3600
        ]);
    }

    /**
     * Test salvataggio opzioni Backend
     */
    private function testBackendOptionsSaving(): void
    {
        echo "<h2>⚙️ Test Salvataggio Opzioni Backend</h2>\n";
        
        // Test Backend Optimizer
        $this->testOptionSaving('backend_optimizer', 'Backend Optimizer', 'FP\\PerfSuite\\Services\\Admin\\BackendOptimizer', [
            'enabled' => true,
            'optimize_heartbeat' => true,
            'limit_revisions' => true,
            'optimize_dashboard' => true,
            'admin_bar' => true
        ]);
    }

    /**
     * Test salvataggio opzioni Mobile
     */
    private function testMobileOptionsSaving(): void
    {
        echo "<h2>📱 Test Salvataggio Opzioni Mobile</h2>\n";
        
        // Test Mobile Optimizer
        $this->testOptionSaving('mobile_optimizer', 'Mobile Optimizer', 'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer', [
            'enabled' => true,
            'touch_optimization' => true,
            'mobile_cache' => true,
            'responsive_images' => true
        ]);
        
        // Test Touch Optimizer
        $this->testOptionSaving('touch_optimizer', 'Touch Optimizer', 'FP\\PerfSuite\\Services\\Mobile\\TouchOptimizer', [
            'enabled' => true,
            'gesture_optimization' => true
        ]);
    }

    /**
     * Test salvataggio opzioni Compression
     */
    private function testCompressionOptionsSaving(): void
    {
        echo "<h2>🗜️ Test Salvataggio Opzioni Compression</h2>\n";
        
        // Test Compression Manager
        $this->testOptionSaving('compression_manager', 'Compression Manager', 'FP\\PerfSuite\\Services\\Compression\\CompressionManager', [
            'enabled' => true,
            'gzip' => true,
            'brotli' => true,
            'minify_html' => true,
            'minify_css' => true,
            'minify_js' => true
        ]);
    }

    /**
     * Test salvataggio opzioni CDN
     */
    private function testCdnOptionsSaving(): void
    {
        echo "<h2>🌐 Test Salvataggio Opzioni CDN</h2>\n";
        
        // Test CDN Manager
        $this->testOptionSaving('cdn_manager', 'CDN Manager', 'FP\\PerfSuite\\Services\\CDN\\CdnManager', [
            'enabled' => true,
            'provider' => 'cloudflare',
            'api_key' => 'test_key',
            'zone_id' => 'test_zone'
        ]);
    }

    /**
     * Test salvataggio opzioni Security
     */
    private function testSecurityOptionsSaving(): void
    {
        echo "<h2>🛡️ Test Salvataggio Opzioni Security</h2>\n";
        
        // Test Htaccess Security
        $this->testOptionSaving('htaccess_security', 'Htaccess Security', 'FP\\PerfSuite\\Services\\Security\\HtaccessSecurity', [
            'enabled' => true,
            'cache_rules' => true,
            'security_headers' => true
        ]);
    }

    /**
     * Test salvataggio opzioni Monitoring
     */
    private function testMonitoringOptionsSaving(): void
    {
        echo "<h2>📊 Test Salvataggio Opzioni Monitoring</h2>\n";
        
        // Test Performance Monitor
        $this->testOptionSaving('performance_monitor', 'Performance Monitor', 'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor', [
            'enabled' => true,
            'core_web_vitals' => true,
            'real_user_monitoring' => true
        ]);
        
        // Test Core Web Vitals Monitor
        $this->testOptionSaving('core_web_vitals_monitor', 'Core Web Vitals Monitor', 'FP\\PerfSuite\\Services\\Monitoring\\CoreWebVitalsMonitor', [
            'enabled' => true,
            'lcp' => true,
            'fid' => true,
            'cls' => true
        ]);
    }

    /**
     * Test salvataggio JavaScript Optimization
     */
    private function testJavaScriptOptimizationSaving(): void
    {
        echo "<h2>⚡ Test Salvataggio JavaScript Optimization</h2>\n";
        
        // Test Unused JavaScript Optimizer
        $this->testOptionSaving('unused_js_optimizer', 'Unused JavaScript Optimizer', 'FP\\PerfSuite\\Services\\Assets\\UnusedJavaScriptOptimizer', [
            'enabled' => true,
            'aggressive_mode' => true
        ]);
        
        // Test Code Splitting Manager
        $this->testOptionSaving('code_splitting_manager', 'Code Splitting Manager', 'FP\\PerfSuite\\Services\\Assets\\CodeSplittingManager', [
            'enabled' => true,
            'chunk_size' => 100000,
            'lazy_loading' => true
        ]);
        
        // Test Tree Shaker
        $this->testOptionSaving('tree_shaker', 'Tree Shaker', 'FP\\PerfSuite\\Services\\Assets\\JavaScriptTreeShaker', [
            'enabled' => true,
            'aggressive_mode' => true
        ]);
    }

    /**
     * Test salvataggio di un'opzione specifica
     */
    private function testOptionSaving(string $optionName, string $description, string $serviceClass, array $testData): void
    {
        echo "<h4>💾 Test Salvataggio {$description} ({$optionName})</h4>\n";
        
        // Verifica se la classe esiste
        if (!class_exists($serviceClass)) {
            $this->addResult("❌ Classe {$serviceClass} non trovata per {$description}", 'error');
            return;
        }
        
        $this->addResult("✅ Classe {$serviceClass} trovata per {$description}", 'success');
        
        // Test istanziazione
        try {
            $service = new $serviceClass();
            $this->addResult("✅ Servizio {$description} istanziato correttamente", 'success');
            
            // Test salvataggio
            if (method_exists($service, 'update')) {
                // Salva dati di test
                $service->update($testData);
                $this->addResult("✅ Dati salvati per {$description}", 'success');
                
                // Verifica salvataggio
                if (method_exists($service, 'settings')) {
                    $savedSettings = $service->settings();
                    
                    // Verifica che i dati siano stati salvati
                    $saved = true;
                    foreach ($testData as $key => $value) {
                        if (is_array($value)) {
                            foreach ($value as $subKey => $subValue) {
                                if (!isset($savedSettings[$key][$subKey]) || $savedSettings[$key][$subKey] !== $subValue) {
                                    $saved = false;
                                    break;
                                }
                            }
                        } else {
                            if (!isset($savedSettings[$key]) || $savedSettings[$key] !== $value) {
                                $saved = false;
                                break;
                            }
                        }
                    }
                    
                    if ($saved) {
                        $this->addResult("✅ Salvataggio verificato per {$description}", 'success');
                    } else {
                        $this->addResult("❌ Salvataggio non verificato per {$description}", 'error');
                    }
                } else {
                    $this->addResult("⚠️ Metodo settings() non trovato per {$description}", 'warning');
                }
                
                // Test status
                if (method_exists($service, 'status')) {
                    $status = $service->status();
                    $this->addResult("✅ Status verificato per {$description}: " . json_encode($status), 'success');
                }
                
            } else {
                $this->addResult("⚠️ Metodo update() non trovato per {$description}", 'warning');
            }
            
        } catch (\Exception $e) {
            $this->addResult("❌ Errore salvataggio {$description}: " . $e->getMessage(), 'error');
        }
    }

    /**
     * Aggiunge risultato
     */
    private function addResult(string $message, string $type): void
    {
        $this->results[] = [
            'message' => $message,
            'type' => $type
        ];
    }

    /**
     * Mostra risultati
     */
    private function displayResults(): void
    {
        echo "<h2>📊 Risultati Test Salvataggio</h2>\n";
        
        $success = 0;
        $warnings = 0;
        $errors = 0;
        
        foreach ($this->results as $result) {
            $color = match($result['type']) {
                'success' => '#28a745',
                'warning' => '#ffc107', 
                'error' => '#dc3545',
                default => '#6c757d'
            };
            
            echo "<div style='color: {$color}; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>";
            echo $result['message'];
            echo "</div>\n";
            
            match($result['type']) {
                'success' => $success++,
                'warning' => $warnings++,
                'error' => $errors++
            };
        }
        
        echo "<div style='margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 8px;'>";
        echo "<h3>📈 Riepilogo Test Salvataggio</h3>";
        echo "<p><strong>✅ Successi:</strong> {$success}</p>";
        echo "<p><strong>⚠️ Avvisi:</strong> {$warnings}</p>";
        echo "<p><strong>❌ Errori:</strong> {$errors}</p>";
        
        $total = $success + $warnings + $errors;
        $score = $total > 0 ? round(($success / $total) * 100) : 0;
        
        echo "<p><strong>🎯 Score Salvataggio:</strong> {$score}%</p>";
        
        if ($score >= 90) {
            echo "<p style='color: #28a745; font-weight: bold;'>✅ Tutte le opzioni si salvano correttamente!</p>";
        } elseif ($score >= 70) {
            echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ La maggior parte delle opzioni si salva, alcune potrebbero avere problemi.</p>";
        } else {
            echo "<p style='color: #dc3545; font-weight: bold;'>❌ Molte opzioni non si salvano correttamente.</p>";
        }
        
        echo "</div>";
    }
}

// Esegui test se chiamato direttamente
if (isset($_GET['test_options_saving'])) {
    $test = new OptionsSavingTest();
}
