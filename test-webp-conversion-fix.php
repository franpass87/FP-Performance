<?php
/**
 * Test per verificare il fix della conversione WebP
 * 
 * Questo script testa il sistema di conversione WebP per identificare
 * e risolvere il problema delle immagini che non vengono convertite.
 */

// Carica WordPress
require_once('wp-config.php');

// Verifica che il plugin sia attivo
if (!class_exists('FP\PerfSuite\Services\Media\WebPConverter')) {
    die('Plugin FP Performance Suite non trovato o non attivo');
}

use FP\PerfSuite\Services\Media\WebPConverter;
use FP\PerfSuite\Services\Media\WebP\WebPQueue;
use FP\PerfSuite\Services\Media\WebP\WebPBatchProcessor;
use FP\PerfSuite\Services\Media\WebP\WebPAttachmentProcessor;
use FP\PerfSuite\Services\Media\WebP\WebPImageConverter;
use FP\PerfSuite\Services\Media\WebP\WebPPathHelper;

echo "=== TEST CONVERSIONE WEBP ===\n\n";

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
$total = $queue->countQueuedImages(0, 20);
echo "Immagini da convertire (query diretta): $total\n\n";

// 3. Test batch processing
echo "3. Test batch processing...\n";
$batchProcessor = new WebPBatchProcessor($queue, new WebPAttachmentProcessor(new WebPImageConverter(), new WebPPathHelper()));
$settings = $converter->settings();
echo "Impostazioni WebP: " . print_r($settings, true) . "\n";

// 4. Test conversione singola
echo "4. Test conversione singola...\n";
$imageConverter = new WebPImageConverter();
$pathHelper = new WebPPathHelper();

// Trova una immagine di test
$attachments = get_posts([
    'post_type' => 'attachment',
    'post_mime_type' => ['image/jpeg', 'image/png'],
    'posts_per_page' => 1,
    'fields' => 'ids'
]);

if (!empty($attachments)) {
    $attachmentId = $attachments[0];
    $file = get_attached_file($attachmentId);
    
    if ($file && file_exists($file)) {
        echo "Testando conversione di: " . basename($file) . "\n";
        
        $webpFile = $pathHelper->getWebPPath($file);
        echo "File WebP target: " . basename($webpFile) . "\n";
        
        $converted = $imageConverter->convert($file, $webpFile, $settings, true);
        echo "Conversione riuscita: " . ($converted ? 'SÌ' : 'NO') . "\n";
        
        if (file_exists($webpFile)) {
            echo "File WebP creato: SÌ\n";
            echo "Dimensione file WebP: " . filesize($webpFile) . " bytes\n";
        } else {
            echo "File WebP creato: NO\n";
        }
    } else {
        echo "Nessuna immagine trovata per il test\n";
    }
} else {
    echo "Nessun attachment trovato\n";
}

// 5. Test stato coda
echo "\n5. Test stato coda...\n";
$state = $queue->getState();
if ($state) {
    echo "Stato coda attivo: SÌ\n";
    echo "Processati: " . $state['processed'] . "\n";
    echo "Convertiti: " . $state['converted'] . "\n";
    echo "Totale: " . $state['total'] . "\n";
} else {
    echo "Stato coda attivo: NO\n";
}

// 6. Test inizializzazione conversione bulk
echo "\n6. Test inizializzazione conversione bulk...\n";
$result = $converter->bulkConvert(5, 0);
echo "Risultato bulk convert: " . print_r($result, true) . "\n";

// 7. Test processing batch
echo "\n7. Test processing batch...\n";
$converter->runQueue();
$newState = $queue->getState();
if ($newState) {
    echo "Nuovo stato dopo processing: " . print_r($newState, true) . "\n";
} else {
    echo "Coda completata o non attiva\n";
}

echo "\n=== FINE TEST ===\n";
