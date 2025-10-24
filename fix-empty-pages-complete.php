<?php
/**
 * Fix completo per le pagine vuote
 * 
 * Questo script risolve il problema delle pagine WordPress vuote
 * causato da conflitti nell'output buffering
 */

// Verifica se siamo in WordPress
if (!defined('ABSPATH')) {
    die('Access denied');
}

// 1. DISABILITA IMMEDIATAMENTE L'OUTPUT BUFFERING SE ATTIVO
if (ob_get_level() > 0) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    error_log('FP-Performance: Buffer pulito all\'avvio');
}

// 2. DISABILITA LA CACHE DELLE PAGINE TEMPORANEAMENTE
add_action('init', function() {
    // Disabilita la cache delle pagine
    update_option('fp_ps_page_cache_enabled', false);
    
    // Pulisce la cache esistente
    $cache_dir = WP_CONTENT_DIR . '/cache/fp-performance/page-cache';
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    error_log('FP-Performance: Cache delle pagine disabilitata');
}, 1);

// 3. HOOK PER PREVENIRE L'AVVIO DI NUOVI BUFFER
add_action('wp_loaded', function() {
    // Disabilita l'output buffering automatico
    add_filter('fp_ps_should_start_buffering', '__return_false');
    add_filter('fp_ps_page_cache_should_start_buffering', '__return_false');
    
    // Disabilita la minificazione HTML se causa problemi
    add_filter('fp_ps_should_minify_html', '__return_false');
    
    error_log('FP-Performance: Output buffering disabilitato');
});

// 4. HOOK PER PULIRE I BUFFER ALLA FINE DEL RENDERING
add_action('wp_footer', function() {
    // Pulisce tutti i buffer rimanenti
    $level = ob_get_level();
    if ($level > 0) {
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        error_log("FP-Performance: Buffer pulito nel footer (livello: $level)");
    }
}, 999);

// 5. HOOK PER GESTIRE ERRORI DI OUTPUT BUFFERING
add_action('shutdown', function() {
    // Controlla se ci sono buffer non bilanciati
    $level = ob_get_level();
    if ($level > 0) {
        error_log("FP-Performance: Buffer non bilanciato rilevato alla fine (livello: $level)");
        
        // Pulisce i buffer problematici
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
});

// 6. FUNZIONE PER RIABILITARE LA CACHE IN MODO SICURO
function fp_enable_page_cache_safe() {
    // Prima pulisce tutti i buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Poi riabilita la cache
    update_option('fp_ps_page_cache_enabled', true);
    
    // Rimuove i filtri che disabilitano la cache
    remove_filter('fp_ps_should_start_buffering', '__return_false');
    remove_filter('fp_ps_page_cache_should_start_buffering', '__return_false');
    
    echo "✅ Cache delle pagine riabilitata in modo sicuro!\n";
}

// 7. FUNZIONE PER DIAGNOSTICARE LO STATO
function fp_diagnose_empty_pages() {
    $level = ob_get_level();
    $contents = ob_get_contents();
    $cache_enabled = get_option('fp_ps_page_cache_enabled', false);
    
    echo "🔍 DIAGNOSTICA PAGINE VUOTE:\n";
    echo "📊 Livello buffer: $level\n";
    echo "📝 Contenuto buffer: " . (empty($contents) ? 'VUOTO' : 'PRESENTE') . "\n";
    echo "🗄️ Cache abilitata: " . ($cache_enabled ? 'SI' : 'NO') . "\n";
    echo "🏠 Is admin: " . (is_admin() ? 'SI' : 'NO') . "\n";
    echo "🔄 Doing AJAX: " . (defined('DOING_AJAX') && DOING_AJAX ? 'SI' : 'NO') . "\n";
    
    if ($level > 0) {
        echo "⚠️  PROBLEMA: Buffer attivo che potrebbe causare pagine vuote!\n";
        return false;
    }
    
    return true;
}

// 8. HOOK PER AGGIUNGERE DEBUG INFO NEL FOOTER (solo per admin)
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('wp_footer', function() {
        if (current_user_can('manage_options')) {
            echo "<!-- FP-Performance Debug: Buffer level = " . ob_get_level() . " -->";
        }
    });
}

// 9. HOOK PER INTERCETTARE I METODI PROBLEMATICI DEL PLUGIN
add_action('wp_loaded', function() {
    // Override del comportamento di saveBuffer per evitare problemi
    add_filter('fp_ps_page_cache_save_buffer', function($should_save) {
        // Non salvare se ci sono problemi con il buffer
        if (ob_get_level() === 0) {
            return false;
        }
        
        // Non salvare se il contenuto è vuoto
        $contents = ob_get_contents();
        if (empty($contents)) {
            return false;
        }
        
        return $should_save;
    });
});

// 10. FUNZIONE PER DISABILITARE COMPLETAMENTE L'OTTIMIZZAZIONE
function fp_disable_all_optimizations() {
    // Disabilita tutte le ottimizzazioni che potrebbero causare problemi
    update_option('fp_ps_page_cache_enabled', false);
    update_option('fp_ps_minify_html_enabled', false);
    update_option('fp_ps_defer_js_enabled', false);
    update_option('fp_ps_remove_emojis_enabled', false);
    
    // Pulisce tutti i buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    echo "🚫 Tutte le ottimizzazioni disabilitate!\n";
}

echo "🔧 Fix completo per pagine vuote applicato!\n";
echo "📋 Funzioni disponibili:\n";
echo "   - fp_enable_page_cache_safe()\n";
echo "   - fp_diagnose_empty_pages()\n";
echo "   - fp_disable_all_optimizations()\n";
