<?php
/**
 * 🔧 INTEGRAZIONE STRUMENTI DI DEBUG NEL PLUGIN PRINCIPALE
 * 
 * Questo script integra tutti gli strumenti di debug nel plugin FP Performance Suite
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

// Integra gli strumenti di debug nel plugin principale
add_action('plugins_loaded', function() {
    // Carica gli strumenti di debug
    $debug_files = [
        'debug-plugin-complete.php',
        'test-plugin-features.php',
        'debug-quick.php',
        'install-debug-tools.php'
    ];
    
    foreach ($debug_files as $file) {
        $file_path = plugin_dir_path(__FILE__) . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
});

// Aggiungi menu di debug al plugin principale
add_action('admin_menu', function() {
    // Aggiungi menu di debug se non esiste già
    if (!menu_page_url('fp-performance-debug', false)) {
        add_submenu_page(
            'fp-performance-suite',
            '🔧 Debug Tool',
            '🔧 Debug',
            'manage_options',
            'fp-performance-debug',
            function() {
                echo '<div class="wrap">';
                echo '<h1>🔧 FP Performance Suite - Debug Tool</h1>';
                echo '<p>Gli strumenti di debug sono stati integrati nel plugin!</p>';
                echo '<p><a href="' . admin_url('admin.php?page=fp-performance-debug') . '" class="button button-primary">Vai al Debug Tool</a></p>';
                echo '</div>';
            }
        );
    }
    
    // Aggiungi menu di test se non esiste già
    if (!menu_page_url('fp-performance-test', false)) {
        add_submenu_page(
            'fp-performance-suite',
            '🧪 Test Funzionalità',
            '🧪 Test',
            'manage_options',
            'fp-performance-test',
            function() {
                echo '<div class="wrap">';
                echo '<h1>🧪 FP Performance Suite - Test Funzionalità</h1>';
                echo '<p>Gli strumenti di test sono stati integrati nel plugin!</p>';
                echo '<p><a href="' . admin_url('admin.php?page=fp-performance-test') . '" class="button button-primary">Vai ai Test</a></p>';
                echo '</div>';
            }
        );
    }
});

// Aggiungi notifica di debug attivo
add_action('admin_notices', function() {
    if (isset($_GET['debug']) && $_GET['debug'] == '1') {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>🔧 Debug Mode Attivo:</strong> Gli strumenti di debug sono attivi. Vedi il panel in basso alla pagina.</p>';
        echo '</div>';
    }
});

// Aggiungi script per il debug panel
add_action('admin_footer', function() {
    if (isset($_GET['debug']) && $_GET['debug'] == '1') {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Aggiungi stili per il debug panel
            $('<style>')
                .prop('type', 'text/css')
                .html(`
                    #fp-debug-panel {
                        font-family: 'Courier New', monospace !important;
                        font-size: 12px !important;
                        line-height: 1.4 !important;
                    }
                    #fp-debug-panel h4 {
                        color: #00ff00 !important;
                        margin: 0 0 10px 0 !important;
                    }
                    #fp-debug-panel p {
                        margin: 2px 0 !important;
                    }
                    #fp-debug-panel strong {
                        color: #ffff00 !important;
                    }
                `)
                .appendTo('head');
            
            // Auto-refresh del debug panel ogni 5 secondi
            setInterval(function() {
                if ($('#fp-debug-panel').length > 0) {
                    location.reload();
                }
            }, 5000);
        });
        </script>
        <?php
    }
});

// Aggiungi shortcut per debug rapido
add_action('admin_bar_menu', function($wp_admin_bar) {
    if (current_user_can('manage_options')) {
        $current_url = $_SERVER['REQUEST_URI'];
        $debug_url = $current_url . (strpos($current_url, '?') !== false ? '&' : '?') . 'debug=1';
        
        $wp_admin_bar->add_node([
            'id' => 'fp-debug-toggle',
            'title' => '🔧 Debug',
            'href' => $debug_url,
            'meta' => [
                'title' => 'Attiva/Disattiva Debug Mode'
            ]
        ]);
    }
}, 999);

// Aggiungi informazioni di debug nella pagina del plugin
add_action('admin_footer', function() {
    $screen = get_current_screen();
    if ($screen && strpos($screen->id, 'fp-performance') !== false) {
        ?>
        <div id="fp-debug-info" style="position: fixed; top: 50px; right: 20px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; z-index: 9999; font-size: 11px; display: none;">
            <strong>🔧 FP Performance Debug</strong><br>
            <a href="<?php echo admin_url('admin.php?page=fp-performance-debug'); ?>" style="color: #00ff00;">Debug Tool</a> | 
            <a href="<?php echo admin_url('admin.php?page=fp-performance-test'); ?>" style="color: #00ff00;">Test Tool</a> | 
            <a href="<?php echo $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?') . 'debug=1'; ?>" style="color: #00ff00;">Quick Debug</a>
        </div>
        <script>
        jQuery(document).ready(function($) {
            // Mostra info debug su hover del menu
            $('.wp-menu-name:contains("FP Performance")').hover(
                function() {
                    $('#fp-debug-info').show();
                },
                function() {
                    $('#fp-debug-info').hide();
                }
            );
        });
        </script>
        <?php
    }
});

// Aggiungi log di debug automatico
add_action('init', function() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // Log delle azioni principali del plugin
        add_action('wp_loaded', function() {
            error_log('FP Performance Suite: Plugin loaded');
        });
        
        add_action('wp_enqueue_scripts', function() {
            error_log('FP Performance Suite: Scripts enqueued');
        });
        
        add_action('wp_head', function() {
            error_log('FP Performance Suite: Head output');
        });
        
        add_action('wp_footer', function() {
            error_log('FP Performance Suite: Footer output');
        });
    }
});

// Aggiungi test automatici per problemi comuni
add_action('admin_init', function() {
    if (isset($_GET['fp_auto_test']) && $_GET['fp_auto_test'] == '1') {
        // Test automatici per problemi comuni
        $this->run_common_tests();
    }
});

function run_common_tests() {
    $tests = [];
    
    // Test 1: Plugin attivo
    $tests[] = [
        'name' => 'Plugin Active',
        'result' => is_plugin_active('fp-performance-suite/fp-performance-suite.php'),
        'message' => 'Plugin FP Performance Suite è attivo'
    ];
    
    // Test 2: Classi principali
    $classes = [
        'FP\PerfSuite\Plugin',
        'FP\PerfSuite\ServiceContainer'
    ];
    
    foreach ($classes as $class) {
        $tests[] = [
            'name' => "Class {$class}",
            'result' => class_exists($class),
            'message' => "Classe {$class} disponibile"
        ];
    }
    
    // Test 3: Database connection
    global $wpdb;
    $tests[] = [
        'name' => 'Database Connection',
        'result' => $wpdb->db_connect(),
        'message' => 'Connessione database funzionante'
    ];
    
    // Mostra risultati
    echo '<div class="wrap">';
    echo '<h1>🧪 Test Automatici Comuni</h1>';
    
    foreach ($tests as $test) {
        $status = $test['result'] ? '✅' : '❌';
        $color = $test['result'] ? 'green' : 'red';
        echo "<p style='color: {$color};'>{$status} {$test['name']}: {$test['message']}</p>";
    }
    
    echo '</div>';
    exit;
}

// Aggiungi informazioni di sistema per debug
add_action('wp_dashboard_setup', function() {
    if (current_user_can('manage_options')) {
        wp_add_dashboard_widget(
            'fp_performance_debug_info',
            '🔧 FP Performance Debug Info',
            function() {
                echo '<p><strong>Debug Mode:</strong> ' . (defined('WP_DEBUG') && WP_DEBUG ? 'Attivo' : 'Disattivo') . '</p>';
                echo '<p><strong>Debug Log:</strong> ' . (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'Attivo' : 'Disattivo') . '</p>';
                echo '<p><strong>Plugin Active:</strong> ' . (is_plugin_active('fp-performance-suite/fp-performance-suite.php') ? 'Sì' : 'No') . '</p>';
                echo '<p><a href="' . admin_url('admin.php?page=fp-performance-debug') . '" class="button button-primary">Vai al Debug Tool</a></p>';
            }
        );
    }
});
