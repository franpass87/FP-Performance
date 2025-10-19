<?php
/**
 * Test Redirect Loop - FP Performance Suite
 * 
 * Questo script aiuta a diagnosticare problemi di "too many redirects"
 * Visita: https://tuosito.com/test-redirect-loop.php
 */

// Disabilita cache per questo test
define('DONOTCACHEPAGE', true);

require_once('wp-load.php');

header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "TEST REDIRECT LOOP\n";
echo "FP Performance Suite\n";
echo "========================================\n\n";

// 1. Check HTTPS
echo "1. HTTPS Status:\n";
echo "   - is_ssl(): " . (is_ssl() ? 'YES ✓' : 'NO ✗') . "\n";
echo "   - \$_SERVER['HTTPS']: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? $_SERVER['HTTPS'] : 'NOT SET or OFF') . "\n";
echo "   - HTTP_X_FORWARDED_PROTO: " . (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : 'NOT SET') . "\n";
echo "   - HTTP_CF_VISITOR: " . (isset($_SERVER['HTTP_CF_VISITOR']) ? $_SERVER['HTTP_CF_VISITOR'] : 'NOT SET') . "\n\n";

// 2. Check URLs
echo "2. WordPress URLs:\n";
$wpHome = defined('WP_HOME') ? WP_HOME : get_option('home');
$wpSiteUrl = defined('WP_SITEURL') ? WP_SITEURL : get_option('siteurl');
$currentUrl = (is_ssl() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

echo "   - WP_HOME: $wpHome\n";
echo "   - WP_SITEURL: $wpSiteUrl\n";
echo "   - Current URL: $currentUrl\n";

// Check for protocol mismatch
$homeProtocol = parse_url($wpHome, PHP_URL_SCHEME);
$siteProtocol = parse_url($wpSiteUrl, PHP_URL_SCHEME);
$currentProtocol = is_ssl() ? 'https' : 'http';

if ($homeProtocol !== $siteProtocol) {
    echo "\n   ⚠️  WARNING: Protocol mismatch between WP_HOME ($homeProtocol) and WP_SITEURL ($siteProtocol)!\n";
}
if ($homeProtocol !== $currentProtocol) {
    echo "   ⚠️  WARNING: WP_HOME uses $homeProtocol but current request is $currentProtocol!\n";
}
echo "\n";

// 3. Check Page Cache
echo "3. FP Performance Suite - Page Cache:\n";
$cacheSettings = get_option('fp_ps_page_cache', []);
$cacheEnabled = !empty($cacheSettings['enabled']);
echo "   - Status: " . ($cacheEnabled ? 'ENABLED ⚠️' : 'DISABLED ✓') . "\n";

if ($cacheEnabled) {
    $cacheDir = WP_CONTENT_DIR . '/cache/fp-performance-suite/page-cache';
    echo "   - Cache Dir: $cacheDir\n";
    
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '/**/*.html');
        $fileCount = is_array($files) ? count($files) : 0;
        echo "   - Cached Files: $fileCount\n";
        
        if ($fileCount > 0) {
            echo "   💡 TIP: Prova a svuotare la cache se il problema persiste\n";
        }
    }
}
echo "\n";

// 4. Check for redirect loops in .htaccess
echo "4. .htaccess Check:\n";
$htaccess = ABSPATH . '.htaccess';
if (file_exists($htaccess)) {
    $content = file_get_contents($htaccess);
    
    // Count redirect rules
    $rewriteRules = substr_count(strtolower($content), 'rewriterule');
    $redirects = substr_count(strtolower($content), 'redirect 301');
    
    echo "   - RewriteRule count: $rewriteRules\n";
    echo "   - Redirect 301 count: $redirects\n";
    
    // Check for HTTPS redirects
    preg_match_all('/RewriteCond.*HTTPS/i', $content, $matches);
    $httpsRedirects = count($matches[0]);
    echo "   - HTTPS Redirect Rules: $httpsRedirects\n";
    
    if ($httpsRedirects > 1) {
        echo "\n   ⚠️  WARNING: Multiple HTTPS redirect rules detected!\n";
        echo "   💡 TIP: Controlla .htaccess per regole duplicate\n";
    }
    
    // Check for www redirects
    preg_match_all('/RewriteCond.*www\./i', $content, $wwwMatches);
    $wwwRedirects = count($wwwMatches[0]);
    if ($wwwRedirects > 0) {
        echo "   - WWW Redirect Rules: $wwwRedirects\n";
    }
} else {
    echo "   - .htaccess not found (this is OK)\n";
}
echo "\n";

// 5. Check active plugins that might cause redirects
echo "5. Potential Conflicting Plugins:\n";
$conflictingPlugins = [
    'really-simple-ssl/rlrsssl-really-simple-ssl.php' => 'Really Simple SSL',
    'wp-force-ssl/wp-force-ssl.php' => 'WP Force SSL',
    'redirection/redirection.php' => 'Redirection',
    'wordpress-https/wordpress-https.php' => 'WordPress HTTPS',
    'wp-rocket/wp-rocket.php' => 'WP Rocket',
    'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
    'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
];

$foundConflicts = false;
foreach ($conflictingPlugins as $file => $name) {
    if (is_plugin_active($file)) {
        echo "   ⚠️  $name is ACTIVE\n";
        $foundConflicts = true;
    }
}

if (!$foundConflicts) {
    echo "   ✓ No conflicting plugins detected\n";
}
echo "\n";

// 6. Check server type
echo "6. Server Environment:\n";
echo "   - Server Software: " . (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown') . "\n";
echo "   - PHP Version: " . PHP_VERSION . "\n";
echo "   - WordPress Version: " . get_bloginfo('version') . "\n\n";

// 7. Recommendations
echo "========================================\n";
echo "RACCOMANDAZIONI\n";
echo "========================================\n\n";

$issues = [];

if ($homeProtocol !== $siteProtocol) {
    $issues[] = "1. Correggi il mismatch tra WP_HOME e WP_SITEURL in wp-config.php";
}

if ($cacheEnabled && $fileCount > 0) {
    $issues[] = "2. Svuota la cache: Dashboard → FP Performance → Cache → Svuota Cache";
}

if ($httpsRedirects > 1) {
    $issues[] = "3. Rimuovi regole HTTPS duplicate da .htaccess";
}

if ($foundConflicts) {
    $issues[] = "4. Disabilita temporaneamente gli altri plugin di cache/redirect";
}

if (isset($_SERVER['HTTP_CF_VISITOR']) && $homeProtocol === 'https' && !is_ssl()) {
    $issues[] = "5. Aggiungi la gestione Cloudflare SSL in wp-config.php (vedi SOLUZIONE_TOO_MANY_REDIRECTS.md)";
}

if (empty($issues)) {
    echo "✓ Nessun problema evidente rilevato!\n\n";
    echo "Se il problema persiste:\n";
    echo "1. Cancella i cookie del browser\n";
    echo "2. Prova in modalità incognito\n";
    echo "3. Controlla le impostazioni SSL di Cloudflare (se usato)\n";
    echo "4. Controlla wp-content/debug.log per errori\n";
} else {
    echo "⚠️  Problemi rilevati:\n\n";
    foreach ($issues as $issue) {
        echo "$issue\n";
    }
    echo "\nConsulta SOLUZIONE_TOO_MANY_REDIRECTS.md per le soluzioni complete\n";
}

echo "\n========================================\n";
echo "TEST COMPLETATO\n";
echo "========================================\n";

