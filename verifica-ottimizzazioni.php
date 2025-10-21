<?php
/**
 * Script per verificare le ottimizzazioni attive
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    // Simula WordPress per test
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Funzione per verificare le ottimizzazioni
function verifica_ottimizzazioni_main_thread() {
    echo "🔍 Verifica Ottimizzazioni Main-Thread Work\n";
    echo "==========================================\n\n";
    
    // 1. Script Optimization
    $scriptOpt = get_option('fp_ps_script_optimization', []);
    echo "📊 Script Optimization:\n";
    echo "   - Enabled: " . ($scriptOpt['enabled'] ?? 'false') . "\n";
    echo "   - Defer JS: " . ($scriptOpt['defer_js'] ?? 'false') . "\n";
    echo "   - Async JS: " . ($scriptOpt['async_js'] ?? 'false') . "\n";
    echo "   - Remove Emoji: " . ($scriptOpt['remove_emoji_scripts'] ?? 'false') . "\n\n";
    
    // 2. Third-Party Scripts
    $thirdParty = get_option('fp_ps_third_party_scripts', []);
    echo "📊 Third-Party Scripts:\n";
    echo "   - Enabled: " . ($thirdParty['enabled'] ?? 'false') . "\n";
    echo "   - Delay All: " . ($thirdParty['delay_all'] ?? 'false') . "\n";
    echo "   - Delay Timeout: " . ($thirdParty['delay_timeout'] ?? 'N/A') . "ms\n";
    echo "   - Load On: " . ($thirdParty['load_on'] ?? 'N/A') . "\n\n";
    
    // 3. jQuery Optimization
    $jqueryOpt = get_option('fp_ps_jquery_optimization', []);
    echo "📊 jQuery Optimization:\n";
    echo "   - Enabled: " . ($jqueryOpt['enabled'] ?? 'false') . "\n";
    echo "   - Batch Operations: " . ($jqueryOpt['batch_operations'] ?? 'false') . "\n";
    echo "   - Cache Selectors: " . ($jqueryOpt['cache_selectors'] ?? 'false') . "\n";
    echo "   - Prevent Reflows: " . ($jqueryOpt['prevent_reflows'] ?? 'false') . "\n\n";
    
    // 4. DOM Reflow Optimization
    $domReflow = get_option('fp_ps_dom_reflow_optimization', []);
    echo "📊 DOM Reflow Optimization:\n";
    echo "   - Enabled: " . ($domReflow['enabled'] ?? 'false') . "\n";
    echo "   - Batch Updates: " . ($domReflow['batch_updates'] ?? 'false') . "\n";
    echo "   - Defer Queries: " . ($domReflow['defer_queries'] ?? 'false') . "\n\n";
    
    // 5. Render Blocking Optimization
    $renderBlocking = get_option('fp_ps_render_blocking_optimization', []);
    echo "📊 Render Blocking Optimization:\n";
    echo "   - Enabled: " . ($renderBlocking['enabled'] ?? 'false') . "\n";
    echo "   - Optimize Fonts: " . ($renderBlocking['optimize_fonts'] ?? 'false') . "\n";
    echo "   - Defer CSS: " . ($renderBlocking['defer_css'] ?? 'false') . "\n\n";
    
    // 6. Font Optimization
    $fontOpt = get_option('fp_ps_font_optimization', []);
    echo "📊 Font Optimization:\n";
    echo "   - Enabled: " . ($fontOpt['enabled'] ?? 'false') . "\n";
    echo "   - Preload Critical: " . ($fontOpt['preload_critical_fonts'] ?? 'false') . "\n";
    echo "   - Font Display Swap: " . ($fontOpt['font_display_swap'] ?? 'false') . "\n\n";
    
    // Calcola punteggio
    $score = 0;
    $total = 0;
    
    if ($scriptOpt['enabled'] ?? false) $score++;
    $total++;
    
    if ($thirdParty['enabled'] ?? false) $score++;
    $total++;
    
    if ($jqueryOpt['enabled'] ?? false) $score++;
    $total++;
    
    if ($domReflow['enabled'] ?? false) $score++;
    $total++;
    
    if ($renderBlocking['enabled'] ?? false) $score++;
    $total++;
    
    if ($fontOpt['enabled'] ?? false) $score++;
    $total++;
    
    $percentage = ($score / $total) * 100;
    
    echo "🎯 Punteggio Ottimizzazione: {$score}/{$total} ({$percentage}%)\n";
    
    if ($percentage >= 80) {
        echo "✅ Ottimizzazioni ECCELLENTI! Main-thread work dovrebbe essere ridotto del 60-70%\n";
    } elseif ($percentage >= 60) {
        echo "⚠️ Ottimizzazioni BUONE, ma puoi migliorare ulteriormente\n";
    } else {
        echo "❌ Ottimizzazioni INSUFFICIENTI. Attiva più funzionalità per ridurre il main-thread work\n";
    }
}

// Esegui verifica
verifica_ottimizzazioni_main_thread();
