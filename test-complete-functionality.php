<?php
/**
 * Test Completo Funzionalità FP Performance Suite
 * 
 * Verifica che ogni checkbox e opzione funzioni correttamente
 * 
 * @package FP\PerfSuite\Tests
 * @author Francesco Passeri
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class CompleteFunctionalityTest
{
    private $results = [];
    private $errors = [];
    private $container;

    public function __construct()
    {
        // Ottieni il container se disponibile
        if (class_exists('FP\\PerfSuite\\Plugin')) {
            $this->container = FP\PerfSuite\Plugin::container();
        }
        
        $this->runAllTests();
    }

    /**
     * Esegue tutti i test di funzionalità
     */
    public function runAllTests(): void
    {
        echo "<h1>🧪 Test Completo Funzionalità FP Performance Suite</h1>\n";
        echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
        
        $this->testCacheOptions();
        $this->testAssetsOptions();
        $this->testDatabaseOptions();
        $this->testBackendOptions();
        $this->testMobileOptions();
        $this->testCompressionOptions();
        $this->testCdnOptions();
        $this->testSecurityOptions();
        $this->testMonitoringOptions();
        $this->testJavaScriptOptimization();
        
        $this->displayResults();
        echo "</div>\n";
    }

    /**
     * Test opzioni Cache
     */
    private function testCacheOptions(): void
    {
        echo "<h2>🚀 Test Opzioni Cache</h2>\n";
        
        // Test Page Cache
        $this->testService('PageCache', 'FP\\PerfSuite\\Services\\Cache\\PageCache', [
            'enabled' => 'Abilitazione Page Cache',
            'ttl' => 'TTL Cache'
        ]);
        
        // Test Browser Cache
        $this->testService('Headers', 'FP\\PerfSuite\\Services\\Cache\\Headers', [
            'enabled' => 'Abilitazione Browser Cache',
            'headers' => 'Header Cache-Control',
            'expires_ttl' => 'TTL Expires'
        ]);
        
        // Test Predictive Prefetching
        $this->testService('PredictivePrefetching', 'FP\\PerfSuite\\Services\\Assets\\PredictivePrefetching', [
            'enabled' => 'Abilitazione Predictive Prefetching',
            'strategy' => 'Strategia Prefetching',
            'hover_delay' => 'Delay Hover',
            'prefetch_limit' => 'Limite Prefetch'
        ]);
        
        // Test PWA
        $this->testService('ServiceWorkerManager', 'FP\\PerfSuite\\Services\\PWA\\ServiceWorkerManager', [
            'enabled' => 'Abilitazione PWA',
            'cache_strategy' => 'Strategia Cache PWA',
            'cache_duration' => 'Durata Cache PWA',
            'offline_fallback' => 'Fallback Offline'
        ]);
        
        // Test Edge Cache
        $this->testService('EdgeCacheManager', 'FP\\PerfSuite\\Services\\Cache\\EdgeCacheManager', [
            'enabled' => 'Abilitazione Edge Cache',
            'provider' => 'Provider CDN',
            'api_key' => 'API Key',
            'zone_id' => 'Zone ID'
        ]);
    }

    /**
     * Test opzioni Assets
     */
    private function testAssetsOptions(): void
    {
        echo "<h2>📦 Test Opzioni Assets</h2>\n";
        
        // Test JavaScript Optimization
        $this->testService('UnusedJavaScriptOptimizer', 'FP\\PerfSuite\\Services\\Assets\\UnusedJavaScriptOptimizer', [
            'enabled' => 'Rimozione JS Non Utilizzato'
        ]);
        
        $this->testService('CodeSplittingManager', 'FP\\PerfSuite\\Services\\Assets\\CodeSplittingManager', [
            'enabled' => 'Code Splitting'
        ]);
        
        $this->testService('JavaScriptTreeShaker', 'FP\\PerfSuite\\Services\\Assets\\JavaScriptTreeShaker', [
            'enabled' => 'Tree Shaking'
        ]);
        
        // Test Font Optimization
        $this->testService('FontOptimizer', 'FP\\PerfSuite\\Services\\Assets\\FontOptimizer', [
            'enabled' => 'Ottimizzazione Font',
            'preload_critical' => 'Preload Font Critici',
            'display_swap' => 'Font Display Swap'
        ]);
        
        // Test Image Optimization
        $this->testService('ImageOptimizer', 'FP\\PerfSuite\\Services\\Assets\\ImageOptimizer', [
            'enabled' => 'Ottimizzazione Immagini',
            'lazy_loading' => 'Lazy Loading',
            'webp_conversion' => 'Conversione WebP'
        ]);
        
        // Test Lazy Loading
        $this->testService('LazyLoadManager', 'FP\\PerfSuite\\Services\\Assets\\LazyLoadManager', [
            'enabled' => 'Lazy Loading Manager',
            'images' => 'Lazy Loading Immagini',
            'videos' => 'Lazy Loading Video',
            'iframes' => 'Lazy Loading Iframe'
        ]);
    }

    /**
     * Test opzioni Database
     */
    private function testDatabaseOptions(): void
    {
        echo "<h2>💾 Test Opzioni Database</h2>\n";
        
        // Test Database Cleaner
        $this->testService('Cleaner', 'FP\\PerfSuite\\Services\\DB\\Cleaner', [
            'enabled' => 'Pulizia Database',
            'revisions' => 'Pulizia Revisioni',
            'spam' => 'Pulizia Spam',
            'trash' => 'Pulizia Cestino'
        ]);
        
        // Test Database Optimizer
        $this->testService('DatabaseOptimizer', 'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer', [
            'enabled' => 'Ottimizzazione Database',
            'auto_optimize' => 'Auto Ottimizzazione',
            'query_cache' => 'Query Cache'
        ]);
        
        // Test Query Cache Manager
        $this->testService('QueryCacheManager', 'FP\\PerfSuite\\Services\\DB\\QueryCacheManager', [
            'enabled' => 'Query Cache Manager',
            'ttl' => 'TTL Query Cache'
        ]);
    }

    /**
     * Test opzioni Backend
     */
    private function testBackendOptions(): void
    {
        echo "<h2>⚙️ Test Opzioni Backend</h2>\n";
        
        // Test Backend Optimizer
        $this->testService('BackendOptimizer', 'FP\\PerfSuite\\Services\\Admin\\BackendOptimizer', [
            'enabled' => 'Ottimizzazione Backend',
            'optimize_heartbeat' => 'Ottimizzazione Heartbeat',
            'limit_revisions' => 'Limitazione Revisioni',
            'optimize_dashboard' => 'Ottimizzazione Dashboard',
            'admin_bar' => 'Ottimizzazioni Admin Bar'
        ]);
    }

    /**
     * Test opzioni Mobile
     */
    private function testMobileOptions(): void
    {
        echo "<h2>📱 Test Opzioni Mobile</h2>\n";
        
        // Test Mobile Optimizer
        $this->testService('MobileOptimizer', 'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer', [
            'enabled' => 'Ottimizzazione Mobile',
            'touch_optimization' => 'Ottimizzazione Touch',
            'mobile_cache' => 'Cache Mobile',
            'responsive_images' => 'Immagini Responsive'
        ]);
        
        // Test Touch Optimizer
        $this->testService('TouchOptimizer', 'FP\\PerfSuite\\Services\\Mobile\\TouchOptimizer', [
            'enabled' => 'Ottimizzazione Touch',
            'gesture_optimization' => 'Ottimizzazione Gesture'
        ]);
    }

    /**
     * Test opzioni Compression
     */
    private function testCompressionOptions(): void
    {
        echo "<h2>🗜️ Test Opzioni Compression</h2>\n";
        
        // Test Compression Manager
        $this->testService('CompressionManager', 'FP\\PerfSuite\\Services\\Compression\\CompressionManager', [
            'enabled' => 'Compressione Abilitata',
            'gzip' => 'Compressione GZIP',
            'brotli' => 'Compressione Brotli',
            'minify_html' => 'Minify HTML',
            'minify_css' => 'Minify CSS',
            'minify_js' => 'Minify JavaScript'
        ]);
    }

    /**
     * Test opzioni CDN
     */
    private function testCdnOptions(): void
    {
        echo "<h2>🌐 Test Opzioni CDN</h2>\n";
        
        // Test CDN Manager
        $this->testService('CdnManager', 'FP\\PerfSuite\\Services\\CDN\\CdnManager', [
            'enabled' => 'CDN Abilitato',
            'provider' => 'Provider CDN',
            'api_key' => 'API Key CDN',
            'zone_id' => 'Zone ID CDN'
        ]);
    }

    /**
     * Test opzioni Security
     */
    private function testSecurityOptions(): void
    {
        echo "<h2>🛡️ Test Opzioni Security</h2>\n";
        
        // Test Htaccess Security
        $this->testService('HtaccessSecurity', 'FP\\PerfSuite\\Services\\Security\\HtaccessSecurity', [
            'enabled' => 'Sicurezza Htaccess',
            'cache_rules' => 'Regole Cache',
            'security_headers' => 'Header Sicurezza'
        ]);
    }

    /**
     * Test opzioni Monitoring
     */
    private function testMonitoringOptions(): void
    {
        echo "<h2>📊 Test Opzioni Monitoring</h2>\n";
        
        // Test Performance Monitor
        $this->testService('PerformanceMonitor', 'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor', [
            'enabled' => 'Monitoraggio Performance',
            'core_web_vitals' => 'Core Web Vitals',
            'real_user_monitoring' => 'Real User Monitoring'
        ]);
        
        // Test Core Web Vitals Monitor
        $this->testService('CoreWebVitalsMonitor', 'FP\\PerfSuite\\Services\\Monitoring\\CoreWebVitalsMonitor', [
            'enabled' => 'Monitoraggio Core Web Vitals',
            'lcp' => 'Largest Contentful Paint',
            'fid' => 'First Input Delay',
            'cls' => 'Cumulative Layout Shift'
        ]);
    }

    /**
     * Test JavaScript Optimization
     */
    private function testJavaScriptOptimization(): void
    {
        echo "<h2>⚡ Test JavaScript Optimization</h2>\n";
        
        // Test Unused JavaScript Optimizer
        $this->testService('UnusedJavaScriptOptimizer', 'FP\\PerfSuite\\Services\\Assets\\UnusedJavaScriptOptimizer', [
            'enabled' => 'Rimozione JavaScript Non Utilizzato',
            'aggressive_mode' => 'Modalità Aggressiva',
            'exclude_patterns' => 'Pattern Esclusi'
        ]);
        
        // Test Code Splitting Manager
        $this->testService('CodeSplittingManager', 'FP\\PerfSuite\\Services\\Assets\\CodeSplittingManager', [
            'enabled' => 'Code Splitting',
            'chunk_size' => 'Dimensione Chunk',
            'lazy_loading' => 'Lazy Loading Chunk'
        ]);
        
        // Test Tree Shaker
        $this->testService('JavaScriptTreeShaker', 'FP\\PerfSuite\\Services\\Assets\\JavaScriptTreeShaker', [
            'enabled' => 'Tree Shaking',
            'aggressive_mode' => 'Modalità Aggressiva Tree Shaking'
        ]);
    }

    /**
     * Test di un servizio specifico
     */
    private function testService(string $serviceName, string $className, array $options): void
    {
        echo "<h3>🔧 Test {$serviceName}</h3>\n";
        
        // Verifica se la classe esiste
        if (!class_exists($className)) {
            $this->addResult("❌ Classe {$className} non trovata", 'error');
            return;
        }
        
        $this->addResult("✅ Classe {$className} trovata", 'success');
        
        // Test istanziazione
        try {
            if ($this->container && $this->container->has($className)) {
                $service = $this->container->get($className);
                $this->addResult("✅ Servizio {$serviceName} istanziato correttamente", 'success');
                
                // Test metodi principali
                $this->testServiceMethods($service, $serviceName, $options);
            } else {
                // Prova istanziazione diretta
                $service = new $className();
                $this->addResult("✅ Classe {$serviceName} istanziata direttamente", 'success');
                
                // Test metodi principali
                $this->testServiceMethods($service, $serviceName, $options);
            }
        } catch (\Exception $e) {
            $this->addResult("❌ Errore istanziazione {$serviceName}: " . $e->getMessage(), 'error');
        }
    }

    /**
     * Test metodi del servizio
     */
    private function testServiceMethods($service, string $serviceName, array $options): void
    {
        // Test metodo settings
        if (method_exists($service, 'settings')) {
            try {
                $settings = $service->settings();
                $this->addResult("✅ Metodo settings() funzionante per {$serviceName}", 'success');
                
                // Verifica opzioni specifiche
                foreach ($options as $option => $description) {
                    if (isset($settings[$option])) {
                        $this->addResult("✅ Opzione '{$option}' ({$description}) disponibile", 'success');
                    } else {
                        $this->addResult("⚠️ Opzione '{$option}' ({$description}) non trovata nelle impostazioni", 'warning');
                    }
                }
            } catch (\Exception $e) {
                $this->addResult("❌ Errore metodo settings() per {$serviceName}: " . $e->getMessage(), 'error');
            }
        } else {
            $this->addResult("⚠️ Metodo settings() non trovato per {$serviceName}", 'warning');
        }
        
        // Test metodo update
        if (method_exists($service, 'update')) {
            $this->addResult("✅ Metodo update() disponibile per {$serviceName}", 'success');
        } else {
            $this->addResult("⚠️ Metodo update() non trovato per {$serviceName}", 'warning');
        }
        
        // Test metodo status
        if (method_exists($service, 'status')) {
            try {
                $status = $service->status();
                $this->addResult("✅ Metodo status() funzionante per {$serviceName}", 'success');
            } catch (\Exception $e) {
                $this->addResult("❌ Errore metodo status() per {$serviceName}: " . $e->getMessage(), 'error');
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
        echo "<h3>📈 Riepilogo Test</h3>";
        echo "<p><strong>✅ Successi:</strong> {$success}</p>";
        echo "<p><strong>⚠️ Avvisi:</strong> {$warnings}</p>";
        echo "<p><strong>❌ Errori:</strong> {$errors}</p>";
        
        $total = $success + $warnings + $errors;
        $score = $total > 0 ? round(($success / $total) * 100) : 0;
        
        echo "<p><strong>🎯 Score Funzionalità:</strong> {$score}%</p>";
        
        if ($score >= 90) {
            echo "<p style='color: #28a745; font-weight: bold;'>✅ Tutte le funzionalità sono implementate correttamente!</p>";
        } elseif ($score >= 70) {
            echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ La maggior parte delle funzionalità funziona, alcune potrebbero essere incomplete.</p>";
        } else {
            echo "<p style='color: #dc3545; font-weight: bold;'>❌ Molte funzionalità non sono implementate o non funzionano.</p>";
        }
        
        echo "</div>";
    }
}

// Esegui test se chiamato direttamente
if (isset($_GET['test_complete_functionality'])) {
    $test = new CompleteFunctionalityTest();
}
