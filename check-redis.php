<?php
/**
 * Script di verifica Redis/Memcached per Ionos
 * 
 * ISTRUZIONI:
 * 1. Carica questo file nella root del tuo sito WordPress
 * 2. Accedi tramite browser: https://tuosito.com/check-redis.php
 * 3. ELIMINA questo file dopo aver verificato!
 * 
 * @package FP-Performance-Suite
 */

// Previeni accesso diretto se non in ambiente WordPress
if (!defined('ABSPATH') && !isset($_SERVER['HTTP_HOST'])) {
    die('Accesso diretto non consentito');
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica Redis/Memcached - Ionos</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2271b1;
            border-bottom: 3px solid #2271b1;
            padding-bottom: 10px;
        }
        h2 {
            color: #333;
            margin-top: 30px;
            padding: 10px;
            background: #f0f0f0;
            border-left: 4px solid #2271b1;
        }
        .success {
            color: #00a32a;
            font-weight: bold;
        }
        .error {
            color: #d63638;
            font-weight: bold;
        }
        .warning {
            color: #dba617;
            font-weight: bold;
        }
        .info-box {
            background: #e7f5ff;
            border-left: 4px solid #2271b1;
            padding: 15px;
            margin: 15px 0;
        }
        .alert-box {
            background: #fcf3cf;
            border-left: 4px solid #f39c12;
            padding: 15px;
            margin: 20px 0;
        }
        .danger-box {
            background: #fadbd8;
            border-left: 4px solid #d63638;
            padding: 15px;
            margin: 20px 0;
            font-weight: bold;
        }
        .result-item {
            padding: 10px;
            margin: 10px 0;
            background: #fafafa;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #2271b1;
            color: white;
        }
        .recommendation {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .recommendation h3 {
            margin-top: 0;
            color: #0c5460;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verifica Redis/Memcached su Ionos</h1>
        
        <div class="info-box">
            <strong>ℹ️ Informazioni:</strong> Questo script verifica se Redis o Memcached sono disponibili sul tuo hosting Ionos.
        </div>

        <?php
        // Inizializza variabili di stato
        $redisAvailable = false;
        $redisConnectable = false;
        $memcachedAvailable = false;
        $memcachedConnectable = false;
        $redisInfo = [];

        ?>

        <h2>1️⃣ Verifica Estensione Redis PHP</h2>
        <div class="result-item">
            <?php if (class_exists('Redis')): ?>
                <?php $redisAvailable = true; ?>
                <span class="success">✅ REDIS È DISPONIBILE!</span><br>
                <strong>Versione estensione PHP Redis:</strong> <?php echo phpversion('redis'); ?><br>
            <?php else: ?>
                <span class="error">❌ Redis NON disponibile</span><br>
                L'estensione php-redis non è installata sul server.
            <?php endif; ?>
        </div>

        <h2>2️⃣ Verifica Estensione Memcached PHP</h2>
        <div class="result-item">
            <?php if (class_exists('Memcached')): ?>
                <?php $memcachedAvailable = true; ?>
                <span class="success">✅ MEMCACHED È DISPONIBILE!</span><br>
                <strong>Versione estensione PHP Memcached:</strong> <?php echo phpversion('memcached'); ?><br>
            <?php else: ?>
                <span class="error">❌ Memcached NON disponibile</span><br>
                L'estensione php-memcached non è installata sul server.
            <?php endif; ?>
        </div>

        <h2>3️⃣ Test Connessione Redis (localhost:6379)</h2>
        <div class="result-item">
            <?php if ($redisAvailable): ?>
                <?php
                try {
                    $redis = new Redis();
                    $connected = @$redis->connect('127.0.0.1', 6379, 1);
                    
                    if ($connected) {
                        $redisConnectable = true;
                        $redisInfo = $redis->info();
                        ?>
                        <span class="success">✅ CONNESSIONE A REDIS RIUSCITA!</span><br>
                        <strong>Host:</strong> 127.0.0.1<br>
                        <strong>Porta:</strong> 6379<br>
                        <strong>Versione server Redis:</strong> <?php echo $redisInfo['redis_version'] ?? 'N/A'; ?><br>
                        <strong>Modalità:</strong> <?php echo $redisInfo['redis_mode'] ?? 'N/A'; ?><br>
                        <strong>Memoria usata:</strong> <?php echo isset($redisInfo['used_memory_human']) ? $redisInfo['used_memory_human'] : 'N/A'; ?><br>
                        <?php
                        $redis->close();
                    } else {
                        ?>
                        <span class="error">❌ Impossibile connettersi a Redis</span><br>
                        Redis è installato ma il server non risponde su 127.0.0.1:6379
                        <?php
                    }
                } catch (Exception $e) {
                    ?>
                    <span class="error">❌ Errore connessione Redis</span><br>
                    <strong>Messaggio:</strong> <?php echo htmlspecialchars($e->getMessage()); ?><br>
                    <strong>Causa probabile:</strong> Server Redis non in esecuzione o non configurato
                    <?php
                }
                ?>
            <?php else: ?>
                <span class="warning">⚠️ Test saltato (estensione Redis non disponibile)</span>
            <?php endif; ?>
        </div>

        <h2>4️⃣ Test Connessione Memcached (localhost:11211)</h2>
        <div class="result-item">
            <?php if ($memcachedAvailable): ?>
                <?php
                try {
                    $memcached = new Memcached();
                    @$memcached->addServer('127.0.0.1', 11211);
                    $stats = @$memcached->getStats();
                    
                    if (!empty($stats) && isset($stats['127.0.0.1:11211'])) {
                        $memcachedConnectable = true;
                        $serverStats = $stats['127.0.0.1:11211'];
                        ?>
                        <span class="success">✅ CONNESSIONE A MEMCACHED RIUSCITA!</span><br>
                        <strong>Host:</strong> 127.0.0.1<br>
                        <strong>Porta:</strong> 11211<br>
                        <strong>Versione:</strong> <?php echo $serverStats['version'] ?? 'N/A'; ?><br>
                        <strong>Items memorizzati:</strong> <?php echo $serverStats['curr_items'] ?? 'N/A'; ?><br>
                        <?php
                    } else {
                        ?>
                        <span class="error">❌ Impossibile connettersi a Memcached</span><br>
                        Memcached è installato ma il server non risponde su 127.0.0.1:11211
                        <?php
                    }
                } catch (Exception $e) {
                    ?>
                    <span class="error">❌ Errore connessione Memcached</span><br>
                    <strong>Messaggio:</strong> <?php echo htmlspecialchars($e->getMessage()); ?>
                    <?php
                }
                ?>
            <?php else: ?>
                <span class="warning">⚠️ Test saltato (estensione Memcached non disponibile)</span>
            <?php endif; ?>
        </div>

        <h2>5️⃣ Informazioni Server e PHP</h2>
        <table>
            <tr>
                <th>Parametro</th>
                <th>Valore</th>
            </tr>
            <tr>
                <td>Versione PHP</td>
                <td><strong><?php echo phpversion(); ?></strong></td>
            </tr>
            <tr>
                <td>Server Software</td>
                <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></td>
            </tr>
            <tr>
                <td>Sistema Operativo</td>
                <td><?php echo PHP_OS; ?></td>
            </tr>
            <tr>
                <td>SAPI</td>
                <td><?php echo php_sapi_name(); ?></td>
            </tr>
            <tr>
                <td>Document Root</td>
                <td><code><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'; ?></code></td>
            </tr>
        </table>

        <h2>📊 Riepilogo e Raccomandazioni</h2>
        
        <?php if ($redisConnectable): ?>
            <div class="recommendation">
                <h3>🎉 Perfetto! Redis è completamente funzionante</h3>
                <p><strong>Configurazione consigliata per FP Performance Suite:</strong></p>
                <ul>
                    <li><strong>Driver:</strong> Redis (o Auto)</li>
                    <li><strong>Host:</strong> <code>127.0.0.1</code></li>
                    <li><strong>Porta:</strong> <code>6379</code></li>
                    <li><strong>Password:</strong> (lascia vuoto se non configurata)</li>
                    <li><strong>Prefisso chiavi:</strong> <code>fp_ps_</code></li>
                </ul>
                <p><strong>✅ Azione:</strong> Puoi abilitare l'Object Cache nel plugin!</p>
            </div>
        <?php elseif ($memcachedConnectable): ?>
            <div class="recommendation">
                <h3>🎉 Ottimo! Memcached è funzionante</h3>
                <p><strong>Configurazione consigliata per FP Performance Suite:</strong></p>
                <ul>
                    <li><strong>Driver:</strong> Memcached (o Auto)</li>
                    <li><strong>Host:</strong> <code>127.0.0.1</code></li>
                    <li><strong>Porta:</strong> <code>11211</code></li>
                    <li><strong>Prefisso chiavi:</strong> <code>fp_ps_</code></li>
                </ul>
                <p><strong>✅ Azione:</strong> Puoi abilitare l'Object Cache nel plugin!</p>
            </div>
        <?php elseif ($redisAvailable || $memcachedAvailable): ?>
            <div class="alert-box">
                <h3>⚠️ Estensione disponibile ma server non raggiungibile</h3>
                <p>L'estensione PHP è installata ma il server di caching non risponde.</p>
                <p><strong>Possibili cause:</strong></p>
                <ul>
                    <li>Il servizio non è stato avviato dall'hosting provider</li>
                    <li>Configurazione non completata</li>
                    <li>Firewall o restrizioni di rete</li>
                </ul>
                <p><strong>🔧 Azione consigliata:</strong> Contatta il supporto Ionos e fornisci questo report.</p>
            </div>
        <?php else: ?>
            <div class="alert-box">
                <h3>❌ Redis/Memcached non disponibili</h3>
                <p>Né Redis né Memcached sono disponibili sul tuo piano Ionos attuale.</p>
                <p><strong>📞 Cosa puoi fare:</strong></p>
                <ol>
                    <li><strong>Contatta il supporto Ionos</strong> e chiedi:
                        <ul>
                            <li>"Posso attivare Redis o Memcached sul mio piano hosting?"</li>
                            <li>"Quali piani includono il supporto per Object Caching?"</li>
                        </ul>
                    </li>
                    <li><strong>Considera un upgrade</strong> a:
                        <ul>
                            <li>Hosting WordPress Gestito Pro/Expert (include Redis)</li>
                            <li>VPS Linux (installazione manuale possibile)</li>
                            <li>Cloud Server (pieno controllo)</li>
                        </ul>
                    </li>
                    <li><strong>Alternative</strong>:
                        <ul>
                            <li>Usa le altre ottimizzazioni del plugin (Page Cache, Browser Cache, ecc.)</li>
                            <li>Considera un servizio Redis esterno (Redis Cloud, DigitalOcean)</li>
                        </ul>
                    </li>
                </ol>
                <p><strong>ℹ️ Nota:</strong> Il plugin FP Performance Suite continuerà a funzionare ottimamente anche senza Redis, usando le altre tecnologie di caching disponibili.</p>
            </div>
        <?php endif; ?>

        <div class="danger-box">
            <strong>🔒 SICUREZZA - AZIONE RICHIESTA:</strong><br>
            ⚠️ <strong>ELIMINA QUESTO FILE IMMEDIATAMENTE</strong> dopo aver letto i risultati!<br>
            <br>
            Questo file espone informazioni sensibili sulla configurazione del server.<br>
            <strong>Nome file da eliminare:</strong> <code>check-redis.php</code>
        </div>

        <div class="info-box">
            <strong>📖 Guida completa:</strong> Consulta il file <code>IONOS_REDIS_SETUP_GUIDE.md</code> nella root del plugin per istruzioni dettagliate su configurazione, installazione manuale (VPS), e soluzioni alternative.
        </div>

        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 2px solid #e0e0e0; color: #666;">
            <p><strong>Script di verifica by FP Performance Suite</strong></p>
            <p>Per supporto tecnico sul plugin: <a href="mailto:support@example.com">support@example.com</a></p>
        </div>
    </div>
</body>
</html>
