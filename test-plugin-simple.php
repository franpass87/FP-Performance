<?php
/**
 * Test Semplice delle Funzionalità del Plugin FP Performance Suite
 * 
 * Verifica file, classi e funzionalità principali senza dipendere da WordPress
 * 
 * @author Francesco Passeri
 * @version 1.0.0
 */

echo "🔍 Test Semplice Plugin FP Performance Suite\n";
echo str_repeat("=", 60) . "\n\n";

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

// Test 1: Verifica file principali
echo "📁 Test 1: File Principali\n";
echo str_repeat("-", 40) . "\n";

$requiredFiles = [
    'fp-performance-suite.php',
    'src/Plugin.php',
    'src/ServiceContainer.php',
    'src/Admin/Pages/Assets.php',
    'src/Admin/Pages/Backend.php',
    'src/Admin/Pages/Database.php',
    'src/Admin/Pages/ML.php',
    'src/Admin/Pages/Media.php',
    'src/Admin/Pages/Mobile.php',
    'src/Services/Assets/Optimizer.php',
    'src/Services/Cache/PageCache.php',
    'src/Services/Media/WebPConverter.php',
    'src/Services/Admin/BackendOptimizer.php',
    'src/Services/DB/DatabaseOptimizer.php',
    'src/Services/ML/MLPredictor.php',
    'src/Services/Mobile/MobileOptimizer.php'
];

$pluginDir = __DIR__ . '/';

foreach ($requiredFiles as $file) {
    $filePath = $pluginDir . $file;
    if (file_exists($filePath)) {
        addSuccess("File trovato: {$file}");
        
        // Verifica che il file non sia vuoto
        if (filesize($filePath) > 0) {
            addSuccess("  File non vuoto: {$file}");
        } else {
            addError("  File vuoto: {$file}");
        }
    } else {
        addError("File mancante: {$file}");
    }
}

echo "\n";

// Test 2: Verifica sintassi PHP
echo "🔧 Test 2: Sintassi PHP\n";
echo str_repeat("-", 40) . "\n";

$phpFiles = [
    'src/Plugin.php',
    'src/ServiceContainer.php',
    'src/Admin/Pages/Assets.php',
    'src/Admin/Pages/Backend.php',
    'src/Admin/Pages/Database.php',
    'src/Admin/Pages/ML.php',
    'src/Admin/Pages/Media.php',
    'src/Admin/Pages/Mobile.php'
];

foreach ($phpFiles as $file) {
    $filePath = $pluginDir . $file;
    if (file_exists($filePath)) {
        // Verifica sintassi PHP
        $output = [];
        $returnCode = 0;
        exec("php -l " . escapeshellarg($filePath) . " 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            addSuccess("Sintassi OK: {$file}");
        } else {
            addError("Errore sintassi {$file}: " . implode(' ', $output));
        }
    }
}

echo "\n";

// Test 3: Verifica namespace e classi
echo "🏗️ Test 3: Namespace e Classi\n";
echo str_repeat("-", 40) . "\n";

$expectedClasses = [
    'src/Plugin.php' => 'FP\\PerfSuite\\Plugin',
    'src/ServiceContainer.php' => 'FP\\PerfSuite\\ServiceContainer',
    'src/Admin/Pages/Assets.php' => 'FP\\PerfSuite\\Admin\\Pages\\Assets',
    'src/Admin/Pages/Backend.php' => 'FP\\PerfSuite\\Admin\\Pages\\Backend',
    'src/Admin/Pages/Database.php' => 'FP\\PerfSuite\\Admin\\Pages\\Database',
    'src/Admin/Pages/ML.php' => 'FP\\PerfSuite\\Admin\\Pages\\ML',
    'src/Admin/Pages/Media.php' => 'FP\\PerfSuite\\Admin\\Pages\\Media',
    'src/Admin/Pages/Mobile.php' => 'FP\\PerfSuite\\Admin\\Pages\\Mobile'
];

