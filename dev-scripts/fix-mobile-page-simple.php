<?php
/**
 * Fix Semplice: Pagina Mobile Vuota
 * 
 * Questo script risolve il problema della pagina mobile vuota
 * inizializzando le opzioni mancanti.
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

echo "🔧 FIX PAGINA MOBILE VUOTA\n";
echo "==========================\n\n";

// Simuliamo l'ambiente WordPress per il test
if (!function_exists('get_option')) {
    echo "⚠️  Questo script deve essere eseguito in un ambiente WordPress.\n";
    echo "💡 Per risolvere il problema:\n\n";
    echo "1. Vai nel tuo sito WordPress\n";
    echo "2. Aggiungi questo codice temporaneamente in functions.php del tema:\n\n";
    
    $fix_code = <<<'PHP'
// Fix temporaneo per pagina mobile vuota
add_action('init', function() {
    // Inizializza opzioni mobile se mancanti
    if (!get_option('fp_ps_mobile_optimizer')) {
        update_option('fp_ps_mobile_optimizer', [
            'enabled' => true,
            'disable_animations' => false,
            'remove_unnecessary_scripts' => true,
            'optimize_touch_targets' => true,
            'enable_responsive_images' => true
        ], false);
    }
    
    if (!get_option('fp_ps_touch_optimizer')) {
        update_option('fp_ps_touch_optimizer', [
            'enabled' => true,
            'disable_hover_effects' => true,
            'improve_touch_targets' => true,
            'optimize_scroll' => true,
            'prevent_zoom' => true
        ], false);
    }
    
    if (!get_option('fp_ps_responsive_images')) {
        update_option('fp_ps_responsive_images', [
            'enabled' => true,
            'enable_lazy_loading' => true,
            'optimize_srcset' => true,
            'max_mobile_width' => 768,
            'max_content_image_width' => '100%'
        ], false);
    }
    
    if (!get_option('fp_ps_mobile_cache')) {
        update_option('fp_ps_mobile_cache', [
            'enabled' => true,
            'enable_mobile_cache_headers' => true,
            'enable_resource_caching' => true,
            'cache_mobile_css' => true,
            'cache_mobile_js' => true,
            'html_cache_duration' => 300,
            'css_cache_duration' => 3600,
            'js_cache_duration' => 3600
        ], false);
    }
    
    echo "✅ Opzioni mobile inizializzate!";
});
PHP;
    
    echo $fix_code;
    echo "\n\n3. Salva e ricarica la pagina\n";
    echo "4. Vai su 'FP Performance > Mobile' per verificare\n";
    echo "5. Rimuovi il codice da functions.php dopo aver verificato\n\n";
    
    echo "🔍 ALTERNATIVA: Verifica manualmente nel database\n";
    echo "Controlla se esistono queste opzioni:\n";
    echo "- fp_ps_mobile_optimizer\n";
    echo "- fp_ps_touch_optimizer\n";
    echo "- fp_ps_responsive_images\n";
    echo "- fp_ps_mobile_cache\n\n";
    
    echo "Se mancano, aggiungi manualmente con i valori di default.\n";
    
} else {
    // Se siamo in WordPress, eseguiamo il fix
    echo "🔨 Applicazione fix automatico...\n\n";
    
    $fixes_applied = 0;
    
    // Mobile Optimizer
    if (!get_option('fp_ps_mobile_optimizer')) {
        update_option('fp_ps_mobile_optimizer', [
            'enabled' => true,
            'disable_animations' => false,
            'remove_unnecessary_scripts' => true,
            'optimize_touch_targets' => true,
            'enable_responsive_images' => true
        ], false);
        echo "✅ fp_ps_mobile_optimizer inizializzata\n";
        $fixes_applied++;
    }
    
    // Touch Optimizer
    if (!get_option('fp_ps_touch_optimizer')) {
        update_option('fp_ps_touch_optimizer', [
            'enabled' => true,
            'disable_hover_effects' => true,
            'improve_touch_targets' => true,
            'optimize_scroll' => true,
            'prevent_zoom' => true
        ], false);
        echo "✅ fp_ps_touch_optimizer inizializzata\n";
        $fixes_applied++;
    }
    
    // Responsive Images
    if (!get_option('fp_ps_responsive_images')) {
        update_option('fp_ps_responsive_images', [
            'enabled' => true,
            'enable_lazy_loading' => true,
            'optimize_srcset' => true,
            'max_mobile_width' => 768,
            'max_content_image_width' => '100%'
        ], false);
        echo "✅ fp_ps_responsive_images inizializzata\n";
        $fixes_applied++;
    }
    
    // Mobile Cache
    if (!get_option('fp_ps_mobile_cache')) {
        update_option('fp_ps_mobile_cache', [
            'enabled' => true,
            'enable_mobile_cache_headers' => true,
            'enable_resource_caching' => true,
            'cache_mobile_css' => true,
            'cache_mobile_js' => true,
            'html_cache_duration' => 300,
            'css_cache_duration' => 3600,
            'js_cache_duration' => 3600
        ], false);
        echo "✅ fp_ps_mobile_cache inizializzata\n";
        $fixes_applied++;
    }
    
    if ($fixes_applied > 0) {
        echo "\n🎉 SUCCESSO! $fixes_applied opzioni inizializzate.\n";
        echo "💡 La pagina mobile dovrebbe ora funzionare correttamente.\n";
    } else {
        echo "\n✅ Tutte le opzioni mobile sono già presenti.\n";
        echo "💡 Se la pagina è ancora vuota, controlla:\n";
        echo "1. Se il plugin è attivo\n";
        echo "2. Se ci sono errori JavaScript nella console\n";
        echo "3. Se i servizi mobile sono abilitati nelle impostazioni\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "FIX COMPLETATO\n";
echo str_repeat("=", 50) . "\n";
