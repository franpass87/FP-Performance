<?php
/**
 * EMERGENCY SCRIPT - Disabilita tutte le ottimizzazioni FP Performance
 * 
 * USO: Carica questo file direttamente nel browser o via WP-CLI
 * URL: https://tuosito.com/wp-content/plugins/FP-Performance/EMERGENCY-DISABLE-ALL.php
 * 
 * ATTENZIONE: Questo script disabilita TUTTE le ottimizzazioni per ripristinare il sito
 */

// Security check
if (!defined('ABSPATH')) {
    // Se chiamato direttamente, carica WordPress
    require_once('../../../wp-load.php');
}

// Verifica permessi
if (!current_user_can('manage_options')) {
    die('Accesso negato. Devi essere amministratore.');
}

// Disabilita tutte le ottimizzazioni attivate da "One-Click Safe Optimizations"
$options_to_disable = [
    // Cache
    'fp_ps_page_cache_settings' => ['enabled' => false],
    'fp_ps_browser_cache' => ['enabled' => false],
    'fp_ps_edge_cache_enabled' => false,
    'fp_ps_predictive_prefetch' => ['enabled' => false],
    
    // Compression
    'fp_ps_compression_enabled' => false,
    'fp_ps_compression_deflate_enabled' => false,
    'fp_ps_compression_brotli_enabled' => false,
    
    // Assets - DISABILITA SUBITO (più probabile causa errore)
    'fp_ps_assets' => [
        'minify_css' => false,
        'minify_js' => false,
        'minify_inline_css' => false,
        'remove_comments' => false,
        'optimize_google_fonts' => false,
        'remove_emojis' => false,
    ],
    
    // Media/Images
    'fp_ps_image_optimizer' => ['enabled' => false, 'lazy_loading' => false],
    'fp_ps_responsive_images' => ['enabled' => false, 'enable_lazy_loading' => false],
    
    // Critical Path (può causare problemi)
    'fp_ps_critical_path_optimization' => ['enabled' => false],
    
    // Mobile
    'fp_ps_mobile_optimizer' => ['enabled' => false],
    'fp_ps_touch_optimizer' => ['enabled' => false],
    'fp_ps_mobile_cache' => ['enabled' => false],
    
    // Database (solo frontend, non dovrebbe causare problemi ma disabilitiamo)
    'fp_ps_db' => ['enabled' => false],
];

$disabled = [];
$errors = [];

foreach ($options_to_disable as $option_name => $value) {
    try {
        if (is_array($value)) {
            $current = get_option($option_name, []);
            $updated = false;
            foreach ($value as $key => $val) {
                if (isset($current[$key]) && $current[$key] !== $val) {
                    $current[$key] = $val;
                    $updated = true;
                }
            }
            if ($updated) {
                update_option($option_name, $current, false);
                $disabled[] = $option_name;
            }
        } else {
            if (get_option($option_name) !== $value) {
                update_option($option_name, $value, false);
                $disabled[] = $option_name;
            }
        }
    } catch (\Exception $e) {
        $errors[] = "$option_name: " . $e->getMessage();
    }
}

// Pulisci cache
wp_cache_flush();

// Output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>FP Performance - Emergency Disable</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        ul { margin: 10px 0; padding-left: 30px; }
    </style>
</head>
<body>
    <h1>🚨 FP Performance - Emergency Disable</h1>
    
    <?php if (!empty($disabled)): ?>
        <div class="success">
            <h2>✅ Ottimizzazioni Disabilitate</h2>
            <p>Le seguenti ottimizzazioni sono state disabilitate:</p>
            <ul>
                <?php foreach ($disabled as $opt): ?>
                    <li><?php echo esc_html($opt); ?></li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Cache pulita.</strong></p>
        </div>
    <?php else: ?>
        <div class="info">
            <p>Nessuna ottimizzazione da disabilitare (già disabilitate o non trovate).</p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <h2>⚠️ Errori</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="info">
        <h2>📋 Prossimi Passi</h2>
        <ol>
            <li><strong>Verifica il frontend</strong> - Il sito dovrebbe ora funzionare</li>
            <li><strong>Controlla i log</strong> - Vai in <code>wp-content/debug.log</code> per vedere l'errore originale</li>
            <li><strong>Riabilita gradualmente</strong> - Attiva le ottimizzazioni una alla volta per identificare il problema</li>
            <li><strong>Rimuovi questo file</strong> - Elimina <code>EMERGENCY-DISABLE-ALL.php</code> dopo l'uso</li>
        </ol>
    </div>
    
    <p><a href="<?php echo admin_url('admin.php?page=fp-performance-suite-overview'); ?>">← Torna a FP Performance</a></p>
</body>
</html>

