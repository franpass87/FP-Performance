<?php
/**
 * FIX EMERGENZA - White Screen WordPress
 * 
 * Questo script risolve immediatamente il problema delle pagine admin vuote
 * causato dal plugin FP Performance Suite.
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

// Verifica permessi admin
if (!current_user_can('manage_options')) {
    die('Permessi insufficienti');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fix White Screen - FP Performance Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #005a87; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix White Screen - FP Performance Suite</h1>
        
        <?php
        $action = $_GET['action'] ?? 'check';
        
        if ($action === 'fix') {
            echo '<h2>🔧 Applicando Fix...</h2>';
            
            try {
                // 1. Disattiva il plugin
                deactivate_plugins('fp-performance-suite/fp-performance-suite.php');
                echo '<div class="success">✅ Plugin disattivato</div>';
                
                // 2. Crea backup del file principale
                $plugin_file = WP_PLUGIN_DIR . '/fp-performance-suite/fp-performance-suite.php';
                $backup_file = $plugin_file . '.backup-' . date('Y-m-d-H-i-s');
                
                if (file_exists($plugin_file)) {
                    copy($plugin_file, $backup_file);
                    echo '<div class="success">✅ Backup creato: ' . basename($backup_file) . '</div>';
                }
                
                // 3. Applica fix al file principale
                $fixed_content = '<?php
/**
 * Plugin Name: FP Performance Suite
 * Plugin URI: https://francescopasseri.com
 * Description: Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.
 * Version: 1.5.1
 * Author: Francesco Passeri
 * Author URI: https://francescopasseri.com
 * Text Domain: fp-performance-suite
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/franpass87/FP-Performance
 * Primary Branch: main
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

defined(\'ABSPATH\') || exit;

// Plugin principale FP Performance Suite - VERSIONE FIXATA WHITE SCREEN

/**
 * Verifica se il database WordPress è disponibile
 */
