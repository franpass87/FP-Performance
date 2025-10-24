<?php
/**
 * Sostituisce il plugin principale con la versione sicura
 */

echo "🔄 Sostituzione Plugin con Versione Sicura\n";
echo "==========================================\n\n";

// Crea backup del file originale
$original_file = __DIR__ . '/fp-performance-suite.php';
$backup_file = __DIR__ . '/fp-performance-suite.php.backup.' . date('Y-m-d-H-i-s');

if (file_exists($original_file)) {
    if (copy($original_file, $backup_file)) {
        echo "✅ Backup creato: " . basename($backup_file) . "\n";
    } else {
        echo "⚠️ Impossibile creare backup\n";
    }
} else {
    echo "❌ File originale non trovato\n";
    exit(1);
}

// Sostituisci con la versione sicura
$safe_file = __DIR__ . '/fp-performance-suite-safe.php';

if (file_exists($safe_file)) {
    if (copy($safe_file, $original_file)) {
        echo "✅ Plugin sostituito con versione sicura\n";
        echo "Il plugin ora dovrebbe funzionare senza causare white screen\n";
    } else {
        echo "❌ Impossibile sostituire il plugin\n";
        exit(1);
    }
} else {
    echo "❌ File versione sicura non trovato\n";
    exit(1);
}

echo "\n📋 Prossimi passi:\n";
echo "1. Verifica che il tuo sito WordPress funzioni normalmente\n";
echo "2. Controlla le pagine principali\n";
echo "3. Se tutto funziona, il problema è risolto\n";
echo "4. Puoi riattivare gradualmente le funzionalità del plugin\n\n";

echo "🔧 Se il problema persiste:\n";
echo "- Ripristina il backup: mv " . basename($backup_file) . " fp-performance-suite.php\n";
echo "- Disattiva completamente il plugin da wp-admin/plugins.php\n\n";

echo "Script completato.\n";
?>
