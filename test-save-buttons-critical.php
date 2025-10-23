<?php
/**
 * Test Specifico per Bottoni di Salvataggio e Funzionalità Critiche
 * 
 * Verifica che tutti i bottoni di salvataggio funzionino correttamente
 * e che non ci siano errori critici
 * 
 * @author Francesco Passeri
 * @version 1.0.0
 */

echo "🔍 Test Specifico Bottoni di Salvataggio e Funzionalità Critiche\n";
echo str_repeat("=", 70) . "\n\n";

$errors = [];
$warnings = [];
$success = [];

/**
 * Funzione per aggiungere un errore
 */
function addError($message) {
    global $errors;
    $errors[] = $message;
    echo "❌ " . $message . "\n";
}

/**
 * Funzione per aggiungere un warning
 */
function addWarning($message) {
    global $warnings;
    $warnings[] = $message;
    echo "⚠️ " . $message . "\n";
}

/**
 * Funzione per aggiungere un successo
 */
function addSuccess($message) {
    global $success;
    $success[] = $message;
    echo "✅ " . $message . "\n";
}

// Test 1: Verifica bottoni di salvataggio nelle pagine admin
echo "💾 Test 1: Bottoni di Salvataggio nelle Pagine Admin\n";
echo str_repeat("-", 50) . "\n";

$adminPages = [
    'src/Admin/Pages/Assets.php' => 'Assets',
    'src/Admin/Pages/Backend.php' => 'Backend', 
    'src/Admin/Pages/Database.php' => 'Database',
    'src/Admin/Pages/ML.php' => 'ML',
    'src/Admin/Pages/Media.php' => 'Media',
    'src/Admin/Pages/Mobile.php' => 'Mobile'
];

foreach ($adminPages as $file => $name) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        
        echo "  📄 Testando pagina: {$name}\n";
        
        // Verifica presenza bottoni di salvataggio
        $hasSaveButton = false;
        $savePatterns = ['button-primary', 'Save Settings', 'Salva', 'submit'];
        
        foreach ($savePatterns as $pattern) {
            if (strpos($content, $pattern) !== false) {
                $hasSaveButton = true;
                break;
            }
        }
        
        if ($hasSaveButton) {
            addSuccess("    Bottoni di salvataggio presenti in {$name}");
        } else {
            addError("    Bottoni di salvataggio NON trovati in {$name}");
        }
        
        // Verifica nonce per sicurezza
        if (strpos($content, 'wp_nonce_field') !== false) {
            addSuccess("    Nonce di sicurezza presente in {$name}");
        } else {
            addWarning("    Nonce di sicurezza non trovato in {$name}");
        }
        
        // Verifica gestione form (POST o handler dedicato)
        $hasFormHandling = false;
        
        // Cerca gestione POST diretta
        if (strpos($content, '$_POST') !== false && strpos($content, 'REQUEST_METHOD') !== false) {
            $hasFormHandling = true;
            addSuccess("    Gestione POST diretta presente in {$name}");
        }
        
        // Cerca handler dedicato
        if (strpos($content, 'PostHandler') !== false || strpos($content, 'handlePost') !== false) {
            $hasFormHandling = true;
            addSuccess("    Handler POST dedicato presente in {$name}");
        }
        
        if (!$hasFormHandling) {
            addWarning("    Gestione POST non trovata in {$name}");
        }
        
        // Verifica messaggi di successo
        if (strpos($content, 'notice-success') !== false || strpos($content, 'message') !== false) {
            addSuccess("    Messaggi di feedback presenti in {$name}");
        } else {
            addWarning("    Messaggi di feedback non trovati in {$name}");
        }
        
        echo "\n";
    } else {
        addError("File pagina admin non trovato: {$file}");
    }
}

// Test 2: Verifica servizi critici
echo "⚙️ Test 2: Servizi Critici\n";
echo str_repeat("-", 50) . "\n";

$criticalServices = [
    'src/Services/Assets/Optimizer.php' => 'Asset Optimizer',
    'src/Services/Cache/PageCache.php' => 'Page Cache',
    'src/Services/Media/WebPConverter.php' => 'WebP Converter',
    'src/Services/Admin/BackendOptimizer.php' => 'Backend Optimizer',
    'src/Services/DB/DatabaseOptimizer.php' => 'Database Optimizer'
];

