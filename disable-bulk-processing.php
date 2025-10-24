<?php
/**
 * Script per Disabilitare il Bulk Processing WebP
 * 
 * Questo script disabilita temporaneamente il bulk processing per prevenire ulteriori danni
 * 
 * @package FP\PerfSuite
 * @author Francesco Passeri
 */

// Verifica che sia eseguito da WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

// Solo per amministratori
if (!current_user_can('manage_options')) {
    wp_die('Accesso negato');
}

/**
 * Disabilita il bulk processing WebP
 */
function disable_webp_bulk_processing() {
    // 1. Disabilita il servizio WebP
    update_option('fp_ps_webp_enabled', false);
    
    // 2. Imposta keep_original su true per sicurezza
    $webp_settings = get_option('fp_ps_webp_settings', []);
    $webp_settings['keep_original'] = true;
    $webp_settings['enabled'] = false;
    update_option('fp_ps_webp_settings', $webp_settings);
    
    // 3. Pulisci la coda WebP
    delete_option('fp_ps_webp_queue');
    
    // 4. Cancella eventuali cron jobs WebP
    wp_clear_scheduled_hook('fp_ps_webp_process_batch');
    
    // 5. Disabilita temporaneamente il plugin
    $active_plugins = get_option('active_plugins', []);
    $plugin_file = 'fp-performance-suite/fp-performance-suite.php';
    
    if (in_array($plugin_file, $active_plugins)) {
        $key = array_search($plugin_file, $active_plugins);
        if ($key !== false) {
            unset($active_plugins[$key]);
            update_option('active_plugins', array_values($active_plugins));
        }
    }
    
    return true;
}

/**
 * Riabilita il plugin con impostazioni sicure
 */
function enable_plugin_safely() {
    // Riabilita il plugin
    $active_plugins = get_option('active_plugins', []);
    $plugin_file = 'fp-performance-suite/fp-performance-suite.php';
    
    if (!in_array($plugin_file, $active_plugins)) {
        $active_plugins[] = $plugin_file;
        update_option('active_plugins', $active_plugins);
    }
    
    // Imposta impostazioni sicure
    $webp_settings = [
        'enabled' => false, // Disabilitato per sicurezza
        'quality' => 82,
        'lossy' => true,
        'keep_original' => true, // IMPORTANTE: mantieni originali
        'auto_convert' => false,
        'bulk_convert' => false
    ];
    update_option('fp_ps_webp_settings', $webp_settings);
    
    return true;
}

// Esegui l'azione richiesta
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'disable':
            if (disable_webp_bulk_processing()) {
                echo "<div style='color: green; background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
                echo "✅ Bulk processing disabilitato e plugin disattivato per sicurezza.";
                echo "</div>";
            }
            break;
            
        case 'enable_safe':
            if (enable_plugin_safely()) {
                echo "<div style='color: green; background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
                echo "✅ Plugin riabilitato con impostazioni sicure (keep_original = true).";
                echo "</div>";
            }
            break;
    }
}

// Mostra interfaccia
?>
<div style="max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd;">
    <h1>🛡️ Controllo Bulk Processing WebP</h1>
    
    <div style="background: #f8d7da; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545;">
        <h3>🚨 Azione Immediata Richiesta</h3>
        <p>Il bulk processing WebP ha danneggiato le immagini. Esegui queste azioni nell'ordine:</p>
    </div>
    
    <div style="background: #fff3cd; padding: 15px; margin: 10px 0; border-left: 4px solid #ffc107;">
        <h3>📋 Piano di Recupero</h3>
        <ol>
            <li><strong>Disabilita il bulk processing</strong> (bottone rosso qui sotto)</li>
            <li><strong>Ripristina le immagini</strong> usando lo script di emergenza</li>
            <li><strong>Riabilita il plugin</strong> con impostazioni sicure</li>
            <li><strong>Testa la conversione</strong> su poche immagini</li>
        </ol>
    </div>
    
    <div style="text-align: center; margin: 20px 0;">
        <a href="?action=disable" 
           style="background: #dc3545; color: white; padding: 15px 30px; text-decoration: none; border-radius: 4px; margin: 5px; display: inline-block;"
           onclick="return confirm('Sei sicuro di voler disabilitare il bulk processing?')">
            🚫 Disabilita Bulk Processing
        </a>
        
        <a href="?action=enable_safe" 
           style="background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 4px; margin: 5px; display: inline-block;"
           onclick="return confirm('Riabilitare il plugin con impostazioni sicure?')">
            ✅ Riabilita Plugin (Sicuro)
        </a>
    </div>
    
    <div style="background: #d1ecf1; padding: 15px; margin: 10px 0; border-left: 4px solid #17a2b8;">
        <h3>🔧 Impostazioni Sicure</h3>
        <p>Quando riabiliti il plugin, verranno applicate queste impostazioni sicure:</p>
        <ul>
            <li><code>keep_original: true</code> - Mantiene sempre i file originali</li>
            <li><code>bulk_convert: false</code> - Disabilita la conversione bulk</li>
            <li><code>auto_convert: false</code> - Disabilita la conversione automatica</li>
        </ul>
    </div>
    
    <div style="background: #f8f9fa; padding: 15px; margin: 10px 0;">
        <h3>📚 Link Utili</h3>
        <ul>
            <li><a href="emergency-image-recovery.php">🔧 Script di Ripristino Immagini</a></li>
            <li><a href="<?php echo admin_url('admin.php?page=fp-performance-suite-media'); ?>">⚙️ Impostazioni Plugin</a></li>
            <li><a href="<?php echo admin_url('upload.php'); ?>">📁 Libreria Media</a></li>
        </ul>
    </div>
</div>