foreach ($expectedClasses as $file => $expectedClass) {
    $filePath = $pluginDir . $file;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        
        // Verifica che il namespace sia presente
        if (strpos($content, 'namespace ' . str_replace('\\', '\\\\', $expectedClass)) !== false) {
            addSuccess("Namespace corretto in {$file}");
        } else {
            addWarning("Namespace non trovato o incorretto in {$file}");
        }
        
        // Verifica che la classe sia definita
        $className = basename($expectedClass);
        if (strpos($content, "class {$className}") !== false) {
            addSuccess("Classe definita in {$file}");
        } else {
            addError("Classe non definita in {$file}");
        }
    }
}

echo "\n";

// Test 4: Verifica bottoni di salvataggio nelle pagine admin
echo "💾 Test 4: Bottoni di Salvataggio\n";
echo str_repeat("-", 40) . "\n";

$adminPages = [
    'src/Admin/Pages/Assets.php' => ['save', 'submit', 'button-primary'],
    'src/Admin/Pages/Backend.php' => ['save', 'submit', 'button-primary'],
    'src/Admin/Pages/Database.php' => ['save', 'submit', 'button-primary'],
    'src/Admin/Pages/ML.php' => ['save', 'submit', 'button-primary'],
    'src/Admin/Pages/Media.php' => ['save', 'submit', 'button-primary'],
    'src/Admin/Pages/Mobile.php' => ['save', 'submit', 'button-primary']
];

foreach ($adminPages as $file => $searchTerms) {
    $filePath = $pluginDir . $file;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        
        $hasSaveButton = false;
        foreach ($searchTerms as $term) {
            if (strpos($content, $term) !== false) {
                $hasSaveButton = true;
                break;
            }
        }
        
        if ($hasSaveButton) {
            addSuccess("Bottoni di salvataggio trovati in {$file}");
        } else {
            addError("Bottoni di salvataggio NON trovati in {$file}");
        }
        
        // Verifica nonce per sicurezza
        if (strpos($content, 'wp_nonce_field') !== false) {
            addSuccess("  Nonce di sicurezza presente in {$file}");
        } else {
            addWarning("  Nonce di sicurezza non trovato in {$file}");
        }
        
        // Verifica gestione POST
        if (strpos($content, '$_POST') !== false && strpos($content, 'REQUEST_METHOD') !== false) {
            addSuccess("  Gestione POST presente in {$file}");
        } else {
            addWarning("  Gestione POST non trovata in {$file}");
        }
    }
}

echo "\n";

// Test 5: Verifica servizi principali
echo "⚙️ Test 5: Servizi Principali\n";
echo str_repeat("-", 40) . "\n";

$servicesFiles = [
    'src/Services/Assets/Optimizer.php' => 'FP\\PerfSuite\\Services\\Assets\\Optimizer',
    'src/Services/Cache/PageCache.php' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
    'src/Services/Media/WebPConverter.php' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
    'src/Services/Admin/BackendOptimizer.php' => 'FP\\PerfSuite\\Services\\Admin\\BackendOptimizer',
    'src/Services/DB/DatabaseOptimizer.php' => 'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer',
    'src/Services/ML/MLPredictor.php' => 'FP\\PerfSuite\\Services\\ML\\MLPredictor',
    'src/Services/Mobile/MobileOptimizer.php' => 'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer'
];

foreach ($servicesFiles as $file => $expectedClass) {
    $filePath = $pluginDir . $file;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        
        // Verifica che il servizio abbia metodi essenziali
        if (strpos($content, 'public function register()') !== false) {
            addSuccess("Metodo register() trovato in {$file}");
        } else {
            addWarning("Metodo register() non trovato in {$file}");
        }
        
        if (strpos($content, 'public function settings()') !== false || strpos($content, 'public function getSettings()') !== false) {
            addSuccess("Metodo settings/getSettings() trovato in {$file}");
        } else {
            addWarning("Metodo settings/getSettings() non trovato in {$file}");
        }
    } else {
        addError("File servizio mancante: {$file}");
    }
}

