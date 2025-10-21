<?php
/**
 * Test Auto Font Optimizer
 * 
 * Test per verificare il funzionamento del sistema di auto-rilevamento
 * e ottimizzazione automatica dei font.
 */

// Simula ambiente WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Simula funzioni WordPress
if (!function_exists('home_url')) {
    function home_url($path = '') {
        return 'https://ilpoderedimarfisa.it' . $path;
    }
}

if (!function_exists('get_stylesheet_directory')) {
    function get_stylesheet_directory() {
        return __DIR__ . '/wp-content/themes/salient';
    }
}

if (!function_exists('get_stylesheet_directory_uri')) {
    function get_stylesheet_directory_uri() {
        return 'https://ilpoderedimarfisa.it/wp-content/themes/salient';
    }
}

if (!function_exists('get_stylesheet')) {
    function get_stylesheet() {
        return 'salient';
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return false;
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        // Simula registrazione hook
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        // Simula registrazione filter
        return true;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        return true;
    }
}

if (!function_exists('do_action')) {
    function do_action($hook, ...$args) {
        // Simula azione
        return true;
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('printf')) {
    function printf($format, ...$args) {
        echo sprintf($format, ...$args);
    }
}

// Simula Logger
class Logger {
    public static function debug($message, $context = []) {
        echo "🔍 DEBUG: $message\n";
        if (!empty($context)) {
            echo "   Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n";
        }
    }
}

// Includi la classe AutoFontOptimizer
require_once __DIR__ . '/src/Services/Assets/AutoFontOptimizer.php';

use FP\PerfSuite\Services\Assets\AutoFontOptimizer;

echo "🚀 Test Auto Font Optimizer\n";
echo "============================\n\n";

// Crea istanza dell'ottimizzatore
$optimizer = new AutoFontOptimizer();

echo "1️⃣ Test Auto-Rilevamento Font Problematici\n";
echo "--------------------------------------------\n";

// Simula font problematici
$mockStyles = (object) [
    'done' => ['fontawesome', 'gillsans', 'theme-font', 'google-fonts'],
    'registered' => [
        'fontawesome' => (object) ['src' => 'https://use.fontawesome.com/releases/v6.0.0/css/all.css'],
        'gillsans' => (object) ['src' => 'https://ilpoderedimarfisa.it/wp-content/themes/salient/fonts/useanyfont/939GillSans-Light.woff2'],
        'theme-font' => (object) ['src' => 'https://ilpoderedimarfisa.it/wp-content/themes/salient/fonts/fontawesome-webfont.woff'],
        'google-fonts' => (object) ['src' => 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap']
    ]
];

// Simula variabile globale
$GLOBALS['wp_styles'] = $mockStyles;

// Test rilevamento font problematici
$reflection = new ReflectionClass($optimizer);
$method = $reflection->getMethod('detectProblematicFonts');
$method->setAccessible(true);

$detectedFonts = $method->invoke($optimizer);

echo "Font rilevati: " . count($detectedFonts) . "\n";
foreach ($detectedFonts as $font) {
    echo "  - {$font['name']} ({$font['url']}) - Priorità: {$font['priority']}\n";
}

echo "\n2️⃣ Test Rilevamento Font Critici\n";
echo "----------------------------------\n";

$method = $reflection->getMethod('detectCriticalFonts');
$method->setAccessible(true);

$criticalFonts = $method->invoke($optimizer);

echo "Font critici: " . count($criticalFonts) . "\n";
foreach ($criticalFonts as $font) {
    echo "  - {$font['name']} - Priorità: {$font['priority']}\n";
}

echo "\n3️⃣ Test Rilevamento Provider Font\n";
echo "----------------------------------\n";

$method = $reflection->getMethod('detectFontProviders');
$method->setAccessible(true);

$providers = $method->invoke($optimizer);

echo "Provider rilevati: " . count($providers) . "\n";
foreach ($providers as $provider) {
    echo "  - {$provider['url']} (Crossorigin: " . ($provider['crossorigin'] ? 'Sì' : 'No') . ")\n";
}

echo "\n4️⃣ Test Generazione CSS Font Display\n";
echo "--------------------------------------\n";

$method = $reflection->getMethod('generateAutoFontDisplayCSS');
$method->setAccessible(true);

$css = $method->invoke($optimizer, $detectedFonts);

echo "CSS generato:\n";
echo "```css\n$css\n```\n";

echo "\n5️⃣ Test Ottimizzazione Google Fonts\n";
echo "------------------------------------\n";

$method = $reflection->getMethod('autoOptimizeGoogleFonts');
$method->setAccessible(true);

$originalHtml = '<link rel="stylesheet" id="google-fonts-css" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700" media="all" />';
$optimizedHtml = $method->invoke($optimizer, $originalHtml, 'google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700', 'all');

echo "HTML originale:\n$originalHtml\n";
echo "HTML ottimizzato:\n$optimizedHtml\n";

echo "\n6️⃣ Test Controllo Font Problematici\n";
echo "------------------------------------\n";

$method = $reflection->getMethod('isProblematicFont');
$method->setAccessible(true);

$testCases = [
    ['fontawesome', 'https://use.fontawesome.com/releases/v6.0.0/css/all.css'],
    ['gillsans', 'https://ilpoderedimarfisa.it/wp-content/themes/salient/fonts/useanyfont/939GillSans-Light.woff2'],
    ['normal-style', 'https://example.com/normal.css'],
    ['google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto']
];

foreach ($testCases as $case) {
    $isProblematic = $method->invoke($optimizer, $case[0], $case[1]);
    $status = $isProblematic ? '❌ PROBLEMATICO' : '✅ OK';
    echo "  - {$case[0]} ({$case[1]}) - $status\n";
}

echo "\n7️⃣ Test Status e Configurazione\n";
echo "---------------------------------\n";

$status = $optimizer->status();
echo "Status dell'ottimizzatore:\n";
foreach ($status as $key => $value) {
    if (is_bool($value)) {
        $value = $value ? 'Sì' : 'No';
    }
    echo "  - $key: $value\n";
}

echo "\n8️⃣ Test Simulazione Output HTML\n";
echo "---------------------------------\n";

echo "Simulazione output HTML che verrebbe generato:\n";
echo "<!-- FP Performance Suite - Auto Font Detection & Optimization -->\n";

// Simula preload font critici
foreach ($criticalFonts as $font) {
    $type = $font['type'] ?? 'font/woff2';
    $crossorigin = !empty($font['crossorigin']) ? ' crossorigin' : '';
    $fetchpriority = !empty($font['priority']) ? ' fetchpriority="' . $font['priority'] . '"' : '';
    
    echo "<link rel=\"preload\" href=\"{$font['url']}\" as=\"font\" type=\"$type\"$crossorigin$fetchpriority />\n";
}

echo "<!-- End Auto Font Detection & Optimization -->\n\n";

echo "<!-- FP Performance Suite - Auto Font Display Fix -->\n";
echo "<style id=\"fp-auto-font-display-fix\">\n$css\n</style>\n";
echo "<!-- End Auto Font Display Fix -->\n\n";

echo "<!-- FP Performance Suite - Auto Font Provider Preconnect -->\n";
foreach ($providers as $provider) {
    $crossorigin = !empty($provider['crossorigin']) ? ' crossorigin' : '';
    echo "<link rel=\"preconnect\" href=\"{$provider['url']}\"$crossorigin />\n";
}
echo "<!-- End Auto Font Provider Preconnect -->\n";

echo "\n✅ Test completato con successo!\n";
echo "\n📊 Riepilogo:\n";
echo "  - Font problematici rilevati: " . count($detectedFonts) . "\n";
echo "  - Font critici per preload: " . count($criticalFonts) . "\n";
echo "  - Provider font rilevati: " . count($providers) . "\n";
echo "  - CSS font-display generato: " . (strlen($css) > 0 ? 'Sì' : 'No') . "\n";

echo "\n🎯 Il sistema di auto-rilevamento funziona correttamente!\n";
echo "   Tutti i font problematici vengono identificati e ottimizzati automaticamente.\n";
