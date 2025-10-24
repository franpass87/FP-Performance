<?php
/**
 * Debug Menu Visibility - FP Performance Suite
 * 
 * Questo script aiuta a diagnosticare perché il menu non è visibile
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
    <title>Debug Menu Visibility - FP Performance Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; margin: 10px 0; }
        .status { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status-ok { background: #d4edda; color: #155724; }
        .status-error { background: #f8d7da; color: #721c24; }
        .status-warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug Menu Visibility - FP Performance Suite</h1>
        
        <?php
        echo '<h2>🔍 Diagnosi Problema Menu</h2>';
        
        // 1. Verifica plugin attivo
        $plugin_active = is_plugin_active('fp-performance-suite/fp-performance-suite.php');
        echo '<p><strong>Plugin attivo:</strong> <span class="status ' . ($plugin_active ? 'status-ok' : 'status-error') . '">' . ($plugin_active ? 'SÌ' : 'NO') . '</span></p>';
        
        // 2. Verifica variabile globale
        global $fp_perf_suite_initialized;
        $global_initialized = isset($fp_perf_suite_initialized) && $fp_perf_suite_initialized;
        echo '<p><strong>Variabile globale inizializzata:</strong> <span class="status ' . ($global_initialized ? 'status-ok' : 'status-error') . '">' . ($global_initialized ? 'SÌ' : 'NO') . '</span></p>';
        
        // 3. Verifica se la classe Plugin esiste
        $plugin_class_exists = class_exists('FP\\PerfSuite\\Plugin');
        echo '<p><strong>Classe Plugin caricata:</strong> <span class="status ' . ($plugin_class_exists ? 'status-ok' : 'status-error') . '">' . ($plugin_class_exists ? 'SÌ' : 'NO') . '</span></p>';
        
        // 4. Verifica se il container esiste
        $container_exists = false;
        if ($plugin_class_exists) {
            try {
                $container = FP\PerfSuite\Plugin::container();
                $container_exists = $container !== null;
            } catch (Exception $e) {
                $container_exists = false;
            }
        }
        echo '<p><strong>Container inizializzato:</strong> <span class="status ' . ($container_exists ? 'status-ok' : 'status-error') . '">' . ($container_exists ? 'SÌ' : 'NO') . '</span></p>';
        
        // 5. Verifica se la classe Menu esiste
        $menu_class_exists = class_exists('FP\\PerfSuite\\Admin\\Menu');
        echo '<p><strong>Classe Menu caricata:</strong> <span class="status ' . ($menu_class_exists ? 'status-ok' : 'status-error') . '">' . ($menu_class_exists ? 'SÌ' : 'NO') . '</span></p>';
        
        // 6. Verifica hook admin_menu
        global $wp_filter;
        $admin_menu_hooks = isset($wp_filter['admin_menu']) ? $wp_filter['admin_menu'] : null;
        $fp_menu_hooks = 0;
        if ($admin_menu_hooks) {
            foreach ($admin_menu_hooks->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    if (is_array($callback['function']) && 
                        is_object($callback['function'][0]) && 
                        get_class($callback['function'][0]) === 'FP\\PerfSuite\\Admin\\Menu') {
                        $fp_menu_hooks++;
                    }
                }
            }
        }
        echo '<p><strong>Hook admin_menu FP Performance:</strong> ' . $fp_menu_hooks . '</p>';
        
        // 7. Verifica menu registrato in WordPress
        global $menu, $submenu;
        $fp_menu_found = false;
        $fp_submenu_count = 0;
        
        if (is_array($menu)) {
            foreach ($menu as $menu_item) {
                if (is_array($menu_item) && isset($menu_item[0]) && 
                    strpos($menu_item[0], 'FP Performance') !== false) {
                    $fp_menu_found = true;
                    break;
                }
            }
        }
        
        if (is_array($submenu) && isset($submenu['fp-performance-suite'])) {
            $fp_submenu_count = count($submenu['fp-performance-suite']);
        }
        
        echo '<p><strong>Menu FP Performance in WordPress:</strong> <span class="status ' . ($fp_menu_found ? 'status-ok' : 'status-error') . '">' . ($fp_menu_found ? 'TROVATO' : 'NON TROVATO') . '</span></p>';
        echo '<p><strong>Sottomenu FP Performance:</strong> ' . $fp_submenu_count . '</p>';
        
        // 8. Test di registrazione manuale
        echo '<h3>🧪 Test di Registrazione Manuale</h3>';
        
        if ($plugin_active && $plugin_class_exists) {
            echo '<div class="info">';
            echo '<h4>Test di Registrazione Menu</h4>';
            echo '<p>Provo a registrare manualmente il menu per test...</p>';
            
            try {
                // Prova a ottenere il container
                $container = FP\PerfSuite\Plugin::container();
                if ($container) {
                    // Prova a ottenere la classe Menu
                    $menu_class = $container->get('FP\\PerfSuite\\Admin\\Menu');
                    if ($menu_class) {
                        echo '<p>✅ Classe Menu ottenuta dal container</p>';
                        
                        // Prova a chiamare il metodo register
                        $menu_class->register();
                        echo '<p>✅ Metodo register() chiamato</p>';
                        
                        // Verifica se il menu è stato registrato
                        global $menu;
                        $fp_menu_found_after = false;
                        if (is_array($menu)) {
                            foreach ($menu as $menu_item) {
                                if (is_array($menu_item) && isset($menu_item[0]) && 
                                    strpos($menu_item[0], 'FP Performance') !== false) {
                                    $fp_menu_found_after = true;
                                    break;
                                }
                            }
                        }
                        
                        echo '<p><strong>Menu registrato dopo test:</strong> <span class="status ' . ($fp_menu_found_after ? 'status-ok' : 'status-error') . '">' . ($fp_menu_found_after ? 'SÌ' : 'NO') . '</span></p>';
                        
                    } else {
                        echo '<p>❌ Impossibile ottenere la classe Menu dal container</p>';
                    }
                } else {
                    echo '<p>❌ Container non disponibile</p>';
                }
            } catch (Exception $e) {
                echo '<p>❌ Errore durante il test: ' . esc_html($e->getMessage()) . '</p>';
            }
            echo '</div>';
        } else {
            echo '<div class="warning">';
            echo '<p>Plugin non attivo o classe non caricata. Impossibile eseguire il test.</p>';
            echo '</div>';
        }
        
        // 9. Soluzioni suggerite
        echo '<h3>💡 Soluzioni Suggerite</h3>';
        
        if (!$plugin_active) {
            echo '<div class="error">';
            echo '<h4>❌ Plugin Non Attivo</h4>';
            echo '<p>Il plugin non è attivo. Per risolvere:</p>';
            echo '<ol>';
            echo '<li>Vai su <strong>wp-admin/plugins.php</strong></li>';
            echo '<li>Attiva "FP Performance Suite"</li>';
            echo '<li>Ricarica la pagina</li>';
            echo '</ol>';
            echo '</div>';
        } elseif (!$plugin_class_exists) {
            echo '<div class="error">';
            echo '<h4>❌ Classe Plugin Non Caricata</h4>';
            echo '<p>La classe Plugin non è stata caricata. Possibili cause:</p>';
            echo '<ul>';
            echo '<li>Errore di sintassi nel file Plugin.php</li>';
            echo '<li>Autoloader non funzionante</li>';
            echo '<li>Conflitto con altri plugin</li>';
            echo '</ul>';
            echo '</div>';
        } elseif (!$container_exists) {
            echo '<div class="error">';
            echo '<h4>❌ Container Non Inizializzato</h4>';
            echo '<p>Il container di servizi non è stato inizializzato. Possibili cause:</p>';
            echo '<ul>';
            echo '<li>Errore durante l\'inizializzazione</li>';
            echo '<li>Servizi non registrati correttamente</li>';
            echo '<li>Conflitto con altri plugin</li>';
            echo '</ul>';
            echo '</div>';
        } elseif (!$fp_menu_found) {
            echo '<div class="warning">';
            echo '<h4>⚠️ Menu Non Registrato</h4>';
            echo '<p>Il menu non è stato registrato in WordPress. Possibili cause:</p>';
            echo '<ul>';
            echo '<li>Hook admin_menu non chiamato</li>';
            echo '<li>Controllo di doppia registrazione troppo restrittivo</li>';
            echo '<li>Errore durante la registrazione del menu</li>';
            echo '</ul>';
            echo '</div>';
        } else {
            echo '<div class="success">';
            echo '<h4>✅ Tutto Sembra OK</h4>';
            echo '<p>Il plugin e il menu sembrano funzionare correttamente.</p>';
            echo '<p>Se il menu non è visibile, potrebbe essere un problema di permessi o di cache.</p>';
            echo '</div>';
        }
        
        // 10. Informazioni di sistema
        echo '<h3>📊 Informazioni Sistema</h3>';
        echo '<p><strong>Versione WordPress:</strong> ' . get_bloginfo('version') . '</p>';
        echo '<p><strong>Versione PHP:</strong> ' . PHP_VERSION . '</p>';
        echo '<p><strong>Memoria disponibile:</strong> ' . ini_get('memory_limit') . '</p>';
        echo '<p><strong>Debug WordPress:</strong> ' . (defined('WP_DEBUG') && WP_DEBUG ? 'Abilitato' : 'Disabilitato') . '</p>';
        echo '<p><strong>Utente corrente:</strong> ' . wp_get_current_user()->user_login . '</p>';
        echo '<p><strong>Capability manage_options:</strong> ' . (current_user_can('manage_options') ? 'SÌ' : 'NO') . '</p>';
        echo '<p><strong>Timestamp debug:</strong> ' . date('Y-m-d H:i:s') . '</p>';
        ?>
        
        <hr>
        <p><small>Debug creato per diagnosticare il problema di visibilità del menu FP Performance Suite.</small></p>
    </div>
</body>
</html>
