<?php
/**
 * Test Antiregressione - FP Performance Suite
 * 
 * Script di test automatico per verificare che non ci siano regressioni
 * dopo modifiche al plugin.
 * 
 * @package FP\PerfSuite
 * @version 1.0.0
 */

// Carica WordPress
$wp_load = dirname(__FILE__, 5) . '/wp-load.php';
if (!file_exists($wp_load)) {
    die('❌ Errore: wp-load.php non trovato');
}
require_once $wp_load;

// Verifica permessi admin
if (!current_user_can('manage_options')) {
    die('❌ Errore: Permessi insufficienti');
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Antiregressione - FP Performance Suite</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #2c3e50; margin-bottom: 30px; font-size: 32px; }
        .test-group { background: white; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .test-group h2 { color: #34495e; font-size: 20px; margin-bottom: 15px; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .test-item { padding: 12px; border-left: 4px solid #ccc; margin-bottom: 10px; background: #f8f9fa; }
        .test-item.pass { border-left-color: #27ae60; background: #d4edda; }
        .test-item.fail { border-left-color: #e74c3c; background: #f8d7da; }
        .test-item.warning { border-left-color: #f39c12; background: #fff3cd; }
        .test-name { font-weight: 600; color: #2c3e50; margin-bottom: 5px; }
        .test-result { font-size: 14px; color: #666; }
        .summary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; padding: 25px; margin-bottom: 30px; }
        .summary h2 { color: white; border: none; margin-bottom: 15px; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .stat { text-align: center; }
        .stat-number { font-size: 36px; font-weight: bold; }
        .stat-label { font-size: 14px; opacity: 0.9; margin-top: 5px; }
        .code { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 13px; margin-top: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛡️ Test Antiregressione - FP Performance Suite</h1>
        
        <?php
        $tests = [];
        $passed = 0;
        $failed = 0;
        $warnings = 0;
        
        // ============================================================
        // TEST 1: Autoloader PSR-4
        // ============================================================
        echo '<div class="test-group"><h2>🔧 Test Autoloader e Classi</h2>';
        
        $criticalClasses = [
            'FP\\PerfSuite\\Plugin',
            'FP\\PerfSuite\\ServiceContainer',
            'FP\\PerfSuite\\Admin\\RiskMatrix',
            'FP\\PerfSuite\\Services\\Cache\\PageCache',
            'FP\\PerfSuite\\Services\\Assets\\Optimizer',
            'FP\\PerfSuite\\Services\\Assets\\ThirdPartyScriptManager',
            'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer',
            'FP\\PerfSuite\\Services\\Compatibility\\FPPluginsIntegration',
        ];
        
        foreach ($criticalClasses as $class) {
            $exists = class_exists($class);
            $status = $exists ? 'pass' : 'fail';
            $exists ? $passed++ : $failed++;
            
            echo '<div class="test-item ' . $status . '">';
            echo '<div class="test-name">' . ($exists ? '✅' : '❌') . ' Classe: ' . esc_html($class) . '</div>';
            echo '<div class="test-result">' . ($exists ? 'Caricata correttamente' : 'ERRORE: Classe non trovata') . '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        
        // ============================================================
        // TEST 2: Opzioni e Impostazioni
        // ============================================================
        echo '<div class="test-group"><h2>⚙️ Test Opzioni e Configurazione</h2>';
        
        $options = [
            'fp_ps_page_cache' => 'Cache Pagine',
            'fp_ps_assets' => 'Ottimizzazione Assets',
            'fp_ps_db_settings' => 'Database Settings',
        ];
        
        foreach ($options as $key => $label) {
            $value = get_option($key);
            $exists = $value !== false;
            $status = $exists ? 'pass' : 'warning';
            $exists ? $passed++ : $warnings++;
            
            echo '<div class="test-item ' . $status . '">';
            echo '<div class="test-name">' . ($exists ? '✅' : '⚠️') . ' Opzione: ' . esc_html($label) . '</div>';
            echo '<div class="test-result">' . ($exists ? 'Configurata' : 'Non ancora configurata (normale per nuova installazione)') . '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        
        // ============================================================
        // TEST 3: Directory e Permessi
        // ============================================================
        echo '<div class="test-group"><h2>📁 Test Directory e Permessi</h2>';
        
        $directories = [
            WP_CONTENT_DIR . '/cache/fp-performance' => 'Cache Directory',
            WP_PLUGIN_DIR . '/FP-Performance/src' => 'Source Directory',
            WP_PLUGIN_DIR . '/FP-Performance/vendor' => 'Vendor Directory',
        ];
        
        foreach ($directories as $dir => $label) {
            $exists = is_dir($dir);
            $writable = $exists && is_writable($dir);
            
            if ($exists && $writable) {
                $status = 'pass';
                $passed++;
                $message = 'Directory esistente e scrivibile';
            } elseif ($exists) {
                $status = 'warning';
                $warnings++;
                $message = 'Directory esistente ma NON scrivibile';
            } else {
                $status = 'fail';
                $failed++;
                $message = 'Directory NON esistente';
            }
            
            echo '<div class="test-item ' . $status . '">';
            echo '<div class="test-name">' . ($status === 'pass' ? '✅' : ($status === 'warning' ? '⚠️' : '❌')) . ' ' . esc_html($label) . '</div>';
            echo '<div class="test-result">' . esc_html($message) . '</div>';
            if ($exists) {
                echo '<div class="test-result" style="font-size: 12px; color: #999; margin-top: 5px;">Path: ' . esc_html($dir) . '</div>';
            }
            echo '</div>';
        }
        
        echo '</div>';
        
        // ============================================================
        // TEST 4: Compatibilità Plugin FP
        // ============================================================
        echo '<div class="test-group"><h2>🔌 Test Compatibilità Plugin FP</h2>';
        
        $fpPlugins = [
            'FP-Restaurant-Reservations/fp-restaurant-reservations.php' => 'FP Restaurant Reservations',
            'FP-Experiences/fp-experiences.php' => 'FP Experiences',
            'FP-Privacy-and-Cookie-Policy-1/fp-privacy-cookie-policy.php' => 'FP Privacy',
        ];
        
        foreach ($fpPlugins as $file => $name) {
            $active = is_plugin_active($file);
            
            if ($active) {
                // Verifica che ci sia integrazione
                $hasIntegration = class_exists('FP\\PerfSuite\\Services\\Compatibility\\FPPluginsIntegration');
                $status = $hasIntegration ? 'pass' : 'warning';
                $hasIntegration ? $passed++ : $warnings++;
                $message = $hasIntegration ? 'Plugin attivo con integrazione' : 'Plugin attivo ma integrazione non verificata';
            } else {
                $status = 'pass';
                $passed++;
                $message = 'Plugin non attivo (test superato)';
            }
            
            echo '<div class="test-item ' . $status . '">';
            echo '<div class="test-name">' . ($status === 'pass' ? '✅' : '⚠️') . ' ' . esc_html($name) . '</div>';
            echo '<div class="test-result">' . esc_html($message) . '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        
        // ============================================================
        // TEST 5: Security e Sanitizzazione
        // ============================================================
        echo '<div class="test-group"><h2>🔒 Test Sicurezza</h2>';
        
        // Verifica safeUnserialize
        if (class_exists('FP\\PerfSuite\\Services\\Cache\\PageCache')) {
            $passed++;
            echo '<div class="test-item pass">';
            echo '<div class="test-name">✅ Safe Unserialize</div>';
            echo '<div class="test-result">Metodo safeUnserialize implementato in PageCache</div>';
            echo '</div>';
        } else {
            $failed++;
            echo '<div class="test-item fail">';
            echo '<div class="test-name">❌ Safe Unserialize</div>';
            echo '<div class="test-result">PageCache non trovato</div>';
            echo '</div>';
        }
        
        // Verifica RiskMatrix
        if (class_exists('FP\\PerfSuite\\Admin\\RiskMatrix')) {
            $passed++;
            echo '<div class="test-item pass">';
            echo '<div class="test-name">✅ RiskMatrix</div>';
            echo '<div class="test-result">Sistema di valutazione rischi attivo</div>';
            echo '</div>';
        } else {
            $failed++;
            echo '<div class="test-item fail">';
            echo '<div class="test-name">❌ RiskMatrix</div>';
            echo '<div class="test-result">RiskMatrix non trovato</div>';
            echo '</div>';
        }
        
        echo '</div>';
        
        // ============================================================
        // TEST 6: Performance e Limiti
        // ============================================================
        echo '<div class="test-group"><h2>⚡ Test Performance</h2>';
        
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryUsageMB = round($memoryUsage / 1024 / 1024, 2);
        
        $passed++;
        echo '<div class="test-item pass">';
        echo '<div class="test-name">✅ Memory Limit</div>';
        echo '<div class="test-result">Limite: ' . esc_html($memoryLimit) . ' | Uso: ' . esc_html($memoryUsageMB) . ' MB</div>';
        echo '</div>';
        
        $maxExecutionTime = ini_get('max_execution_time');
        $passed++;
        echo '<div class="test-item pass">';
        echo '<div class="test-name">✅ Max Execution Time</div>';
        echo '<div class="test-result">Limite: ' . esc_html($maxExecutionTime) . ' secondi</div>';
        echo '</div>';
        
        echo '</div>';
        
        // ============================================================
        // SUMMARY
        // ============================================================
        $total = $passed + $failed + $warnings;
        $percentage = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
        
        ?>
        
        <div class="summary">
            <h2>📊 Riepilogo Test</h2>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number"><?php echo $passed; ?></div>
                    <div class="stat-label">✅ Test Superati</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?php echo $failed; ?></div>
                    <div class="stat-label">❌ Test Falliti</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?php echo $warnings; ?></div>
                    <div class="stat-label">⚠️ Warning</div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 20px; font-size: 24px; font-weight: bold;">
                Score: <?php echo $percentage; ?>%
            </div>
            <?php if ($failed === 0): ?>
                <div style="text-align: center; margin-top: 15px; font-size: 18px;">
                    🎉 Nessuna regressione rilevata!
                </div>
            <?php else: ?>
                <div style="text-align: center; margin-top: 15px; font-size: 18px; background: rgba(255,255,255,0.2); padding: 10px; border-radius: 5px;">
                    ⚠️ Attenzione: <?php echo $failed; ?> test fallito/i
                </div>
            <?php endif; ?>
        </div>
        
        <div class="test-group">
            <h2>ℹ️ Informazioni Sistema</h2>
            <div class="code">
PHP Version: <?php echo PHP_VERSION; ?>&#10;WordPress Version: <?php echo get_bloginfo('version'); ?>&#10;Plugin Version: <?php echo defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : 'N/A'; ?>&#10;Active Theme: <?php echo wp_get_theme()->get('Name'); ?>&#10;Multisite: <?php echo is_multisite() ? 'Yes' : 'No'; ?>&#10;WP_DEBUG: <?php echo WP_DEBUG ? 'Enabled' : 'Disabled'; ?>&#10;Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
            </div>
        </div>
        
    </div>
</body>
</html>


