<?php
/**
 * Test System Metrics Collection
 * 
 * Questo script testa la raccolta delle metriche del sistema server
 * 
 * @package FP\PerfSuite
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

// Carica WordPress
require_once dirname(__FILE__) . '/wp-config.php';

// Carica il plugin
require_once dirname(__FILE__) . '/fp-performance-suite.php';

use FP\PerfSuite\Services\Monitoring\SystemMonitor;

echo "=== Test System Metrics Collection ===\n\n";

try {
    // Ottieni istanza del monitor
    $monitor = SystemMonitor::instance();
    
    echo "1. Raccogliendo metriche del sistema...\n";
    $metrics = $monitor->collectMetrics();
    
    echo "   ✓ Metriche raccolte con successo\n";
    echo "   - Timestamp: " . date('Y-m-d H:i:s', $metrics['timestamp']) . "\n";
    echo "   - Memoria corrente: " . $metrics['memory_usage']['current_mb'] . " MB\n";
    echo "   - Memoria picco: " . $metrics['memory_usage']['peak_mb'] . " MB\n";
    echo "   - Utilizzo memoria: " . $metrics['memory_usage']['usage_percent'] . "%\n";
    echo "   - Spazio disco usato: " . $metrics['disk_usage']['used_gb'] . " GB\n";
    echo "   - Spazio disco libero: " . $metrics['disk_usage']['free_gb'] . " GB\n";
    echo "   - Utilizzo disco: " . $metrics['disk_usage']['usage_percent'] . "%\n";
    echo "   - Carico sistema (1min): " . $metrics['load_average']['1min'] . "\n";
    echo "   - PHP Version: " . $metrics['php_version'] . "\n";
    echo "   - Server: " . $metrics['server_software'] . "\n";
    echo "   - Database size: " . $metrics['database_size']['size_mb'] . " MB\n";
    
    echo "\n2. Ottenendo statistiche aggregate...\n";
    $stats = $monitor->getStats(7);
    
    echo "   ✓ Statistiche ottenute\n";
    echo "   - Campioni: " . $stats['samples'] . "\n";
    echo "   - Memoria media: " . $stats['memory']['avg_usage_mb'] . " MB\n";
    echo "   - Memoria picco max: " . $stats['memory']['max_peak_mb'] . " MB\n";
    echo "   - Utilizzo memoria medio: " . $stats['memory']['avg_usage_percent'] . "%\n";
    echo "   - Spazio disco totale: " . $stats['disk']['total_gb'] . " GB\n";
    echo "   - Spazio disco libero: " . $stats['disk']['free_gb'] . " GB\n";
    echo "   - Utilizzo disco: " . $stats['disk']['usage_percent'] . "%\n";
    echo "   - Carico medio (1min): " . $stats['load']['avg_1min'] . "\n";
    echo "   - Database size: " . $stats['database']['size_mb'] . " MB\n";
    echo "   - Database tables: " . $stats['database']['tables'] . "\n";
    
    echo "\n3. Test completato con successo! ✓\n";
    
} catch (Exception $e) {
    echo "❌ Errore durante il test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Fine Test ===\n";
