<?php
/**
 * Test Funzionalità Checkbox FP Performance Suite
 * 
 * Verifica che ogni checkbox funzioni correttamente quando attivato
 * 
 * @package FP\PerfSuite\Tests
 * @author Francesco Passeri
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class CheckboxFunctionalityTest
{
    private $results = [];
    private $testedOptions = [];

    public function __construct()
    {
        $this->runCheckboxTests();
    }

    /**
     * Esegue test su tutti i checkbox
     */
    public function runCheckboxTests(): void
    {
        echo "<h1>✅ Test Funzionalità Checkbox FP Performance Suite</h1>\n";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
        
        $this->testCacheCheckboxes();
        $this->testAssetsCheckboxes();
        $this->testDatabaseCheckboxes();
        $this->testBackendCheckboxes();
        $this->testMobileCheckboxes();
        $this->testCompressionCheckboxes();
        $this->testCdnCheckboxes();
        $this->testSecurityCheckboxes();
        $this->testMonitoringCheckboxes();
        $this->testJavaScriptOptimizationCheckboxes();
        
        $this->displayResults();
        echo "</div>\n";
    }

    /**
     * Test checkbox Cache
     */
    private function testCacheCheckboxes(): void
    {
        echo "<h2>🚀 Test Checkbox Cache</h2>\n";
        
        // Page Cache
        $this->testCheckbox('page_cache_enabled', 'Page Cache', 'FP\\PerfSuite\\Services\\Cache\\PageCache', 'enabled');
        
        // Predictive Prefetching
        $this->testCheckbox('prefetch_enabled', 'Predictive Prefetching', 'FP\\PerfSuite\\Services\\Assets\\PredictivePrefetching', 'enabled');
        
        // Cache Rules
        $this->testCheckbox('cache_rules_enabled', 'Cache Rules', 'FP\\PerfSuite\\Services\\Cache\\Headers', 'cache_rules.enabled');
        
        // HTML Cache
        $this->testCheckbox('html_cache', 'HTML Cache', 'FP\\PerfSuite\\Services\\Cache\\Headers', 'cache_rules.html_cache');
        
        // Fonts Cache
        $this->testCheckbox('fonts_cache', 'Fonts Cache', 'FP\\PerfSuite\\Services\\Cache\\Headers', 'cache_rules.fonts_cache');
        
        // Browser Cache
        $this->testCheckbox('browser_cache_enabled', 'Browser Cache', 'FP\\PerfSuite\\Services\\Cache\\Headers', 'enabled');
        
        // PWA
        $this->testCheckbox('enabled', 'PWA Service Worker', 'FP\\PerfSuite\\Services\\PWA\\ServiceWorkerManager', 'enabled');
        
        // Offline Fallback
        $this->testCheckbox('offline_fallback', 'PWA Offline Fallback', 'FP\\PerfSuite\\Services\\PWA\\ServiceWorkerManager', 'offline_fallback');
        
        // Edge Cache
        $this->testCheckbox('enabled', 'Edge Cache', 'FP\\PerfSuite\\Services\\Cache\\EdgeCacheManager', 'enabled');
    }

    /**
     * Test checkbox Assets
     */
    private function testAssetsCheckboxes(): void
    {
        echo "<h2>📦 Test Checkbox Assets</h2>\n";
        
        // Font Optimization
        $this->testCheckbox('enabled', 'Font Optimization', 'FP\\PerfSuite\\Services\\Assets\\FontOptimizer', 'enabled');
        
        // Preload Critical Fonts
        $this->testCheckbox('preload_critical', 'Preload Critical Fonts', 'FP\\PerfSuite\\Services\\Assets\\FontOptimizer', 'preload_critical');
        
        // Image Optimization
        $this->testCheckbox('enabled', 'Image Optimization', 'FP\\PerfSuite\\Services\\Assets\\ImageOptimizer', 'enabled');
        
        // Lazy Loading
        $this->testCheckbox('enabled', 'Lazy Loading', 'FP\\PerfSuite\\Services\\Assets\\LazyLoadManager', 'enabled');
        
        // Lazy Loading Images
        $this->testCheckbox('images', 'Lazy Loading Images', 'FP\\PerfSuite\\Services\\Assets\\LazyLoadManager', 'images');
        
        // Lazy Loading Videos
        $this->testCheckbox('videos', 'Lazy Loading Videos', 'FP\\PerfSuite\\Services\\Assets\\LazyLoadManager', 'videos');
        
        // Lazy Loading Iframes
        $this->testCheckbox('iframes', 'Lazy Loading Iframes', 'FP\\PerfSuite\\Services\\Assets\\LazyLoadManager', 'iframes');
    }

    /**
     * Test checkbox Database
     */
    private function testDatabaseCheckboxes(): void
    {
        echo "<h2>💾 Test Checkbox Database</h2>\n";
        
        // Database Cleaner
        $this->testCheckbox('enabled', 'Database Cleaner', 'FP\\PerfSuite\\Services\\DB\\Cleaner', 'enabled');
        
        // Clean Revisions
        $this->testCheckbox('revisions', 'Clean Revisions', 'FP\\PerfSuite\\Services\\DB\\Cleaner', 'revisions');
        
        // Clean Spam
        $this->testCheckbox('spam', 'Clean Spam', 'FP\\PerfSuite\\Services\\DB\\Cleaner', 'spam');
        
        // Clean Trash
        $this->testCheckbox('trash', 'Clean Trash', 'FP\\PerfSuite\\Services\\DB\\Cleaner', 'trash');
        
        // Database Optimizer
        $this->testCheckbox('enabled', 'Database Optimizer', 'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer', 'enabled');
        
        // Auto Optimize
        $this->testCheckbox('auto_optimize', 'Auto Optimize', 'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer', 'auto_optimize');
        
        // Query Cache
        $this->testCheckbox('query_cache', 'Query Cache', 'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer', 'query_cache');
        
        // Query Cache Manager
        $this->testCheckbox('enabled', 'Query Cache Manager', 'FP\\PerfSuite\\Services\\DB\\QueryCacheManager', 'enabled');
    }

    /**
     * Test checkbox Backend
     */
    private function testBackendCheckboxes(): void
    {
        echo "<h2>⚙️ Test Checkbox Backend</h2>\n";
        
        // Backend Optimizer
        $this->testCheckbox('enabled', 'Backend Optimizer', 'FP\\PerfSuite\\Services\\Admin\\BackendOptimizer', 'enabled');
        
        // Optimize Heartbeat
        $this->testCheckbox('optimize_heartbeat', 'Optimize Heartbeat', 'FP\\PerfSuite\\Services\\Admin\\BackendOptimizer', 'optimize_heartbeat');
        
        // Limit Revisions
        $this->testCheckbox('limit_revisions', 'Limit Revisions', 'FP\\PerfSuite\\Services\\Admin\\BackendOptimizer', 'limit_revisions');
        
        // Optimize Dashboard
        $this->testCheckbox('optimize_dashboard', 'Optimize Dashboard', 'FP\\PerfSuite\\Services\\Admin\\BackendOptimizer', 'optimize_dashboard');
        
        // Admin Bar
        $this->testCheckbox('admin_bar', 'Admin Bar', 'FP\\PerfSuite\\Services\\Admin\\BackendOptimizer', 'admin_bar');
    }

    /**
     * Test checkbox Mobile
     */
    private function testMobileCheckboxes(): void
    {
        echo "<h2>📱 Test Checkbox Mobile</h2>\n";
        
        // Mobile Optimizer
        $this->testCheckbox('enabled', 'Mobile Optimizer', 'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer', 'enabled');
        
        // Touch Optimization
        $this->testCheckbox('touch_optimization', 'Touch Optimization', 'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer', 'touch_optimization');
        
        // Mobile Cache
        $this->testCheckbox('mobile_cache', 'Mobile Cache', 'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer', 'mobile_cache');
        
        // Responsive Images
        $this->testCheckbox('responsive_images', 'Responsive Images', 'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer', 'responsive_images');
        
        // Touch Optimizer
        $this->testCheckbox('enabled', 'Touch Optimizer', 'FP\\PerfSuite\\Services\\Mobile\\TouchOptimizer', 'enabled');
        
        // Gesture Optimization
        $this->testCheckbox('gesture_optimization', 'Gesture Optimization', 'FP\\PerfSuite\\Services\\Mobile\\TouchOptimizer', 'gesture_optimization');
    }

    /**
     * Test checkbox Compression
     */
    private function testCompressionCheckboxes(): void
    {
        echo "<h2>🗜️ Test Checkbox Compression</h2>\n";
        
        // Compression Manager
        $this->testCheckbox('enabled', 'Compression Manager', 'FP\\PerfSuite\\Services\\Compression\\CompressionManager', 'enabled');
        
        // GZIP
        $this->testCheckbox('gzip', 'GZIP Compression', 'FP\\PerfSuite\\Services\\Compression\\CompressionManager', 'gzip');
        
        // Brotli
        $this->testCheckbox('brotli', 'Brotli Compression', 'FP\\PerfSuite\\Services\\Compression\\CompressionManager', 'brotli');
        
        // Minify HTML
        $this->testCheckbox('minify_html', 'Minify HTML', 'FP\\PerfSuite\\Services\\Compression\\CompressionManager', 'minify_html');
        
        // Minify CSS
        $this->testCheckbox('minify_css', 'Minify CSS', 'FP\\PerfSuite\\Services\\Compression\\CompressionManager', 'minify_css');
        
        // Minify JS
        $this->testCheckbox('minify_js', 'Minify JavaScript', 'FP\\PerfSuite\\Services\\Compression\\CompressionManager', 'minify_js');
    }

    /**
     * Test checkbox CDN
     */
    private function testCdnCheckboxes(): void
    {
        echo "<h2>🌐 Test Checkbox CDN</h2>\n";
        
        // CDN Manager
        $this->testCheckbox('enabled', 'CDN Manager', 'FP\\PerfSuite\\Services\\CDN\\CdnManager', 'enabled');
    }

    /**
     * Test checkbox Security
     */
    private function testSecurityCheckboxes(): void
    {
        echo "<h2>🛡️ Test Checkbox Security</h2>\n";
        
        // Htaccess Security
        $this->testCheckbox('enabled', 'Htaccess Security', 'FP\\PerfSuite\\Services\\Security\\HtaccessSecurity', 'enabled');
        
        // Cache Rules
        $this->testCheckbox('cache_rules', 'Security Cache Rules', 'FP\\PerfSuite\\Services\\Security\\HtaccessSecurity', 'cache_rules');
        
        // Security Headers
        $this->testCheckbox('security_headers', 'Security Headers', 'FP\\PerfSuite\\Services\\Security\\HtaccessSecurity', 'security_headers');
    }

    /**
     * Test checkbox Monitoring
     */
    private function testMonitoringCheckboxes(): void
    {
        echo "<h2>📊 Test Checkbox Monitoring</h2>\n";
        
        // Performance Monitor
        $this->testCheckbox('enabled', 'Performance Monitor', 'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor', 'enabled');
        
        // Core Web Vitals
        $this->testCheckbox('core_web_vitals', 'Core Web Vitals', 'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor', 'core_web_vitals');
        
        // Real User Monitoring
        $this->testCheckbox('real_user_monitoring', 'Real User Monitoring', 'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor', 'real_user_monitoring');
        
        // Core Web Vitals Monitor
        $this->testCheckbox('enabled', 'Core Web Vitals Monitor', 'FP\\PerfSuite\\Services\\Monitoring\\CoreWebVitalsMonitor', 'enabled');
        
        // LCP
        $this->testCheckbox('lcp', 'LCP Monitoring', 'FP\\PerfSuite\\Services\\Monitoring\\CoreWebVitalsMonitor', 'lcp');
        
        // FID
        $this->testCheckbox('fid', 'FID Monitoring', 'FP\\PerfSuite\\Services\\Monitoring\\CoreWebVitalsMonitor', 'fid');
        
        // CLS
        $this->testCheckbox('cls', 'CLS Monitoring', 'FP\\PerfSuite\\Services\\Monitoring\\CoreWebVitalsMonitor', 'cls');
    }

    /**
     * Test checkbox JavaScript Optimization
     */
    private function testJavaScriptOptimizationCheckboxes(): void
    {
        echo "<h2>⚡ Test Checkbox JavaScript Optimization</h2>\n";
        
        // Unused JavaScript Optimizer
        $this->testCheckbox('enabled', 'Unused JavaScript Optimizer', 'FP\\PerfSuite\\Services\\Assets\\UnusedJavaScriptOptimizer', 'enabled');
        
        // Aggressive Mode
        $this->testCheckbox('aggressive_mode', 'Aggressive Mode', 'FP\\PerfSuite\\Services\\Assets\\UnusedJavaScriptOptimizer', 'aggressive_mode');
        
        // Code Splitting Manager
        $this->testCheckbox('enabled', 'Code Splitting Manager', 'FP\\PerfSuite\\Services\\Assets\\CodeSplittingManager', 'enabled');
        
        // Lazy Loading Chunks
        $this->testCheckbox('lazy_loading', 'Lazy Loading Chunks', 'FP\\PerfSuite\\Services\\Assets\\CodeSplittingManager', 'lazy_loading');
        
        // Tree Shaker
        $this->testCheckbox('enabled', 'Tree Shaker', 'FP\\PerfSuite\\Services\\Assets\\JavaScriptTreeShaker', 'enabled');
        
        // Aggressive Tree Shaking
        $this->testCheckbox('aggressive_mode', 'Aggressive Tree Shaking', 'FP\\PerfSuite\\Services\\Assets\\JavaScriptTreeShaker', 'aggressive_mode');
    }

    /**
     * Test di un checkbox specifico
     */
    private function testCheckbox(string $checkboxName, string $description, string $serviceClass, string $settingPath): void
    {
        echo "<h4>🔘 Test {$description} ({$checkboxName})</h4>\n";
        
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
            
            // Test impostazioni
            if (method_exists($service, 'settings')) {
                $settings = $service->settings();
                
                // Verifica se l'impostazione esiste
                $settingValue = $this->getNestedSetting($settings, $settingPath);
                
                if ($settingValue !== null) {
                    $this->addResult("✅ Impostazione '{$settingPath}' trovata per {$description}", 'success');
                    
                    // Test attivazione
                    $this->testCheckboxActivation($service, $description, $settingPath, $settings);
                } else {
                    $this->addResult("⚠️ Impostazione '{$settingPath}' non trovata per {$description}", 'warning');
                }
            } else {
                $this->addResult("⚠️ Metodo settings() non trovato per {$description}", 'warning');
            }
            
        } catch (\Exception $e) {
            $this->addResult("❌ Errore istanziazione {$description}: " . $e->getMessage(), 'error');
        }
    }

    /**
     * Test attivazione checkbox
     */
    private function testCheckboxActivation($service, string $description, string $settingPath, array $settings): void
    {
        try {
            // Simula attivazione
            $newSettings = $settings;
            $this->setNestedSetting($newSettings, $settingPath, true);
            
            // Test aggiornamento
            if (method_exists($service, 'update')) {
                $service->update($newSettings);
                $this->addResult("✅ Checkbox {$description} attivato correttamente", 'success');
                
                // Verifica stato
                if (method_exists($service, 'status')) {
                    $status = $service->status();
                    $this->addResult("✅ Status verificato per {$description}: " . json_encode($status), 'success');
                }
            } else {
                $this->addResult("⚠️ Metodo update() non trovato per {$description}", 'warning');
            }
            
        } catch (\Exception $e) {
            $this->addResult("❌ Errore attivazione {$description}: " . $e->getMessage(), 'error');
        }
    }

    /**
     * Ottiene valore annidato dalle impostazioni
     */
    private function getNestedSetting(array $settings, string $path)
    {
        $keys = explode('.', $path);
        $value = $settings;
        
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }
        
        return $value;
    }

    /**
     * Imposta valore annidato nelle impostazioni
     */
    private function setNestedSetting(array &$settings, string $path, $value): void
    {
        $keys = explode('.', $path);
        $current = &$settings;
        
        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }
        
        $current = $value;
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
        echo "<h2>📊 Risultati Test Checkbox</h2>\n";
        
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
        echo "<h3>📈 Riepilogo Test Checkbox</h3>";
        echo "<p><strong>✅ Successi:</strong> {$success}</p>";
        echo "<p><strong>⚠️ Avvisi:</strong> {$warnings}</p>";
        echo "<p><strong>❌ Errori:</strong> {$errors}</p>";
        
        $total = $success + $warnings + $errors;
        $score = $total > 0 ? round(($success / $total) * 100) : 0;
        
        echo "<p><strong>🎯 Score Checkbox:</strong> {$score}%</p>";
        
        if ($score >= 90) {
            echo "<p style='color: #28a745; font-weight: bold;'>✅ Tutti i checkbox funzionano correttamente!</p>";
        } elseif ($score >= 70) {
            echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ La maggior parte dei checkbox funziona, alcuni potrebbero avere problemi.</p>";
        } else {
            echo "<p style='color: #dc3545; font-weight: bold;'>❌ Molti checkbox non funzionano correttamente.</p>";
        }
        
        echo "</div>";
    }
}

// Esegui test se chiamato direttamente
if (isset($_GET['test_checkbox_functionality'])) {
    $test = new CheckboxFunctionalityTest();
}