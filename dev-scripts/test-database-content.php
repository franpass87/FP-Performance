<?php
/**
 * Test rapido per verificare il contenuto della pagina Database
 */

// Supponiamo che tu stia eseguendo questo dal root del plugin WordPress

echo "Test Database Page Content\n";
echo "===========================\n\n";

// 1. Verifica che il file esista
$file = __DIR__ . '/../src/Admin/Pages/Database.php';
if (!file_exists($file)) {
    echo "❌ File non trovato: $file\n";
    exit(1);
}
echo "✓ File trovato\n";

// 2. Verifica sintassi
$output = [];
$returnVar = 0;
exec('php -l "' . $file . '" 2>&1', $output, $returnVar);
if ($returnVar !== 0) {
    echo "❌ Errore sintassi:\n";
    echo implode("\n", $output) . "\n";
    exit(1);
}
echo "✓ Sintassi corretta\n";

// 3. Controlla il metodo content() - cerca problemi comuni
$content = file_get_contents($file);

// Verifica che il metodo content() esista
if (strpos($content, 'protected function content(): string') === false) {
    echo "⚠️  Metodo content() non trovato o ha firma diversa\n";
}

// Conta ob_start e ob_get_clean
$obStartCount = substr_count($content, 'ob_start()');
$obGetCleanCount = substr_count($content, 'ob_get_clean()');

echo "\n";
echo "Analisi Output Buffering:\n";
echo "  ob_start():     $obStartCount\n";
echo "  ob_get_clean(): $obGetCleanCount\n";

if ($obStartCount !== $obGetCleanCount) {
    echo "  ❌ PROBLEMA: ob_start e ob_get_clean non bilanciati!\n";
    echo "     Questo potrebbe causare pagina vuota.\n";
} else {
    echo "  ✓ Output buffering bilanciato\n";
}

// 4. Verifica che ci sia un return nel metodo content()
$contentMethodPattern = '/protected function content\(\): string\s*\{(.*?)^\s*\}/ms';
if (preg_match($contentMethodPattern, $content, $matches)) {
    $methodBody = $matches[1];
    
    // Controlla return statement
    if (strpos($methodBody, 'return') === false) {
        echo "\n❌ PROBLEMA: Metodo content() non ha statement 'return'!\n";
    } else {
        // Verifica che il return sia alla fine del metodo
        $returnMatches = [];
        preg_match_all('/return\s+/', $methodBody, $returnMatches);
        $returnCount = count($returnMatches[0]);
        echo "\nStatements 'return': $returnCount\n";
        
        if (strpos($methodBody, 'return \'\'') !== false) {
            echo "⚠️  Trovato 'return ''' vuoto - questo causa pagina vuota!\n";
        }
    }
} else {
    echo "\n⚠️  Non riesco a estrarre il corpo del metodo content()\n";
}

// 5. Cerca errori comuni
echo "\nControllo problemi comuni:\n";

$issues = [];

// Cerca var_dump/die/exit che potrebbero interrompere l'esecuzione
if (stripos($content, 'var_dump') !== false) {
    $issues[] = "Trovato var_dump() - potrebbe causare output non desiderato";
}
if (preg_match('/\b(die|exit)\s*\(/', $content, $matches)) {
    $issues[] = "Trovato {$matches[0]} - potrebbe interrompere l'esecuzione";
}

// Cerca errori di escape PHP
$phpOpenCount = substr_count($content, '<?php');
$phpCloseCount = substr_count($content, '?>');
if ($phpOpenCount !== $phpCloseCount + 1) { // +1 perché il file inizia con <?php ma non dovrebbe chiudere
    $issues[] = "Tag PHP non bilanciati: <?php=$phpOpenCount, ?>=$phpCloseCount";
}

if (empty($issues)) {
    echo "  ✓ Nessun problema ovvio trovato\n";
} else {
    foreach ($issues as $issue) {
        echo "  ⚠️  $issue\n";
    }
}

// 6. Conta righe di codice nel metodo content()
echo "\n";
echo "Informazioni file:\n";
echo "  Dimensione: " . number_format(filesize($file)) . " bytes\n";
echo "  Righe totali: " . substr_count($content, "\n") . "\n";

// 7. Verifica dipendenze
echo "\n";
echo "Verifica dipendenze (use statements):\n";
$useStatements = [];
preg_match_all('/^use\s+([^;]+);/m', $content, $useStatements);
echo "  Trovati " . count($useStatements[1]) . " use statements\n";

// Verifica che le classi usate esistano
$missingClasses = [];
foreach ($useStatements[1] as $useClass) {
    $className = trim($useClass);
    // Salta function use
    if (strpos($className, 'function ') === 0) {
        continue;
    }
    
    // Mappa la classe al file
    $classFile = str_replace('\\', '/', str_replace('FP\\PerfSuite\\', '', $className)) . '.php';
    $fullPath = __DIR__ . '/../src/' . $classFile;
    
    if (!file_exists($fullPath)) {
        $missingClasses[] = $className;
    }
}

if (!empty($missingClasses)) {
    echo "\n❌ PROBLEMA: Classi mancanti:\n";
    foreach ($missingClasses as $class) {
        echo "  - $class\n";
    }
    echo "\nQuesta potrebbe essere la causa della pagina vuota!\n";
} else {
    echo "  ✓ Tutte le classi dipendenze esistono\n";
}

echo "\n";
echo "===========================\n";
echo "CONCLUSIONE\n";
echo "===========================\n\n";

if (!empty($missingClasses)) {
    echo "❌ Trovate classi mancanti. Questo causa sicuramente una pagina vuota.\n";
    echo "   Soluzione: Crea o ripristina i file mancanti.\n";
} elseif ($obStartCount !== $obGetCleanCount) {
    echo "❌ Output buffering non bilanciato. Questo può causare pagina vuota.\n";
    echo "   Soluzione: Controlla che ogni ob_start() abbia un ob_get_clean().\n";
} elseif (!empty($issues)) {
    echo "⚠️  Trovati possibili problemi ma non definitivi.\n";
    echo "   Soluzione: Controlla manualmente i problemi segnalati.\n";
} else {
    echo "✓ Non trovati problemi evidenti nel file.\n";
    echo "  Il problema potrebbe essere:\n";
    echo "  1. Errore runtime (servizi non disponibili)\n";
    echo "  2. Permessi insufficienti\n";
    echo "  3. Conflitto con altri plugin\n";
    echo "  4. Errore silenzioso di PHP\n\n";
    echo "  PROSSIMI PASSI:\n";
    echo "  1. Abilita WP_DEBUG e WP_DEBUG_LOG in wp-config.php\n";
    echo "  2. Controlla i log PHP del server\n";
    echo "  3. Disabilita altri plugin temporaneamente\n";
    echo "  4. Verifica i permessi dell'utente WordPress\n";
}

echo "\n";

