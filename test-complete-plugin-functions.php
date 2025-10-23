<?php
/**
 * Test Completo delle Funzionalità del Plugin FP Performance Suite
 * 
 * Questo script verifica:
 * 1. Tutti i bottoni di salvataggio nelle pagine admin
 * 2. Funzionalità principali dei servizi
 * 3. Operazioni database e salvataggio opzioni
 * 4. Identificazione errori critici
 * 
 * @author Francesco Passeri
 * @version 1.0.0
 */

// Carica WordPress se non è già caricato
if (!defined('ABSPATH')) {
    // Prova a caricare WordPress da diverse posizioni
    $wpPaths = [
        dirname(__FILE__) . '/../../../wp-load.php',
        dirname(__FILE__) . '/../../../../wp-load.php',
        dirname(__FILE__) . '/../../../../../wp-load.php'
    ];
    
    $wpLoaded = false;
    foreach ($wpPaths as $wpPath) {
        if (file_exists($wpPath)) {
            require_once($wpPath);
            $wpLoaded = true;
            break;
        }
    }
    
    if (!$wpLoaded) {
        die("WordPress non trovato. Assicurati che lo script sia nella directory del plugin.\n");
    }
}

class FPPerformanceSuiteTest {
    
    private $errors = [];
    private $warnings = [];
    private $success = [];
    private $testResults = [];
    
    public function __construct() {
        $this->runAllTests();
    }
    
    /**
     * Esegue tutti i test
     */
    public function runAllTests() {
        echo "<h1>🔍 Test Completo Plugin FP Performance Suite</h1>\n";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 8px;'>\n";
        
        // Test 1: Verifica struttura del plugin
        $this->testPluginStructure();
        
        // Test 2: Verifica pagine admin e bottoni di salvataggio
        $this->testAdminPages();
        
        // Test 3: Verifica servizi principali
        $this->testMainServices();
        
        // Test 4: Verifica operazioni database
        $this->testDatabaseOperations();
        
        // Test 5: Verifica configurazioni e opzioni
        $this->testConfigurations();
        
        // Test 6: Verifica errori critici
        $this->testCriticalErrors();
        
        // Mostra risultati
        $this->displayResults();
        
        echo "</div>\n";
    }
    
    /**
     * Test 1: Verifica struttura del plugin
     */
    private function testPluginStructure() {
        echo "\n📁 Test 1: Struttura del Plugin\n";
        echo str_repeat("-", 50) . "\n";
        
        $requiredFiles = [
            'fp-performance-suite.php',
            'src/Plugin.php',
            'src/ServiceContainer.php',
            'src/Admin/Pages/Assets.php',
            'src/Admin/Pages/Backend.php',
            'src/Admin/Pages/Database.php',
            'src/Admin/Pages/ML.php',
            'src/Admin/Pages/Media.php',
            'src/Admin/Pages/Mobile.php'
        ];
        
        $pluginDir = plugin_dir_path(__FILE__);
        
        foreach ($requiredFiles as $file) {
            $filePath = $pluginDir . $file;
            if (file_exists($filePath)) {
                $this->addSuccess("✓ File trovato: {$file}");
            } else {
                $this->addError("✗ File mancante: {$file}");
            }
        }
        
        // Verifica classi principali
        $requiredClasses = [
            'FP\\PerfSuite\\Plugin',
            'FP\\PerfSuite\\ServiceContainer',
            'FP\\PerfSuite\\Admin\\Pages\\Assets',
            'FP\\PerfSuite\\Admin\\Pages\\Backend',
            'FP\\PerfSuite\\Admin\\Pages\\Database',
            'FP\\PerfSuite\\Admin\\Pages\\ML',
            'FP\\PerfSuite\\Admin\\Pages\\Media',
            'FP\\PerfSuite\\Admin\\Pages\\Mobile'
        ];
        
        foreach ($requiredClasses as $class) {
            if (class_exists($class)) {
                $this->addSuccess("✓ Classe caricata: {$class}");
            } else {
                $this->addError("✗ Classe non trovata: {$class}");
            }
        }
    }
    
    /**
     * Test 2: Verifica pagine admin e bottoni di salvataggio
     */
    private function testAdminPages() {
        echo "\n🎛️ Test 2: Pagine Admin e Bottoni di Salvataggio\n";
        echo str_repeat("-", 50) . "\n";
        
        $adminPages = [
            'fp-performance-suite-assets' => 'Assets',
            'fp-performance-suite-backend' => 'Backend',
            'fp-performance-suite-database' => 'Database',
            'fp-performance-suite-ml' => 'ML',
            'fp-performance-suite-media' => 'Media',
            'fp-performance-suite-mobile' => 'Mobile'
        ];
        
        foreach ($adminPages as $slug => $name) {
            $this->testAdminPage($slug, $name);
        }
    }
    
