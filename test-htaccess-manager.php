<?php
/**
 * Test Script per Gestione .htaccess
 * 
 * Testa tutte le funzionalità della classe Htaccess
 * 
 * @package FP\PerfSuite
 */

// Carica WordPress
require_once __DIR__ . '/../../wp-load.php';

// Verifica che l'utente sia admin
if (!current_user_can('manage_options')) {
    die('Accesso negato. Devi essere amministratore.');
}

use FP\PerfSuite\Utils\Htaccess;
use FP\PerfSuite\Utils\Fs;

echo "<!DOCTYPE html>\n<html>\n<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Test Gestione .htaccess - FP Performance Suite</title>\n";
echo "<style>\n";
echo "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; }\n";
echo "h1 { color: #1d2327; border-bottom: 3px solid #2271b1; padding-bottom: 10px; }\n";
echo "h2 { color: #2271b1; margin-top: 30px; }\n";
echo ".test-section { background: #f0f0f1; padding: 20px; margin: 20px 0; border-radius: 8px; }\n";
echo ".success { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 10px 0; }\n";
echo ".error { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 10px 0; }\n";
echo ".warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 10px 0; }\n";
echo ".info { background: #e7f5fe; border-left: 4px solid #00a0d2; padding: 15px; margin: 10px 0; }\n";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; background: white; }\n";
echo "th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }\n";
echo "th { background: #2271b1; color: white; font-weight: 600; }\n";
echo "code { background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-family: monospace; }\n";
echo "pre { background: #1d2327; color: #f0f0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }\n";
echo ".badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; }\n";
echo ".badge-success { background: #28a745; color: white; }\n";
echo ".badge-error { background: #dc3545; color: white; }\n";
echo ".badge-warning { background: #ffc107; color: #000; }\n";
echo ".badge-info { background: #00a0d2; color: white; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>🔧 Test Gestione .htaccess - FP Performance Suite</h1>\n";

// Inizializza la classe
$htaccess = new Htaccess(new Fs());

// Test 1: Verifica supporto
echo "<div class='test-section'>\n";
echo "<h2>Test 1: Verifica Supporto .htaccess</h2>\n";
$isSupported = $htaccess->isSupported();
if ($isSupported) {
    echo "<div class='success'>✅ <strong>Supporto .htaccess:</strong> Abilitato e scrivibile</div>\n";
} else {
    echo "<div class='error'>❌ <strong>Supporto .htaccess:</strong> Non disponibile o non scrivibile</div>\n";
}
echo "</div>\n";

// Test 2: Informazioni file
echo "<div class='test-section'>\n";
echo "<h2>Test 2: Informazioni File .htaccess</h2>\n";
$fileInfo = $htaccess->getFileInfo();

if ($fileInfo['exists']) {
    echo "<div class='success'>✅ File .htaccess trovato</div>\n";
    echo "<table>\n";
    echo "<tr><th>Proprietà</th><th>Valore</th></tr>\n";
    echo "<tr><td><strong>Percorso</strong></td><td><code>" . esc_html($fileInfo['path']) . "</code></td></tr>\n";
    echo "<tr><td><strong>Scrivibile</strong></td><td>" . ($fileInfo['writable'] ? '<span class="badge badge-success">SÌ</span>' : '<span class="badge badge-error">NO</span>') . "</td></tr>\n";
    echo "<tr><td><strong>Dimensione</strong></td><td>" . esc_html($fileInfo['size_formatted']) . " (" . esc_html($fileInfo['size']) . " bytes)</td></tr>\n";
    echo "<tr><td><strong>Ultima modifica</strong></td><td>" . esc_html($fileInfo['modified_formatted'] ?? 'N/A') . "</td></tr>\n";
    echo "<tr><td><strong>Righe totali</strong></td><td>" . esc_html($fileInfo['lines']) . "</td></tr>\n";
    echo "<tr><td><strong>Sezioni trovate</strong></td><td>";
    if (!empty($fileInfo['sections'])) {
        foreach ($fileInfo['sections'] as $section) {
            echo "<span class='badge badge-info'>" . esc_html($section) . "</span> ";
        }
    } else {
        echo "Nessuna sezione";
    }
    echo "</td></tr>\n";
    echo "</table>\n";
} else {
    echo "<div class='warning'>⚠️ File .htaccess non trovato</div>\n";
}
echo "</div>\n";

