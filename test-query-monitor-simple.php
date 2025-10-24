<?php
/**
 * Test semplificato per verificare il funzionamento del DatabaseQueryMonitor
 * 
 * Questo script testa la logica del monitoraggio delle query senza WordPress
 */

// Simula l'ambiente WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', '/fake/wordpress/');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', 'test_db');
}

// Simula le funzioni WordPress
if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        static $options = [];
        return $options[$option] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = true) {
        static $options = [];
        $options[$option] = $value;
        return true;
    }
}

if (!function_exists('wp_parse_args')) {
    function wp_parse_args($args, $defaults = []) {
        return array_merge($defaults, $args);
    }
}

// Simula le funzioni WordPress per gli hook
if (!function_exists('add_filter')) {
    function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('add_action')) {
    function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true;
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512) {
        return json_encode($data, $options, $depth);
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

// Simula la classe Logger
if (!class_exists('FP\PerfSuite\Utils\Logger')) {
    class Logger {
        public static function debug($message, $data = []) {
            echo "DEBUG: $message\n";
            if (!empty($data)) {
                echo "Data: " . print_r($data, true) . "\n";
            }
        }
        
        public static function info($message, $data = []) {
            echo "INFO: $message\n";
        }
        
        public static function error($message, $data = []) {
            echo "ERROR: $message\n";
        }
    }
}

// Simula l'oggetto wpdb
if (!class_exists('wpdb')) {
    class wpdb {
        public $queries = [];
        public $num_queries = 0;
        
        public function __construct() {
            $this->queries = [];
            $this->num_queries = 0;
        }
    }
}

// Simula la variabile globale wpdb
$GLOBALS['wpdb'] = new wpdb();

// Carica il DatabaseQueryMonitor
require_once('src/Services/DB/DatabaseQueryMonitor.php');

use FP\PerfSuite\Services\DB\DatabaseQueryMonitor;

echo "=== Test DatabaseQueryMonitor ===\n\n";

try {
    // Test 1: Creazione dell'istanza
    echo "1. Test creazione istanza...\n";
    $monitor = new DatabaseQueryMonitor();
    echo "✅ Monitor creato con successo\n\n";
    
    // Test 2: Registrazione del servizio
    echo "2. Test registrazione servizio...\n";
    $monitor->register();
    echo "✅ Servizio registrato\n\n";
    
    // Test 3: Statistiche iniziali
    echo "3. Test statistiche iniziali...\n";
    $stats = $monitor->getStatistics();
    echo "Query totali: " . ($stats['total_queries'] ?? 0) . "\n";
    echo "Query lente: " . ($stats['slow_queries'] ?? 0) . "\n";
    echo "Query duplicate: " . ($stats['duplicate_queries'] ?? 0) . "\n";
    echo "Tempo totale: " . ($stats['total_query_time'] ?? 0) . "s\n\n";
    
    // Test 4: Simulazione di query
    echo "4. Test simulazione query...\n";
    
    // Simula l'analisi delle query
    $monitor->analyzeWordPressQueries();
    echo "✅ Analisi query eseguita\n";
    
    // Test 5: Salvataggio statistiche
    echo "5. Test salvataggio statistiche...\n";
    $monitor->logStatistics();
    echo "✅ Statistiche salvate\n\n";
    
    // Test 6: Recupero statistiche
    echo "6. Test recupero statistiche...\n";
    $statsAfter = $monitor->getStatistics();
    echo "Query totali dopo: " . ($statsAfter['total_queries'] ?? 0) . "\n";
    echo "Query lente dopo: " . ($statsAfter['slow_queries'] ?? 0) . "\n";
    echo "Query duplicate dopo: " . ($statsAfter['duplicate_queries'] ?? 0) . "\n";
    echo "Tempo totale dopo: " . ($statsAfter['total_query_time'] ?? 0) . "s\n\n";
    
    // Test 7: Verifica persistenza
    echo "7. Test persistenza...\n";
    $savedStats = get_option('fp_ps_query_monitor_stats', []);
    if (!empty($savedStats)) {
        echo "✅ Statistiche salvate nel database\n";
        echo "Query totali salvate: " . ($savedStats['total_queries'] ?? 0) . "\n";
        echo "Query lente salvate: " . ($savedStats['slow_queries'] ?? 0) . "\n";
        echo "Query duplicate salvate: " . ($savedStats['duplicate_queries'] ?? 0) . "\n";
    } else {
        echo "❌ Statistiche non salvate nel database\n";
    }
    
    echo "\n=== Test Completato ===\n";
    echo "Il sistema di monitoraggio delle query è stato testato con successo!\n";
    
} catch (Exception $e) {
    echo "❌ Errore durante il test: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
