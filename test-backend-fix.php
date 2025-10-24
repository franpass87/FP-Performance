<?php
/**
 * Test Backend Optimization Fix
 * 
 * Verifica se le correzioni funzionano
 */

echo "<h1>🔧 Test Backend Optimization Fix</h1>\n";

// Test 1: Verifica se i file esistono
$files = [
    'src/Admin/Pages/Backend.php',
    'src/Services/Admin/BackendOptimizer.php',
    'src/Plugin.php'
];

echo "<h2>📁 Verifica File</h2>\n";
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file esiste\n";
    } else {
        echo "❌ $file non trovato\n";
    }
}

// Test 2: Verifica contenuto file Backend.php
echo "<h2>🔍 Verifica Contenuto Backend.php</h2>\n";
$backendContent = file_get_contents('src/Admin/Pages/Backend.php');

// Verifica se contiene il debug log
if (strpos($backendContent, 'error_log') !== false) {
    echo "✅ Debug log aggiunto\n";
} else {
    echo "❌ Debug log non trovato\n";
}

// Verifica se contiene forceInit
if (strpos($backendContent, 'forceInit') !== false) {
    echo "✅ forceInit aggiunto\n";
} else {
    echo "❌ forceInit non trovato\n";
}

// Test 3: Verifica contenuto BackendOptimizer.php
echo "<h2>🔍 Verifica Contenuto BackendOptimizer.php</h2>\n";
$optimizerContent = file_get_contents('src/Services/Admin/BackendOptimizer.php');

// Verifica se contiene il metodo forceInit
if (strpos($optimizerContent, 'forceInit') !== false) {
    echo "✅ Metodo forceInit aggiunto\n";
} else {
    echo "❌ Metodo forceInit non trovato\n";
}

// Verifica se contiene debug log
if (strpos($optimizerContent, 'Logger::debug') !== false) {
    echo "✅ Debug logging aggiunto\n";
} else {
    echo "❌ Debug logging non trovato\n";
}

// Test 4: Verifica contenuto Plugin.php
echo "<h2>🔍 Verifica Contenuto Plugin.php</h2>\n";
$pluginContent = file_get_contents('src/Plugin.php');

// Verifica se contiene la registrazione sempre attiva
if (strpos($pluginContent, 'Registra sempre il servizio') !== false) {
    echo "✅ Registrazione sempre attiva aggiunta\n";
} else {
    echo "❌ Registrazione sempre attiva non trovata\n";
}

// Test 5: Verifica sintassi PHP
echo "<h2>🔍 Verifica Sintassi PHP</h2>\n";
$phpFiles = [
    'src/Admin/Pages/Backend.php',
    'src/Services/Admin/BackendOptimizer.php'
];

foreach ($phpFiles as $file) {
    $output = [];
    $return = 0;
    exec("php -l $file 2>&1", $output, $return);
    
    if ($return === 0) {
        echo "✅ $file sintassi OK\n";
    } else {
        echo "❌ $file errore sintassi: " . implode("\n", $output) . "\n";
    }
}

echo "<h2>✅ Test Completato</h2>\n";
echo "Le correzioni sono state applicate. Ora testa la pagina Backend Optimization nel WordPress admin.\n";
