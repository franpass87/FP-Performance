<?php
/**
 * Test per verificare le ottimizzazioni del sistema di logging
 * 
 * Questo script testa che i messaggi di debug ripetitivi vengano filtrati
 * e che il sistema di logging funzioni correttamente.
 */

// Simula l'ambiente WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Includi le classi necessarie
require_once 'src/Utils/Logger.php';

use FP\PerfSuite\Utils\Logger;

echo "=== Test Ottimizzazioni Debug Logging ===\n\n";

// Test 1: Verifica che i messaggi ripetitivi vengano filtrati
echo "Test 1: Filtro messaggi ripetitivi\n";
echo "-----------------------------------\n";

$repetitiveMessages = [
    'Theme compatibility initialized',
    'Compatibility filters registered',
    'Predictive Prefetching registered',
    'Cache file count refreshed',
];

foreach ($repetitiveMessages as $message) {
    echo "Logging: {$message}\n";
    Logger::debug($message, ['test' => true]);
    
    // Secondo log dello stesso messaggio (dovrebbe essere filtrato)
    echo "Logging again: {$message}\n";
    Logger::debug($message, ['test' => true]);
}

echo "\n";

// Test 2: Verifica che i messaggi non ripetitivi vengano loggati
echo "Test 2: Messaggi non ripetitivi\n";
echo "--------------------------------\n";

$uniqueMessages = [
    'Unique message 1',
    'Unique message 2',
    'Another unique message',
];

foreach ($uniqueMessages as $message) {
    echo "Logging: {$message}\n";
    Logger::debug($message, ['test' => true]);
}

echo "\n";

// Test 3: Verifica livelli di logging
echo "Test 3: Livelli di logging\n";
echo "--------------------------\n";

Logger::error('Test error message', ['test' => true]);
Logger::warning('Test warning message', ['test' => true]);
Logger::info('Test info message', ['test' => true]);
Logger::debug('Test debug message', ['test' => true]);

echo "\n";

// Test 4: Verifica configurazione FP_PS_DISABLE_DEBUG_LOGS
echo "Test 4: Disabilitazione debug logs\n";
echo "-----------------------------------\n";

// Simula la disabilitazione
define('FP_PS_DISABLE_DEBUG_LOGS', true);

echo "Con FP_PS_DISABLE_DEBUG_LOGS = true:\n";
Logger::debug('This should not be logged', ['test' => true]);

// I messaggi di errore e warning dovrebbero ancora essere loggati
Logger::error('This error should still be logged', ['test' => true]);
Logger::warning('This warning should still be logged', ['test' => true]);

echo "\n";

// Test 5: Verifica cache dei messaggi
echo "Test 5: Cache messaggi ripetitivi\n";
echo "---------------------------------\n";

// Reset della cache (simulato)
$reflection = new ReflectionClass(Logger::class);
$loggedMessagesProperty = $reflection->getProperty('loggedMessages');
$loggedMessagesProperty->setAccessible(true);
$loggedMessagesProperty->setValue(null, []);

$lastCleanupProperty = $reflection->getProperty('lastCleanup');
$lastCleanupProperty->setAccessible(true);
$lastCleanupProperty->setValue(null, 0);

echo "Cache resettata. Testando di nuovo messaggi ripetitivi:\n";

foreach ($repetitiveMessages as $message) {
    echo "Logging: {$message}\n";
    Logger::debug($message, ['test' => true]);
}

echo "\n=== Test Completato ===\n";
echo "Controlla il file di log per verificare che:\n";
echo "1. I messaggi ripetitivi vengano loggati solo una volta\n";
echo "2. I messaggi unici vengano loggati normalmente\n";
echo "3. I livelli di logging funzionino correttamente\n";
echo "4. La disabilitazione dei debug logs funzioni\n";
echo "5. La cache dei messaggi ripetitivi funzioni\n";
