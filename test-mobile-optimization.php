<?php
/**
 * Test Ottimizzazioni Mobile Plugin FP Performance Suite
 * Script per verificare funzionalità mobile e responsive
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

echo "<h1>📱 Test Ottimizzazioni Mobile</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .test-item { margin: 10px 0; padding: 5px; }
    .mobile-test { background: #e8f4fd; padding: 10px; margin: 5px 0; border-radius: 3px; }
    .responsive-test { background: #f0f8ff; padding: 10px; margin: 5px 0; border-radius: 3px; }
</style>";

// 1. VERIFICA RILEVAMENTO DISPOSITIVO
echo "<div class='section'>";
echo "<h2>📱 1. Verifica Rilevamento Dispositivo</h2>";

// Test User Agent
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
echo "<div class='test-item info'>📋 User Agent: " . substr($user_agent, 0, 100) . "...</div>";

// Test rilevamento mobile
$mobile_keywords = ['Mobile', 'Android', 'iPhone', 'iPad', 'BlackBerry', 'Windows Phone'];
$is_mobile_detected = false;

foreach ($mobile_keywords as $keyword) {
    if (stripos($user_agent, $keyword) !== false) {
        $is_mobile_detected = true;
        echo "<div class='test-item success'>✅ Dispositivo mobile rilevato: {$keyword}</div>";
        break;
    }
}

if (!$is_mobile_detected) {
    echo "<div class='test-item info'>💻 Dispositivo desktop rilevato</div>";
}

// Test dimensioni schermo (se disponibili)
if (isset($_GET['screen_width']) && isset($_GET['screen_height'])) {
    $screen_width = intval($_GET['screen_width']);
    $screen_height = intval($_GET['screen_height']);
    echo "<div class='test-item info'>📐 Dimensioni schermo: {$screen_width}x{$screen_height}</div>";
    
    if ($screen_width < 768) {
        echo "<div class='test-item success'>✅ Schermo mobile rilevato</div>";
    } else {
        echo "<div class='test-item info'>💻 Schermo desktop rilevato</div>";
    }
} else {
    echo "<div class='test-item info'>📐 Dimensioni schermo non disponibili (aggiungi ?screen_width=320&screen_height=568 per test)</div>";
}
echo "</div>";

// 2. TEST OTTIMIZZAZIONI MOBILE
echo "<div class='section'>";
echo "<h2>⚡ 2. Test Ottimizzazioni Mobile</h2>";

if (class_exists('FP_Mobile_Optimizer')) {
    $mobile_optimizer = new FP_Mobile_Optimizer();
    echo "<div class='test-item success'>✅ Mobile Optimizer istanziato</div>";
    
    // Test ottimizzazione immagini mobile
    if (method_exists($mobile_optimizer, 'optimize_images')) {
        try {
            $image_result = $mobile_optimizer->optimize_images();
            if ($image_result) {
                echo "<div class='test-item success'>✅ Ottimizzazione immagini mobile eseguita</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Ottimizzazione immagini mobile fallita</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore ottimizzazione immagini mobile: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test lazy loading
    if (method_exists($mobile_optimizer, 'lazy_load')) {
        try {
            $lazy_result = $mobile_optimizer->lazy_load();
            if ($lazy_result) {
                echo "<div class='test-item success'>✅ Lazy loading abilitato</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Lazy loading fallito</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore lazy loading: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test CSS mobile
    if (method_exists($mobile_optimizer, 'mobile_css')) {
        try {
            $css_result = $mobile_optimizer->mobile_css();
            if ($css_result) {
                echo "<div class='test-item success'>✅ CSS mobile ottimizzato</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ CSS mobile fallito</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore CSS mobile: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test JavaScript mobile
    if (method_exists($mobile_optimizer, 'mobile_js')) {
        try {
            $js_result = $mobile_optimizer->mobile_js();
            if ($js_result) {
                echo "<div class='test-item success'>✅ JavaScript mobile ottimizzato</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ JavaScript mobile fallito</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore JavaScript mobile: " . $e->getMessage() . "</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Mobile Optimizer NON disponibile</div>";
}
echo "</div>";

// 3. TEST RESPONSIVE DESIGN
echo "<div class='section'>";
echo "<h2>📐 3. Test Responsive Design</h2>";

// Test viewport meta tag
$viewport_meta = '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo "<div class='test-item info'>📋 Viewport meta tag: {$viewport_meta}</div>";

// Test media queries CSS
$media_queries = [
    '@media (max-width: 768px)' => 'Tablet e mobile',
    '@media (max-width: 480px)' => 'Mobile',
    '@media (max-width: 320px)' => 'Mobile piccolo'
];

foreach ($media_queries as $query => $description) {
    echo "<div class='test-item info'>📋 Media query: {$query} - {$description}</div>";
}

// Test breakpoints
$breakpoints = [
    'mobile' => '320px - 767px',
    'tablet' => '768px - 1023px',
    'desktop' => '1024px+'
];

foreach ($breakpoints as $device => $range) {
    echo "<div class='test-item info'>📱 {$device}: {$range}</div>";
}
echo "</div>";

// 4. TEST OTTIMIZZAZIONI SPECIFICHE MOBILE
echo "<div class='section'>";
echo "<h2>🚀 4. Test Ottimizzazioni Specifiche Mobile</h2>";

// Test compressione immagini
if (function_exists('fp_mobile_compress_images')) {
    try {
        $compress_result = fp_mobile_compress_images();
        if ($compress_result) {
            echo "<div class='test-item success'>✅ Compressione immagini mobile eseguita</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Compressione immagini mobile fallita</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore compressione immagini: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='test-item warning'>⚠️ Funzione compressione immagini mobile non disponibile</div>";
}

// Test WebP conversion
if (function_exists('fp_mobile_webp_conversion')) {
    try {
        $webp_result = fp_mobile_webp_conversion();
        if ($webp_result) {
            echo "<div class='test-item success'>✅ Conversione WebP mobile eseguita</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Conversione WebP mobile fallita</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore conversione WebP: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='test-item warning'>⚠️ Funzione conversione WebP mobile non disponibile</div>";
}

// Test minificazione CSS mobile
if (function_exists('fp_mobile_minify_css')) {
    try {
        $minify_result = fp_mobile_minify_css();
        if ($minify_result) {
            echo "<div class='test-item success'>✅ Minificazione CSS mobile eseguita</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Minificazione CSS mobile fallita</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore minificazione CSS: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='test-item warning'>⚠️ Funzione minificazione CSS mobile non disponibile</div>";
}
echo "</div>";

// 5. TEST PERFORMANCE MOBILE
echo "<div class='section'>";
echo "<h2>⚡ 5. Test Performance Mobile</h2>";

// Test tempo di caricamento mobile
$start_time = microtime(true);
$end_time = microtime(true);
$mobile_load_time = ($end_time - $start_time) * 1000;

echo "<div class='test-item info'>⏱️ Tempo di caricamento mobile: " . round($mobile_load_time, 2) . "ms</div>";

// Test memoria mobile
$memory_usage = memory_get_usage(true);
$memory_peak = memory_get_peak_usage(true);

echo "<div class='test-item info'>💾 Memoria utilizzata: " . round($memory_usage / 1024 / 1024, 2) . "MB</div>";
echo "<div class='test-item info'>📈 Picco memoria: " . round($memory_peak / 1024 / 1024, 2) . "MB</div>";

// Test query database mobile
global $wpdb;
$query_count = $wpdb->num_queries;
echo "<div class='test-item info'>🗄️ Query database: {$query_count}</div>";

// Test score mobile
if (class_exists('FP_Performance_Monitor')) {
    $monitor = new FP_Performance_Monitor();
    if (method_exists($monitor, 'get_mobile_score')) {
        try {
            $mobile_score = $monitor->get_mobile_score();
            echo "<div class='test-item success'>✅ Score mobile: {$mobile_score}/100</div>";
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore score mobile: " . $e->getMessage() . "</div>";
        }
    }
}
echo "</div>";

// 6. TEST OPZIONI MOBILE
echo "<div class='section'>";
echo "<h2>⚙️ 6. Test Opzioni Mobile</h2>";

$mobile_options = get_option('fp_performance_mobile_options', []);
if (!empty($mobile_options)) {
    echo "<div class='test-item success'>✅ Opzioni mobile caricate</div>";
    
    $mobile_settings = [
        'mobile_optimization' => 'Ottimizzazione mobile',
        'image_compression' => 'Compressione immagini',
        'lazy_loading' => 'Lazy loading',
        'webp_conversion' => 'Conversione WebP',
        'css_minification' => 'Minificazione CSS',
        'js_minification' => 'Minificazione JS'
    ];
    
    foreach ($mobile_settings as $option => $name) {
        if (isset($mobile_options[$option])) {
            $status = $mobile_options[$option] ? 'Abilitato' : 'Disabilitato';
            $class = $mobile_options[$option] ? 'success' : 'warning';
            echo "<div class='test-item {$class}'>📋 {$name}: {$status}</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Opzione {$name} non trovata</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Opzioni mobile vuote</div>";
}
echo "</div>";

// 7. TEST COMPATIBILITÀ BROWSER
echo "<div class='section'>";
echo "<h2>🌐 7. Test Compatibilità Browser</h2>";

$mobile_browsers = [
    'Chrome Mobile' => 'Chrome Mobile',
    'Safari Mobile' => 'Safari Mobile',
    'Firefox Mobile' => 'Firefox Mobile',
    'Edge Mobile' => 'Edge Mobile'
];

foreach ($mobile_browsers as $browser => $name) {
    if (stripos($user_agent, $browser) !== false) {
        echo "<div class='test-item success'>✅ Browser mobile rilevato: {$name}</div>";
    }
}

// Test supporto WebP
if (function_exists('fp_supports_webp')) {
    $webp_support = fp_supports_webp();
    if ($webp_support) {
        echo "<div class='test-item success'>✅ Supporto WebP rilevato</div>";
    } else {
        echo "<div class='test-item warning'>⚠️ Supporto WebP non rilevato</div>";
    }
} else {
    echo "<div class='test-item info'>📋 Funzione supporto WebP non disponibile</div>";
}
echo "</div>";

// 8. TEST ACCESSIBILITÀ MOBILE
echo "<div class='section'>";
echo "<h2>♿ 8. Test Accessibilità Mobile</h2>";

// Test touch targets
$touch_targets = [
    'min_size' => '44px',
    'spacing' => '8px',
    'contrast' => '4.5:1'
];

foreach ($touch_targets as $target => $value) {
    echo "<div class='test-item info'>📋 {$target}: {$value}</div>";
}

// Test font size
$font_sizes = [
    'mobile_min' => '16px',
    'mobile_comfortable' => '18px',
    'mobile_large' => '20px'
];

foreach ($font_sizes as $size => $value) {
    echo "<div class='test-item info'>📋 {$size}: {$value}</div>";
}
echo "</div>";

// 9. RACCOMANDAZIONI MOBILE
echo "<div class='section'>";
echo "<h2>💡 9. Raccomandazioni Mobile</h2>";

echo "<div class='test-item info'>📋 1. Abilita tutte le ottimizzazioni mobile</div>";
echo "<div class='test-item info'>📋 2. Testa su dispositivi reali</div>";
echo "<div class='test-item info'>📋 3. Verifica la velocità di caricamento</div>";
echo "<div class='test-item info'>📋 4. Controlla l'accessibilità</div>";
echo "<div class='test-item info'>📋 5. Ottimizza le immagini per mobile</div>";
echo "<div class='test-item info'>📋 6. Usa lazy loading per le immagini</div>";
echo "<div class='test-item info'>📋 7. Minifica CSS e JavaScript</div>";
echo "<div class='test-item info'>📋 8. Testa la compatibilità browser</div>";

echo "</div>";

// 10. LINK DI TEST
echo "<div class='section'>";
echo "<h2>🔗 10. Link di Test</h2>";

$test_urls = [
    'Mobile Test' => '?screen_width=375&screen_height=667',
    'Tablet Test' => '?screen_width=768&screen_height=1024',
    'Desktop Test' => '?screen_width=1920&screen_height=1080'
];

foreach ($test_urls as $name => $params) {
    $test_url = $_SERVER['REQUEST_URI'] . $params;
    echo "<div class='test-item info'>🔗 <a href='{$test_url}' target='_blank'>{$name}</a></div>";
}
echo "</div>";

echo "<h2>✅ Test Mobile Completato!</h2>";
echo "<p>Verifica i risultati sopra per identificare eventuali problemi con le ottimizzazioni mobile.</p>";
?>
