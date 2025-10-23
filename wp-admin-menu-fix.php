<?php
/**
 * Plugin temporaneo per fix menu admin FP Performance Suite
 * 
 * Aggiungi questo codice al file functions.php del tuo tema
 * oppure crea un plugin temporaneo
 */

// Aggiungi questa funzione al tuo functions.php del tema attivo
// oppure crea un plugin temporaneo con questo codice

add_action('admin_menu', 'fp_performance_menu_fix_admin_page');

function fp_performance_menu_fix_admin_page() {
    add_management_page(
        'Fix Menu FP Performance',
        'Fix Menu FP Performance',
        'manage_options',
        'fp-performance-menu-fix',
        'fp_performance_menu_fix_page'
    );
}

function fp_performance_menu_fix_page() {
    ?>
    <div class="wrap">
        <h1>🛠️ Fix Menu Admin FP Performance Suite</h1>
        
        <?php
        // Esegui i fix se richiesto
        if (isset($_POST['run_fix']) && wp_verify_nonce($_POST['_wpnonce'], 'fp_menu_fix')) {
            echo '<div class="notice notice-info"><p>Eseguendo fix...</p></div>';
            
            $fixes_applied = [];
            
            // 1. Fix impostazioni plugin
            $current_settings = get_option('fp_ps_settings', []);
            if (!is_array($current_settings)) {
                $current_settings = [];
            }
            if (!isset($current_settings['allowed_role']) || !in_array($current_settings['allowed_role'], ['administrator', 'editor'])) {
                $current_settings['allowed_role'] = 'administrator';
                update_option('fp_ps_settings', $current_settings);
                $fixes_applied[] = 'Impostazioni plugin corrette';
            }
            
            // 2. Rimuovi errori di attivazione
            $activation_error = get_option('fp_perfsuite_activation_error');
            if ($activation_error) {
                delete_option('fp_perfsuite_activation_error');
                $fixes_applied[] = 'Errore di attivazione rimosso';
            }
            
            // 3. Pulisci cache
            if (function_exists('wp_cache_delete')) {
                wp_cache_delete('admin_menu', 'options');
            }
            
            // 4. Forza rigenerazione menu
            do_action('admin_menu');
            $fixes_applied[] = 'Menu admin rigenerato';
            
            if (!empty($fixes_applied)) {
                echo '<div class="notice notice-success"><p><strong>Fix applicati:</strong></p><ul>';
                foreach ($fixes_applied as $fix) {
                    echo '<li>' . esc_html($fix) . '</li>';
                }
                echo '</ul></div>';
            }
        }
        ?>
        
        <div class="card">
            <h2>🔍 Diagnostica Attuale</h2>
            
            <h3>1. Stato Plugin</h3>
            <?php if (class_exists('FP\\PerfSuite\\Plugin')): ?>
                <p style="color: green;">✅ Plugin FP Performance Suite è attivo</p>
            <?php else: ?>
                <p style="color: red;">❌ Plugin FP Performance Suite NON è attivo</p>
            <?php endif; ?>
            
            <h3>2. Impostazioni Plugin</h3>
            <?php 
            $settings = get_option('fp_ps_settings', []);
            $allowed_role = $settings['allowed_role'] ?? 'administrator';
            ?>
            <p><strong>Ruolo configurato:</strong> <?php echo esc_html($allowed_role); ?></p>
            
            <h3>3. Permessi Utente</h3>
            <?php 
            $capability = ($allowed_role === 'editor') ? 'edit_pages' : 'manage_options';
            $can_access = current_user_can($capability);
            ?>
            <p><strong>Capability richiesta:</strong> <?php echo esc_html($capability); ?></p>
            <p><strong>Utente può accedere:</strong> <?php echo $can_access ? '✅ SÌ' : '❌ NO'; ?></p>
            
            <h3>4. Menu Admin</h3>
            <?php
            global $menu;
            $menu_found = false;
            if (isset($menu)) {
                foreach ($menu as $menu_item) {
                    if (isset($menu_item[2]) && $menu_item[2] === 'fp-performance-suite') {
                        $menu_found = true;
                        break;
                    }
                }
            }
            ?>
            <p><strong>Menu FP Performance visibile:</strong> <?php echo $menu_found ? '✅ SÌ' : '❌ NO'; ?></p>
            
            <h3>5. Errori</h3>
            <?php 
            $activation_error = get_option('fp_perfsuite_activation_error');
            if ($activation_error): ?>
                <p style="color: red;">❌ Errore di attivazione: <?php echo esc_html($activation_error); ?></p>
            <?php else: ?>
                <p style="color: green;">✅ Nessun errore di attivazione</p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>🛠️ Azioni</h2>
            
            <form method="post">
                <?php wp_nonce_field('fp_menu_fix'); ?>
                <p>
                    <input type="submit" name="run_fix" class="button button-primary" value="🔧 Esegui Fix Automatico" />
                </p>
            </form>
            
            <p><strong>Azioni manuali:</strong></p>
            <ul>
                <li><a href="<?php echo admin_url('admin.php?page=fp-performance-suite'); ?>" class="button">🚀 Vai a FP Performance</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=fp-performance-suite-settings'); ?>" class="button">🔧 Impostazioni</a></li>
                <li><a href="<?php echo admin_url('plugins.php'); ?>" class="button">📦 Gestisci Plugin</a></li>
            </ul>
        </div>
        
        <div class="card">
            <h2>📋 Soluzioni Manuali</h2>
            
            <h3>Se il fix automatico non funziona:</h3>
            <ol>
                <li><strong>Disattiva e riattiva il plugin:</strong>
                    <br>Vai in Plugin → Plugin Installati → Disattiva FP Performance Suite → Riattiva
                </li>
                <li><strong>Verifica conflitti:</strong>
                    <br>Disattiva temporaneamente altri plugin per vedere se c'è un conflitto
                </li>
                <li><strong>Reset completo:</strong>
                    <br>Disattiva il plugin, elimina le opzioni dal database, riattiva
                </li>
            </ol>
        </div>
    </div>
    
    <style>
    .card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    .card h2 {
        margin-top: 0;
        color: #23282d;
    }
    .card h3 {
        color: #0073aa;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }
    </style>
    <?php
}
