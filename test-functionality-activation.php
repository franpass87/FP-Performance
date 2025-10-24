<?php
/**
 * Test Attivazione Funzionalità FP Performance Suite
 * 
 * Verifica che ogni funzionalità si attivi correttamente quando abilitata
 * 
 * @package FP\PerfSuite\Tests
 * @author Francesco Passeri
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class FunctionalityActivationTest
{
    private $results = [];
    private $activatedFeatures = [];

    public function __construct()
    {
        $this->runActivationTests();
    }

    /**
     * Esegue test di attivazione
     */
    public function runActivationTests(): void
    {
        echo "<h1>🚀 Test Attivazione Funzionalità FP Performance Suite</h1>\n";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
        
        $this->testCacheActivation();
        $this->testAssetsActivation();
        $this->testDatabaseActivation();
        $this->testBackendActivation();
        $this->testMobileActivation();
        $this->testCompressionActivation();
        $this->testCdnActivation();
        $this->testSecurityActivation();
        $this->testMonitoringActivation();
        $this->testJavaScriptOptimizationActivation();
        
        $this->displayResults();
        echo "</div>\n";
    }

    /**
     * Test attivazione Cache
     */
    private function testCacheActivation(): void
    {
        echo "<h2>🚀 Test Attivazione Cache</h2>\n";
        
        // Test Page Cache
        $this->testFeatureActivation('Page Cache', 'FP\\PerfSuite\\Services\\Cache\\PageCache', [
            'enabled' => true,
            'ttl' => 3600
        ]);
        
        // Test Predictive Prefetching
        $this->testFeatureActivation('Predictive Prefetching', 'FP\\PerfSuite\\Services\\Assets\\PredictivePrefetching', [
            'enabled' => true,
            'strategy' => 'hover',
            'hover_delay' => 100
        ]);
        
        // Test Browser Cache
        $this->testFeatureActivation('Browser Cache', 'FP\\PerfSuite\\Services\\Cache\\Headers', [
            'enabled' => true,
            'headers' => true
        ]);
        
        // Test PWA
        $this->testFeatureActivation('PWA Service Worker', 'FP\\PerfSuite\\Services\\PWA\\ServiceWorkerManager', [
            'enabled' => true,
            'cache_strategy' => 'cache_first'
        ]);
        
        // Test Edge Cache
        $this->testFeatureActivation('Edge Cache', 'FP\\PerfSuite\\Services\\Cache\\EdgeCacheManager', [
            'enabled' => true,
            'provider' => 'cloudflare'
        ]);
    }

    /**
     * Test attivazione Assets
     */
    private function testAssetsActivation(): void
    {
        echo "<h2>📦 Test Attivazione Assets</h2>\n";
        
        // Test Font Optimization
        $this->testFeatureActivation('Font Optimization', 'FP\\PerfSuite\\Services\\Assets\\FontOptimizer', [
            'enabled' => true,
            'preload_critical' => true
        ]);
        
        // Test Image Optimization
        $this->testFeatureActivation('Image Optimization', 'FP\\PerfSuite\\Services\\Assets\\ImageOptimizer', [
            'enabled' => true,
            'lazy_loading' => true
        ]);
        
        // Test Lazy Loading
        $this->testFeatureActivation('Lazy Loading', 'FP\\PerfSuite\\Services\\Assets\\LazyLoadManager', [
            'enabled' => true,
            'images' => true
        ]);
    }

    /**
     * Test attivazione Database
     */
    private function testDatabaseActivation(): void
    {
        echo "<h2>💾 Test Attivazione Database</h2>\n";
        
        // Test Database Cleaner
        $this->testFeatureActivation('Database Cleaner', 'FP\\PerfSuite\\Services\\DB\\Cleaner', [
            'enabled' => true,
            'revisions' => true
        ]);
        
        // Test Database Optimizer
        $this->testFeatureActivation('Database Optimizer', 'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer', [
            'enabled' => true,
            'auto_optimize' => true
        ]);
        
        // Test Query Cache Manager
        $this->testFeatureActivation('Query Cache Manager', 'FP\\PerfSuite\\Services\\DB\\QueryCacheManager', [
            'enabled' => true,
            'ttl' => 3600
        ]);
    }

    /**
     * Test attivazione Backend
     */
    private function testBackendActivation(): void
    {
        echo "<h2>⚙️ Test Attivazione Backend</h2>\n";
        
        // Test Backend Optimizer
        $this->testFeatureActivation('Backend Optimizer', 'FP\\PerfSuite\\Services\\Admin\\BackendOptimizer', [
            'enabled' => true,
            'optimize_heartbeat' => true
        ]);
    }

    /**
     * Test attivazione Mobile
     */
    private function testMobileActivation(): void
    {
        echo "<h2>📱 Test Attivazione Mobile</h2>\n";
        
        // Test Mobile Optimizer
        $this->testFeatureActivation('Mobile Optimizer', 'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer', [
            'enabled' => true,
            'touch_optimization' => true
        ]);
        
        // Test Touch Optimizer
        $this->testFeatureActivation('Touch Optimizer', 'FP\\PerfSuite\\Services\\Mobile\\TouchOptimizer', [
            'enabled' => true,
            'gesture_optimization' => true
        ]);
    }

    /**
     * Test attivazione Compression
     */
    private function testCompressionActivation(): void
    {
        echo "<h2>🗜️ Test Attivazione Compression</h2>\n";
        
        // Test Compression Manager
        $this->testFeatureActivation('Compression Manager', 'FP\\PerfSuite\\Services\\Compression\\CompressionManager', [
            'enabled' => true,
            'gzip' => true
        ]);
    }

    /**
     * Test attivazione CDN
     */
    private function testCdnActivation(): void
    {
        echo "<h2>🌐 Test Attivazione CDN</h2>\n";
        
        // Test CDN Manager
        $this->testFeatureActivation('CDN Manager', 'FP\\PerfSuite\\Services\\CDN\\CdnManager', [
            'enabled' => true,
            'provider' => 'cloudflare'
        ]);
    }

    /**
     * Test attivazione Security
     */
    private function testSecurityActivation(): void
    {
        echo "<h2>🛡️ Test Attivazione Security</h2>\n";
        
        // Test Htaccess Security
        $this->testFeatureActivation('Htaccess Security', 'FP\\PerfSuite\\Services\\Security\\HtaccessSecurity', [
            'enabled' => true,
            'cache_rules' => true
        ]);
    }

    /**
     * Test attivazione Monitoring
     */
    private function testMonitoringActivation(): void
    {
        echo "<h2>📊 Test Attivazione Monitoring</h2>\n";
        
        // Test Performance Monitor
        $this->testFeatureActivation('Performance Monitor', 'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor', [
            'enabled' => true,
            'core_web_vitals' => true
        ]);
        
        // Test Core Web Vitals Monitor
        $this->testFeatureActivation('Core Web Vitals Monitor', 'FP\\PerfSuite\\Services\\Monitoring\\CoreWebVitalsMonitor', [
            'enabled' => true,
            'lcp' => true
        ]);
    }

    /**
     * Test attivazione JavaScript Optimization
     */
    private function testJavaScriptOptimizationActivation(): void
    {
        echo "<h2>⚡ Test Attivazione JavaScript Optimization</h2>\n";
        
        // Test Unused JavaScript Optimizer
        $this->testFeatureActivation('Unused JavaScript Optimizer', 'FP\\PerfSuite\\Services\\Assets\\UnusedJavaScriptOptimizer', [
            'enabled' => true,
            'aggressive_mode' => true
        ]);
        
        // Test Code Splitting Manager
        $this->testFeatureActivation('Code Splitting Manager', 'FP\\PerfSuite\\Services\\Assets\\CodeSplittingManager', [
            'enabled' => true,
            'chunk_size' => 100000
        ]);
        
        // Test Tree Shaker
        $this->testFeatureActivation('Tree Shaker', 'FP\\PerfSuite\\Services\\Assets\\JavaScriptTreeShaker', [
            'enabled' => true,
            'aggressive_mode' => true
        ]);
    }

    /**
     * Test attivazione di una funzionalità specifica
     */
    private function testFeatureActivation(string $featureName, string $serviceClass, array $activationData): void
    {
        echo "<h4>🚀 Test Attivazione {$featureName}</h4>\n";
        
        // Verifica se la classe esiste
        if (!class_exists($serviceClass)) {
            $this->addResult("❌ Classe {$serviceClass} non trovata per {$featureName}", 'error');
            return;
        }
        
        $this->addResult("✅ Classe {$serviceClass} trovata per {$featureName}", 'success');
        
        // Test istanziazione
        try {
            $service = new $serviceClass();
            $this->addResult("✅ Servizio {$featureName} istanziato correttamente", 'success');
            
            // Test attivazione
            if (method_exists($service, 'update')) {
                // Attiva la funzionalità
                $service->update($activationData);
                $this->addResult("✅ Funzionalità {$featureName} attivata", 'success');
                
                // Verifica attivazione
                if (method_exists($service, 'status')) {
                    $status = $service->status();
                    
                    if (isset($status['enabled']) && $status['enabled']) {
                        $this->addResult("✅ Attivazione verificata per {$featureName}", 'success');
                        $this->activatedFeatures[] = $featureName;
                    } else {
                        $this->addResult("❌ Attivazione non verificata per {$featureName}", 'error');
                    }
                } else {
                    $this->addResult("⚠️ Metodo status() non trovato per {$featureName}", 'warning');
                }
                
                // Test funzionalità specifiche
                $this->testFeatureSpecifics($service, $featureName, $activationData);
                
            } else {
                $this->addResult("⚠️ Metodo update() non trovato per {$featureName}", 'warning');
            }
            
        } catch (\Exception $e) {
            $this->addResult("❌ Errore attivazione {$featureName}: " . $e->getMessage(), 'error');
        }
    }

    /**
     * Test funzionalità specifiche
     */
    private function testFeatureSpecifics($service, string $featureName, array $activationData): void
    {
        // Test metodi specifici per tipo di funzionalità
        $specificMethods = [
            'Page Cache' => ['getCacheStats', 'clearCache'],
            'Browser Cache' => ['getHeaders', 'setHeaders'],
            'PWA Service Worker' => ['generateServiceWorker', 'getCacheStats'],
            'Database Optimizer' => ['optimize', 'getStats'],
            'Image Optimizer' => ['optimizeImage', 'getOptimizationStats'],
            'Lazy Loading' => ['enableLazyLoading', 'getLazyLoadingStats'],
            'Compression Manager' => ['compress', 'getCompressionStats'],
            'Performance Monitor' => ['startMonitoring', 'getMetrics']
        ];
        
        if (isset($specificMethods[$featureName])) {
            foreach ($specificMethods[$featureName] as $method) {
                if (method_exists($service, $method)) {
                    try {
                        $result = $service->$method();
                        $this->addResult("✅ Metodo {$method}() funzionante per {$featureName}", 'success');
                    } catch (\Exception $e) {
                        $this->addResult("⚠️ Errore metodo {$method}() per {$featureName}: " . $e->getMessage(), 'warning');
                    }
                } else {
                    $this->addResult("⚠️ Metodo {$method}() non trovato per {$featureName}", 'warning');
                }
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
     * Mostra risultati
     */
    private function displayResults(): void
    {
        echo "<h2>📊 Risultati Test Attivazione</h2>\n";
        
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
        echo "<h3>📈 Riepilogo Test Attivazione</h3>";
        echo "<p><strong>✅ Successi:</strong> {$success}</p>";
        echo "<p><strong>⚠️ Avvisi:</strong> {$warnings}</p>";
        echo "<p><strong>❌ Errori:</strong> {$errors}</p>";
        
        $total = $success + $warnings + $errors;
        $score = $total > 0 ? round(($success / $total) * 100) : 0;
        
        echo "<p><strong>🎯 Score Attivazione:</strong> {$score}%</p>";
        
        if (count($this->activatedFeatures) > 0) {
            echo "<p><strong>🚀 Funzionalità Attivate:</strong> " . implode(', ', $this->activatedFeatures) . "</p>";
        }
        
        if ($score >= 90) {
            echo "<p style='color: #28a745; font-weight: bold;'>✅ Tutte le funzionalità si attivano correttamente!</p>";
        } elseif ($score >= 70) {
            echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ La maggior parte delle funzionalità si attiva, alcune potrebbero avere problemi.</p>";
        } else {
            echo "<p style='color: #dc3545; font-weight: bold;'>❌ Molte funzionalità non si attivano correttamente.</p>";
        }
        
        echo "</div>";
    }
}

// Esegui test se chiamato direttamente
if (isset($_GET['test_functionality_activation'])) {
    $test = new FunctionalityActivationTest();
}
