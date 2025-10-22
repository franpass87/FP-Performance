<?php
/**
 * Test per verificare se le pagine admin generano contenuto HTML
 */

echo "=== TEST RENDER PAGINE ADMIN ===\n\n";

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

// Carica le classi necessarie
require_once 'src/ServiceContainer.php';
require_once 'src/Utils/Capabilities.php';
require_once 'src/Admin/Pages/AbstractPage.php';

// Test delle pagine
echo "1. Test render pagina Overview...\n";
try {
    require_once 'src/Admin/Pages/Overview.php';
    $container = new \FP\PerfSuite\ServiceContainer();
    $overview = new \FP\PerfSuite\Admin\Pages\Overview($container);
    
    // Cattura l'output del render
    ob_start();
    $overview->render();
    $output = ob_get_clean();
    
    if (!empty($output)) {
        echo "✓ Overview genera output (" . strlen($output) . " caratteri)\n";
        echo "Prime 200 caratteri: " . substr($output, 0, 200) . "...\n";
    } else {
        echo "❌ Overview non genera output\n";
    }
} catch (Exception $e) {
    echo "❌ Errore Overview: " . $e->getMessage() . "\n";
}

echo "\n2. Test render pagina Assets...\n";
try {
    require_once 'src/Admin/Pages/Assets.php';
    $container = new \FP\PerfSuite\ServiceContainer();
    $assets = new \FP\PerfSuite\Admin\Pages\Assets($container);
    
    // Cattura l'output del render
    ob_start();
    $assets->render();
    $output = ob_get_clean();
    
    if (!empty($output)) {
        echo "✓ Assets genera output (" . strlen($output) . " caratteri)\n";
        echo "Prime 200 caratteri: " . substr($output, 0, 200) . "...\n";
    } else {
        echo "❌ Assets non genera output\n";
    }
} catch (Exception $e) {
    echo "❌ Errore Assets: " . $e->getMessage() . "\n";
}

echo "\n3. Test render pagina Cache...\n";
try {
    require_once 'src/Admin/Pages/Cache.php';
    $container = new \FP\PerfSuite\ServiceContainer();
    $cache = new \FP\PerfSuite\Admin\Pages\Cache($container);
    
    // Cattura l'output del render
    ob_start();
    $cache->render();
    $output = ob_get_clean();
    
    if (!empty($output)) {
        echo "✓ Cache genera output (" . strlen($output) . " caratteri)\n";
        echo "Prime 200 caratteri: " . substr($output, 0, 200) . "...\n";
    } else {
        echo "❌ Cache non genera output\n";
    }
} catch (Exception $e) {
    echo "❌ Errore Cache: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETATO ===\n";