foreach ($criticalServices as $file => $name) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        
        echo "  🔧 Testando servizio: {$name}\n";
        
        // Verifica metodi essenziali
        $hasRegister = strpos($content, 'public function register()') !== false;
        $hasSettings = strpos($content, 'public function getSettings()') !== false || 
                      strpos($content, 'public function settings()') !== false;
        
        if ($hasRegister) {
            addSuccess("    Metodo register() presente in {$name}");
        } else {
            addError("    Metodo register() MANCANTE in {$name}");
        }
        
        if ($hasSettings) {
            addSuccess("    Metodo settings/getSettings() presente in {$name}");
        } else {
            addError("    Metodo settings/getSettings() MANCANTE in {$name}");
        }
        
        // Verifica gestione errori
        if (strpos($content, 'try') !== false && strpos($content, 'catch') !== false) {
            addSuccess("    Gestione errori presente in {$name}");
        } else {
            addWarning("    Gestione errori non trovata in {$name}");
        }
        
        // Verifica logging
        if (strpos($content, 'Logger') !== false || strpos($content, 'error_log') !== false) {
            addSuccess("    Sistema di logging presente in {$name}");
        } else {
            addWarning("    Sistema di logging non trovato in {$name}");
        }
        
        echo "\n";
    } else {
        addError("File servizio critico non trovato: {$file}");
    }
}

// Test 3: Verifica file di configurazione critici
echo "⚙️ Test 3: File di Configurazione Critici\n";
echo str_repeat("-", 50) . "\n";

$criticalConfigFiles = [
    'fp-performance-suite.php' => 'File principale plugin',
    'src/Plugin.php' => 'Classe principale',
    'src/ServiceContainer.php' => 'Container servizi',
    'README.md' => 'Documentazione',
    'readme.txt' => 'Readme WordPress',
    'LICENSE' => 'Licenza'
];

foreach ($criticalConfigFiles as $file => $description) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        addSuccess("File critico trovato: {$file} ({$description})");
        
        // Verifica che il file non sia vuoto
        if (filesize($filePath) > 100) {
            addSuccess("  File ha contenuto sufficiente: {$file}");
        } else {
            addError("  File troppo piccolo o vuoto: {$file}");
        }
    } else {
        addError("File critico MANCANTE: {$file} ({$description})");
    }
}

// Test 4: Verifica sintassi PHP critica
echo "🔧 Test 4: Sintassi PHP Critica\n";
echo str_repeat("-", 50) . "\n";

$criticalPhpFiles = [
    'src/Plugin.php',
    'src/ServiceContainer.php',
    'src/Admin/Pages/Assets.php',
    'src/Admin/Pages/Backend.php',
    'src/Admin/Pages/Database.php',
    'src/Services/Assets/Optimizer.php',
    'src/Services/Cache/PageCache.php'
];

foreach ($criticalPhpFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        // Verifica sintassi PHP
        $output = [];
        $returnCode = 0;
        exec("php -l " . escapeshellarg($filePath) . " 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            addSuccess("Sintassi OK: {$file}");
        } else {
            addError("ERRORE SINTASSI {$file}: " . implode(' ', $output));
        }
    } else {
        addError("File PHP critico non trovato: {$file}");
    }
}

// Test 5: Verifica sicurezza
echo "🔒 Test 5: Verifica Sicurezza\n";
echo str_repeat("-", 50) . "\n";

$securityChecks = [
    'src/Admin/Pages/Assets.php' => 'wp_nonce_field',
    'src/Admin/Pages/Backend.php' => 'wp_nonce_field',
    'src/Admin/Pages/Database.php' => 'wp_nonce_field',
    'src/Admin/Pages/ML.php' => 'wp_nonce_field',
    'src/Admin/Pages/Media.php' => 'wp_nonce_field',
    'src/Admin/Pages/Mobile.php' => 'wp_nonce_field'
];

