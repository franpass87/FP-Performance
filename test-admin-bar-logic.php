<?php
/**
 * Test Logica Admin Bar Optimization
 * 
 * Verifica solo la logica delle ottimizzazioni senza dipendenze WordPress
 */

echo "=== TEST LOGICA OTTIMIZZAZIONI ADMIN BAR ===\n\n";

// Test 1: Verifica che i metodi esistano
echo "1. Test esistenza metodi...\n";

$methods = [
    'optimizeAdminBar' => 'Metodo principale ottimizzazione',
    'removeWordPressLogo' => 'Rimuovi logo WordPress',
    'removeUpdatesMenu' => 'Rimuovi menu aggiornamenti',
    'removeCommentsMenu' => 'Rimuovi menu commenti',
    'removeNewMenu' => 'Rimuovi menu + Nuovo',
    'removeCustomizeLink' => 'Rimuovi link Personalizza'
];

// Carica il file e verifica i metodi
$fileContent = file_get_contents('src/Services/Admin/BackendOptimizer.php');

$allMethodsExist = true;
foreach ($methods as $method => $description) {
    $hasMethod = strpos($fileContent, "function $method") !== false || 
                 strpos($fileContent, "public function $method") !== false ||
                 strpos($fileContent, "private function $method") !== false;
    echo "   ✓ Metodo '$method' ($description): " . ($hasMethod ? 'EXISTS' : 'MISSING') . "\n";
    if (!$hasMethod) {
        $allMethodsExist = false;
    }
}

echo "\n2. Test logica Admin Bar...\n";

// Verifica che la logica di ottimizzazione sia presente
$adminBarLogic = [
    'add_filter(\'show_admin_bar\', \'__return_false\')' => 'Disabilita Admin Bar frontend',
    'add_action(\'admin_bar_menu\', [' => 'Registra hook admin bar',
    'remove_node(\'wp-logo\')' => 'Rimuovi logo WordPress',
    'remove_node(\'updates\')' => 'Rimuovi menu aggiornamenti',
    'remove_node(\'comments\')' => 'Rimuovi menu commenti',
    'remove_node(\'new-content\')' => 'Rimuovi menu + Nuovo',
    'remove_node(\'customize\')' => 'Rimuovi link Personalizza'
];

$allLogicPresent = true;
foreach ($adminBarLogic as $code => $description) {
    $hasCode = strpos($fileContent, $code) !== false;
    echo "   ✓ Logica '$description': " . ($hasCode ? 'PRESENT' : 'MISSING') . "\n";
    if (!$hasCode) {
        $allLogicPresent = false;
    }
}

echo "\n3. Test integrazione nel sistema...\n";

// Verifica che l'ottimizzazione Admin Bar sia integrata nel metodo init
$initIntegration = [
    'if (!empty($settings[\'admin_bar\']))' => 'Controllo impostazioni Admin Bar',
    '$this->optimizeAdminBar($settings[\'admin_bar\'])' => 'Chiamata metodo ottimizzazione'
];

$integrationPresent = true;
foreach ($initIntegration as $code => $description) {
    $hasCode = strpos($fileContent, $code) !== false;
    echo "   ✓ Integrazione '$description': " . ($hasCode ? 'PRESENT' : 'MISSING') . "\n";
    if (!$hasCode) {
        $integrationPresent = false;
    }
}

echo "\n4. Test report ottimizzazioni...\n";

// Verifica che il report includa le informazioni Admin Bar
$reportLogic = [
    '\'admin_bar\' => [' => 'Sezione Admin Bar nel report',
    '\'frontend_disabled\'' => 'Stato disabilitazione frontend',
    '\'logo_removed\'' => 'Stato rimozione logo',
    '\'updates_removed\'' => 'Stato rimozione aggiornamenti',
    '\'comments_removed\'' => 'Stato rimozione commenti',
    '\'new_menu_removed\'' => 'Stato rimozione menu nuovo',
    '\'customize_removed\'' => 'Stato rimozione personalizza'
];

