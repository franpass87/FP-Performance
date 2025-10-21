<?php
/**
 * Emergency WSOD Fix Script
 * 
 * Questo script risolve i problemi di WSOD causati da:
 * - Cache OPcache corrotta
 * - Problemi di connessione al database
 * - Plugin problematici
 * 
 * Carica questo file via FTP nella root di WordPress e visitalo nel browser.
 */

// Prevent direct execution in WordPress context
define('EMERGENCY_FIX_MODE', true);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency WSOD Fix</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
            max-width: 800px;
            margin: 50px auto;
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
            border-bottom: 2px solid #2271b1;
            padding-bottom: 10px;
        }
        .success {
            background: #00a32a;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .error {
            background: #d63638;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .warning {
            background: #dba617;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .info {
            background: #2271b1;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #2271b1;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .button:hover {
            background: #135e96;
        }
        .button-danger {
            background: #d63638;
        }
        .button-danger:hover {
            background: #b32d2e;
        }
        pre {
            background: #f6f7f7;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            border-left: 4px solid #2271b1;
        }
        .action-list {
            list-style: none;
            padding: 0;
        }
        .action-list li {
            padding: 10px;
            margin: 5px 0;
            background: #f6f7f7;
            border-left: 4px solid #00a32a;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚨 Emergency WSOD Fix</h1>
        
        <?php
        
        $action = $_GET['action'] ?? 'status';
        $messages = [];
        
        // Check if wp-config.php exists
        $wpConfigPath = __DIR__ . '/wp-config.php';
        if (!file_exists($wpConfigPath)) {
            echo '<div class="error">❌ File wp-config.php non trovato! Assicurati di essere nella root di WordPress.</div>';
            exit;
        }
        
        // Function to clear OPcache
        function clearOpcache() {
            $cleared = false;
            if (function_exists('opcache_reset')) {
                $cleared = opcache_reset();
            }
            return $cleared;
        }
        
        // Function to check database connection
        function checkDatabaseConnection() {
            if (!file_exists(__DIR__ . '/wp-config.php')) {
                return ['success' => false, 'message' => 'wp-config.php non trovato'];
            }
            
            // Load wp-config to get DB credentials
            define('ABSPATH', __DIR__ . '/');
            require_once(__DIR__ . '/wp-config.php');
            
            // Try to connect
            $mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            
            if ($mysqli->connect_error) {
                return [
                    'success' => false, 
                    'message' => 'Errore connessione: ' . $mysqli->connect_error,
                    'errno' => $mysqli->connect_errno
                ];
            }
            
            $mysqli->close();
            return ['success' => true, 'message' => 'Connessione database OK'];
        }
        
        // Function to disable plugins
        function disableAllPlugins() {
            if (!file_exists(__DIR__ . '/wp-config.php')) {
                return false;
            }
            
            define('ABSPATH', __DIR__ . '/');
            require_once(__DIR__ . '/wp-config.php');
            
            $mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            if ($mysqli->connect_error) {
                return false;
            }
            
            // Get table prefix
            $prefix = $GLOBALS['table_prefix'] ?? 'wp_';
            
            // Disable all plugins
            $query = "UPDATE {$prefix}options SET option_value = '' WHERE option_name = 'active_plugins'";
            $result = $mysqli->query($query);
            
            $mysqli->close();
            return $result;
        }
        
        // Handle actions
        switch ($action) {
            case 'clear_cache':
                if (clearOpcache()) {
                    $messages[] = ['type' => 'success', 'text' => '✅ Cache OPcache pulita con successo!'];
                } else {
                    $messages[] = ['type' => 'warning', 'text' => '⚠️ OPcache non disponibile o non abilitata'];
                }
                break;
                
            case 'check_db':
                $result = checkDatabaseConnection();
                if ($result['success']) {
                    $messages[] = ['type' => 'success', 'text' => '✅ ' . $result['message']];
                } else {
                    $messages[] = ['type' => 'error', 'text' => '❌ ' . $result['message']];
                }
                break;
                
            case 'disable_plugins':
                if (disableAllPlugins()) {
                    $messages[] = ['type' => 'success', 'text' => '✅ Tutti i plugin sono stati disabilitati!'];
                    $messages[] = ['type' => 'info', 'text' => '💡 Ora prova ad accedere al sito. Se funziona, riattiva i plugin uno alla volta.'];
                } else {
                    $messages[] = ['type' => 'error', 'text' => '❌ Impossibile disabilitare i plugin. Verifica la connessione al database.'];
                }
                break;
                
            case 'full_fix':
                // Clear cache
                $cacheCleared = clearOpcache();
                
                // Check database
                $dbResult = checkDatabaseConnection();
                
                if ($cacheCleared) {
                    $messages[] = ['type' => 'success', 'text' => '✅ Cache OPcache pulita'];
                }
                
                if ($dbResult['success']) {
                    $messages[] = ['type' => 'success', 'text' => '✅ Connessione database OK'];
                    
                    // Try to disable plugins
                    if (disableAllPlugins()) {
                        $messages[] = ['type' => 'success', 'text' => '✅ Plugin disabilitati'];
                        $messages[] = ['type' => 'info', 'text' => '💡 Prova ora ad accedere al sito!'];
                    }
                } else {
                    $messages[] = ['type' => 'error', 'text' => '❌ Problema database: ' . $dbResult['message']];
                }
                break;
        }
        
        // Display messages
        foreach ($messages as $msg) {
            echo "<div class=\"{$msg['type']}\">{$msg['text']}</div>";
        }
        
        // Status display
        if ($action === 'status' || !empty($messages)) {
            echo '<h2>📊 Stato Sistema</h2>';
            
            // OPcache status
            if (function_exists('opcache_get_status')) {
                $opcache = opcache_get_status(false);
                if ($opcache && $opcache['opcache_enabled']) {
                    echo '<div class="info">✅ OPcache: Abilitata</div>';
                    if (isset($opcache['opcache_statistics'])) {
                        $stats = $opcache['opcache_statistics'];
                        echo '<pre>';
                        echo "Memory Used: " . round($opcache['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
                        echo "Cached Scripts: " . $stats['num_cached_scripts'] . "\n";
                        echo "Hits: " . $stats['hits'] . "\n";
                        echo "Misses: " . $stats['misses'] . "\n";
                        echo '</pre>';
                    }
                } else {
                    echo '<div class="warning">⚠️ OPcache: Non abilitata</div>';
                }
            } else {
                echo '<div class="warning">⚠️ OPcache: Non disponibile</div>';
            }
            
            // Database status
            $dbStatus = checkDatabaseConnection();
            if ($dbStatus['success']) {
                echo '<div class="info">✅ Database: Connesso</div>';
            } else {
                echo '<div class="error">❌ Database: ' . $dbStatus['message'] . '</div>';
            }
            
            // PHP version
            echo '<div class="info">PHP Version: ' . phpversion() . '</div>';
            
            // Memory limit
            echo '<div class="info">Memory Limit: ' . ini_get('memory_limit') . '</div>';
        }
        
        ?>
        
        <h2>🛠️ Azioni Disponibili</h2>
        
        <div style="margin: 20px 0;">
            <a href="?action=full_fix" class="button">🚀 Fix Completo (Consigliato)</a>
            <p style="color: #666; font-size: 13px; margin-top: 5px;">
                Pulisce cache, verifica database e disabilita i plugin
            </p>
        </div>
        
        <div style="margin: 20px 0;">
            <a href="?action=clear_cache" class="button">🧹 Pulisci Cache OPcache</a>
            <p style="color: #666; font-size: 13px; margin-top: 5px;">
                Risolve problemi di cache PHP corrotta
            </p>
        </div>
        
        <div style="margin: 20px 0;">
            <a href="?action=check_db" class="button">🔍 Verifica Database</a>
            <p style="color: #666; font-size: 13px; margin-top: 5px;">
                Controlla la connessione al database MySQL
            </p>
        </div>
        
        <div style="margin: 20px 0;">
            <a href="?action=disable_plugins" class="button button-danger">🔌 Disabilita Tutti i Plugin</a>
            <p style="color: #666; font-size: 13px; margin-top: 5px;">
                <strong>Attenzione:</strong> Disabilita tutti i plugin. Usare solo in emergenza.
            </p>
        </div>
        
        <hr style="margin: 30px 0;">
        
        <h2>📝 Problemi Identificati dai Log</h2>
        <ul class="action-list">
            <li><strong>Cache OPcache Corrotta:</strong> La cache PHP potrebbe contenere codice vecchio o corrotto</li>
            <li><strong>Connessione Database Intermittente:</strong> Il database perde la connessione durante le richieste AJAX</li>
            <li><strong>Tempi di Esecuzione Anomali:</strong> Le richieste AJAX riportano tempi impossibili (55+ anni)</li>
        </ul>
        
        <h2>💡 Passi Consigliati</h2>
        <ol style="line-height: 1.8;">
            <li>Clicca su <strong>"Fix Completo"</strong> per pulire tutto</li>
            <li>Prova ad accedere al sito WordPress</li>
            <li>Se funziona, riattiva i plugin uno alla volta dal pannello admin</li>
            <li>Se non funziona, contatta il supporto hosting per verificare:
                <ul>
                    <li>Stabilità della connessione MySQL</li>
                    <li>Configurazione OPcache</li>
                    <li>Limiti di memoria e timeout PHP</li>
                </ul>
            </li>
        </ol>
        
        <hr style="margin: 30px 0;">
        
        <p style="text-align: center; color: #666; font-size: 13px;">
            <strong>Nota:</strong> Dopo aver risolto il problema, elimina questo file per sicurezza.
        </p>
    </div>
</body>
</html>

