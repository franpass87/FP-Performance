<?php
/**
 * Test Semplificato Sistema Semafori - FP Performance Suite
 * 
 * Verifica che tutti i semafori e rate limiter funzionino correttamente
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

// Simula funzioni WordPress
if (!function_exists('sanitize_key')) {
    function sanitize_key($key) {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower($key));
    }
}

if (!function_exists('get_transient')) {
    function get_transient($key) {
        return isset($_GLOBALS['transients'][$key]) ? $_GLOBALS['transients'][$key] : false;
    }
}

if (!function_exists('set_transient')) {
    function set_transient($key, $value, $expiration) {
        $_GLOBALS['transients'][$key] = $value;
        return true;
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient($key) {
        unset($_GLOBALS['transients'][$key]);
        return true;
    }
}

if (!function_exists('do_action')) {
    function do_action($hook, ...$args) {
        // Simula hook WordPress
        return true;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) {
        return true;
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512) {
        return json_encode($data, $options, $depth);
    }
}

// Logger è incluso dal file src/Utils/Logger.php

// Include le classi necessarie
require_once __DIR__ . '/src/Utils/Logger.php';
require_once __DIR__ . '/src/Utils/RateLimiter.php';
require_once __DIR__ . '/src/Utils/MLSemaphore.php';
require_once __DIR__ . '/src/Utils/MobileRateLimiter.php';
require_once __DIR__ . '/src/Utils/CompressionLock.php';

use FP\PerfSuite\Utils\RateLimiter;
use FP\PerfSuite\Utils\MLSemaphore;
use FP\PerfSuite\Utils\MobileRateLimiter;
use FP\PerfSuite\Utils\CompressionLock;

echo "🚦 TEST SISTEMA SEMAFORI SEMPLIFICATO\n";
echo "=====================================\n\n";

$tests = [];
$passed = 0;
$failed = 0;

/**
 * Helper per eseguire test
 */
