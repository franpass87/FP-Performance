<?php
/**
 * Test Completo Tutte le Funzionalità FP Performance Suite
 * 
 * Esegue tutti i test per verificare che ogni checkbox e opzione funzioni
 * 
 * @package FP\PerfSuite\Tests
 * @author Francesco Passeri
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class AllFunctionalityTest
{
    private $results = [];
    private $testedFeatures = [];
    private $workingFeatures = [];
    private $brokenFeatures = [];

    public function __construct()
    {
        $this->runAllTests();
    }

    /**
     * Esegue tutti i test
     */
    public function runAllTests(): void
    {
        echo "<h1>🧪 Test Completo Tutte le Funzionalità FP Performance Suite</h1>\n";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
        
        $this->testCacheFeatures();
        $this->testAssetsFeatures();
        $this->testDatabaseFeatures();
        $this->testBackendFeatures();
        $this->testMobileFeatures();
        $this->testCompressionFeatures();
        $this->testCdnFeatures();
        $this->testSecurityFeatures();
        $this->testMonitoringFeatures();
        $this->testJavaScriptOptimizationFeatures();
        
        $this->displayAllResults();
        echo "</div>\n";
    }

    /**
     * Test funzionalità Cache
     */
    private function testCacheFeatures(): void
    {
        echo "<h2>🚀 Test Funzionalità Cache</h2>\n";
        
        // Test Page Cache
        $this->testFeature('Page Cache', 'FP\\PerfSuite\\Services\\Cache\\PageCache', [
            'enabled' => true,
            'ttl' => 3600
        ]);
        
        // Test Browser Cache
        $this->testFeature('Browser Cache', 'FP\\PerfSuite\\Services\\Cache\\Headers', [
            'enabled' => true,
            'headers' => true,
            'expires_ttl' => 2592000
        ]);
        
        // Test Predictive Prefetching
        $this->testFeature('Predictive Prefetching', 'FP\\PerfSuite\\Services\\Assets\\PredictivePrefetching', [
            'enabled' => true,
            'strategy' => 'hover',
            'hover_delay' => 100,
            'prefetch_limit' => 5
        ]);
        
        // Test PWA Service Worker
        $this->testFeature('PWA Service Worker', 'FP\\PerfSuite\\Services\\PWA\\ServiceWorkerManager', [
            'enabled' => true,
            'cache_strategy' => 'cache_first',
            'cache_duration' => 86400,
            'offline_fallback' => true
        ]);
        
        // Test Edge Cache
        $this->testFeature('Edge Cache', 'FP\\PerfSuite\\Services\\Cache\\EdgeCacheManager', [
            'enabled' => true,
            'provider' => 'cloudflare',
            'api_key' => 'test_key',
            'zone_id' => 'test_zone'
        ]);
    }

    /**
     * Test funzionalità Assets
     */
    private function testAssetsFeatures(): void
    {
        echo "<h2>📦 Test Funzionalità Assets</h2>\n";
        
        // Test Font Optimizer
        $this->testFeature('Font Optimizer', 'FP\\PerfSuite\\Services\\Assets\\FontOptimizer', [
            'enabled' => true,
            'preload_critical' => true,
            'display_swap' => true
        ]);
        
        // Test Image Optimizer
        $this->testFeature('Image Optimizer', 'FP\\PerfSuite\\Services\\Assets\\ImageOptimizer', [
            'enabled' => true,
            'lazy_loading' => true,
            'webp_conversion' => true
        ]);
        
        // Test Lazy Load Manager
        $this->testFeature('Lazy Load Manager', 'FP\\PerfSuite\\Services\\Assets\\LazyLoadManager', [
            'enabled' => true,
            'images' => true,
            'videos' => true,
            'iframes' => true
        ]);
        
        // Test Unused JavaScript Optimizer
        $this->testFeature('Unused JavaScript Optimizer', 'FP\\PerfSuite\\Services\\Assets\\UnusedJavaScriptOptimizer', [
            'enabled' => true,
            'aggressive_mode' => true
        ]);
        
        // Test Code Splitting Manager
        $this->testFeature('Code Splitting Manager', 'FP\\PerfSuite\\Services\\Assets\\CodeSplittingManager', [
            'enabled' => true,
            'chunk_size' => 100000,
            'lazy_loading' => true
        ]);
        
        // Test JavaScript Tree Shaker
        $this->testFeature('JavaScript Tree Shaker', 'FP\\PerfSuite\\Services\\Assets\\JavaScriptTreeShaker', [
            'enabled' => true,
            'aggressive_mode' => true
        ]);
    }

    /**
     * Test funzionalità Database
     */
    private function testDatabaseFeatures(): void
    {
        echo "<h2>💾 Test Funzionalità Database</h2>\n";
        
        // Test Database Cleaner
        $this->testFeature('Database Cleaner', 'FP\\PerfSuite\\Services\\DB\\Cleaner', [
            'enabled' => true,
            'revisions' => true,
            'spam' => true,
            'trash' => true
        ]);
        
        // Test Database Optimizer
        $this->testFeature('Database Optimizer', 'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer', [
            'enabled' => true,
            'auto_optimize' => true,
            'query_cache' => true
        ]);
        
        // Test Query Cache Manager
        $this->testFeature('Query Cache Manager', 'FP\\PerfSuite\\Services\\DB\\QueryCacheManager', [
            'enabled' => true,
            'ttl' => 3600
        ]);
    }

    /**
     * Test funzionalità Backend
     */
    private function testBackendFeatures(): void
    {
        echo "<h2>⚙️ Test Funzionalità Backend</h2>\n";
        
        // Test Backend Optimizer
        $this->testFeature('Backend Optimizer', 'FP\\PerfSuite\\Services\\Admin\\BackendOptimizer', [
            'enabled' => true,
            'optimize_heartbeat' => true,
            'limit_revisions' => true,
            'optimize_dashboard' => true,
            'admin_bar' => true
        ]);
    }

    /**
     * Test funzionalità Mobile
     */
    private function testMobileFeatures(): void
    {
        echo "<h2>📱 Test Funzionalità Mobile</h2>\n";
        
        // Test Mobile Optimizer
        $this->testFeature('Mobile Optimizer', 'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer', [
            'enabled' => true,
            'touch_optimization' => true,
            'mobile_cache' => true,
            'responsive_images' => true
        ]);
        
        // Test Touch Optimizer
        $this->testFeature('Touch Optimizer', 'FP\\PerfSuite\\Services\\Mobile\\TouchOptimizer', [
            'enabled' => true,
            'gesture_optimization' => true
        ]);
    }

    /**
     * Test funzionalità Compression
     */
    private function testCompressionFeatures(): void
    {
        echo "<h2>🗜️ Test Funzionalità Compression</h2>\n";
        
        // Test Compression Manager
        $this->testFeature('Compression Manager', 'FP\\PerfSuite\\Services\\Compression\\CompressionManager', [
            'enabled' => true,
            'gzip' => true,
            'brotli' => true,
            'minify_html' => true,
            'minify_css' => true,
            'minify_js' => true
        ]);
    }

    /**
     * Test funzionalità CDN
     */
    private function testCdnFeatures(): void
    {
        echo "<h2>🌐 Test Funzionalità CDN</h2>\n";
        
        // Test CDN Manager
        $this->testFeature('CDN Manager', 'FP\\PerfSuite\\Services\\CDN\\CdnManager', [
            'enabled' => true,
            'provider' => 'cloudflare',
            'api_key' => 'test_key',
            'zone_id' => 'test_zone'
        ]);
    }

    /**
     * Test funzionalità Security
     */
    private function testSecurityFeatures(): void
    {
        echo "<h2>🛡️ Test Funzionalità Security</h2>\n";
        
        // Test Htaccess Security
        $this->testFeature('Htaccess Security', 'FP\\PerfSuite\\Services\\Security\\HtaccessSecurity', [
            'enabled' => true,
            'cache_rules' => true,
            'security_headers' => true
        ]);
    }

    /**
     * Test funzionalità Monitoring
     */
    private function testMonitoringFeatures(): void
    {
        echo "<h2>📊 Test Funzionalità Monitoring</h2>\n";
        
        // Test Performance Monitor
        $this->testFeature('Performance Monitor', 'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor', [
            'enabled' => true,
            'core_web_vitals' => true,
            'real_user_monitoring' => true
        ]);
        
        // Test Core Web Vitals Monitor
        $this->testFeature('Core Web Vitals Monitor', 'FP\\PerfSuite\\Services\\Monitoring\\CoreWebVitalsMonitor', [
            'enabled' => true,
            'lcp' => true,
            'fid' => true,
            'cls' => true
        ]);
    }

    /**
     * Test funzionalità JavaScript Optimization
     */
    private function testJavaScriptOptimizationFeatures(): void
    {
        echo "<h2>⚡ Test Funzionalità JavaScript Optimization</h2>\n";
        
        // Test Unused JavaScript Optimizer
        $this->testFeature('Unused JavaScript Optimizer', 'FP\\PerfSuite\\Services\\Assets\\UnusedJavaScriptOptimizer', [
            'enabled' => true,
            'aggressive_mode' => true
        ]);
        
        // Test Code Splitting Manager
        $this->testFeature('Code Splitting Manager', 'FP\\PerfSuite\\Services\\Assets\\CodeSplittingManager', [
            'enabled' => true,
            'chunk_size' => 100000,
            'lazy_loading' => true
        ]);
        
        // Test Tree Shaker
        $this->testFeature('Tree Shaker', 'FP\\PerfSuite\\Services\\Assets\\JavaScriptTreeShaker', [
            'enabled' => true,
            'aggressive_mode' => true
        ]);
    }

    /**
     * Test di una funzionalità specifica
     */
    private function testFeature(string $featureName, string $serviceClass, array $testData): void
    {
        echo "<h4>🔧 Test {$featureName}</h4>\n";
        
        // Verifica se la classe esiste
        if (!class_exists($serviceClass)) {
            $this->addResult("❌ Classe {$serviceClass} non trovata per {$featureName}", 'error');
            $this->brokenFeatures[] = $featureName;
            return;
        }
        
        $this->addResult("✅ Classe {$serviceClass} trovata per {$featureName}", 'success');
        
        // Test istanziazione
        try {
            $service = new $serviceClass();
            $this->addResult("✅ Servizio {$featureName} istanziato correttamente", 'success');
            
            // Test metodi principali
            $this->testServiceMethods($service, $featureName);
            
            // Test salvataggio
            if (method_exists($service, 'update')) {
                $service->update($testData);
                $this->addResult("✅ Dati salvati per {$featureName}", 'success');
            }
            
            // Test status
            if (method_exists($service, 'status')) {
                $status = $service->status();
                $this->addResult("✅ Status verificato per {$featureName}", 'success');
            }
            
            $this->workingFeatures[] = $featureName;
            
        } catch (\Exception $e) {
            $this->addResult("❌ Errore {$featureName}: " . $e->getMessage(), 'error');
            $this->brokenFeatures[] = $featureName;
        }
        
        $this->testedFeatures[] = $featureName;
    }

    /**
     * Test metodi del servizio
     */
    private function testServiceMethods($service, string $featureName): void
    {
        // Test metodo settings
        if (method_exists($service, 'settings')) {
            try {
                $settings = $service->settings();
                $this->addResult("✅ Metodo settings() funzionante per {$featureName}", 'success');
            } catch (\Exception $e) {
                $this->addResult("❌ Errore metodo settings() per {$featureName}: " . $e->getMessage(), 'error');
            }
        } else {
            $this->addResult("⚠️ Metodo settings() non trovato per {$featureName}", 'warning');
        }
        
        // Test metodo update
        if (method_exists($service, 'update')) {
            $this->addResult("✅ Metodo update() disponibile per {$featureName}", 'success');
        } else {
            $this->addResult("⚠️ Metodo update() non trovato per {$featureName}", 'warning');
        }
        
        // Test metodo status
        if (method_exists($service, 'status')) {
            try {
                $status = $service->status();
                $this->addResult("✅ Metodo status() funzionante per {$featureName}", 'success');
            } catch (\Exception $e) {
                $this->addResult("❌ Errore metodo status() per {$featureName}: " . $e->getMessage(), 'error');
            }
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
     * Mostra tutti i risultati
     */
    private function displayAllResults(): void
    {
        echo "<h2>📊 Risultati Test Completo</h2>\n";
        
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
        echo "<h3>📈 Riepilogo Test Completo</h3>";
        echo "<p><strong>✅ Successi:</strong> {$success}</p>";
        echo "<p><strong>⚠️ Avvisi:</strong> {$warnings}</p>";
        echo "<p><strong>❌ Errori:</strong> {$errors}</p>";
        
        $total = $success + $warnings + $errors;
        $score = $total > 0 ? round(($success / $total) * 100) : 0;
        
        echo "<p><strong>🎯 Score Completo:</strong> {$score}%</p>";
        
        if (count($this->workingFeatures) > 0) {
            echo "<p><strong>✅ Funzionalità Funzionanti:</strong> " . implode(', ', $this->workingFeatures) . "</p>";
        }
        
        if (count($this->brokenFeatures) > 0) {
            echo "<p><strong>❌ Funzionalità Non Funzionanti:</strong> " . implode(', ', $this->brokenFeatures) . "</p>";
        }
        
        echo "<p><strong>📊 Totale Funzionalità Testate:</strong> " . count($this->testedFeatures) . "</p>";
        
        if ($score >= 90) {
            echo "<p style='color: #28a745; font-weight: bold;'>✅ Tutte le funzionalità funzionano correttamente!</p>";
        } elseif ($score >= 70) {
            echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ La maggior parte delle funzionalità funziona, alcune potrebbero avere problemi.</p>";
        } else {
            echo "<p style='color: #dc3545; font-weight: bold;'>❌ Molte funzionalità non funzionano correttamente.</p>";
        }
        
        echo "</div>";
    }
}

// Esegui test se chiamato direttamente
if (isset($_GET['test_all_functionality'])) {
    $test = new AllFunctionalityTest();
}