    /**
     * Test di una singola pagina admin
     */
    private function testAdminPage($slug, $name) {
        echo "  📄 Testando pagina: {$name}\n";
        
        // Verifica se la pagina è registrata nel menu
        global $admin_page_hooks;
        if (isset($admin_page_hooks[$slug])) {
            $this->addSuccess("  ✓ Pagina {$name} registrata nel menu");
        } else {
            $this->addWarning("  ⚠ Pagina {$name} non trovata nel menu admin");
        }
        
        // Test salvataggio opzioni per pagina Assets
        if ($slug === 'fp-performance-suite-assets') {
            $this->testAssetsPageSave();
        }
        
        // Test salvataggio opzioni per pagina Backend
        if ($slug === 'fp-performance-suite-backend') {
            $this->testBackendPageSave();
        }
        
        // Test salvataggio opzioni per pagina Database
        if ($slug === 'fp-performance-suite-database') {
            $this->testDatabasePageSave();
        }
    }
    
    /**
     * Test salvataggio pagina Assets
     */
    private function testAssetsPageSave() {
        echo "    🔧 Test salvataggio Assets...\n";
        
        // Test salvataggio opzioni principali
        $testOptions = [
            'fp_ps_assets' => ['enabled' => true, 'minify_js' => true, 'minify_css' => true],
            'fp_ps_asset_optimization_enabled' => true
        ];
        
        foreach ($testOptions as $option => $value) {
            if (update_option($option, $value)) {
                $this->addSuccess("    ✓ Salvataggio opzione {$option} riuscito");
                
                // Verifica che sia stata salvata
                $saved = get_option($option);
                if ($saved === $value || (is_array($saved) && is_array($value) && $saved == $value)) {
                    $this->addSuccess("    ✓ Verifica opzione {$option} riuscita");
                } else {
                    $this->addError("    ✗ Verifica opzione {$option} fallita");
                }
            } else {
                $this->addError("    ✗ Salvataggio opzione {$option} fallito");
            }
        }
    }
    
    /**
     * Test salvataggio pagina Backend
     */
    private function testBackendPageSave() {
        echo "    🔧 Test salvataggio Backend...\n";
        
        $testOptions = [
            'fp_ps_backend_optimizer' => [
                'enabled' => true,
                'admin_bar' => ['disable_frontend' => true],
                'dashboard' => ['disable_welcome' => true],
                'heartbeat' => ['frontend' => 'disable']
            ]
        ];
        
        foreach ($testOptions as $option => $value) {
            if (update_option($option, $value)) {
                $this->addSuccess("    ✓ Salvataggio opzione {$option} riuscito");
            } else {
                $this->addError("    ✗ Salvataggio opzione {$option} fallito");
            }
        }
    }
    
    /**
     * Test salvataggio pagina Database
     */
    private function testDatabasePageSave() {
        echo "    🔧 Test salvataggio Database...\n";
        
        $testOptions = [
            'fp_ps_db' => [
                'enabled' => true,
                'schedule' => 'daily',
                'batch' => 200,
                'cleanup_scope' => ['posts', 'comments']
            ]
        ];
        
        foreach ($testOptions as $option => $value) {
            if (update_option($option, $value)) {
                $this->addSuccess("    ✓ Salvataggio opzione {$option} riuscito");
            } else {
                $this->addError("    ✗ Salvataggio opzione {$option} fallito");
            }
        }
    }
    
