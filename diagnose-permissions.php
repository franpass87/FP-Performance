<?php
/**
 * Script di diagnostica per problemi di permessi
 * Caricare questo file via browser per diagnosticare problemi di accesso
 * 
 * URL: http://your-site.com/wp-content/plugins/fp-performance-suite/diagnose-permissions.php
 */

// Carica WordPress
require_once __DIR__ . '/../../../wp-load.php';

// Verifica che l'utente sia loggato
if (!is_user_logged_in()) {
    wp_die('Devi essere loggato per accedere a questa pagina di diagnostica.');
}

// Header
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FP Performance Suite - Diagnostica Permessi</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f0f0f1;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1d2327;
            border-bottom: 3px solid #2271b1;
            padding-bottom: 15px;
        }
        h2 {
            color: #2271b1;
            margin-top: 30px;
        }
        .info-box {
            background: #f0f6fc;
            border-left: 4px solid #2271b1;
            padding: 15px;
            margin: 15px 0;
        }
        .success {
            background: #d1fae5;
            border-left-color: #059669;
        }
        .warning {
            background: #fef3c7;
            border-left-color: #f59e0b;
        }
        .error {
            background: #fee2e2;
            border-left-color: #dc2626;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
        }
        .status-yes {
            color: #059669;
            font-weight: 600;
        }
        .status-no {
            color: #dc2626;
            font-weight: 600;
        }
        code {
            background: #f0f0f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .action-button {
            display: inline-block;
            background: #2271b1;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 5px;
        }
        .action-button:hover {
            background: #135e96;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 FP Performance Suite - Diagnostica Permessi</h1>
        
        <?php
        $current_user = wp_get_current_user();
        $fp_settings = get_option('fp_ps_settings', ['allowed_role' => 'administrator']);
        $required_role = $fp_settings['allowed_role'] ?? 'administrator';
        $required_capability = 'manage_options'; // Default
        
        if ($required_role === 'editor') {
            $required_capability = 'edit_pages';
        }
        
        // Verifica capability
        $has_capability = current_user_can($required_capability);
        $is_admin = current_user_can('manage_options');
        ?>
        
        <h2>👤 Informazioni Utente Corrente</h2>
        <table>
            <tr>
                <th>Username</th>
                <td><?php echo esc_html($current_user->user_login); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo esc_html($current_user->user_email); ?></td>
            </tr>
            <tr>
                <th>Ruoli</th>
                <td><?php echo esc_html(implode(', ', $current_user->roles)); ?></td>
            </tr>
            <tr>
                <th>È Amministratore?</th>
                <td class="<?php echo $is_admin ? 'status-yes' : 'status-no'; ?>">
                    <?php echo $is_admin ? '✅ SÌ' : '❌ NO'; ?>
                </td>
            </tr>
        </table>
        
        <h2>⚙️ Configurazione FP Performance Suite</h2>
        <table>
            <tr>
                <th>Ruolo Richiesto (configurato)</th>
                <td><code><?php echo esc_html($required_role); ?></code></td>
            </tr>
            <tr>
                <th>Capability Richiesta</th>
                <td><code><?php echo esc_html($required_capability); ?></code></td>
            </tr>
            <tr>
                <th>Hai questa Capability?</th>
                <td class="<?php echo $has_capability ? 'status-yes' : 'status-no'; ?>">
                    <?php echo $has_capability ? '✅ SÌ' : '❌ NO'; ?>
                </td>
            </tr>
        </table>
        
        <h2>🔐 Verifica Permessi Specifici</h2>
        <table>
            <thead>
                <tr>
                    <th>Capability</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $capabilities_to_check = [
                    'manage_options' => 'Gestione opzioni (Amministratore)',
                    'edit_pages' => 'Modifica pagine (Editor)',
                    'edit_posts' => 'Modifica articoli',
                    'read' => 'Lettura',
                ];
                
                foreach ($capabilities_to_check as $cap => $label) {
                    $has = current_user_can($cap);
                    ?>
                    <tr>
                        <td><?php echo esc_html($label); ?> <code><?php echo esc_html($cap); ?></code></td>
                        <td class="<?php echo $has ? 'status-yes' : 'status-no'; ?>">
                            <?php echo $has ? '✅ SÌ' : '❌ NO'; ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        
        <h2>📊 Risultato Diagnostica</h2>
        
        <?php if ($has_capability): ?>
            <div class="info-box success">
                <p><strong>✅ Tutto OK!</strong></p>
                <p>Hai i permessi necessari per accedere a FP Performance Suite.</p>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=fp-performance-suite'); ?>" class="action-button">
                        Vai a FP Performance Suite
                    </a>
                </p>
            </div>
        <?php else: ?>
            <div class="info-box error">
                <p><strong>❌ Problema Rilevato!</strong></p>
                <p>Non hai i permessi necessari per accedere a FP Performance Suite.</p>
                
                <h3>Possibili Soluzioni:</h3>
                <ol>
                    <li><strong>Se sei un amministratore:</strong> Il problema potrebbe essere nelle impostazioni del plugin. Prova a reimpostare le impostazioni predefinite.</li>
                    <li><strong>Se non sei un amministratore:</strong> Contatta l'amministratore del sito per ottenere i permessi necessari.</li>
                    <li><strong>Verifica conflitti:</strong> Alcuni plugin di gestione ruoli potrebbero interferire con i permessi.</li>
                </ol>
                
                <?php if ($is_admin): ?>
                    <form method="post" action="">
                        <?php wp_nonce_field('reset_fp_settings', 'fp_reset_nonce'); ?>
                        <input type="hidden" name="action" value="reset_settings">
                        <button type="submit" class="action-button" style="background: #dc2626;">
                            🔄 Reimposta Impostazioni Predefinite
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php
        // Gestisci il reset delle impostazioni
        if (isset($_POST['action']) && $_POST['action'] === 'reset_settings') {
            if (isset($_POST['fp_reset_nonce']) && wp_verify_nonce($_POST['fp_reset_nonce'], 'reset_fp_settings')) {
                if (current_user_can('manage_options')) {
                    // Reset delle impostazioni
                    update_option('fp_ps_settings', ['allowed_role' => 'administrator']);
                    
                    echo '<div class="info-box success" style="margin-top: 20px;">';
                    echo '<p><strong>✅ Impostazioni Reimpostate!</strong></p>';
                    echo '<p>Le impostazioni sono state reimpostate ai valori predefiniti. Ricarica la pagina per verificare.</p>';
                    echo '<p><a href="" class="action-button">Ricarica Pagina</a></p>';
                    echo '</div>';
                }
            }
        }
        ?>
        
        <h2>📝 Log di Debug</h2>
        <div class="info-box">
            <p>Se il problema persiste, controlla i log di WordPress per informazioni dettagliate.</p>
            <p>Le informazioni di debug sono state registrate in:</p>
            <ul>
                <li><code><?php echo WP_CONTENT_DIR . '/debug.log'; ?></code></li>
            </ul>
            <p>Se <code>WP_DEBUG</code> è abilitato, vedrai informazioni dettagliate sui permessi richiesti.</p>
        </div>
        
        <h2>🔧 Configurazione WordPress</h2>
        <table>
            <tr>
                <th>WP_DEBUG</th>
                <td><?php echo defined('WP_DEBUG') && WP_DEBUG ? '✅ Abilitato' : '❌ Disabilitato'; ?></td>
            </tr>
            <tr>
                <th>WP_DEBUG_LOG</th>
                <td><?php echo defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? '✅ Abilitato' : '❌ Disabilitato'; ?></td>
            </tr>
            <tr>
                <th>Versione WordPress</th>
                <td><?php echo esc_html(get_bloginfo('version')); ?></td>
            </tr>
        </table>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 14px;">
            <p><strong>FP Performance Suite</strong> - Diagnostica Permessi v1.0</p>
            <p>Per ulteriore supporto, visita <a href="https://francescopasseri.com/support">francescopasseri.com/support</a></p>
        </div>
    </div>
</body>
</html>

