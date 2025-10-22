<?php
/**
 * Test per verificare se le pagine admin generano contenuto
 */

// Simula l'ambiente WordPress minimo
define('ABSPATH', __DIR__ . '/');
define('FP_PERF_SUITE_DIR', __DIR__);
define('FP_PERF_SUITE_FILE', __DIR__ . '/fp-performance-suite.php');

// Mock delle funzioni WordPress essenziali
function current_user_can($capability) {
    return true;
}

function wp_die($message) {
    echo "❌ ERRORE: $message\n";
    exit(1);
}

function esc_html__($text, $domain = 'default') {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function is_readable($filename) {
    return \is_readable($filename);
}

function __($text, $domain = 'default') {
    return $text;
}

function esc_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esc_attr($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esc_textarea($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function checked($checked, $current = true, $echo = true) {
    if ($echo) {
        echo $checked == $current ? 'checked="checked"' : '';
    } else {
        return $checked == $current ? 'checked="checked"' : '';
    }
}

function selected($selected, $current = true, $echo = true) {
    if ($echo) {
        echo $selected == $current ? 'selected="selected"' : '';
    } else {
        return $selected == $current ? 'selected="selected"' : '';
    }
}

function sanitize_text_field($str) {
    return trim($str);
}

function wp_nonce_field($action = -1, $name = "_wpnonce", $referer = true, $echo = true) {
    $nonce = wp_create_nonce($action);
    $field = '<input type="hidden" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="' . esc_attr($nonce) . '" />';
    if ($echo) {
        echo $field;
    } else {
        return $field;
    }
}

function wp_create_nonce($action = -1) {
    return 'test_nonce';
}

function wp_unslash($value) {
    return stripslashes($value);
}

function wp_verify_nonce($nonce, $action = -1) {
    return true;
}

function get_option($option, $default = false) {
    return $default;
}

function number_format_i18n($number, $decimals = 0) {
    return number_format($number, $decimals);
}

function date_i18n($format, $timestamp = null) {
    return date($format, $timestamp ?: time());
}

function array_map($callback, $array) {
    return \array_map($callback, $array);
}

function printf($format, ...$args) {
    echo sprintf($format, ...$args);
}

function sprintf($format, ...$args) {
    return \sprintf($format, ...$args);
}

// Mock delle classi WordPress
class WP_Error {
    public function __construct($code = '', $message = '', $data = '') {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }
}

// Mock del database
class MockWpdb {
    public $dbh;
    
    public function __construct() {
        $this->dbh = new \stdClass();
    }
    
    public function query($query) {
        return true;
    }
    
    public function get_row($query, $output = OBJECT, $y = 0) {
        return new \stdClass();
    }
    
    public function get_results($query, $output = OBJECT) {
        return [];
    }
    
    public function prepare($query, ...$args) {
        return $query;
    }
}

$wpdb = new MockWpdb();

// Mock delle funzioni di database
function DB_NAME() {
    return 'test_db';
}

// Carica il plugin
require_once 'src/ServiceContainer.php';
require_once 'src/Utils/Capabilities.php';

// Test delle pagine
echo "=== TEST CONTENUTO PAGINE ADMIN ===\n\n";

try {
    // Test Overview
    echo "1. Test pagina Overview...\n";
    require_once 'src/Admin/Pages/Overview.php';
    $container = new \FP\PerfSuite\ServiceContainer();
    $overview = new \FP\PerfSuite\Admin\Pages\Overview($container);
    
    // Cattura l'output del metodo content()
    ob_start();
    try {
        $content = $overview->content();
        ob_end_clean();
        if (!empty($content)) {
            echo "✓ Overview genera contenuto (" . strlen($content) . " caratteri)\n";
        } else {
            echo "❌ Overview non genera contenuto\n";
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ Errore Overview: " . $e->getMessage() . "\n";
    }
    
    // Test Assets
    echo "\n2. Test pagina Assets...\n";
    require_once 'src/Admin/Pages/Assets.php';
    $assets = new \FP\PerfSuite\Admin\Pages\Assets($container);
    
    ob_start();
    try {
        $content = $assets->content();
        ob_end_clean();
        if (!empty($content)) {
            echo "✓ Assets genera contenuto (" . strlen($content) . " caratteri)\n";
        } else {
            echo "❌ Assets non genera contenuto\n";
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ Errore Assets: " . $e->getMessage() . "\n";
    }
    
    // Test Cache
    echo "\n3. Test pagina Cache...\n";
    require_once 'src/Admin/Pages/Cache.php';
    $cache = new \FP\PerfSuite\Admin\Pages\Cache($container);
    
    ob_start();
    try {
        $content = $cache->content();
        ob_end_clean();
        if (!empty($content)) {
            echo "✓ Cache genera contenuto (" . strlen($content) . " caratteri)\n";
        } else {
            echo "❌ Cache non genera contenuto\n";
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ Errore Cache: " . $e->getMessage() . "\n";
    }
    
    // Test Database
    echo "\n4. Test pagina Database...\n";
    require_once 'src/Admin/Pages/Database.php';
    $database = new \FP\PerfSuite\Admin\Pages\Database($container);
    
    ob_start();
    try {
        $content = $database->content();
        ob_end_clean();
        if (!empty($content)) {
            echo "✓ Database genera contenuto (" . strlen($content) . " caratteri)\n";
        } else {
            echo "❌ Database non genera contenuto\n";
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ Errore Database: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== TEST COMPLETATO ===\n";
    
} catch (Exception $e) {
    echo "❌ ERRORE CRITICO: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Linea: " . $e->getLine() . "\n";
    exit(1);
} catch (Error $e) {
    echo "❌ ERRORE FATALE: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Linea: " . $e->getLine() . "\n";
    exit(1);
}
