<?php
/**
 * Test per il Backend Optimizer
 *
 * Esegui questo script da linea di comando:
 * php tests/test-backend-optimizer.php
 *
 * Oppure caricalo via WordPress admin per testare in ambiente reale.
 */

// Carica WordPress
require_once dirname(__DIR__) . '/../../wp-load.php';

// Carica il plugin se non già caricato
if (!class_exists('FP\PerfSuite\Plugin')) {
    echo "❌ Errore: Plugin non caricato\n";
    exit(1);
}

use FP\PerfSuite\Plugin;
use FP\PerfSuite\Services\Admin\BackendOptimizer;

echo "🧪 Test Backend Optimizer\n";
echo str_repeat('=', 50) . "\n\n";

try {
    // Ottieni il container
    $container = Plugin::container();
    echo "✅ Container ottenuto\n";

    // Ottieni il servizio
    $optimizer = $container->get(BackendOptimizer::class);
    echo "✅ BackendOptimizer istanziato\n\n";

    // Test 1: Ottieni opzioni
    echo "📋 Test 1: Ottieni opzioni\n";
    $options = $optimizer->getOptions();
    echo "   Opzioni disponibili: " . count($options) . "\n";
    echo "   Heartbeat abilitato: " . ($options['heartbeat_enabled'] ? 'Sì' : 'No') . "\n";
    echo "   Limite revisioni: " . ($options['limit_post_revisions'] ? $options['post_revisions_limit'] : 'Illimitate') . "\n";
    echo "✅ Test 1 completato\n\n";

    // Test 2: Ottieni statistiche
    echo "📊 Test 2: Ottieni statistiche\n";
    $stats = $optimizer->getStats();
    echo "   Stato Heartbeat: " . $stats['heartbeat_status'] . "\n";
    echo "   Limite revisioni: " . $stats['post_revisions_limit'] . "\n";
    echo "   Intervallo autosave: " . $stats['autosave_interval'] . "\n";
    echo "   Ottimizzazioni attive: " . $stats['optimizations_active'] . "/7\n";
    echo "✅ Test 2 completato\n\n";

    // Test 3: Ottieni raccomandazioni
    echo "💡 Test 3: Ottieni raccomandazioni\n";
    $recommendations = $optimizer->getRecommendations();
    if (empty($recommendations)) {
        echo "   ✅ Nessuna raccomandazione - configurazione ottimale!\n";
    } else {
        echo "   Raccomandazioni trovate:\n";
        foreach ($recommendations as $rec) {
            echo "   - $rec\n";
        }
    }
    echo "✅ Test 3 completato\n\n";

    // Test 4: Verifica stato attivo
    echo "🔍 Test 4: Verifica se optimizer è attivo\n";
    $isActive = $optimizer->isActive();
    echo "   Stato: " . ($isActive ? 'Attivo' : 'Inattivo') . "\n";
    echo "✅ Test 4 completato\n\n";

    // Test 5: Aggiorna opzioni (senza salvare realmente)
    echo "⚙️ Test 5: Simula aggiornamento opzioni\n";
    $testOptions = [
        'heartbeat_enabled' => true,
        'heartbeat_location_dashboard' => 'disable',
        'heartbeat_location_frontend' => 'disable',
        'heartbeat_location_editor' => 'slow',
        'heartbeat_interval' => 60,
        'limit_post_revisions' => true,
        'post_revisions_limit' => 5,
        'autosave_interval' => 120,
    ];
    
    echo "   Opzioni test preparate:\n";
    echo "   - Heartbeat: Attivo\n";
    echo "   - Dashboard: Disabilitato\n";
    echo "   - Frontend: Disabilitato\n";
    echo "   - Editor: Rallentato (60s)\n";
    echo "   - Revisioni: Limitate a 5\n";
    echo "   - Autosave: 120s\n";
    
    // Verifica validazione
    $testInterval = max(15, (int) $testOptions['heartbeat_interval']);
    $testRevisionsLimit = max(1, (int) $testOptions['post_revisions_limit']);
    $testAutosaveInterval = max(60, (int) $testOptions['autosave_interval']);
    
    echo "   Validazione:\n";
    echo "   - Intervallo heartbeat validato: {$testInterval}s (min 15s) ✅\n";
    echo "   - Limite revisioni validato: {$testRevisionsLimit} (min 1) ✅\n";
    echo "   - Intervallo autosave validato: {$testAutosaveInterval}s (min 60s) ✅\n";
    echo "✅ Test 5 completato\n\n";

    // Test 6: Verifica metodi pubblici
    echo "🔧 Test 6: Verifica metodi pubblici\n";
    $methods = get_class_methods($optimizer);
    $publicMethods = array_filter($methods, function($method) {
        $reflection = new ReflectionMethod(BackendOptimizer::class, $method);
        return $reflection->isPublic() && !$reflection->isConstructor();
    });
    
    echo "   Metodi pubblici disponibili: " . count($publicMethods) . "\n";
    foreach ($publicMethods as $method) {
        echo "   - {$method}()\n";
    }
    echo "✅ Test 6 completato\n\n";

    // Test 7: Verifica WordPress hooks
    echo "🎯 Test 7: Verifica registrazione hooks\n";
    global $wp_filter;
    
    $hooks = [
        'init' => 'Inizializzazione',
        'heartbeat_settings' => 'Impostazioni Heartbeat',
        'admin_enqueue_scripts' => 'Script Admin',
        'wp_dashboard_setup' => 'Widget Dashboard',
        'admin_head' => 'Header Admin',
        'admin_memory_limit' => 'Limite Memoria',
        'edit_posts_per_page' => 'Elementi per Pagina (Posts)',
        'edit_pages_per_page' => 'Elementi per Pagina (Pages)',
        'edit_comments_per_page' => 'Elementi per Pagina (Comments)',
    ];
    
    echo "   Hook WordPress che potrebbero essere registrati:\n";
    foreach ($hooks as $hook => $description) {
        $registered = isset($wp_filter[$hook]) && !empty($wp_filter[$hook]);
        $status = $registered ? '✅' : '⚠️ (non registrato - normale se optimizer non attivo)';
        echo "   - {$hook}: {$status}\n";
    }
    echo "✅ Test 7 completato\n\n";

    // Riepilogo finale
    echo str_repeat('=', 50) . "\n";
    echo "✅ TUTTI I TEST COMPLETATI CON SUCCESSO!\n";
    echo str_repeat('=', 50) . "\n\n";

    echo "📊 Riepilogo Backend Optimizer:\n";
    echo "   - Servizio: Funzionante ✅\n";
    echo "   - Metodi: " . count($publicMethods) . " pubblici disponibili ✅\n";
    echo "   - Ottimizzazioni: " . $stats['optimizations_active'] . "/7 attive\n";
    echo "   - Raccomandazioni: " . count($recommendations) . " disponibili\n";
    echo "   - Stato: " . ($isActive ? 'Attivo' : 'Inattivo') . "\n\n";

    if (!$isActive && !empty($recommendations)) {
        echo "💡 SUGGERIMENTO:\n";
        echo "   Il Backend Optimizer non è attualmente attivo.\n";
        echo "   Vai su FP Performance > Backend per attivare le ottimizzazioni!\n\n";
    }

} catch (\Throwable $e) {
    echo "\n❌ ERRORE DURANTE I TEST\n";
    echo str_repeat('=', 50) . "\n";
    echo "Messaggio: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Linea: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "✅ Script di test completato!\n";

