<?php
/**
 * Analisi Dettagliata dei Bottoni di Salvataggio
 * 
 * Verifica specifica dei problemi identificati nei bottoni di salvataggio
 * 
 * @author Francesco Passeri
 * @version 1.0.0
 */

echo "🔍 Analisi Dettagliata Bottoni di Salvataggio\n";
echo str_repeat("=", 70) . "\n\n";

$issues = [];
$fixes = [];

/**
 * Funzione per aggiungere un problema
 */
function addIssue($file, $line, $description, $severity = 'warning') {
    global $issues;
    $issues[] = [
        'file' => $file,
        'line' => $line,
        'description' => $description,
        'severity' => $severity
    ];
    $icon = $severity === 'error' ? '❌' : '⚠️';
    echo "{$icon} {$file}:{$line} - {$description}\n";
}

/**
 * Funzione per aggiungere una soluzione
 */
function addFix($file, $description) {
    global $fixes;
    $fixes[] = [
        'file' => $file,
        'description' => $description
    ];
    echo "✅ SOLUZIONE per {$file}: {$description}\n";
}

// Analisi 1: Pagina Mobile - Form senza action
echo "📱 Analisi 1: Pagina Mobile\n";
echo str_repeat("-", 50) . "\n";

$mobileFile = 'src/Admin/Pages/Mobile.php';
if (file_exists($mobileFile)) {
    $content = file_get_contents($mobileFile);
    $lines = explode("\n", $content);
    
    foreach ($lines as $num => $line) {
        if (strpos($line, '<form method="post" action="">') !== false) {
            addIssue($mobileFile, $num + 1, 'Form senza action specificata', 'error');
        }
    }
    
    // Verifica gestione form
    if (strpos($content, 'handleFormSubmission') !== false) {
        echo "✅ Gestione form presente\n";
    } else {
        addIssue($mobileFile, 0, 'Metodo handleFormSubmission non trovato', 'error');
    }
    
    // Verifica nonce
    if (strpos($content, 'wp_nonce_field') !== false) {
        echo "✅ Nonce presente\n";
    } else {
        addIssue($mobileFile, 0, 'Nonce non trovato', 'error');
    }
}

// Analisi 2: Pagina Assets - PostHandler
echo "\n📦 Analisi 2: Pagina Assets\n";
echo str_repeat("-", 50) . "\n";

$assetsFile = 'src/Admin/Pages/Assets.php';
if (file_exists($assetsFile)) {
    $content = file_get_contents($assetsFile);
    
    // Verifica PostHandler
    if (strpos($content, 'PostHandler') !== false) {
        echo "✅ PostHandler presente\n";
    } else {
        addIssue($assetsFile, 0, 'PostHandler non trovato', 'error');
    }
    
    // Verifica gestione errori
    if (strpos($content, 'try') !== false && strpos($content, 'catch') !== false) {
        echo "✅ Gestione errori presente\n";
    } else {
        addIssue($assetsFile, 0, 'Gestione errori mancante', 'warning');
    }
    
    // Verifica fallback
    if (strpos($content, 'handleDirectFormSubmission') !== false) {
        echo "✅ Metodo fallback presente\n";
    } else {
        addIssue($assetsFile, 0, 'Metodo fallback mancante', 'warning');
    }
}

// Analisi 3: Pagina Backend - Gestione form complessa
echo "\n⚙️ Analisi 3: Pagina Backend\n";
echo str_repeat("-", 50) . "\n";

$backendFile = 'src/Admin/Pages/Backend.php';
if (file_exists($backendFile)) {
    $content = file_get_contents($backendFile);
    $lines = explode("\n", $content);
    
    $formCount = 0;
    $nonceCount = 0;
    
    foreach ($lines as $num => $line) {
        if (strpos($line, '<form') !== false) {
            $formCount++;
        }
        if (strpos($line, 'wp_nonce_field') !== false) {
            $nonceCount++;
        }
    }
    
    echo "📊 Statistiche Backend:\n";
    echo "  - Form trovati: {$formCount}\n";
    echo "  - Nonce trovati: {$nonceCount}\n";
    
    if ($formCount > $nonceCount) {
        addIssue($backendFile, 0, "Mismatch form/nonce: {$formCount} form vs {$nonceCount} nonce", 'warning');
    }
    
    // Verifica gestione POST
    if (strpos($content, 'REQUEST_METHOD') !== false) {
        echo "✅ Gestione POST presente\n";
    } else {
        addIssue($backendFile, 0, 'Gestione POST mancante', 'error');
    }
}

// Analisi 4: Pagina Settings - Nonce multipli
echo "\n🔧 Analisi 4: Pagina Settings\n";
echo str_repeat("-", 50) . "\n";

