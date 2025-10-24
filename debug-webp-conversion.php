<?php
/**
 * Debug script per verificare il funzionamento della conversione WebP
 * 
 * Questo script può essere eseguito da WordPress per verificare
 * il funzionamento del sistema di conversione WebP.
 */

// Abilita logging dettagliato
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Abilita logging WordPress
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}
if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', true);
}

echo "=== DEBUG CONVERSIONE WEBP ===\n\n";

// Verifica che il plugin sia attivo
if (!class_exists('FP\PerfSuite\Services\Media\WebPConverter')) {
    die('Plugin FP Performance Suite non trovato o non attivo');
}

use FP\PerfSuite\Services\Media\WebPConverter;
use FP\PerfSuite\Services\Media\WebP\WebPQueue;

try {
    // 1. Test conteggio immagini
    echo "1. Test conteggio immagini...\n";
    $converter = new WebPConverter();
    $status = $converter->status();
    echo "Totale immagini: " . $status['total_images'] . "\n";
    echo "Immagini convertite: " . $status['converted_images'] . "\n";
    echo "Copertura WebP: " . $status['coverage'] . "%\n\n";

    // 2. Test query per immagini da convertire
    echo "2. Test query per immagini da convertire...\n";
    $queue = new WebPQueue();
    
    // Test diretto della query
    $query = new WP_Query([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'post_mime_type' => ['image/jpeg', 'image/png'],
        'posts_per_page' => 1,
        'offset' => 0,
        'fields' => 'ids',
        'no_found_rows' => false,
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => '_fp_ps_webp_generated',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key' => '_fp_ps_webp_generated',
                'value' => '1',
                'compare' => '!=',
            ],
        ],
    ]);
    
    $total = (int) $query->found_posts;
    echo "Immagini da convertire (query diretta): $total\n";
    echo "Query SQL: " . $query->request . "\n\n";

    // 3. Test inizializzazione conversione bulk
    echo "3. Test inizializzazione conversione bulk...\n";
    $result = $converter->bulkConvert(5, 0);
    echo "Risultato bulk convert: " . print_r($result, true) . "\n";

    // 4. Test stato coda dopo inizializzazione
    echo "4. Test stato coda dopo inizializzazione...\n";
    $state = $queue->getState();
    if ($state) {
        echo "Stato coda attivo: SÌ\n";
        echo "Processati: " . $state['processed'] . "\n";
        echo "Convertiti: " . $state['converted'] . "\n";
        echo "Totale: " . $state['total'] . "\n";
    } else {
        echo "Stato coda attivo: NO\n";
    }

    // 5. Test processing batch
    echo "\n5. Test processing batch...\n";
    $converter->runQueue();
    $newState = $queue->getState();
    if ($newState) {
        echo "Nuovo stato dopo processing: " . print_r($newState, true) . "\n";
    } else {
        echo "Coda completata o non attiva\n";
    }

    // 6. Test finale
    echo "\n6. Test finale...\n";
    $finalStatus = $converter->status();
    echo "Stato finale:\n";
    echo "- Totale immagini: " . $finalStatus['total_images'] . "\n";
    echo "- Immagini convertite: " . $finalStatus['converted_images'] . "\n";
    echo "- Copertura WebP: " . $finalStatus['coverage'] . "%\n";

} catch (Exception $e) {
    echo "ERRORE: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FINE DEBUG ===\n";