function runTest(string $name, callable $test): void
{
    global $tests, $passed, $failed;
    
    echo "🧪 Test: $name\n";
    
    try {
        $result = $test();
        if ($result) {
            echo "✅ PASSED\n";
            $passed++;
        } else {
            echo "❌ FAILED\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        $failed++;
    }
    
    echo "\n";
}

/**
 * Test Rate Limiter
 */
runTest('Rate Limiter - Operazioni permesse', function() {
    $rateLimiter = new RateLimiter();
    
    // Prima operazione dovrebbe essere permessa
    $result1 = $rateLimiter->isAllowed('test_operation', 3, 60);
    
    // Seconda operazione dovrebbe essere permessa
    $result2 = $rateLimiter->isAllowed('test_operation', 3, 60);
    
    return $result1 && $result2;
});

runTest('Rate Limiter - Limite raggiunto', function() {
    $rateLimiter = new RateLimiter();
    
    // Reset per test pulito
    $rateLimiter->reset('test_operation_limit');
    
    // Consuma tutti i tentativi
    for ($i = 0; $i < 3; $i++) {
        $rateLimiter->isAllowed('test_operation_limit', 3, 60);
    }
    
    // Il quarto tentativo dovrebbe fallire
    $result = $rateLimiter->isAllowed('test_operation_limit', 3, 60);
    
    return !$result; // Dovrebbe essere false
});

/**
 * Test ML Semaphore
 */
runTest('ML Semaphore - Acquisizione e rilascio', function() {
    // Acquire semaphore
    $acquired = MLSemaphore::acquire('test_ml_operation');
    
    if (!$acquired) {
        return false;
    }
    
    // Check if acquired
    $isAcquired = MLSemaphore::isAcquired('test_ml_operation');
    
    // Release semaphore
    $released = MLSemaphore::release('test_ml_operation');
    
    return $acquired && $isAcquired && $released;
});

runTest('ML Semaphore - Conflitto simultaneo', function() {
    // Acquire first semaphore
    $acquired1 = MLSemaphore::acquire('test_ml_conflict');
    
    if (!$acquired1) {
        return false;
    }
    
    // Try to acquire same semaphore (should fail)
    $acquired2 = MLSemaphore::acquire('test_ml_conflict');
    
    // Release first
    MLSemaphore::release('test_ml_conflict');
    
    return $acquired1 && !$acquired2;
});

/**
 * Test Mobile Rate Limiter
 */
runTest('Mobile Rate Limiter - Operazioni permesse', function() {
    // Reset per test pulito
    MobileRateLimiter::clearAll();
    
    // Prima operazione dovrebbe essere permessa
    $result1 = MobileRateLimiter::isAllowed('responsive_images');
    
    // Seconda operazione dovrebbe essere permessa
    $result2 = MobileRateLimiter::isAllowed('responsive_images');
    
    return $result1 && $result2;
});

runTest('Mobile Rate Limiter - Limite raggiunto', function() {
    // Reset per test pulito
    MobileRateLimiter::clearAll();
    
    // Consuma tutti i tentativi (limite default: 10)
    for ($i = 0; $i < 10; $i++) {
        MobileRateLimiter::isAllowed('responsive_images');
    }
    
    // L'undicesimo tentativo dovrebbe fallire
    $result = MobileRateLimiter::isAllowed('responsive_images');
    
    return !$result; // Dovrebbe essere false
});

/**
 * Test Compression Lock
 */
runTest('Compression Lock - Acquisizione e rilascio', function() {
    $testFile = sys_get_temp_dir() . '/test_compression_file.jpg';
    
    // Acquire lock
    $lock = CompressionLock::acquire('test_compression', $testFile);
    
    if (!$lock) {
        return false;
    }
    
    // Check if locked
    $isLocked = CompressionLock::isLocked('test_compression', $testFile);
    
    // Release lock
    $released = CompressionLock::release($lock, 'test_compression', $testFile);
    
    return $lock && $isLocked && $released;
});

runTest('Compression Lock - Conflitto simultaneo', function() {
    $testFile = sys_get_temp_dir() . '/test_compression_conflict.jpg';
    
    // Acquire first lock
    $lock1 = CompressionLock::acquire('test_compression_conflict', $testFile);
    
    if (!$lock1) {
        return false;
    }
    
    // Try to acquire same lock (should fail)
    $lock2 = CompressionLock::acquire('test_compression_conflict', $testFile);
    
    // Release first
    CompressionLock::release($lock1, 'test_compression_conflict', $testFile);
    
    return $lock1 && !$lock2;
});

/**
 * Test Integrazione Cache Locks
 */
runTest('Cache Locks - Simulazione scrittura sicura', function() {
    $testFile = sys_get_temp_dir() . '/test_cache_file.html';
    $testContent = '<html><body>Test Cache Content</body></html>';
    
    // Simula scrittura cache con lock
    $lockFile = $testFile . '.lock';
    $lock = fopen($lockFile, 'c+');
    
    if (!$lock) {
        return false;
    }
    
    // Acquire exclusive lock
    if (!flock($lock, LOCK_EX | LOCK_NB)) {
        fclose($lock);
        return false;
    }
    
    try {
        // Write content
        $result = file_put_contents($testFile, $testContent);
        
        if ($result === false) {
            return false;
        }
        
        // Verify content
        $readContent = file_get_contents($testFile);
        
        return $readContent === $testContent;
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
        @unlink($lockFile);
        @unlink($testFile);
    }
});

/**
 * Test Cleanup
 */
runTest('Cleanup - Pulizia semafori', function() {
    // Test cleanup ML semaphores
    $mlCleaned = MLSemaphore::cleanupAll();
    
    // Test cleanup compression locks
    $compressionCleaned = CompressionLock::cleanupAll();
    
    // Test cleanup mobile rate limiter
    $mobileCleaned = MobileRateLimiter::clearAll();
    
    return $mlCleaned >= 0 && $compressionCleaned >= 0 && $mobileCleaned >= 0;
});

echo "📊 RISULTATI FINALI\n";
echo "==================\n";
echo "✅ Test Passati: $passed\n";
echo "❌ Test Falliti: $failed\n";
echo "📈 Success Rate: " . round(($passed / ($passed + $failed)) * 100, 2) . "%\n\n";

if ($failed === 0) {
    echo "🎉 TUTTI I TEST SONO PASSATI! Sistema di semafori funzionante.\n";
} else {
    echo "⚠️  Alcuni test sono falliti. Verificare l'implementazione.\n";
}

echo "\n🧹 Pulizia finale...\n";

// Cleanup finale
MLSemaphore::cleanupAll();
CompressionLock::cleanupAll();
MobileRateLimiter::clearAll();

echo "✅ Pulizia completata.\n";
echo "\n🚦 Test Sistema Semafori Completato!\n";
