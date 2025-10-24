<?php
/**
 * Fix di emergenza per disabilitare l'output buffering
 * che causa pagine vuote
 */

// Verifica se siamo in WordPress
if (!defined('ABSPATH')) {
    die('Access denied');
}

// Disabilita immediatamente l'output buffering se attivo
if (ob_get_level() > 0) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}

// Hook per prevenire l'avvio di nuovi buffer
add_action('init', function() {
    // Disabilita la cache delle pagine se causa problemi
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
}, 1);

// Hook per pulire i buffer alla fine del rendering
add_action('wp_footer', function() {
    // Pulisce tutti i buffer rimanenti
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
}, 999);

// Hook per gestire errori di output buffering
add_action('shutdown', function() {
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

// Funzione per riabilitare la cache delle pagine in modo sicuro
function fp_enable_page_cache_safe() {
    // Prima pulisce tutti i buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Poi riabilita la cache
    update_option('fp_ps_page_cache_enabled', true);
    
    echo "✅ Cache delle pagine riabilitata in modo sicuro!\n";
}

// Funzione per diagnosticare lo stato
function fp_diagnose_buffering() {
    $level = ob_get_level();
    $contents = ob_get_contents();
    
    echo "🔍 DIAGNOSTICA OUTPUT BUFFERING:\n";
    echo "📊 Livello buffer: $level\n";
    echo "📝 Contenuto: " . (empty($contents) ? 'VUOTO' : 'PRESENTE') . "\n";
    
    if ($level > 0) {
        echo "⚠️  PROBLEMA: Buffer attivo!\n";
        return false;
    }
    
    return true;
}

echo "🚨 Fix di emergenza per output buffering applicato!\n";
echo "📋 Funzioni disponibili:\n";
echo "   - fp_enable_page_cache_safe()\n";
echo "   - fp_diagnose_buffering()\n";