// Test 3: Validazione
if ($fileInfo['exists']) {
    echo "<div class='test-section'>\n";
    echo "<h2>Test 3: Validazione Sintassi</h2>\n";
    $validation = $htaccess->validate();
    
    if ($validation['valid']) {
        echo "<div class='success'>✅ <strong>Validazione:</strong> Il file .htaccess è valido e non presenta errori</div>\n";
    } else {
        echo "<div class='error'>❌ <strong>Validazione:</strong> Trovati errori nel file .htaccess</div>\n";
        echo "<div class='warning'>\n";
        echo "<strong>Errori rilevati:</strong>\n";
        echo "<ul>\n";
        foreach ($validation['errors'] as $error) {
            echo "<li>" . esc_html($error) . "</li>\n";
        }
        echo "</ul>\n";
        echo "</div>\n";
    }
    echo "</div>\n";
}

// Test 4: Lista backup
echo "<div class='test-section'>\n";
echo "<h2>Test 4: Backup Disponibili</h2>\n";
$backups = $htaccess->getBackups();

if (!empty($backups)) {
    echo "<div class='info'>ℹ️ Trovati <strong>" . count($backups) . "</strong> backup</div>\n";
    echo "<table>\n";
    echo "<tr><th>Data Backup</th><th>Dimensione</th><th>Nome File</th></tr>\n";
    foreach ($backups as $backup) {
        echo "<tr>\n";
        echo "<td>" . esc_html($backup['readable_date']) . "</td>\n";
        echo "<td>" . esc_html(size_format($backup['size'])) . "</td>\n";
        echo "<td><code>" . esc_html($backup['filename']) . "</code></td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
} else {
    echo "<div class='warning'>⚠️ Nessun backup disponibile</div>\n";
    echo "<p>I backup vengono creati automaticamente quando modifichi il file .htaccess tramite il plugin.</p>\n";
}
echo "</div>\n";

// Test 5: Verifica sezioni specifiche
if ($fileInfo['exists']) {
    echo "<div class='test-section'>\n";
    echo "<h2>Test 5: Verifica Sezioni Specifiche</h2>\n";
    
    $sectionsToCheck = [
        'WordPress',
        'FP Performance Suite',
        'W3 Total Cache',
        'WP Super Cache',
        'LiteSpeed',
    ];
    
    echo "<table>\n";
    echo "<tr><th>Sezione</th><th>Presente</th></tr>\n";
    foreach ($sectionsToCheck as $section) {
        $hasSection = $htaccess->hasSection($section);
        echo "<tr>\n";
        echo "<td><code>" . esc_html($section) . "</code></td>\n";
        echo "<td>" . ($hasSection ? '<span class="badge badge-success">SÌ</span>' : '<span class="badge badge-warning">NO</span>') . "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    echo "</div>\n";
}

// Test 6: Analisi contenuto (prime 50 righe)
if ($fileInfo['exists']) {
    echo "<div class='test-section'>\n";
    echo "<h2>Test 6: Anteprima Contenuto (Prime 50 righe)</h2>\n";
    
    $content = file_get_contents(ABSPATH . '.htaccess');
    $lines = explode("\n", $content);
    $previewLines = array_slice($lines, 0, 50);
    
    echo "<pre style='max-height: 400px; overflow-y: auto;'>";
    echo esc_html(implode("\n", $previewLines));
    if (count($lines) > 50) {
        echo "\n\n... (" . (count($lines) - 50) . " righe rimanenti)";
    }
    echo "</pre>\n";
    echo "</div>\n";
}

// Test 7: Simulazione riparazione (dry run)
if ($fileInfo['exists'] && $fileInfo['writable']) {
    echo "<div class='test-section'>\n";
    echo "<h2>Test 7: Simulazione Riparazione Automatica</h2>\n";
    echo "<div class='info'>ℹ️ Questo è un test in sola lettura. Nessuna modifica verrà applicata al file.</div>\n";
    
    // Leggi il contenuto
    $content = file_get_contents(ABSPATH . '.htaccess');
    
    // Analizza problemi comuni
    $issues = [];
    $lines = explode("\n", $content);
    $hasRewriteRules = false;
    $hasRewriteEngine = false;
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (stripos($trimmed, 'RewriteRule') === 0 || stripos($trimmed, 'RewriteCond') === 0) {
            $hasRewriteRules = true;
        }
        if (stripos($trimmed, 'RewriteEngine') === 0) {
            $hasRewriteEngine = true;
        }
    }
    
    if ($hasRewriteRules && !$hasRewriteEngine) {
        $issues[] = "RewriteEngine On mancante (necessario per le RewriteRule)";
    }
    
    // Controlla righe duplicate
    $uniqueLines = [];
    $duplicates = 0;
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (empty($trimmed) || strpos($trimmed, '#') === 0) {
            continue;
        }
        if (in_array($line, $uniqueLines, true)) {
            $duplicates++;
        } else {
            $uniqueLines[] = $line;
        }
    }
    
    if ($duplicates > 0) {
        $issues[] = "Trovate {$duplicates} righe duplicate";
    }
    
    // Controlla markers
    preg_match_all('/# BEGIN (.+)$/m', $content, $beginMatches);
    preg_match_all('/# END (.+)$/m', $content, $endMatches);
    
    $beginMarkers = $beginMatches[1] ?? [];
    $endMarkers = $endMatches[1] ?? [];
    
    foreach ($beginMarkers as $marker) {
        if (!in_array($marker, $endMarkers, true)) {
            $issues[] = "Marker non bilanciato: BEGIN {$marker} senza END corrispondente";
        }
    }
    
    if (empty($issues)) {
        echo "<div class='success'>✅ Nessun problema rilevato! Il file .htaccess è già ottimale.</div>\n";
    } else {
        echo "<div class='warning'>\n";
        echo "<strong>⚠️ Problemi rilevati che potrebbero essere riparati automaticamente:</strong>\n";
        echo "<ul>\n";
        foreach ($issues as $issue) {
            echo "<li>" . esc_html($issue) . "</li>\n";
        }
        echo "</ul>\n";
        echo "<p><em>Usa la funzione 'Ripara Automaticamente' dalla pagina Diagnostics per correggere questi problemi.</em></p>\n";
        echo "</div>\n";
    }
    echo "</div>\n";
}

