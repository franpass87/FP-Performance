<?php
/**
 * Script di emergenza per ripristinare i permessi
 * Esegui questo file direttamente dal browser se non riesci ad accedere al plugin
 * 
 * URL: http://your-site.com/wp-content/plugins/fp-performance-suite/fix-permissions.php
 */

// Carica WordPress
require_once __DIR__ . '/../../../wp-load.php';

// Verifica che l'utente sia un amministratore
if (!current_user_can('manage_options')) {
    wp_die('Solo gli amministratori possono eseguire questo script.');
}

// Ripristina le impostazioni predefinite
$current_settings = get_option('fp_ps_settings', []);
$current_settings['allowed_role'] = 'administrator';
update_option('fp_ps_settings', $current_settings);

// Cancella eventuali cache
wp_cache_delete('fp_ps_settings', 'options');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FP Performance Suite - Permessi Ripristinati</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 800px;
            margin: 60px auto;
            padding: 20px;
            background: #f0f0f1;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #059669;
            margin-bottom: 20px;
        }
        .success-icon {
            font-size: 72px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: #2271b1;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 10px;
            font-weight: 600;
        }
        .button:hover {
            background: #135e96;
        }
        .info-box {
            background: #d1fae5;
            border-left: 4px solid #059669;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✅</div>
        <h1>Permessi Ripristinati con Successo!</h1>
        
        <div class="info-box">
            <p><strong>Cosa è stato fatto:</strong></p>
            <ul>
                <li>✅ Impostazione <code>allowed_role</code> ripristinata a <code>administrator</code></li>
                <li>✅ Capability richiesta impostata a <code>manage_options</code></li>
                <li>✅ Cache delle opzioni cancellata</li>
            </ul>
        </div>
        
        <p style="margin: 30px 0;">
            Ora dovresti essere in grado di accedere a tutte le pagine di FP Performance Suite.
        </p>
        
        <a href="<?php echo admin_url('admin.php?page=fp-performance-suite'); ?>" class="button">
            Vai a FP Performance Suite
        </a>
        
        <a href="<?php echo admin_url(); ?>" class="button" style="background: #6c757d;">
            Torna alla Dashboard
        </a>
        
        <p style="margin-top: 40px; color: #666; font-size: 14px;">
            Se il problema persiste, controlla i log di WordPress per ulteriori informazioni.
        </p>
    </div>
</body>
</html>

