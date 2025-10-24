<?php
/**
 * Fix specifico per il problema dell'output buffering in PageCache
 * 
 * Questo script corregge il problema delle pagine vuote causato da
 * output buffering non bilanciato nel servizio PageCache
 */

// Verifica se siamo in WordPress
if (!defined('ABSPATH')) {
    die('Access denied');
}

// Hook per intercettare e correggere l'output buffering del PageCache
add_action('init', function() {
    // Solo se il plugin FP-Performance è attivo
    if (!class_exists('FP\PerfSuite\Services\Cache\PageCache')) {
        return;
    }
    
    // Override del metodo startBuffering per aggiungere controlli di sicurezza
    add_filter('fp_ps_page_cache_should_start_buffering', function($should) {
        // Non avviare buffer se siamo in admin
        if (is_admin()) {
            return false;
        }
        
        // Non avviare buffer se ci sono già buffer attivi
        if (ob_get_level() > 0) {
            return false;
        }
        
        // Non avviare buffer su richieste AJAX
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return false;
        }
        
        // Non avviare buffer su richieste REST
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return false;
        }
        
        return $should;
    });
    
    // Hook per pulire i buffer alla fine del rendering
    add_action('wp_footer', function() {
        // Solo se ci sono buffer attivi e siamo nel frontend
        if (!is_admin() && ob_get_level() > 0) {
            // Pulisce tutti i buffer rimanenti
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
        }
    }, 999);
    
    // Hook per gestire errori di output buffering
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
    
}, 1);

// Funzione per disabilitare temporaneamente la cache delle pagine
function fp_disable_page_cache_emergency() {
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
    
    // Pulisce tutti i buffer attivi
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    echo "🚨 Cache delle pagine disabilitata e buffer puliti!\n";
}

// Funzione per riabilitare la cache delle pagine
function fp_enable_page_cache_safe() {
    // Prima pulisce tutti i buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Poi riabilita la cache
    update_option('fp_ps_page_cache_enabled', true);
    
    echo "✅ Cache delle pagine riabilitata in modo sicuro!\n";
}

// Funzione per diagnosticare lo stato dell'output buffering
function fp_diagnose_pagecache_buffering() {
    $level = ob_get_level();
    $contents = ob_get_contents();
    
    echo "🔍 DIAGNOSTICA PAGECACHE BUFFERING:\n";
    echo "📊 Livello buffer attuale: $level\n";
    echo "📝 Contenuto buffer: " . (empty($contents) ? 'VUOTO' : 'PRESENTE') . "\n";
    echo "🏠 Is admin: " . (is_admin() ? 'SI' : 'NO') . "\n";
    echo "🔄 Doing AJAX: " . (defined('DOING_AJAX') && DOING_AJAX ? 'SI' : 'NO') . "\n";
    echo "🌐 REST Request: " . (defined('REST_REQUEST') && REST_REQUEST ? 'SI' : 'NO') . "\n";
    
    if ($level > 0) {
        echo "⚠️  PROBLEMA: Buffer attivo che potrebbe causare pagine vuote!\n";
        return false;
    }
    
    return true;
}

// Hook per aggiungere debug info nel footer (solo per admin)
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('wp_footer', function() {
        if (current_user_can('manage_options')) {
            echo "<!-- FP-Performance PageCache Debug: Buffer level = " . ob_get_level() . " -->";
        }
    });
}

// Hook per intercettare il metodo saveBuffer del PageCache
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

echo "🔧 Fix PageCache output buffering applicato!\n";
echo "📋 Funzioni disponibili:\n";
echo "   - fp_disable_page_cache_emergency()\n";
echo "   - fp_enable_page_cache_safe()\n";
echo "   - fp_diagnose_pagecache_buffering()\n";
