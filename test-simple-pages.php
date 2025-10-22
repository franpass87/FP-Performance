<?php
/**
 * Test semplice per verificare se le pagine admin generano contenuto
 */

echo "=== TEST SEMPLICE PAGINE ADMIN ===\n\n";

// Test 1: Verifica che le pagine abbiano sintassi corretta
echo "1. Verifica sintassi pagine...\n";

$pages = [
    'Overview' => 'src/Admin/Pages/Overview.php',
    'Assets' => 'src/Admin/Pages/Assets.php',
    'Cache' => 'src/Admin/Pages/Cache.php',
    'Database' => 'src/Admin/Pages/Database.php',
    'Media' => 'src/Admin/Pages/Media.php',
    'Mobile' => 'src/Admin/Pages/Mobile.php',
    'Backend' => 'src/Admin/Pages/Backend.php',
    'Compression' => 'src/Admin/Pages/Compression.php',
    'Cdn' => 'src/Admin/Pages/Cdn.php',
    'Security' => 'src/Admin/Pages/Security.php',
    'Settings' => 'src/Admin/Pages/Settings.php',
    'Logs' => 'src/Admin/Pages/Logs.php',
    'Diagnostics' => 'src/Admin/Pages/Diagnostics.php'
];

foreach ($pages as $name => $file) {
    $output = shell_exec("php -l $file 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "✓ $name: sintassi corretta\n";
    } else {
        echo "❌ $name: errori di sintassi\n";
        echo $output . "\n";
    }
}

// Test 2: Verifica che i file di vista esistano
echo "\n2. Verifica file di vista...\n";
if (file_exists('views/admin-page.php') && filesize('views/admin-page.php') > 0) {
    echo "✓ admin-page.php esiste e non è vuoto\n";
} else {
    echo "❌ admin-page.php mancante o vuoto\n";
}

// Test 3: Verifica che le classi di servizio esistano
echo "\n3. Verifica classi di servizio...\n";
$services = [
    'ServiceContainer' => 'src/ServiceContainer.php',
    'Capabilities' => 'src/Utils/Capabilities.php',
    'Optimizer' => 'src/Services/Assets/Optimizer.php',
    'PageCache' => 'src/Services/Cache/PageCache.php',
    'Cleaner' => 'src/Services/DB/Cleaner.php'
];

foreach ($services as $name => $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l $file 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "✓ $name: sintassi corretta\n";
        } else {
            echo "❌ $name: errori di sintassi\n";
        }
    } else {
        echo "❌ $name: file mancante\n";
    }
}

// Test 4: Verifica che le classi delle tab esistano
echo "\n4. Verifica classi delle tab...\n";
$tabs = [
    'JavaScriptTab' => 'src/Admin/Pages/Assets/Tabs/JavaScriptTab.php',
    'CssTab' => 'src/Admin/Pages/Assets/Tabs/CssTab.php',
    'FontsTab' => 'src/Admin/Pages/Assets/Tabs/FontsTab.php',
    'ThirdPartyTab' => 'src/Admin/Pages/Assets/Tabs/ThirdPartyTab.php'
];

foreach ($tabs as $name => $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l $file 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "✓ $name: sintassi corretta\n";
        } else {
            echo "❌ $name: errori di sintassi\n";
        }
    } else {
        echo "❌ $name: file mancante\n";
    }
}

// Test 5: Verifica che non ci siano errori di import
echo "\n5. Verifica import delle classi...\n";
$imports = [
    'AbstractPage' => 'src/Admin/Pages/AbstractPage.php',
    'PostHandler' => 'src/Admin/Pages/Assets/Handlers/PostHandler.php'
];

foreach ($imports as $name => $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l $file 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "✓ $name: sintassi corretta\n";
        } else {
            echo "❌ $name: errori di sintassi\n";
        }
    } else {
        echo "❌ $name: file mancante\n";
    }
}

echo "\n=== TEST COMPLETATO ===\n";
echo "Se tutti i test sono passati, il problema potrebbe essere:\n";
echo "1. Errori JavaScript nella console del browser\n";
echo "2. Problemi di caricamento CSS\n";
echo "3. Errori PHP durante l'esecuzione (non di sintassi)\n";
echo "4. Problemi di permessi o configurazione WordPress\n";