$reportPresent = true;
foreach ($reportLogic as $code => $description) {
    $hasCode = strpos($fileContent, $code) !== false;
    echo "   ✓ Report '$description': " . ($hasCode ? 'PRESENT' : 'MISSING') . "\n";
    if (!$hasCode) {
        $reportPresent = false;
    }
}

echo "\n5. Test punteggio ottimizzazione...\n";

// Verifica che il punteggio includa Admin Bar
$scoreLogic = [
    '// Admin Bar (25 punti)' => 'Commento punti Admin Bar',
    'if ($optimizations[\'admin_bar\'][\'status\'] === \'optimal\')' => 'Controllo status Admin Bar',
    '$score += 25' => 'Aggiunta punti Admin Bar'
];

$scorePresent = true;
foreach ($scoreLogic as $code => $description) {
    $hasCode = strpos($fileContent, $code) !== false;
    echo "   ✓ Punteggio '$description': " . ($hasCode ? 'PRESENT' : 'MISSING') . "\n";
    if (!$hasCode) {
        $scorePresent = false;
    }
}

echo "\n6. Test registrazione nel Plugin...\n";

// Verifica che BackendOptimizer sia registrato nel Plugin
$pluginContent = file_get_contents('src/Plugin.php');
$pluginRegistration = [
    'BackendOptimizer::class' => 'Importazione classe BackendOptimizer',
    '$container->set(BackendOptimizer::class' => 'Registrazione nel container',
    'new BackendOptimizer()' => 'Creazione istanza'
];

$pluginPresent = true;
foreach ($pluginRegistration as $code => $description) {
    $hasCode = strpos($pluginContent, $code) !== false;
    echo "   ✓ Plugin '$description': " . ($hasCode ? 'PRESENT' : 'MISSING') . "\n";
    if (!$hasCode) {
        $pluginPresent = false;
    }
}

echo "\n=== RISULTATO FINALE ===\n";
$allTestsPassed = $allMethodsExist && 
                  $allLogicPresent && 
                  $integrationPresent && 
                  $reportPresent && 
                  $scorePresent && 
                  $pluginPresent;

echo "TUTTI I TEST: " . ($allTestsPassed ? '✅ PASSATI' : '❌ FALLITI') . "\n";

if ($allTestsPassed) {
    echo "\n🎉 Le ottimizzazioni della Admin Bar sono state implementate correttamente!\n";
    echo "   ✓ Tutti i metodi necessari sono presenti\n";
    echo "   ✓ La logica di ottimizzazione è implementata\n";
    echo "   ✓ L'integrazione nel sistema è completa\n";
    echo "   ✓ Il report include le informazioni Admin Bar\n";
    echo "   ✓ Il punteggio di ottimizzazione include Admin Bar\n";
    echo "   ✓ Il servizio è registrato nel Plugin\n";
    echo "\n📋 PROSSIMI PASSI:\n";
    echo "   1. Attiva le ottimizzazioni backend nel plugin\n";
    echo "   2. Configura le opzioni Admin Bar nella pagina Backend\n";
    echo "   3. Verifica che gli elementi vengano rimossi dalla Admin Bar\n";
    echo "   4. Testa le funzionalità in un ambiente WordPress reale\n";
} else {
    echo "\n⚠️  Alcuni test sono falliti. Controllare l'implementazione.\n";
    
    if (!$allMethodsExist) {
        echo "   - Alcuni metodi Admin Bar sono mancanti\n";
    }
    if (!$allLogicPresent) {
        echo "   - La logica di ottimizzazione è incompleta\n";
    }
    if (!$integrationPresent) {
        echo "   - L'integrazione nel sistema è mancante\n";
    }
    if (!$reportPresent) {
        echo "   - Il report non include le informazioni Admin Bar\n";
    }
    if (!$scorePresent) {
        echo "   - Il punteggio non include Admin Bar\n";
    }
    if (!$pluginPresent) {
        echo "   - Il servizio non è registrato nel Plugin\n";
    }
}

echo "\n=== FINE TEST ===\n";
