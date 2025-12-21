<?php
/**
 * Script Diagnostica Page Cache
 * 
 * Verifica perché la cache è vuota e fornisce soluzioni
 * 
 * URL: https://tuosito.com/wp-content/plugins/FP-Performance/DIAGNOSTICA-CACHE.php
 */

if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

if (!current_user_can('manage_options')) {
    die('Accesso negato. Devi essere amministratore.');
}

// Carica il servizio PageCache
$container = \FP\PerfSuite\Plugin::container();
$pageCache = $container->get(\FP\PerfSuite\Services\Cache\PageCache::class);

$settings = $pageCache->settings();
$status = $pageCache->status();
$cacheDir = $status['cache_dir'];

// Diagnostica
$diagnostics = [];

// 1. Verifica se la cache è abilitata
$diagnostics['enabled'] = [
    'label' => 'Cache Abilitata',
    'value' => $status['enabled'] ? '✅ Sì' : '❌ No',
    'status' => $status['enabled'] ? 'ok' : 'error',
    'fix' => $status['enabled'] ? null : 'Vai in FP Performance → Cache → Page Cache e abilita la cache'
];

// 2. Verifica directory
$diagnostics['dir_exists'] = [
    'label' => 'Directory Cache Esiste',
    'value' => $status['dir_exists'] ? '✅ Sì' : '❌ No',
    'status' => $status['dir_exists'] ? 'ok' : 'error',
    'fix' => $status['dir_exists'] ? null : "Crea la directory: <code>$cacheDir</code>"
];

// 3. Verifica permessi scrittura
$diagnostics['dir_writable'] = [
    'label' => 'Directory Scrivibile',
    'value' => $status['dir_writable'] ? '✅ Sì' : '❌ No',
    'status' => $status['dir_writable'] ? 'ok' : 'error',
    'fix' => $status['dir_writable'] ? null : "Imposta permessi 755 o 775 sulla directory: <code>$cacheDir</code>"
];

// 4. Conta file
$fileCount = $status['files'];
$diagnostics['files'] = [
    'label' => 'File in Cache',
    'value' => number_format($fileCount),
    'status' => $fileCount > 0 ? 'ok' : 'warning',
    'fix' => $fileCount === 0 ? 'Vedi sezione "Perché la cache è vuota?" sotto' : null
];

// 5. Verifica se il servizio è registrato
$diagnostics['service_registered'] = [
    'label' => 'Servizio Registrato',
    'value' => $container->has(\FP\PerfSuite\Services\Cache\PageCache::class) ? '✅ Sì' : '❌ No',
    'status' => $container->has(\FP\PerfSuite\Services\Cache\PageCache::class) ? 'ok' : 'error',
    'fix' => $container->has(\FP\PerfSuite\Services\Cache\PageCache::class) ? null : 'Il servizio non è registrato. Controlla i log.'
];

// 6. Verifica TTL
$ttl = $settings['ttl'] ?? 3600;
$diagnostics['ttl'] = [
    'label' => 'TTL (Tempo di Vita)',
    'value' => number_format($ttl) . ' secondi (' . round($ttl / 60) . ' minuti)',
    'status' => $ttl > 0 ? 'ok' : 'error',
    'fix' => $ttl <= 0 ? 'Imposta un TTL maggiore di 0 (consigliato: 3600 secondi)' : null
];

// 7. Lista file nella directory (se esiste)
$filesList = [];
if ($status['dir_exists'] && is_readable($cacheDir)) {
    try {
        $files = glob($cacheDir . '/*.cache');
        if ($files !== false) {
            foreach (array_slice($files, 0, 10) as $file) {
                $filesList[] = [
                    'name' => basename($file),
                    'size' => filesize($file),
                    'modified' => filemtime($file),
                ];
            }
        }
    } catch (\Exception $e) {
        $filesList = ['error' => $e->getMessage()];
    }
}