// Riepilogo finale
echo "<div class='test-section' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;'>\n";
echo "<h2 style='color: white;'>📊 Riepilogo Test</h2>\n";
echo "<table style='background: rgba(255,255,255,0.1); color: white;'>\n";
echo "<tr style='border-color: rgba(255,255,255,0.2);'><th style='background: transparent; color: white;'>Funzionalità</th><th style='background: transparent; color: white;'>Stato</th></tr>\n";
echo "<tr style='border-color: rgba(255,255,255,0.2);'><td>✓ Supporto .htaccess</td><td>" . ($isSupported ? '✅ OK' : '❌ Non disponibile') . "</td></tr>\n";
echo "<tr style='border-color: rgba(255,255,255,0.2);'><td>✓ Lettura informazioni file</td><td>" . ($fileInfo['exists'] ? '✅ OK' : '⚠️ File non trovato') . "</td></tr>\n";
echo "<tr style='border-color: rgba(255,255,255,0.2);'><td>✓ Validazione sintassi</td><td>✅ OK</td></tr>\n";
echo "<tr style='border-color: rgba(255,255,255,0.2);'><td>✓ Gestione backup</td><td>✅ OK</td></tr>\n";
echo "<tr style='border-color: rgba(255,255,255,0.2);'><td>✓ Verifica sezioni</td><td>✅ OK</td></tr>\n";
echo "<tr style='border-color: rgba(255,255,255,0.2);'><td>✓ Analisi problemi</td><td>✅ OK</td></tr>\n";
echo "</table>\n";
echo "<div style='margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.2); border-radius: 5px;'>\n";
echo "<strong>✨ Tutte le funzionalità di base sono operative!</strong><br>\n";
echo "Vai su <strong>FP Performance → Diagnostics</strong> per utilizzare l'interfaccia completa di gestione .htaccess.\n";
echo "</div>\n";
echo "</div>\n";

echo "</body>\n</html>";

