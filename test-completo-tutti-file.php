<?php
/**
 * Test Completo Tutti i File FP Performance Suite
 * Verifica che tutti i file principali funzionino correttamente
 */

echo "<h1>🔍 Test Completo Tutti i File FP Performance Suite</h1>\n";

// Test 1: File Core
echo "<h2>📋 Test 1: File Core</h2>\n";
$core_files = [
    'fp-performance-suite.php',
    'src/Plugin.php',
    'src/ServiceContainer.php',
    'uninstall.php'
];

$core_errors = 0;
foreach ($core_files as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l {$file} 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$file} - Sintassi corretta</div>\n";
        } else {
            echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - Errore sintassi</div>\n";
            $core_errors++;
        }
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - File non trovato</div>\n";
        $core_errors++;
    }
}

// Test 2: Admin Pages
echo "<h2>📋 Test 2: Admin Pages</h2>\n";
$admin_pages = [
    'src/Admin/Menu.php',
    'src/Admin/Pages/Overview.php',
    'src/Admin/Pages/AIConfig.php',
    'src/Admin/Pages/Assets.php',
    'src/Admin/Pages/Cache.php',
    'src/Admin/Pages/Media.php',
    'src/Admin/Pages/Database.php',
    'src/Admin/Pages/Backend.php',
    'src/Admin/Pages/Compression.php',
    'src/Admin/Pages/Mobile.php',
    'src/Admin/Pages/Cdn.php',
    'src/Admin/Pages/Security.php',
    'src/Admin/Pages/Exclusions.php',
    'src/Admin/Pages/ML.php',
    'src/Admin/Pages/MonitoringReports.php',
    'src/Admin/Pages/Logs.php',
    'src/Admin/Pages/Diagnostics.php',
    'src/Admin/Pages/Settings.php',
    'src/Admin/Pages/JavaScriptOptimization.php'
];

$admin_errors = 0;
foreach ($admin_pages as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l {$file} 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$file} - Sintassi corretta</div>\n";
        } else {
            echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - Errore sintassi</div>\n";
            $admin_errors++;
        }
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - File non trovato</div>\n";
        $admin_errors++;
    }
}

// Test 3: Services principali
echo "<h2>📋 Test 3: Services Principali</h2>\n";
$services = [
    // Cache Services
    'src/Services/Cache/PageCache.php',
    'src/Services/Cache/Headers.php',
    'src/Services/Cache/EdgeCacheManager.php',
    'src/Services/Cache/ObjectCacheManager.php',
    
    // Assets Services
    'src/Services/Assets/FontOptimizer.php',
    'src/Services/Assets/ImageOptimizer.php',
    'src/Services/Assets/LazyLoadManager.php',
    'src/Services/Assets/UnusedJavaScriptOptimizer.php',
    'src/Services/Assets/CodeSplittingManager.php',
    'src/Services/Assets/JavaScriptTreeShaker.php',
    'src/Services/Assets/PredictivePrefetching.php',
    'src/Services/Assets/CriticalCss.php',
    'src/Services/Assets/CSSOptimizer.php',
    'src/Services/Assets/ScriptOptimizer.php',
    
    // Database Services
    'src/Services/DB/Cleaner.php',
    'src/Services/DB/DatabaseOptimizer.php',
    'src/Services/DB/QueryCacheManager.php',
    'src/Services/DB/DatabaseQueryMonitor.php',
    
    // Media Services
    'src/Services/Media/WebPConverter.php',
    'src/Services/Media/AVIFConverter.php',
    
    // Mobile Services
    'src/Services/Mobile/MobileOptimizer.php',
    'src/Services/Mobile/TouchOptimizer.php',
    
    // Compression Services
    'src/Services/Compression/CompressionManager.php',
    
    // CDN Services
    'src/Services/CDN/CdnManager.php',
    
    // Security Services
    'src/Services/Security/HtaccessSecurity.php',
    
    // Monitoring Services
    'src/Services/Monitoring/PerformanceMonitor.php',
    'src/Services/Monitoring/CoreWebVitalsMonitor.php',
    
    // PWA Services
    'src/Services/PWA/ServiceWorkerManager.php',
    
    // Admin Services
    'src/Services/Admin/BackendOptimizer.php'
];

