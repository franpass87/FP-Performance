<?php
/**
 * 🚀 DEBUG RAPIDO PER FP PERFORMANCE SUITE
 * 
 * Aggiungi ?debug=1 a qualsiasi URL del plugin per attivare il debug
 * 
 * Esempi:
 * - /wp-admin/admin.php?page=fp-performance-suite-assets&debug=1
 * - /wp-admin/admin.php?page=fp-performance-suite-database&debug=1
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

class FP_Performance_Quick_Debug {
    
    private static $instance = null;
    private $debug_active = false;
    private $debug_data = [];
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('init', [$this, 'check_debug_mode']);
        add_action('wp_footer', [$this, 'show_debug_info']);
        add_action('admin_footer', [$this, 'show_debug_info']);
    }
    
    public function check_debug_mode() {
        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
            $this->debug_active = true;
            $this->enable_debug_mode();
        }
    }
    
    private function enable_debug_mode() {
        // Abilita error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Log di debug
        add_action('wp_loaded', [$this, 'log_debug_info']);
        
        $this->log('🔧 DEBUG MODE ACTIVATED');
    }
    
    public function log_debug_info() {
        $this->log('WordPress loaded');
        $this->log('Current page: ' . $_SERVER['REQUEST_URI']);
        $this->log('POST data: ' . print_r($_POST, true));
        $this->log('GET data: ' . print_r($_GET, true));
    }
    
    public function show_debug_info() {
        if ($this->debug_active) {
            echo '<div id="fp-debug-panel" style="position: fixed; bottom: 0; left: 0; width: 100%; background: rgba(0,0,0,0.9); color: white; padding: 10px; z-index: 9999; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto;">';
            echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
            echo '<strong>🔧 FP PERFORMANCE DEBUG MODE</strong>';
            echo '<button onclick="document.getElementById(\'fp-debug-panel\').style.display=\'none\'" style="background: #ff4444; color: white; border: none; padding: 5px 10px; cursor: pointer;">Chiudi</button>';
            echo '</div>';
            
            echo '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">';
            
            // Debug Info
            echo '<div>';
            echo '<h4 style="margin: 0 0 10px 0; color: #00ff00;">📊 Debug Info</h4>';
            echo '<p><strong>URL:</strong> ' . $_SERVER['REQUEST_URI'] . '</p>';
            echo '<p><strong>Method:</strong> ' . $_SERVER['REQUEST_METHOD'] . '</p>';
            echo '<p><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</p>';
            echo '</div>';
            
            // POST Data
            echo '<div>';
            echo '<h4 style="margin: 0 0 10px 0; color: #ffaa00;">📝 POST Data</h4>';
            if (!empty($_POST)) {
                foreach ($_POST as $key => $value) {
                    if (is_array($value)) {
                        echo '<p><strong>' . $key . ':</strong> ' . print_r($value, true) . '</p>';
                    } else {
                        echo '<p><strong>' . $key . ':</strong> ' . $value . '</p>';
                    }
                }
            } else {
                echo '<p>Nessun dato POST</p>';
            }
            echo '</div>';
            
            // Debug Log
            echo '<div>';
            echo '<h4 style="margin: 0 0 10px 0; color: #00aaff;">📋 Debug Log</h4>';
            foreach ($this->debug_data as $log) {
                echo '<p style="margin: 2px 0;">' . $log . '</p>';
            }
            echo '</div>';
            
            echo '</div>';
            echo '</div>';
        }
    }
    
    public function log($message) {
        $timestamp = date('H:i:s');
        $this->debug_data[] = "[{$timestamp}] {$message}";
        
        // Log anche nel WordPress debug log
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("FP Performance Debug: {$message}");
        }
    }
    
    public function test_feature($feature_name, $test_function) {
        if (!$this->debug_active) return false;
        
        try {
            $result = $test_function();
            $this->log("✅ Test {$feature_name}: SUCCESS");
            return $result;
        } catch (Exception $e) {
            $this->log("❌ Test {$feature_name}: ERROR - " . $e->getMessage());
            return false;
        }
    }
    
    public function is_debug_active() {
        return $this->debug_active;
    }
}

// Inizializza il debug rapido
FP_Performance_Quick_Debug::get_instance();

// Funzioni helper per debug rapido
function fp_ps_debug_log($message) {
    $debug = FP_Performance_Quick_Debug::get_instance();
    $debug->log($message);
}

function fp_ps_debug_test($feature_name, $test_function) {
    $debug = FP_Performance_Quick_Debug::get_instance();
    return $debug->test_feature($feature_name, $test_function);
}

function fp_ps_is_debug_active() {
    $debug = FP_Performance_Quick_Debug::get_instance();
    return $debug->is_debug_active();
}

// Hook per test automatici quando il debug è attivo
add_action('wp_loaded', function() {
    if (fp_ps_is_debug_active()) {
        // Test automatici
        fp_ps_debug_log('🧪 Eseguendo test automatici...');
        
        // Test 1: Plugin attivo
        fp_ps_debug_test('Plugin Active', function() {
            return function_exists('is_plugin_active') && is_plugin_active('fp-performance-suite/fp-performance-suite.php');
        });
        
        // Test 2: Classi principali
        fp_ps_debug_test('Main Classes', function() {
            $classes = [
                'FP\PerfSuite\Plugin',
                'FP\PerfSuite\ServiceContainer'
            ];
            $all_exist = true;
            foreach ($classes as $class) {
                if (!class_exists($class)) {
                    $all_exist = false;
                    break;
                }
            }
            return $all_exist;
        });
        
        // Test 3: Database connection
        fp_ps_debug_test('Database Connection', function() {
            global $wpdb;
            return $wpdb->db_connect();
        });
        
        fp_ps_debug_log('✅ Test automatici completati');
    }
});