    /**
     * Test 3: Verifica servizi principali
     */
    private function testMainServices() {
        echo "\n⚙️ Test 3: Servizi Principali\n";
        echo str_repeat("-", 50) . "\n";
        
        $services = [
            'FP\\PerfSuite\\Services\\Assets\\Optimizer' => 'Asset Optimizer',
            'FP\\PerfSuite\\Services\\Cache\\PageCache' => 'Page Cache',
            'FP\\PerfSuite\\Services\\Media\\WebPConverter' => 'WebP Converter',
            'FP\\PerfSuite\\Services\\Admin\\BackendOptimizer' => 'Backend Optimizer',
            'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer' => 'Database Optimizer',
            'FP\\PerfSuite\\Services\\ML\\MLPredictor' => 'ML Predictor',
            'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer' => 'Mobile Optimizer'
        ];
        
        foreach ($services as $serviceClass => $serviceName) {
            try {
                if (class_exists($serviceClass)) {
                    $this->addSuccess("✓ Servizio {$serviceName} disponibile");
                    
                    // Test istanziazione del servizio
                    try {
                        $container = FP\PerfSuite\Plugin::container();
                        $service = $container->get($serviceClass);
                        $this->addSuccess("  ✓ Istanziazione {$serviceName} riuscita");
                    } catch (Exception $e) {
                        $this->addError("  ✗ Errore istanziazione {$serviceName}: " . $e->getMessage());
                    }
                } else {
                    $this->addError("✗ Servizio {$serviceName} non trovato");
                }
            } catch (Exception $e) {
                $this->addError("✗ Errore test servizio {$serviceName}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Test 4: Verifica operazioni database
     */
    private function testDatabaseOperations() {
        echo "\n🗄️ Test 4: Operazioni Database\n";
        echo str_repeat("-", 50) . "\n";
        
        global $wpdb;
        
        // Test connessione database
        if ($wpdb->last_error) {
            $this->addError("✗ Errore connessione database: " . $wpdb->last_error);
        } else {
            $this->addSuccess("✓ Connessione database OK");
        }
        
        // Test query semplici
        $testQuery = "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE 'fp_ps_%'";
        $result = $wpdb->get_var($testQuery);
        
        if ($result !== null) {
            $this->addSuccess("✓ Query test riuscita: {$result} opzioni FP Performance trovate");
        } else {
            $this->addError("✗ Query test fallita");
        }
        
        // Test salvataggio opzioni nel database
        $testOption = 'fp_ps_test_option_' . time();
        $testValue = ['test' => true, 'timestamp' => time()];
        
        if (add_option($testOption, $testValue)) {
            $this->addSuccess("✓ Salvataggio opzione test nel database riuscito");
            
            // Verifica lettura
            $savedValue = get_option($testOption);
            if ($savedValue === $testValue) {
                $this->addSuccess("✓ Lettura opzione test dal database riuscita");
            } else {
                $this->addError("✗ Lettura opzione test dal database fallita");
            }
            
            // Pulisci opzione test
            delete_option($testOption);
        } else {
            $this->addError("✗ Salvataggio opzione test nel database fallito");
        }
    }
    
    /**
     * Test 5: Verifica configurazioni e opzioni
     */
    private function testConfigurations() {
        echo "\n⚙️ Test 5: Configurazioni e Opzioni\n";
        echo str_repeat("-", 50) . "\n";
        
        // Verifica opzioni di default
        $defaultOptions = [
            'fp_ps_assets',
            'fp_ps_backend_optimizer',
            'fp_ps_db',
            'fp_ps_mobile_optimizer',
            'fp_ps_ml_predictor'
        ];
        
        foreach ($defaultOptions as $option) {
            $value = get_option($option);
            if ($value !== false) {
                $this->addSuccess("✓ Opzione {$option} configurata");
            } else {
                $this->addWarning("⚠ Opzione {$option} non configurata");
            }
        }
        
        // Verifica costanti del plugin
        $requiredConstants = [
            'FP_PERF_SUITE_VERSION',
            'FP_PERF_SUITE_DIR',
            'FP_PERF_SUITE_FILE'
        ];
        
        foreach ($requiredConstants as $constant) {
            if (defined($constant)) {
                $this->addSuccess("✓ Costante {$constant} definita: " . constant($constant));
            } else {
                $this->addError("✗ Costante {$constant} non definita");
            }
        }
    }
    
    /**
     * Test 6: Verifica errori critici
     */
    private function testCriticalErrors() {
        echo "\n🚨 Test 6: Verifica Errori Critici\n";
        echo str_repeat("-", 50) . "\n";
        
        // Verifica errori PHP
        $errorLog = ini_get('log_errors') ? ini_get('error_log') : 'Non configurato';
        $this->addSuccess("✓ Log errori: {$errorLog}");
        
        // Verifica memoria disponibile
        $memoryLimit = ini_get('memory_limit');
        $this->addSuccess("✓ Limite memoria: {$memoryLimit}");
        
        // Verifica timeout
        $maxExecutionTime = ini_get('max_execution_time');
        $this->addSuccess("✓ Timeout massimo: {$maxExecutionTime}s");
        
        // Verifica permessi directory
        $uploadDir = wp_upload_dir();
        if (is_array($uploadDir) && !empty($uploadDir['basedir'])) {
            $pluginCacheDir = $uploadDir['basedir'] . '/fp-performance-suite';
            
            if (is_dir($pluginCacheDir)) {
                if (is_writable($pluginCacheDir)) {
                    $this->addSuccess("✓ Directory cache scrivibile: {$pluginCacheDir}");
                } else {
                    $this->addError("✗ Directory cache non scrivibile: {$pluginCacheDir}");
                }
            } else {
                $this->addWarning("⚠ Directory cache non esiste: {$pluginCacheDir}");
            }
        }
        
        // Verifica errori di attivazione
        $activationError = get_option('fp_perfsuite_activation_error');
        if ($activationError) {
            $this->addError("✗ Errore di attivazione trovato: " . print_r($activationError, true));
        } else {
            $this->addSuccess("✓ Nessun errore di attivazione");
        }
    }
    
    /**
     * Aggiunge un errore
     */
    private function addError($message) {
        $this->errors[] = $message;
        echo "<span style='color: red;'>" . esc_html($message) . "</span>\n";
    }
    
    /**
     * Aggiunge un warning
     */
    private function addWarning($message) {
        $this->warnings[] = $message;
        echo "<span style='color: orange;'>" . esc_html($message) . "</span>\n";
    }
    
    /**
     * Aggiunge un successo
     */
    private function addSuccess($message) {
        $this->success[] = $message;
        echo "<span style='color: green;'>" . esc_html($message) . "</span>\n";
    }
    
    /**
     * Mostra risultati finali
     */
    private function displayResults() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 RISULTATI FINALI\n";
        echo str_repeat("=", 60) . "\n";
        
        $totalTests = count($this->success) + count($this->warnings) + count($this->errors);
        
        echo "<div style='margin: 20px 0;'>\n";
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin-bottom: 10px;'>\n";
        echo "<strong style='color: #155724;'>✅ SUCCESSI: " . count($this->success) . "</strong>\n";
        echo "</div>\n";
        
        if (!empty($this->warnings)) {
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 10px;'>\n";
            echo "<strong style='color: #856404;'>⚠️ WARNINGS: " . count($this->warnings) . "</strong>\n";
            echo "</div>\n";
        }
        
        if (!empty($this->errors)) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin-bottom: 10px;'>\n";
            echo "<strong style='color: #721c24;'>❌ ERRORI: " . count($this->errors) . "</strong>\n";
            echo "</div>\n";
        }
        echo "</div>\n";
        
        // Riepilogo
        $successRate = $totalTests > 0 ? round((count($this->success) / $totalTests) * 100, 2) : 0;
        
        if ($successRate >= 90) {
            echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; text-align: center;'>\n";
            echo "<h2 style='color: #155724; margin: 0;'>🎉 PLUGIN IN PERFETTO STATO!</h2>\n";
            echo "<p style='color: #155724; margin: 10px 0 0 0; font-size: 18px;'>Tasso di successo: {$successRate}%</p>\n";
            echo "</div>\n";
        } elseif ($successRate >= 70) {
            echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; text-align: center;'>\n";
            echo "<h2 style='color: #856404; margin: 0;'>⚠️ PLUGIN FUNZIONANTE CON AVVISI</h2>\n";
            echo "<p style='color: #856404; margin: 10px 0 0 0; font-size: 18px;'>Tasso di successo: {$successRate}%</p>\n";
            echo "</div>\n";
        } else {
            echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; text-align: center;'>\n";
            echo "<h2 style='color: #721c24; margin: 0;'>❌ PROBLEMI CRITICI TROVATI</h2>\n";
            echo "<p style='color: #721c24; margin: 10px 0 0 0; font-size: 18px;'>Tasso di successo: {$successRate}%</p>\n";
            echo "</div>\n";
        }
        
        // Dettagli errori se presenti
        if (!empty($this->errors)) {
            echo "\n<h3 style='color: #721c24;'>🔍 DETTAGLI ERRORI:</h3>\n";
            foreach ($this->errors as $error) {
                echo "<div style='background: #f8d7da; padding: 10px; margin: 5px 0; border-radius: 4px;'>\n";
                echo "<span style='color: #721c24;'>" . esc_html($error) . "</span>\n";
                echo "</div>\n";
            }
        }
        
        // Raccomandazioni
        echo "\n<h3 style='color: #495057;'>💡 RACCOMANDAZIONI:</h3>\n";
        echo "<ul style='color: #495057;'>\n";
        
        if (count($this->errors) > 0) {
            echo "<li>Risolvi gli errori critici prima di utilizzare il plugin in produzione</li>\n";
        }
        
        if (count($this->warnings) > 0) {
            echo "<li>Rivedi i warning per ottimizzare le performance</li>\n";
        }
        
        echo "<li>Esegui questo test periodicamente per monitorare la salute del plugin</li>\n";
        echo "<li>Fai backup delle configurazioni prima di apportare modifiche</li>\n";
        echo "</ul>\n";
        
        echo "\n<p style='text-align: center; color: #6c757d; font-size: 14px;'>\n";
        echo "Test completato il " . date('d/m/Y H:i:s') . "\n";
        echo "</p>\n";
    }
}

// Esegui il test solo se chiamato direttamente
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    new FPPerformanceSuiteTest();
}