// Output HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>FP Performance - Diagnostica Cache</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #1e293b; border-bottom: 3px solid #3b82f6; padding-bottom: 10px; }
        h2 { color: #334155; margin-top: 30px; }
        .diagnostic { margin: 15px 0; padding: 15px; border-radius: 6px; border-left: 4px solid; }
        .diagnostic.ok { background: #d1fae5; border-color: #10b981; }
        .diagnostic.warning { background: #fef3c7; border-color: #f59e0b; }
        .diagnostic.error { background: #fee2e2; border-color: #ef4444; }
        .diagnostic-label { font-weight: 600; color: #1e293b; margin-bottom: 5px; }
        .diagnostic-value { font-size: 18px; color: #475569; }
        .diagnostic-fix { margin-top: 10px; padding: 10px; background: rgba(0,0,0,0.05); border-radius: 4px; font-size: 14px; }
        code { background: #f1f5f9; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; color: #475569; }
        .info-box { background: #e0f2fe; border-left: 4px solid #0ea5e9; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .warning-box { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .btn { display: inline-block; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px 0 0; }
        .btn:hover { background: #2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostica Page Cache - FP Performance</h1>
        
        <div class="info-box">
            <strong>📋 Informazioni Generali</strong>
            <ul style="margin: 10px 0 0 20px;">
                <li><strong>Directory Cache:</strong> <code><?php echo esc_html($cacheDir); ?></code></li>
                <li><strong>File in Cache:</strong> <strong><?php echo number_format($fileCount); ?></strong></li>
                <li><strong>TTL:</strong> <?php echo number_format($ttl); ?> secondi</li>
            </ul>
        </div>
        
        <h2>📊 Risultati Diagnostica</h2>
        <?php foreach ($diagnostics as $key => $diag): ?>
            <div class="diagnostic <?php echo esc_attr($diag['status']); ?>">
                <div class="diagnostic-label"><?php echo esc_html($diag['label']); ?></div>
                <div class="diagnostic-value"><?php echo $diag['value']; ?></div>
                <?php if (!empty($diag['fix'])): ?>
                    <div class="diagnostic-fix">
                        <strong>🔧 Soluzione:</strong> <?php echo $diag['fix']; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <?php if ($fileCount === 0 && $status['enabled'] && $status['dir_exists'] && $status['dir_writable']): ?>
            <div class="warning-box">
                <h2>❓ Perché la cache è vuota?</h2>
                <p>La cache è abilitata e la directory è scrivibile, ma non ci sono file. Questo può accadere perché:</p>
                <ol style="margin: 10px 0 0 20px; line-height: 1.8;">
                    <li><strong>Nessun visitatore anonimo ha visitato il sito</strong><br>
                        La cache viene generata solo quando utenti <strong>non loggati</strong> visitano pagine frontend.</li>
                    <li><strong>Le pagine sono state visitate solo da utenti loggati</strong><br>
                        Se sei loggato come admin, le pagine non vengono cachate per sicurezza.</li>
                    <li><strong>La cache è stata appena pulita</strong><br>
                        Se hai cliccato "Clear Cache", tutti i file sono stati eliminati.</li>
                    <li><strong>Le pagine sono escluse dalla cache</strong><br>
                        Controlla le esclusioni in Cache → Smart Exclusions.</li>
                </ol>
                
                <h3 style="margin-top: 20px;">✅ Come popolare la cache:</h3>
                <ol style="margin: 10px 0 0 20px; line-height: 1.8;">
                    <li><strong>Disconnettiti</strong> da WordPress (logout)</li>
                    <li><strong>Visita alcune pagine</strong> del sito in modalità incognito o da un browser diverso</li>
                    <li><strong>Attendi qualche secondo</strong> e ricarica questa pagina diagnostica</li>
                    <li><strong>Oppure usa questo script</strong> per forzare la generazione della cache (vedi sotto)</li>
                </ol>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($filesList) && !isset($filesList['error'])): ?>
            <h2>📁 File in Cache (primi 10)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nome File</th>
                        <th>Dimensione</th>
                        <th>Ultima Modifica</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filesList as $file): ?>
                        <tr>
                            <td><code><?php echo esc_html($file['name']); ?></code></td>
                            <td><?php echo size_format($file['size']); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', $file['modified']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e2e8f0;">
            <a href="<?php echo admin_url('admin.php?page=fp-performance-suite-cache'); ?>" class="btn">← Torna a Cache</a>
            <a href="<?php echo admin_url('admin.php?page=fp-performance-suite-overview'); ?>" class="btn">← Torna a Overview</a>
        </div>
        
        <div class="info-box" style="margin-top: 20px;">
            <strong>💡 Nota:</strong> Questo script diagnostico può essere eliminato dopo l'uso per sicurezza.
        </div>
    </div>
</body>
</html>