echo "\n";

// Test 6: Verifica struttura directory
echo "📂 Test 6: Struttura Directory\n";
echo str_repeat("-", 40) . "\n";

$requiredDirs = [
    'src',
    'src/Admin',
    'src/Admin/Pages',
    'src/Services',
    'src/Services/Assets',
    'src/Services/Cache',
    'src/Services/Media',
    'src/Services/Admin',
    'src/Services/DB',
    'src/Services/ML',
    'src/Services/Mobile',
    'assets',
    'assets/css',
    'assets/js',
    'views',
    'languages'
];

foreach ($requiredDirs as $dir) {
    $dirPath = $pluginDir . $dir;
    if (is_dir($dirPath)) {
        addSuccess("Directory trovata: {$dir}");
        
        // Verifica che la directory non sia vuota (tranne per alcune)
        $skipEmptyCheck = ['languages', 'views'];
        if (!in_array($dir, $skipEmptyCheck)) {
            $files = scandir($dirPath);
            $fileCount = count($files) - 2; // Sottrai . e ..
            
            if ($fileCount > 0) {
                addSuccess("  Directory non vuota: {$dir} ({$fileCount} elementi)");
            } else {
                addWarning("  Directory vuota: {$dir}");
            }
        }
    } else {
        addError("Directory mancante: {$dir}");
    }
}

echo "\n";

// Test 7: Verifica file di configurazione
echo "⚙️ Test 7: File di Configurazione\n";
echo str_repeat("-", 40) . "\n";

$configFiles = [
    'composer.json',
    'phpcs.xml.dist',
    'phpstan.neon.dist',
    'phpunit.xml.dist',
    'README.md',
    'readme.txt',
    'LICENSE'
];

foreach ($configFiles as $configFile) {
    $filePath = $pluginDir . $configFile;
    if (file_exists($filePath)) {
        addSuccess("File configurazione trovato: {$configFile}");
    } else {
        addWarning("File configurazione mancante: {$configFile}");
    }
}

echo "\n";

// Risultati finali
echo str_repeat("=", 60) . "\n";
echo "📊 RISULTATI FINALI\n";
echo str_repeat("=", 60) . "\n";

$totalTests = count($success) + count($warnings) + count($errors);
$successRate = $totalTests > 0 ? round((count($success) / $totalTests) * 100, 2) : 0;

echo "✅ SUCCESSI: " . count($success) . "\n";
echo "⚠️ WARNINGS: " . count($warnings) . "\n";
echo "❌ ERRORI: " . count($errors) . "\n";
echo "📈 TASSO DI SUCCESSO: {$successRate}%\n\n";

if ($successRate >= 90) {
    echo "🎉 PLUGIN IN PERFETTO STATO!\n";
    echo "Tutti i test principali sono stati superati con successo.\n";
} elseif ($successRate >= 70) {
    echo "⚠️ PLUGIN FUNZIONANTE CON AVVISI\n";
    echo "Il plugin funziona ma ci sono alcuni aspetti da migliorare.\n";
} else {
    echo "❌ PROBLEMI CRITICI TROVATI\n";
    echo "Ci sono errori che devono essere risolti prima dell'uso.\n";
}

if (!empty($errors)) {
    echo "\n🔍 ERRORI TROVATI:\n";
    foreach ($errors as $error) {
        echo "  - " . $error . "\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️ WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  - " . $warning . "\n";
    }
}

echo "\n💡 RACCOMANDAZIONI:\n";
echo "  - Risolvi gli errori critici prima di utilizzare il plugin\n";
echo "  - Rivedi i warning per ottimizzare le performance\n";
echo "  - Esegui questo test periodicamente per monitorare la salute del plugin\n";
echo "  - Fai backup delle configurazioni prima di apportare modifiche\n";

echo "\nTest completato il " . date('d/m/Y H:i:s') . "\n";
