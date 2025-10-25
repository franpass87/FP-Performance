<?php
/**
 * Test Pagine Admin Plugin FP Performance Suite
 * Script per verificare accessibilità e funzionalità delle pagine admin
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

echo "<h1>🎛️ Test Pagine Admin FP Performance Suite</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .test-item { margin: 10px 0; padding: 5px; }
    .admin-link { background: #0073aa; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px; display: inline-block; margin: 5px; }
    .admin-link:hover { background: #005a87; }
</style>";

// 1. VERIFICA MENU ADMIN
echo "<div class='section'>";
echo "<h2>📋 1. Verifica Menu Admin</h2>";

// Verifica se il menu è stato aggiunto
global $menu, $submenu;

$fp_menu_found = false;
$fp_submenu_found = false;

if (isset($menu)) {
    foreach ($menu as $item) {
        if (is_array($item) && isset($item[0]) && strpos($item[0], 'FP Performance') !== false) {
            $fp_menu_found = true;
            echo "<div class='test-item success'>✅ Menu principale FP Performance trovato</div>";
            break;
        }
    }
}

if (!$fp_menu_found) {
    echo "<div class='test-item error'>❌ Menu principale FP Performance NON trovato</div>";
}

// Verifica submenu
if (isset($submenu['fp-performance'])) {
    $fp_submenu_found = true;
    echo "<div class='test-item success'>✅ Submenu FP Performance trovato</div>";
    
    $expected_submenus = [
        'Dashboard',
        'Cache',
        'Database', 
        'Assets',
        'Mobile',
        'Security',
        'Settings'
    ];
    
    foreach ($expected_submenus as $submenu_name) {
        $found = false;
        foreach ($submenu['fp-performance'] as $sub_item) {
            if (strpos($sub_item[0], $submenu_name) !== false) {
                $found = true;
                break;
            }
        }
        
        if ($found) {
            echo "<div class='test-item success'>✅ Submenu '{$submenu_name}' trovato</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Submenu '{$submenu_name}' NON trovato</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Submenu FP Performance NON trovato</div>";
}
echo "</div>";

// 2. VERIFICA PAGINE ADMIN SPECIFICHE
echo "<div class='section'>";
echo "<h2>📄 2. Verifica Pagine Admin Specifiche</h2>";

$admin_pages = [
    'fp-performance' => 'Dashboard',
    'fp-performance-cache' => 'Cache',
    'fp-performance-database' => 'Database',
    'fp-performance-assets' => 'Assets',
    'fp-performance-mobile' => 'Mobile',
    'fp-performance-security' => 'Security',
    'fp-performance-settings' => 'Settings'
];

foreach ($admin_pages as $page_slug => $page_name) {
    // Verifica se la pagina è registrata
    if (function_exists('fp_performance_admin_page_' . str_replace('fp-performance-', '', $page_slug))) {
        echo "<div class='test-item success'>✅ Pagina '{$page_name}' registrata</div>";
    } else {
        echo "<div class='test-item warning'>⚠️ Pagina '{$page_name}' non registrata</div>";
    }
    
    // Crea link per testare la pagina
    $admin_url = admin_url("admin.php?page={$page_slug}");
    echo "<div class='test-item info'>🔗 <a href='{$admin_url}' class='admin-link' target='_blank'>Testa {$page_name}</a></div>";
}
echo "</div>";

// 3. VERIFICA FUNZIONI ADMIN
echo "<div class='section'>";
echo "<h2>⚙️ 3. Verifica Funzioni Admin</h2>";

$admin_functions = [
    'fp_performance_admin_init' => 'Inizializzazione admin',
    'fp_performance_add_admin_menu' => 'Aggiunta menu admin',
    'fp_performance_admin_scripts' => 'Script admin',
    'fp_performance_admin_styles' => 'Stili admin',
    'fp_performance_save_options' => 'Salvataggio opzioni',
    'fp_performance_ajax_handler' => 'Gestione AJAX'
];

foreach ($admin_functions as $function => $description) {
    if (function_exists($function)) {
        echo "<div class='test-item success'>✅ {$description} disponibile</div>";
    } else {
        echo "<div class='test-item error'>❌ {$description} NON disponibile</div>";
    }
}
echo "</div>";

// 4. VERIFICA HOOK ADMIN
echo "<div class='section'>";
echo "<h2>🔗 4. Verifica Hook Admin</h2>";

$admin_hooks = [
    'admin_menu' => 'Menu admin',
    'admin_init' => 'Inizializzazione admin',
    'admin_enqueue_scripts' => 'Script admin',
    'wp_ajax_fp_performance_save' => 'AJAX salvataggio',
    'wp_ajax_fp_performance_test' => 'AJAX test',
    'admin_notices' => 'Notifiche admin'
];

foreach ($admin_hooks as $hook => $description) {
    if (has_action($hook)) {
        echo "<div class='test-item success'>✅ Hook '{$hook}' registrato</div>";
    } else {
        echo "<div class='test-item warning'>⚠️ Hook '{$hook}' non registrato</div>";
    }
}
echo "</div>";

// 5. VERIFICA PERMESSI UTENTE
echo "<div class='section'>";
echo "<h2>👤 5. Verifica Permessi Utente</h2>";

$current_user = wp_get_current_user();
echo "<div class='test-item info'>📋 Utente corrente: {$current_user->user_login}</div>";

$required_capabilities = [
    'manage_options' => 'Gestione opzioni',
    'edit_posts' => 'Modifica post',
    'activate_plugins' => 'Attivazione plugin'
];

foreach ($required_capabilities as $capability => $description) {
    if (current_user_can($capability)) {
        echo "<div class='test-item success'>✅ {$description} consentita</div>";
    } else {
        echo "<div class='test-item error'>❌ {$description} NON consentita</div>";
    }
}
echo "</div>";

// 6. TEST ACCESSO PAGINE
echo "<div class='section'>";
echo "<h2>🌐 6. Test Accesso Pagine</h2>";

echo "<div class='test-item info'>📋 Clicca sui link qui sotto per testare l'accesso alle pagine:</div>";

$test_pages = [
    'Dashboard' => 'fp-performance',
    'Cache' => 'fp-performance-cache',
    'Database' => 'fp-performance-database',
    'Assets' => 'fp-performance-assets',
    'Mobile' => 'fp-performance-mobile',
    'Security' => 'fp-performance-security',
    'Settings' => 'fp-performance-settings'
];

foreach ($test_pages as $name => $slug) {
    $url = admin_url("admin.php?page={$slug}");
    echo "<div class='test-item info'>🔗 <a href='{$url}' class='admin-link' target='_blank'>Testa {$name}</a></div>";
}
echo "</div>";

// 7. VERIFICA CONTENUTO PAGINE
echo "<div class='section'>";
echo "<h2>📝 7. Verifica Contenuto Pagine</h2>";

// Simula caricamento delle pagine
$page_functions = [
    'fp_performance_dashboard_page' => 'Dashboard',
    'fp_performance_cache_page' => 'Cache',
    'fp_performance_database_page' => 'Database',
    'fp_performance_assets_page' => 'Assets',
    'fp_performance_mobile_page' => 'Mobile',
    'fp_performance_security_page' => 'Security',
    'fp_performance_settings_page' => 'Settings'
];

foreach ($page_functions as $function => $page_name) {
    if (function_exists($function)) {
        echo "<div class='test-item success'>✅ Funzione pagina '{$page_name}' disponibile</div>";
        
        // Test se la funzione può essere chiamata
        try {
            ob_start();
            $result = call_user_func($function);
            $output = ob_get_clean();
            
            if (!empty($output) || $result !== false) {
                echo "<div class='test-item success'>✅ Pagina '{$page_name}' genera output</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Pagina '{$page_name}' non genera output</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore pagina '{$page_name}': " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='test-item error'>❌ Funzione pagina '{$page_name}' NON disponibile</div>";
    }
}
echo "</div>";

// 8. VERIFICA FORM E SALVATAGGIO
echo "<div class='section'>";
echo "<h2>💾 8. Verifica Form e Salvataggio</h2>";

// Verifica se ci sono form nelle pagine
$form_elements = [
    'input[type="checkbox"]' => 'Checkbox',
    'input[type="text"]' => 'Campi testo',
    'select' => 'Menu a tendina',
    'button[type="submit"]' => 'Pulsanti salva'
];

foreach ($form_elements as $selector => $description) {
    echo "<div class='test-item info'>📋 Verifica presenza {$description} nelle pagine admin</div>";
}

// Verifica funzioni di salvataggio
$save_functions = [
    'fp_performance_save_cache_options' => 'Salvataggio opzioni cache',
    'fp_performance_save_database_options' => 'Salvataggio opzioni database',
    'fp_performance_save_asset_options' => 'Salvataggio opzioni assets',
    'fp_performance_save_mobile_options' => 'Salvataggio opzioni mobile'
];

foreach ($save_functions as $function => $description) {
    if (function_exists($function)) {
        echo "<div class='test-item success'>✅ {$description} disponibile</div>";
    } else {
        echo "<div class='test-item warning'>⚠️ {$description} NON disponibile</div>";
    }
}
echo "</div>";

// 9. VERIFICA AJAX
echo "<div class='section'>";
echo "<h2>🔄 9. Verifica Funzionalità AJAX</h2>";

$ajax_actions = [
    'fp_performance_save_options' => 'Salvataggio opzioni',
    'fp_performance_test_cache' => 'Test cache',
    'fp_performance_optimize_database' => 'Ottimizzazione database',
    'fp_performance_clear_cache' => 'Pulizia cache'
];

foreach ($ajax_actions as $action => $description) {
    if (has_action("wp_ajax_{$action}")) {
        echo "<div class='test-item success'>✅ AJAX '{$description}' registrato</div>";
    } else {
        echo "<div class='test-item warning'>⚠️ AJAX '{$description}' non registrato</div>";
    }
}
echo "</div>";

// 10. RACCOMANDAZIONI
echo "<div class='section'>";
echo "<h2>💡 10. Raccomandazioni</h2>";

echo "<div class='test-item info'>📋 1. Testa ogni pagina cliccando sui link sopra</div>";
echo "<div class='test-item info'>📋 2. Verifica che le pagine si carichino senza errori</div>";
echo "<div class='test-item info'>📋 3. Controlla che i form funzionino correttamente</div>";
echo "<div class='test-item info'>📋 4. Testa il salvataggio delle opzioni</div>";
echo "<div class='test-item info'>📋 5. Verifica che non ci siano pagine vuote</div>";

echo "</div>";

echo "<h2>✅ Test Pagine Admin Completato!</h2>";
echo "<p>Usa i link sopra per testare manualmente ogni pagina admin del plugin.</p>";
?>
