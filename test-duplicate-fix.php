<?php
/**
 * Script di test per verificare la correzione dei duplicati
 * 
 * Uso:
 * 1. Caricare su WordPress
 * 2. Accedere via browser: /test-duplicate-fix.php
 * 3. Verificare l'output
 */

// Carica WordPress
require_once __DIR__ . '/../../wp-load.php';

// Verifica autenticazione
if (!current_user_can('manage_options')) {
    wp_die('Non hai i permessi per accedere a questa pagina.');
}

echo '<h1>Test Correzione Duplicati - FP Performance Suite</h1>';
echo '<hr>';

// Verifica la situazione attuale
$trackedExclusions = get_option('fp_ps_tracked_exclusions', []);

echo '<h2>📊 Situazione Attuale</h2>';
echo '<p><strong>Totale esclusioni:</strong> ' . count($trackedExclusions) . '</p>';

// Raggruppa per URL
$urlGroups = [];
foreach ($trackedExclusions as $id => $exclusion) {
    $url = $exclusion['url'];
    if (!isset($urlGroups[$url])) {
        $urlGroups[$url] = [];
    }
    $urlGroups[$url][$id] = $exclusion;
}

// Trova duplicati
$duplicates = array_filter($urlGroups, function($group) {
    return count($group) > 1;
});

echo '<p><strong>URL unici:</strong> ' . count($urlGroups) . '</p>';
echo '<p><strong>URL con duplicati:</strong> ' . count($duplicates) . '</p>';

if (!empty($duplicates)) {
    echo '<h3>⚠️ Duplicati Trovati:</h3>';
    echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
    echo '<tr><th>URL</th><th>Numero Copie</th><th>Date Applicazione</th></tr>';
    
    foreach ($duplicates as $url => $group) {
        $dates = array_map(function($ex) {
            return date('Y-m-d H:i:s', $ex['applied_at']);
        }, $group);
        
        echo '<tr>';
        echo '<td><code>' . esc_html($url) . '</code></td>';
        echo '<td style="text-align: center; color: red; font-weight: bold;">' . count($group) . '</td>';
        echo '<td><small>' . implode('<br>', array_map('esc_html', $dates)) . '</small></td>';
        echo '</tr>';
    }
    
    echo '</table>';
} else {
    echo '<p style="color: green; font-weight: bold;">✅ Nessun duplicato trovato!</p>';
}

// Test del metodo di pulizia (dry run)
if (!empty($duplicates)) {
    echo '<hr>';
    echo '<h2>🧪 Test Pulizia (Simulazione)</h2>';
    
    $toRemove = 0;
    foreach ($duplicates as $url => $group) {
        $toRemove += count($group) - 1;
    }
    
    echo '<p><strong>Duplicati da rimuovere:</strong> ' . $toRemove . '</p>';
    echo '<p><strong>Esclusioni finali:</strong> ' . (count($trackedExclusions) - $toRemove) . '</p>';
    
    echo '<hr>';
    echo '<h3>🔧 Per ripulire i duplicati:</h3>';
    echo '<ol>';
    echo '<li>Vai su <strong>FP Performance → Exclusions</strong></li>';
    echo '<li>Clicca sul pulsante <strong>"🧹 Ripulisci Duplicati"</strong></li>';
    echo '<li>Conferma l\'operazione</li>';
    echo '</ol>';
}

// Verifica anche la cache page
echo '<hr>';
echo '<h2>📦 Configurazione Cache Page</h2>';
$settings = get_option('fp_ps_page_cache', []);
$currentExclusions = $settings['exclude_urls'] ?? '';
$exclusionsList = array_filter(explode("\n", $currentExclusions));

echo '<p><strong>Esclusioni in cache page:</strong> ' . count($exclusionsList) . '</p>';

$cacheDuplicates = count($exclusionsList) - count(array_unique($exclusionsList));
if ($cacheDuplicates > 0) {
    echo '<p style="color: red; font-weight: bold;">⚠️ Duplicati trovati in cache page: ' . $cacheDuplicates . '</p>';
} else {
    echo '<p style="color: green; font-weight: bold;">✅ Nessun duplicato in cache page</p>';
}

echo '<hr>';
echo '<p><em>Test completato. Puoi chiudere questa pagina.</em></p>';