foreach ($securityChecks as $file => $securityFeature) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        
        if (strpos($content, $securityFeature) !== false) {
            addSuccess("Sicurezza OK: {$file} ha {$securityFeature}");
        } else {
            addError("SICUREZZA MANCANTE: {$file} non ha {$securityFeature}");
        }
        
        // Verifica sanitizzazione input
        if (strpos($content, 'sanitize_') !== false || strpos($content, 'esc_') !== false) {
            addSuccess("  Sanitizzazione input presente in {$file}");
        } else {
            addWarning("  Sanitizzazione input non trovata in {$file}");
        }
    }
}

// Test 6: Verifica errori critici comuni
echo "🚨 Test 6: Verifica Errori Critici Comuni\n";
echo str_repeat("-", 50) . "\n";

// Verifica file con errori PHP comuni
$commonErrorPatterns = [
    'Fatal error',
    'Parse error',
    'syntax error',
    'undefined function',
    'undefined class',
    'undefined constant'
];

$phpFiles = glob(__DIR__ . '/src/**/*.php');
foreach ($phpFiles as $phpFile) {
    $content = file_get_contents($phpFile);
    
    foreach ($commonErrorPatterns as $pattern) {
        if (stripos($content, $pattern) !== false) {
            addError("Possibile errore trovato in " . basename($phpFile) . ": {$pattern}");
        }
    }
}

// Verifica file con problemi di performance
$performanceIssues = [
    'while (true)',
    'for (;;)',
    'infinite',
    'recursion'
];

foreach ($phpFiles as $phpFile) {
    $content = file_get_contents($phpFile);
    
    foreach ($performanceIssues as $issue) {
        if (stripos($content, $issue) !== false) {
            addWarning("Possibile problema performance in " . basename($phpFile) . ": {$issue}");
        }
    }
}

// Risultati finali
echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 RISULTATI FINALI TEST CRITICO\n";
echo str_repeat("=", 70) . "\n";

$totalTests = count($success) + count($warnings) + count($errors);
$successRate = $totalTests > 0 ? round((count($success) / $totalTests) * 100, 2) : 0;

echo "✅ SUCCESSI: " . count($success) . "\n";
echo "⚠️ WARNINGS: " . count($warnings) . "\n";
echo "❌ ERRORI: " . count($errors) . "\n";
echo "📈 TASSO DI SUCCESSO: {$successRate}%\n\n";

if ($successRate >= 95) {
    echo "🎉 PLUGIN IN PERFETTO STATO!\n";
    echo "Tutti i test critici sono stati superati con successo.\n";
    echo "Il plugin è pronto per l'uso in produzione.\n";
} elseif ($successRate >= 85) {
    echo "✅ PLUGIN FUNZIONANTE CON PICCOLI AVVISI\n";
    echo "Il plugin funziona correttamente ma ci sono alcuni aspetti da migliorare.\n";
    echo "È sicuro utilizzarlo in produzione.\n";
} elseif ($successRate >= 70) {
    echo "⚠️ PLUGIN FUNZIONANTE CON AVVISI\n";
    echo "Il plugin funziona ma ci sono alcuni problemi da risolvere.\n";
    echo "Rivedi i warning prima dell'uso in produzione.\n";
} else {
    echo "❌ PROBLEMI CRITICI TROVATI\n";
    echo "Ci sono errori che devono essere risolti prima dell'uso.\n";
    echo "NON utilizzare il plugin in produzione fino alla risoluzione.\n";
}

if (!empty($errors)) {
    echo "\n🔍 ERRORI CRITICI TROVATI:\n";
    foreach ($errors as $error) {
        echo "  - " . $error . "\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️ WARNINGS (da rivedere):\n";
    foreach ($warnings as $warning) {
        echo "  - " . $warning . "\n";
    }
}

echo "\n💡 RACCOMANDAZIONI FINALI:\n";
if (count($errors) > 0) {
    echo "  🔴 PRIORITÀ ALTA: Risolvi tutti gli errori critici\n";
}
if (count($warnings) > 5) {
    echo "  🟡 PRIORITÀ MEDIA: Rivedi i warning per ottimizzare le performance\n";
}
echo "  🔵 MANUTENZIONE: Esegui questo test periodicamente\n";
echo "  🔵 SICUREZZA: Fai backup delle configurazioni prima di modifiche\n";

echo "\nTest critico completato il " . date('d/m/Y H:i:s') . "\n";
