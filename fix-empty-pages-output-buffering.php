<?php
/**
 * Fix per le pagine vuote causate da output buffering
 * 
 * Questo script risolve il problema delle pagine WordPress vuote
 * causato da output buffering non bilanciato nel plugin FP-Performance
 */

// Verifica se siamo in WordPress
if (!defined('ABSPATH')) {
    die('Access denied');
}

// Funzione per diagnosticare l'output buffering
function fp_diagnose_output_buffering() {
    $level = ob_get_level();
    $contents = ob_get_contents();
    
    echo "🔍 DIAGNOSTICA OUTPUT BUFFERING:\n";
    echo "📊 Livello buffer attuale: $level\n";
    echo "📝 Contenuto buffer: " . (empty($contents) ? 'VUOTO' : 'PRESENTE') . "\n";
    
    if ($level > 0) {
        echo "⚠️  PROBLEMA: Buffer attivo che potrebbe causare pagine vuote!\n";
        return false;
    }
    
    return true;
}

// Funzione per pulire tutti i buffer
function fp_clean_all_buffers() {
    $cleaned = 0;
    while (ob_get_level() > 0) {
        ob_end_clean();
        $cleaned++;
    }
    
    echo "🧹 Buffer puliti: $cleaned\n";
    return $cleaned;
}

// Funzione per disabilitare temporaneamente la cache delle pagine
function fp_disable_page_cache_temporarily() {
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
    
    echo "🚫 Cache delle pagine disabilitata temporaneamente\n";
}

// Funzione per riabilitare la cache delle pagine
function fp_enable_page_cache() {
    update_option('fp_ps_page_cache_enabled', true);
    echo "✅ Cache delle pagine riabilitata\n";
}

// Hook per intercettare l'output buffering problematico
add_action('init', function() {
    // Solo se il plugin FP-Performance è attivo
    if (!class_exists('FP\PerfSuite\Services\Cache\PageCache')) {
        return;
    }
    
    // Disabilita l'output buffering automatico se causa problemi
    add_filter('fp_ps_should_start_buffering', function($should) {
        // Controlla se ci sono già buffer attivi
        if (ob_get_level() > 0) {
            return false;
        }
        
        // Controlla se siamo in admin
        if (is_admin()) {
            return false;
        }
        
        return $should;
    });
}, 1);

// Hook per pulire i buffer alla fine del rendering
add_action('wp_footer', function() {
    // Solo se ci sono buffer attivi
    if (ob_get_level() > 0) {
        // Pulisce tutti i buffer rimanenti
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
    }
}, 999);

// Hook per gestire errori di output buffering
add_action('wp_loaded', function() {
    // Controlla se ci sono buffer non bilanciati
    $level = ob_get_level();
    if ($level > 0) {
        error_log("FP-Performance: Buffer non bilanciato rilevato (livello: $level)");
        
        // Pulisce i buffer problematici
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
});

// Funzione di emergenza per ripristinare l'output
function fp_emergency_output_fix() {
    // Pulisce tutti i buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Disabilita la cache delle pagine
    fp_disable_page_cache_temporarily();
    
    // Forza il flush dell'output
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    
    echo "🚨 Fix di emergenza applicato!\n";
}

// Se siamo in modalità debug, mostra informazioni
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('wp_footer', function() {
        if (current_user_can('manage_options')) {
            echo "<!-- FP-Performance Debug: Buffer level = " . ob_get_level() . " -->";
        }
    });
}

echo "🔧 Fix per pagine vuote applicato!\n";
echo "📋 Funzioni disponibili:\n";
echo "   - fp_diagnose_output_buffering()\n";
echo "   - fp_clean_all_buffers()\n";
echo "   - fp_disable_page_cache_temporarily()\n";
echo "   - fp_enable_page_cache()\n";
echo "   - fp_emergency_output_fix()\n";