$settingsFile = 'src/Admin/Pages/Settings.php';
if (file_exists($settingsFile)) {
    $content = file_get_contents($settingsFile);
    
    // Conta nonce diversi
    $nonceTypes = [];
    preg_match_all('/wp_nonce_field\([\'"]([^\'"]+)[\'"]/', $content, $matches);
    if (!empty($matches[1])) {
        $nonceTypes = array_unique($matches[1]);
    }
    
    echo "📊 Nonce trovati: " . implode(', ', $nonceTypes) . "\n";
    
    if (count($nonceTypes) > 2) {
        addIssue($settingsFile, 0, 'Troppi tipi di nonce diversi: ' . count($nonceTypes), 'warning');
    }
    
    // Verifica gestione form
    $formHandlers = 0;
    if (strpos($content, 'fp_ps_settings_nonce') !== false) $formHandlers++;
    if (strpos($content, 'fp_ps_import_nonce') !== false) $formHandlers++;
    
    echo "📊 Handler form: {$formHandlers}\n";
}

// Analisi 5: Verifica file PostHandler
echo "\n🔧 Analisi 5: PostHandler\n";
echo str_repeat("-", 50) . "\n";

$postHandlerFile = 'src/Admin/Pages/Assets/Handlers/PostHandler.php';
if (file_exists($postHandlerFile)) {
    $content = file_get_contents($postHandlerFile);
    
    // Verifica metodi essenziali
    $methods = ['handlePost', 'handleJavaScriptForm', 'handleCssForm', 'handleThirdPartyForm'];
    foreach ($methods as $method) {
        if (strpos($content, "private function {$method}") !== false) {
            echo "✅ Metodo {$method} presente\n";
        } else {
            addIssue($postHandlerFile, 0, "Metodo {$method} mancante", 'error');
        }
    }
    
    // Verifica gestione errori
    if (strpos($content, 'try') !== false && strpos($content, 'catch') !== false) {
        echo "✅ Gestione errori presente\n";
    } else {
        addIssue($postHandlerFile, 0, 'Gestione errori mancante', 'warning');
    }
} else {
    addIssue('PostHandler', 0, 'File PostHandler non trovato', 'error');
}

// Analisi 6: Verifica Menu.php per handler admin_post
echo "\n📋 Analisi 6: Menu.php Handler\n";
echo str_repeat("-", 50) . "\n";

$menuFile = 'src/Admin/Menu.php';
if (file_exists($menuFile)) {
    $content = file_get_contents($menuFile);
    
    // Verifica handler admin_post
    $handlers = ['handleCompressionSave', 'handleCdnSave', 'handleMonitoringSave'];
    foreach ($handlers as $handler) {
        if (strpos($content, "public function {$handler}") !== false) {
            echo "✅ Handler {$handler} presente\n";
        } else {
            addIssue($menuFile, 0, "Handler {$handler} mancante", 'warning');
        }
    }
    
    // Verifica registrazione hook
    if (strpos($content, 'admin_post_fp_ps_save_') !== false) {
        echo "✅ Hook admin_post registrati\n";
    } else {
        addIssue($menuFile, 0, 'Hook admin_post non registrati', 'error');
    }
}

// Risultati finali
echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 RISULTATI ANALISI\n";
echo str_repeat("=", 70) . "\n";

$errorCount = count(array_filter($issues, function($issue) {
    return $issue['severity'] === 'error';
}));

$warningCount = count(array_filter($issues, function($issue) {
    return $issue['severity'] === 'warning';
}));

echo "❌ ERRORI CRITICI: {$errorCount}\n";
echo "⚠️ WARNINGS: {$warningCount}\n";
echo "✅ SOLUZIONI IDENTIFICATE: " . count($fixes) . "\n\n";

if ($errorCount > 0) {
    echo "🚨 PROBLEMI CRITICI TROVATI:\n";
    foreach ($issues as $issue) {
        if ($issue['severity'] === 'error') {
            echo "  - {$issue['file']}:{$issue['line']} - {$issue['description']}\n";
        }
    }
    echo "\n";
}

if ($warningCount > 0) {
    echo "⚠️ WARNINGS (da rivedere):\n";
    foreach ($issues as $issue) {
        if ($issue['severity'] === 'warning') {
            echo "  - {$issue['file']}:{$issue['line']} - {$issue['description']}\n";
        }
    }
    echo "\n";
}

echo "💡 RACCOMANDAZIONI:\n";
echo "  1. Correggi tutti gli errori critici prima dell'uso\n";
echo "  2. Rivedi i warnings per migliorare la stabilità\n";
echo "  3. Testa ogni bottone di salvataggio dopo le correzioni\n";
echo "  4. Implementa logging per debug dei form\n";
echo "  5. Aggiungi validazione client-side per UX migliore\n\n";

echo "🔧 CORREZIONI APPLICATE:\n";
foreach ($fixes as $fix) {
    echo "  ✅ {$fix['file']}: {$fix['description']}\n";
}

echo "\nAnalisi completata il " . date('d/m/Y H:i:s') . "\n";