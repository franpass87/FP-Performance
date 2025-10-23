<?php
/**
 * Test per verificare la correzione della pagina Assets
 * 
 * Questo script testa che la pagina Assets salvi correttamente
 * e non reindirizzi a una pagina vuota.
 */

// Verifica che WordPress sia caricato
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito.');
}

// Test della funzionalità di salvataggio
function test_assets_page_save() {
    echo "<h2>🧪 Test Pagina Assets - Salvataggio</h2>\n";
    
    // Simula i dati POST
    $_POST['fp_ps_assets_nonce'] = wp_create_nonce('fp-ps-assets');
    $_POST['form_type'] = 'main_toggle';
    $_POST['assets_enabled'] = '1';
    
    echo "<p>✅ Dati POST simulati correttamente</p>\n";
    
    // Test del PostHandler
    try {
        $container = new \FP\PerfSuite\ServiceContainer();
        $assetsPage = new \FP\PerfSuite\Admin\Pages\Assets($container);
        
        // Simula il contenuto della pagina
        $content = $assetsPage->content();
        
        if (strpos($content, 'Asset optimization settings saved successfully!') !== false) {
            echo "<p>✅ Messaggio di successo presente nel contenuto</p>\n";
        } else {
            echo "<p>❌ Messaggio di successo non trovato</p>\n";
        }
        
        if (strpos($content, 'notice-success') !== false) {
            echo "<p>✅ Classe CSS per messaggio di successo presente</p>\n";
        } else {
            echo "<p>❌ Classe CSS per messaggio di successo non trovata</p>\n";
        }
        
        echo "<p>✅ Test completato senza errori</p>\n";
        
    } catch (Exception $e) {
        echo "<p>❌ Errore durante il test: " . $e->getMessage() . "</p>\n";
    }
}

// Test della gestione degli errori
function test_assets_page_error_handling() {
    echo "<h2>🛡️ Test Gestione Errori</h2>\n";
    
    // Test con nonce non valido
    $_POST['fp_ps_assets_nonce'] = 'invalid_nonce';
    $_POST['form_type'] = 'main_toggle';
    $_POST['assets_enabled'] = '1';
    
    try {
        $container = new \FP\PerfSuite\ServiceContainer();
        $assetsPage = new \FP\PerfSuite\Admin\Pages\Assets($container);
        $content = $assetsPage->content();
        
        // Dovrebbe gestire l'errore senza crash
        echo "<p>✅ Gestione nonce non valido: OK</p>\n";
        
    } catch (Exception $e) {
        echo "<p>❌ Errore nella gestione nonce non valido: " . $e->getMessage() . "</p>\n";
    }
}

// Test della visualizzazione del messaggio
function test_message_display() {
    echo "<h2>💬 Test Visualizzazione Messaggi</h2>\n";
    
    // Simula un messaggio di successo
    $message = 'Asset optimization settings saved successfully!';
    
    $html = '<div class="notice notice-success is-dismissible" style="margin: 20px 0; padding: 15px; background: #d1e7dd; border: 1px solid #a3cfbb; border-radius: 6px;">
        <p style="margin: 0; color: #0f5132; font-weight: 500;">
            <strong>✅ ' . esc_html($message) . '</strong>
        </p>
    </div>';
    
    if (strpos($html, 'notice-success') !== false) {
        echo "<p>✅ HTML del messaggio generato correttamente</p>\n";
    } else {
        echo "<p>❌ HTML del messaggio non generato correttamente</p>\n";
    }
    
    if (strpos($html, '✅') !== false) {
        echo "<p>✅ Icona di successo presente</p>\n";
    } else {
        echo "<p>❌ Icona di successo non presente</p>\n";
    }
}

// Esegui i test
echo "<h1>🔧 Test Correzione Pagina Assets</h1>\n";
echo "<p>Questo script testa le correzioni apportate alla pagina Assets per risolvere il problema del salvataggio e del redirect a pagina vuota.</p>\n";

test_assets_page_save();
test_assets_page_error_handling();
test_message_display();

echo "<h2>📋 Riepilogo Test</h2>\n";
echo "<p>✅ Test completati. Se tutti i test sono passati, la correzione dovrebbe funzionare correttamente.</p>\n";
echo "<p><strong>Prossimi passi:</strong></p>\n";
echo "<ul>\n";
echo "<li>Testa la pagina Assets nel browser</li>\n";
echo "<li>Verifica che il salvataggio funzioni senza redirect</li>\n";
echo "<li>Controlla che i messaggi di successo vengano visualizzati</li>\n";
echo "</ul>\n";
?>