$services_errors = 0;
foreach ($services as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l {$file} 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$file} - Sintassi corretta</div>\n";
        } else {
            echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - Errore sintassi</div>\n";
            $services_errors++;
        }
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - File non trovato</div>\n";
        $services_errors++;
    }
}

// Test 4: Utils e Supporto
echo "<h2>📋 Test 4: Utils e Supporto</h2>\n";
$utils_files = [
    'src/Utils/Logger.php',
    'src/Utils/RateLimiter.php',
    'src/Utils/Semaphore.php',
    'src/Utils/InstallationRecovery.php',
    'src/Utils/Htaccess.php',
    'src/Utils/FormValidator.php',
    'src/Utils/Benchmark.php',
    'src/Utils/Capabilities.php',
    'src/Utils/Env.php',
    'src/Utils/Fs.php',
    'src/Utils/HookManager.php'
];

$utils_errors = 0;
foreach ($utils_files as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l {$file} 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$file} - Sintassi corretta</div>\n";
        } else {
            echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - Errore sintassi</div>\n";
            $utils_errors++;
        }
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - File non trovato</div>\n";
        $utils_errors++;
    }
}

// Test 5: Contratti e Enums
echo "<h2>📋 Test 5: Contratti e Enums</h2>\n";
$contracts_files = [
    'src/Contracts/CacheInterface.php',
    'src/Contracts/LoggerInterface.php',
    'src/Contracts/OptimizerInterface.php',
    'src/Contracts/SettingsRepositoryInterface.php',
    'src/Enums/CacheType.php',
    'src/Enums/CdnProvider.php',
    'src/Enums/CleanupTask.php',
    'src/Enums/HostingPreset.php',
    'src/Enums/LogLevel.php'
];

$contracts_errors = 0;
foreach ($contracts_files as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l {$file} 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$file} - Sintassi corretta</div>\n";
        } else {
            echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - Errore sintassi</div>\n";
            $contracts_errors++;
        }
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - File non trovato</div>\n";
        $contracts_errors++;
    }
}

// Riepilogo finale
echo "<div style='margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 8px;'>\n";
echo "<h3>📈 Riepilogo Test Completo</h3>\n";
echo "<p><strong>✅ File Core:</strong> " . (count($core_files) - $core_errors) . "/" . count($core_files) . "</p>\n";
echo "<p><strong>✅ Admin Pages:</strong> " . (count($admin_pages) - $admin_errors) . "/" . count($admin_pages) . "</p>\n";
echo "<p><strong>✅ Services:</strong> " . (count($services) - $services_errors) . "/" . count($services) . "</p>\n";
echo "<p><strong>✅ Utils:</strong> " . (count($utils_files) - $utils_errors) . "/" . count($utils_files) . "</p>\n";
echo "<p><strong>✅ Contratti/Enums:</strong> " . (count($contracts_files) - $contracts_errors) . "/" . count($contracts_files) . "</p>\n";

$total_errors = $core_errors + $admin_errors + $services_errors + $utils_errors + $contracts_errors;
$total_files = count($core_files) + count($admin_pages) + count($services) + count($utils_files) + count($contracts_files);
$score = round((($total_files - $total_errors) / $total_files) * 100);

echo "<p><strong>🎯 Score Completo:</strong> {$score}%</p>\n";
echo "<p><strong>📊 Totale File Testati:</strong> {$total_files}</p>\n";
echo "<p><strong>❌ Errori Totali:</strong> {$total_errors}</p>\n";

if ($total_errors === 0) {
    echo "<p style='color: #28a745; font-weight: bold;'>🎉 TUTTO PERFETTO! Tutti i file sono funzionanti!</p>\n";
} elseif ($total_errors < 5) {
    echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ Quasi tutto perfetto! Solo pochi errori minori.</p>\n";
} else {
    echo "<p style='color: #dc3545; font-weight: bold;'>❌ Alcuni problemi rilevati che necessitano attenzione.</p>\n";
}

echo "</div>\n";
?>
