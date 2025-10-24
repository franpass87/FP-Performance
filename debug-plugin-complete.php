<?php
/**
 * 🔧 STRUMENTO DI DEBUG COMPLETO PER FP PERFORMANCE SUITE
 * 
 * Questo strumento testa tutte le funzionalità del plugin e identifica i problemi
 * 
 * Uso: Aggiungi ?debug=1 alla URL di qualsiasi pagina del plugin per attivare il debug
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

class FP_Performance_Debug_Tool {
    
    private $debug_data = [];
    private $errors = [];
    private $warnings = [];
    private $success = [];
    
    public function __construct() {
        add_action('init', [$this, 'init_debug']);
        add_action('admin_menu', [$this, 'add_debug_menu']);
        add_action('wp_footer', [$this, 'show_debug_info']);
        add_action('admin_footer', [$this, 'show_debug_info']);
    }
    
    public function init_debug() {
        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
            $this->start_debug_session();
        }
    }
    
    private function start_debug_session() {
        // Abilita error reporting completo
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Log di debug
        add_action('wp_loaded', [$this, 'debug_plugin_initialization']);
        add_action('wp_enqueue_scripts', [$this, 'debug_scripts_enqueue']);
        add_action('wp_head', [$this, 'debug_head_output']);
        add_action('wp_footer', [$this, 'debug_footer_output']);
        
        $this->log('DEBUG SESSION STARTED', 'info');
    }
    
    public function add_debug_menu() {
        add_submenu_page(
            'fp-performance-suite',
            '🔧 Debug Tool',
            '🔧 Debug',
            'manage_options',
            'fp-performance-debug',
            [$this, 'debug_page']
        );
    }
    
    public function debug_page() {
        echo '<div class="wrap">';
        echo '<h1>🔧 FP Performance Suite - Debug Tool</h1>';
        
        // Test automatici
        $this->run_all_tests();
        
        // Mostra risultati
        $this->display_debug_results();
        
        echo '</div>';
    }
    
    private function run_all_tests() {
        $this->log('=== INIZIO TEST AUTOMATICI ===', 'info');
        
        // Test 1: Inizializzazione Plugin
        $this->test_plugin_initialization();
        
        // Test 2: Servizi del Plugin
        $this->test_plugin_services();
        
        // Test 3: Database e Settings
        $this->test_database_settings();
        
        // Test 4: Form e Salvataggio
        $this->test_forms_saving();
        
        // Test 5: Asset Optimization
        $this->test_asset_optimization();
        
        // Test 6: Performance Monitoring
        $this->test_performance_monitoring();
        
        // Test 7: Mobile Optimization
        $this->test_mobile_optimization();
        
        // Test 8: Database Optimization
        $this->test_database_optimization();
        
        // Test 9: Backend Optimization
        $this->test_backend_optimization();
        
        // Test 10: ML e AI Features
        $this->test_ml_features();
        
        $this->log('=== FINE TEST AUTOMATICI ===', 'info');
    }
    
    private function test_plugin_initialization() {
        $this->log('🧪 Test 1: Inizializzazione Plugin', 'test');
        
        try {
            // Verifica che il plugin sia attivo
            if (!function_exists('is_plugin_active')) {
                include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            
            $plugin_file = 'fp-performance-suite/fp-performance-suite.php';
            $is_active = is_plugin_active($plugin_file);
            
            if ($is_active) {
                $this->success[] = '✅ Plugin attivo e funzionante';
            } else {
                $this->errors[] = '❌ Plugin non attivo';
            }
            
            // Verifica classi principali
            $main_classes = [
                'FP\PerfSuite\Plugin',
                'FP\PerfSuite\ServiceContainer',
                'FP\PerfSuite\Admin\Pages\Assets',
                'FP\PerfSuite\Admin\Pages\Database',
                'FP\PerfSuite\Admin\Pages\Mobile',
                'FP\PerfSuite\Admin\Pages\Backend',
                'FP\PerfSuite\Admin\Pages\ML'
            ];
            
            foreach ($main_classes as $class) {
                if (class_exists($class)) {
                    $this->success[] = "✅ Classe {$class} disponibile";
                } else {
                    $this->errors[] = "❌ Classe {$class} mancante";
                }
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Errore test inizializzazione: " . $e->getMessage();
        }
    }
    
    private function test_plugin_services() {
        $this->log('🧪 Test 2: Servizi del Plugin', 'test');
        
        try {
            $container = new \FP\PerfSuite\ServiceContainer();
            
            // Test servizi principali
            $services = [
                'Assets\Optimizer',
                'Assets\FontOptimizer',
                'Assets\ThirdPartyScriptManager',
                'Database\Optimizer',
                'Mobile\Optimizer',
                'Backend\Optimizer',
                'ML\Optimizer'
            ];
            
            foreach ($services as $service) {
                try {
                    $service_class = "FP\\PerfSuite\\Services\\{$service}";
                    $service_instance = $container->get($service_class);
                    
                    if ($service_instance) {
                        $this->success[] = "✅ Servizio {$service} funzionante";
                    } else {
                        $this->errors[] = "❌ Servizio {$service} non disponibile";
                    }
                } catch (Exception $e) {
                    $this->errors[] = "❌ Errore servizio {$service}: " . $e->getMessage();
                }
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Errore test servizi: " . $e->getMessage();
        }
    }
    
    private function test_database_settings() {
        $this->log('🧪 Test 3: Database e Settings', 'test');
        
        try {
            // Test salvataggio settings
            $test_option = 'fp_ps_debug_test_' . time();
            $test_value = ['test' => 'value', 'timestamp' => time()];
            
            $saved = update_option($test_option, $test_value);
            if ($saved) {
                $retrieved = get_option($test_option);
                if ($retrieved === $test_value) {
                    $this->success[] = '✅ Database settings funzionanti';
                } else {
                    $this->errors[] = '❌ Problema recupero settings dal database';
                }
                delete_option($test_option);
            } else {
                $this->errors[] = '❌ Problema salvataggio settings nel database';
            }
            
            // Test transients
            $transient_key = 'fp_ps_debug_transient_' . time();
            $transient_value = ['test' => 'transient', 'timestamp' => time()];
            
            $saved = set_transient($transient_key, $transient_value, 60);
            if ($saved) {
                $retrieved = get_transient($transient_key);
                if ($retrieved === $transient_value) {
                    $this->success[] = '✅ Transients funzionanti';
                } else {
                    $this->errors[] = '❌ Problema recupero transients';
                }
                delete_transient($transient_key);
            } else {
                $this->errors[] = '❌ Problema salvataggio transients';
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Errore test database: " . $e->getMessage();
        }
    }
    
    private function test_forms_saving() {
        $this->log('🧪 Test 4: Form e Salvataggio', 'test');
        
        try {
            // Simula POST data per test salvataggio
            $original_post = $_POST;
            
            // Test Assets form
            $_POST = [
                'fp_ps_assets_nonce' => wp_create_nonce('fp-ps-assets'),
                'form_type' => 'main_toggle',
                'assets_enabled' => '1'
            ];
            
            $post_handler = new \FP\PerfSuite\Admin\Pages\Assets\Handlers\PostHandler();
            $settings = [];
            $font_settings = [];
            $third_party_settings = [];
            
            $message = $post_handler->handlePost($settings, $font_settings, $third_party_settings);
            
            if ($message && strpos($message, 'successfully') !== false) {
                $this->success[] = '✅ Form Assets salvataggio funzionante';
            } else {
                $this->errors[] = '❌ Problema salvataggio form Assets';
            }
            
            // Test con checkbox non selezionato
            $_POST['assets_enabled'] = '0';
            $message = $post_handler->handlePost($settings, $font_settings, $third_party_settings);
            
            if ($message && strpos($message, 'successfully') !== false) {
                $this->success[] = '✅ Form Assets checkbox unchecked funzionante';
            } else {
                $this->errors[] = '❌ Problema salvataggio checkbox unchecked';
            }
            
            // Ripristina POST originale
            $_POST = $original_post;
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Errore test form: " . $e->getMessage();
        }
    }
    
    private function test_asset_optimization() {
        $this->log('🧪 Test 5: Asset Optimization', 'test');
        
        try {
            $optimizer = new \FP\PerfSuite\Services\Assets\Optimizer();
            
            // Test settings
            $settings = $optimizer->settings();
            if (is_array($settings)) {
                $this->success[] = '✅ Asset Optimizer settings funzionanti';
            } else {
                $this->errors[] = '❌ Problema Asset Optimizer settings';
            }
            
            // Test update
            $test_settings = $settings;
            $test_settings['enabled'] = true;
            $result = $optimizer->update($test_settings);
            
            if ($result) {
                $this->success[] = '✅ Asset Optimizer update funzionante';
            } else {
                $this->errors[] = '❌ Problema Asset Optimizer update';
            }
            
            // Test font optimizer
            $font_optimizer = new \FP\PerfSuite\Services\Assets\FontOptimizer();
            $font_settings = $font_optimizer->getSettings();
            
            if (is_array($font_settings)) {
                $this->success[] = '✅ Font Optimizer funzionante';
            } else {
                $this->errors[] = '❌ Problema Font Optimizer';
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Errore test Asset Optimization: " . $e->getMessage();
        }
    }
    
    private function test_performance_monitoring() {
        $this->log('🧪 Test 6: Performance Monitoring', 'test');
        
        try {
            // Test se esiste il servizio di monitoring
            if (class_exists('\FP\PerfSuite\Services\Monitoring\PerformanceMonitor')) {
                $monitor = new \FP\PerfSuite\Services\Monitoring\PerformanceMonitor();
                $this->success[] = '✅ Performance Monitor disponibile';
            } else {
                $this->warnings[] = '⚠️ Performance Monitor non disponibile';
            }
            
            // Test Core Web Vitals
            if (function_exists('fp_ps_get_core_web_vitals')) {
                $this->success[] = '✅ Core Web Vitals functions disponibili';
            } else {
                $this->warnings[] = '⚠️ Core Web Vitals functions non disponibili';
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Errore test Performance Monitoring: " . $e->getMessage();
        }
    }
    
    private function test_mobile_optimization() {
        $this->log('🧪 Test 7: Mobile Optimization', 'test');
        
        try {
            if (class_exists('\FP\PerfSuite\Services\Mobile\Optimizer')) {
                $mobile_optimizer = new \FP\PerfSuite\Services\Mobile\Optimizer();
                $this->success[] = '✅ Mobile Optimizer disponibile';
            } else {
                $this->warnings[] = '⚠️ Mobile Optimizer non disponibile';
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Errore test Mobile Optimization: " . $e->getMessage();
        }
    }
    
    private function test_database_optimization() {
        $this->log('🧪 Test 8: Database Optimization', 'test');
        
        try {
            if (class_exists('\FP\PerfSuite\Services\Database\Optimizer')) {
                $db_optimizer = new \FP\PerfSuite\Services\Database\Optimizer();
                $this->success[] = '✅ Database Optimizer disponibile';
            } else {
                $this->warnings[] = '⚠️ Database Optimizer non disponibile';
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Errore test Database Optimization: " . $e->getMessage();
        }
    }
    
    private function test_backend_optimization() {
        $this->log('🧪 Test 9: Backend Optimization', 'test');
        
        try {
            if (class_exists('\FP\PerfSuite\Services\Backend\Optimizer')) {
                $backend_optimizer = new \FP\PerfSuite\Services\Backend\Optimizer();
                $this->success[] = '✅ Backend Optimizer disponibile';
            } else {
                $this->warnings[] = '⚠️ Backend Optimizer non disponibile';
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Errore test Backend Optimization: " . $e->getMessage();
        }
    }
    
    private function test_ml_features() {
        $this->log('🧪 Test 10: ML e AI Features', 'test');
        
        try {
            if (class_exists('\FP\PerfSuite\Services\ML\Optimizer')) {
                $ml_optimizer = new \FP\PerfSuite\Services\ML\Optimizer();
                $this->success[] = '✅ ML Optimizer disponibile';
            } else {
                $this->warnings[] = '⚠️ ML Optimizer non disponibile';
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Errore test ML Features: " . $e->getMessage();
        }
    }
    
    private function display_debug_results() {
        echo '<div style="margin: 20px 0;">';
        
        // Statistiche
        $total_tests = count($this->success) + count($this->errors) + count($this->warnings);
        $success_rate = $total_tests > 0 ? round((count($this->success) / $total_tests) * 100, 2) : 0;
        
        echo '<div style="background: #f0f8ff; border: 1px solid #0066cc; padding: 15px; border-radius: 5px; margin-bottom: 20px;">';
        echo '<h3>📊 Statistiche Debug</h3>';
        echo '<p><strong>Test Totali:</strong> ' . $total_tests . '</p>';
        echo '<p><strong>Successi:</strong> ' . count($this->success) . '</p>';
        echo '<p><strong>Errori:</strong> ' . count($this->errors) . '</p>';
        echo '<p><strong>Warning:</strong> ' . count($this->warnings) . '</p>';
        echo '<p><strong>Tasso di Successo:</strong> ' . $success_rate . '%</p>';
        echo '</div>';
        
        // Successi
        if (!empty($this->success)) {
            echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px;">';
            echo '<h3>✅ Successi</h3>';
            foreach ($this->success as $success) {
                echo '<p>' . $success . '</p>';
            }
            echo '</div>';
        }
        
        // Errori
        if (!empty($this->errors)) {
            echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px;">';
            echo '<h3>❌ Errori</h3>';
            foreach ($this->errors as $error) {
                echo '<p>' . $error . '</p>';
            }
            echo '</div>';
        }
        
        // Warning
        if (!empty($this->warnings)) {
            echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">';
            echo '<h3>⚠️ Warning</h3>';
            foreach ($this->warnings as $warning) {
                echo '<p>' . $warning . '</p>';
            }
            echo '</div>';
        }
        
        // Debug log
        if (!empty($this->debug_data)) {
            echo '<div style="background: #e9ecef; border: 1px solid #adb5bd; padding: 15px; border-radius: 5px;">';
            echo '<h3>📝 Debug Log</h3>';
            foreach ($this->debug_data as $log) {
                echo '<p><small>' . $log . '</small></p>';
            }
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    public function show_debug_info() {
        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
            echo '<div style="position: fixed; bottom: 0; right: 0; background: rgba(0,0,0,0.8); color: white; padding: 10px; z-index: 9999; font-size: 12px;">';
            echo '<strong>🔧 DEBUG MODE ACTIVE</strong><br>';
            echo 'Errors: ' . count($this->errors) . ' | Warnings: ' . count($this->warnings) . ' | Success: ' . count($this->success);
            echo '</div>';
        }
    }
    
    private function log($message, $type = 'info') {
        $timestamp = date('Y-m-d H:i:s');
        $this->debug_data[] = "[{$timestamp}] [{$type}] {$message}";
        
        // Log anche nel WordPress debug log
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("FP Performance Debug: {$message}");
        }
    }
    
    public function debug_plugin_initialization() {
        $this->log('Plugin initialization started', 'info');
    }
    
    public function debug_scripts_enqueue() {
        $this->log('Scripts enqueue started', 'info');
    }
    
    public function debug_head_output() {
        $this->log('Head output started', 'info');
    }
    
    public function debug_footer_output() {
        $this->log('Footer output started', 'info');
    }
}

// Inizializza il debug tool
new FP_Performance_Debug_Tool();

// Funzione helper per debug rapido
function fp_ps_debug($message, $type = 'info') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("FP Performance Debug [{$type}]: {$message}");
    }
}

// Funzione per test rapido di una funzionalità
function fp_ps_test_feature($feature_name, $test_function) {
    try {
        $result = $test_function();
        fp_ps_debug("✅ Test {$feature_name}: SUCCESS", 'success');
        return $result;
    } catch (Exception $e) {
        fp_ps_debug("❌ Test {$feature_name}: ERROR - " . $e->getMessage(), 'error');
        return false;
    }
}

// Hook per attivare debug automatico
add_action('init', function() {
    if (isset($_GET['fp_debug']) && $_GET['fp_debug'] == '1') {
        add_action('wp_footer', function() {
            echo '<script>console.log("🔧 FP Performance Debug Mode Active");</script>';
        });
    }
});