function fp_perf_suite_is_db_available(): bool {
    global $wpdb;
    
    if (!isset($wpdb) || !is_object($wpdb)) {
        return false;
    }
    
    if (!isset($wpdb->dbh)) {
        return false;
    }
    
    if (is_object($wpdb->dbh) && $wpdb->dbh instanceof \\mysqli) {
        return true;
    }
    
    try {
        $result = @$wpdb->query(\'SELECT 1\');
        return $result !== false;
    } catch (\\Throwable $e) {
        return false;
    }
}

/**
 * Log sicuro senza dipendenze dal database
 */
function fp_perf_suite_safe_log(string $message, string $level = \'ERROR\'): void {
    $timestamp = gmdate(\'Y-m-d H:i:s\');
    $logMessage = sprintf(
        \'[%s] [FP-PerfSuite] [%s] %s\',
        $timestamp,
        $level,
        $message
    );
    
    if (defined(\'WP_DEBUG\') && WP_DEBUG && defined(\'WP_DEBUG_LOG\') && WP_DEBUG_LOG) {
        error_log($logMessage);
    }
}

// Autoload con gestione errori migliorata
$autoload = __DIR__ . \'/vendor/autoload.php\';
if (is_readable($autoload)) {
    try {
        require_once $autoload;
    } catch (\\Throwable $e) {
        fp_perf_suite_safe_log(\'Errore caricamento autoloader Composer: \' . $e->getMessage());
    }
} else {
    // Autoloader personalizzato con gestione errori
    spl_autoload_register(static function ($class) {
        if (strpos($class, \'FP\\\\PerfSuite\\\\\') !== 0) {
            return;
        }
        
        try {
            $path = __DIR__ . \'/src/\' . str_replace([\'FP\\\\PerfSuite\\\\\', \'\\\\\'], [\'\', \'/\'], $class) . \'.php\';
            if (file_exists($path)) {
                require_once $path;
            }
        } catch (\\Throwable $e) {
            fp_perf_suite_safe_log(\'Errore autoloader per classe \' . $class . \': \' . $e->getMessage());
        }
    });
}

defined(\'FP_PERF_SUITE_VERSION\') || define(\'FP_PERF_SUITE_VERSION\', \'1.5.1\');
defined(\'FP_PERF_SUITE_DIR\') || define(\'FP_PERF_SUITE_DIR\', __DIR__);
defined(\'FP_PERF_SUITE_FILE\') || define(\'FP_PERF_SUITE_FILE\', __FILE__);

// Activation/Deactivation hooks con gestione errori migliorata
register_activation_hook(__FILE__, static function () {
    try {
        if (!class_exists(\'FP\\\\PerfSuite\\\\Plugin\')) {
            $pluginFile = __DIR__ . \'/src/Plugin.php\';
            if (!file_exists($pluginFile)) {
                wp_die(\'Errore critico: File Plugin.php non trovato in \' . esc_html($pluginFile));
            }
            require_once $pluginFile;
        }
        
        FP\\PerfSuite\\Plugin::onActivate();
    } catch (\\Throwable $e) {
        fp_perf_suite_safe_log(\'Errore attivazione: \' . $e->getMessage());
        wp_die(sprintf(
            \'<h1>Errore di Attivazione Plugin</h1><p><strong>Messaggio:</strong> %s</p><p><strong>File:</strong> %s:%d</p>\',
            esc_html($e->getMessage()),
            esc_html($e->getFile()),
            $e->getLine()
        ));
    }
});

register_deactivation_hook(__FILE__, static function () {
    try {
        if (!class_exists(\'FP\\\\PerfSuite\\\\Plugin\')) {
            $pluginFile = __DIR__ . \'/src/Plugin.php\';
            if (file_exists($pluginFile)) {
                require_once $pluginFile;
            }
        }
        
        if (class_exists(\'FP\\\\PerfSuite\\\\Plugin\')) {
            FP\\PerfSuite\\Plugin::onDeactivate();
        }
    } catch (\\Throwable $e) {
        fp_perf_suite_safe_log(\'Errore disattivazione: \' . $e->getMessage());
    }
});

// Sistema di inizializzazione ultra-sicuro
if (function_exists(\'add_action\')) {
    global $fp_perf_suite_initialized;
    if (!isset($fp_perf_suite_initialized)) {
        $fp_perf_suite_initialized = false;
    }
    
    // Inizializzazione con protezione massima
    add_action(\'init\', static function () {
        global $fp_perf_suite_initialized;
        
        // Prevenire inizializzazioni multiple
        if ($fp_perf_suite_initialized) {
            return;
        }
        
        // Inizializzazione sicura con try-catch
        try {
            fp_perf_suite_initialize_plugin_fixed();
        } catch (\\Throwable $e) {
            fp_perf_suite_safe_log(\'Errore inizializzazione plugin: \' . $e->getMessage());
            
            // Mostra avviso in admin se possibile
            if (is_admin() && current_user_can(\'manage_options\')) {
                add_action(\'admin_notices\', static function () use ($e) {
                    printf(
                        \'<div class="notice notice-error"><p><strong>FP Performance Suite:</strong> Errore di inizializzazione: %s</p></div>\',
                        esc_html($e->getMessage())
                    );
                });
            }
        }
    }, 1);
}

/**
 * Funzione di inizializzazione FIXATA del plugin
 */
function fp_perf_suite_initialize_plugin_fixed(): void {
    global $fp_perf_suite_initialized;
    
    // Prevenire inizializzazioni multiple
    if ($fp_perf_suite_initialized) {
        fp_perf_suite_safe_log(\'Plugin initialization prevented - already initialized\', \'DEBUG\');
        return;
    }
    
    // Verifica che il file Plugin.php esista
    $pluginFile = __DIR__ . \'/src/Plugin.php\';
    
    if (!file_exists($pluginFile)) {
        fp_perf_suite_safe_log(\'ERRORE CRITICO: File Plugin.php non trovato in \' . $pluginFile, \'ERROR\');
        
        add_action(\'admin_notices\', static function () use ($pluginFile) {
            if (current_user_can(\'manage_options\')) {
                printf(
                    \'<div class="notice notice-error"><p><strong>FP Performance Suite - Errore Critico:</strong> File Plugin.php non trovato.<br>Percorso cercato: <code>%s</code><br>Reinstalla il plugin completamente.</p></div>\',
                    esc_html($pluginFile)
                );
            }
        });
        
        return;
    }
    
    // Carica la classe Plugin con protezione da errori
    try {
        if (!class_exists(\'FP\\\\PerfSuite\\\\Plugin\')) {
            require_once $pluginFile;
        }
        
        if (!class_exists(\'FP\\\\PerfSuite\\\\Plugin\')) {
            throw new \\RuntimeException(\'Classe Plugin non trovata dopo require_once. Possibile errore di sintassi nel file.\');
        }
        
    } catch (\\Throwable $e) {
        fp_perf_suite_safe_log(\'Errore caricamento Plugin.php: \' . $e->getMessage() . \' in \' . $e->getFile() . \':\' . $e->getLine(), \'ERROR\');
        
        add_action(\'admin_notices\', static function () use ($e) {
            if (current_user_can(\'manage_options\')) {
                printf(
                    \'<div class="notice notice-error"><p><strong>FP Performance Suite - Errore di Caricamento:</strong><br>%s<br><small>File: %s:%d</small></p></div>\',
                    esc_html($e->getMessage()),
                    esc_html($e->getFile()),
                    $e->getLine()
                );
            }
        });
        
        return;
    }
    
    // Inizializza con protezione massima
    try {
        \\FP\\PerfSuite\\Plugin::init();
        fp_perf_suite_safe_log(\'Plugin initialized successfully\', \'DEBUG\');
        
        if (FP\\PerfSuite\\Plugin::isInitialized()) {
            $fp_perf_suite_initialized = true;
        }
        
    } catch (\\Throwable $e) {
        fp_perf_suite_safe_log(\'Plugin initialization error: \' . $e->getMessage() . \' in \' . $e->getFile() . \':\' . $e->getLine(), \'ERROR\');
        
        add_action(\'admin_notices\', static function () use ($e) {
            if (current_user_can(\'manage_options\')) {
                printf(
                    \'<div class="notice notice-error"><p><strong>FP Performance Suite:</strong> Errore di inizializzazione: %s<br><small>File: %s:%d</small></p></div>\',
                    esc_html($e->getMessage()),
                    esc_html($e->getFile()),
                    $e->getLine()
                );
            }
        });
    }
}

// Aggiungi avviso di versione fixata
add_action(\'admin_notices\', static function () {
    if (current_user_can(\'manage_options\')) {
        echo \'<div class="notice notice-success"><p><strong>FP Performance Suite:</strong> Versione fixata caricata - problemi di white screen risolti.</p></div>\';
    }
});
?>';
                
                file_put_contents($plugin_file, $fixed_content);
                echo '<div class="success">✅ File principale fixato</div>';
                
                // 4. Rimuovi file di debug problematici
                $debug_files = [
                    'debug-initialization-issues.php',
                    'fix-register-meta-errors.php',
                    'fix-fp-git-updater-deprecated.php'
                ];
                
                $plugin_dir = WP_PLUGIN_DIR . '/fp-performance-suite/';
                foreach ($debug_files as $file) {
                    $file_path = $plugin_dir . $file;
                    if (file_exists($file_path)) {
                        unlink($file_path);
                        echo '<div class="success">✅ Rimosso file problematico: ' . $file . '</div>';
                    }
                }
                
                // 5. Riattiva il plugin
                activate_plugin('fp-performance-suite/fp-performance-suite.php');
                echo '<div class="success">✅ Plugin riattivato</div>';
                
                echo '<div class="success"><h3>🎉 Fix Completato!</h3><p>Il plugin è stato fixato e riattivato. Le pagine admin dovrebbero ora funzionare normalmente.</p></div>';
                
            } catch (Exception $e) {
                echo '<div class="error"><h3>❌ Errore durante il fix</h3><p>' . esc_html($e->getMessage()) . '</p></div>';
            }
            
        } elseif ($action === 'disable') {
            echo '<h2>🚫 Disattivando Plugin...</h2>';
            
            try {
                deactivate_plugins('fp-performance-suite/fp-performance-suite.php');
                echo '<div class="success">✅ Plugin disattivato con successo</div>';
                echo '<div class="warning"><h3>⚠️ Plugin Disattivato</h3><p>Il plugin è stato disattivato. Le pagine admin dovrebbero ora funzionare normalmente.</p></div>';
            } catch (Exception $e) {
                echo '<div class="error"><h3>❌ Errore durante la disattivazione</h3><p>' . esc_html($e->getMessage()) . '</p></div>';
            }
            
        } else {
            echo '<h2>🔍 Diagnosi del Problema</h2>';
            
            // Verifica se il plugin è attivo
            $plugin_active = is_plugin_active('fp-performance-suite/fp-performance-suite.php');
            echo '<p><strong>Plugin attivo:</strong> ' . ($plugin_active ? '✅ Sì' : '❌ No') . '</p>';
            
            // Verifica file problematici
            $plugin_dir = WP_PLUGIN_DIR . '/fp-performance-suite/';
            $debug_files = [
                'debug-initialization-issues.php',
                'fix-register-meta-errors.php',
                'fix-fp-git-updater-deprecated.php'
            ];
            
            echo '<h3>File di debug problematici:</h3>';
            foreach ($debug_files as $file) {
                $file_path = $plugin_dir . $file;
                $exists = file_exists($file_path);
                echo '<p><strong>' . $file . ':</strong> ' . ($exists ? '❌ Presente (problematico)' : '✅ Non presente') . '</p>';
            }
            
            // Verifica errori PHP
            $error_log = ini_get('error_log');
            if ($error_log && file_exists($error_log)) {
                $recent_errors = file_get_contents($error_log);
                if (strpos($recent_errors, 'FP Performance Suite') !== false) {
                    echo '<div class="warning"><h3>⚠️ Errori Recenti Trovati</h3><p>Sono stati trovati errori recenti del plugin nei log. Controlla il file di log per maggiori dettagli.</p></div>';
                }
            }
            
            echo '<h3>🔧 Soluzioni Disponibili:</h3>';
            echo '<div class="code">';
            echo '<p><strong>Opzione 1 - Fix Automatico (Raccomandato):</strong></p>';
            echo '<a href="?action=fix" class="btn">🔧 Applica Fix Automatico</a>';
            echo '<p>Questo fixerà automaticamente il plugin rimuovendo i file problematici e applicando le correzioni necessarie.</p>';
            echo '</div>';
            
            echo '<div class="code">';
            echo '<p><strong>Opzione 2 - Disattiva Plugin:</strong></p>';
            echo '<a href="?action=disable" class="btn btn-danger">🚫 Disattiva Plugin</a>';
            echo '<p>Disattiva completamente il plugin per risolvere immediatamente il problema.</p>';
            echo '</div>';
        }
        ?>
        
        <hr>
        <h3>📋 Informazioni Aggiuntive</h3>
        <p><strong>Versione WordPress:</strong> <?php echo get_bloginfo('version'); ?></p>
        <p><strong>Versione PHP:</strong> <?php echo PHP_VERSION; ?></p>
        <p><strong>Memoria disponibile:</strong> <?php echo ini_get('memory_limit'); ?></p>
        <p><strong>Debug WordPress:</strong> <?php echo defined('WP_DEBUG') && WP_DEBUG ? 'Abilitato' : 'Disabilitato'; ?></p>
        
        <hr>
        <p><small>Script di fix creato per risolvere il problema delle pagine admin vuote causato dal plugin FP Performance Suite.</small></p>
    </div>
</body>
</html>
