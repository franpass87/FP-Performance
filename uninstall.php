<?php
/**
 * Uninstall Script - FP Performance Suite
 * 
 * Rimuove TUTTE le opzioni, transient, file e directory del plugin
 * Eseguito SOLO quando plugin viene disinstallato
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// === 1. RIMUOVI TUTTE LE OPZIONI ===
$optionKeys = [
    // Core
    'fp_ps_page_cache',
    'fp_ps_browser_cache',
    'fp_ps_assets',
    'fp_ps_db',
    'fp_ps_settings',
    'fp_ps_preset',
    'fp_ps_critical_css',
    'fp_perfsuite_version',
    'fp_ps_version_check',
    'fp_ps_assets_version',
    
    // Cache
    'fp_ps_cache',
    'fp_ps_cache_headers',
    'fp_ps_edge_cache',
    'fp_ps_object_cache',
    
    // Assets
    'fp_ps_third_party_scripts',
    'fp_ps_custom_scripts',
    'fp_ps_exclusions',
    'fp_ps_font_settings',
    'fp_ps_http2_push',
    'fp_ps_smart_delivery',
    
    // Database
    'fp_ps_db_settings',
    'fp_ps_db_stats',
    
    // Backend
    'fp_ps_backend',
    'fp_ps_admin_bar_settings',
    'fp_ps_heartbeat',
    
    // Security
    'fp_ps_security',
    'fp_ps_htaccess_security',
    
    // Mobile
    'fp_ps_mobile',
    'fp_ps_touch_settings',
    'fp_ps_responsive_images',
    
    // ML/AI
    'fp_ps_ml_settings',
    'fp_ps_ml_predictor',
    'fp_ps_auto_tuner',
    'fp_ps_intelligence_enabled',
    
    // Monitoring
    'fp_ps_monitoring',
    'fp_ps_core_web_vitals',
    'fp_ps_performance_data',
    
    // CDN/Compression
    'fp_ps_cdn',
    'fp_ps_compression',
    
    // PWA
    'fp_ps_pwa',
    
    // Theme
    'fp_ps_salient_wpbakery',
    
    // Media
    'fp_ps_media_settings',
    
    // Recovery
    'fp_ps_recovery_mode',
];

foreach ($optionKeys as $key) {
    delete_option($key);
}

// === 2. RIMUOVI TUTTI I TRANSIENT ===
$wpdb->query(
    "DELETE FROM {$wpdb->options} 
     WHERE option_name LIKE '_transient_fp_%' 
     OR option_name LIKE '_transient_timeout_fp_%'"
);

// === 3. RIMUOVI DIRECTORY CACHE ===
$cacheDir = WP_CONTENT_DIR . '/cache/fp-performance';
if (is_dir($cacheDir)) {
    // Rimuovi ricorsivamente
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            @rmdir($file->getPathname());
        } else {
            @unlink($file->getPathname());
        }
    }
    
    @rmdir($cacheDir);
}

// === 4. RIMUOVI LOCK FILES ===
$uploadDir = wp_upload_dir();
$lockDir = $uploadDir['basedir'] . '/fp-performance-locks';
if (is_dir($lockDir)) {
    $locks = glob($lockDir . '/*.lock');
    foreach ($locks as $lock) {
        @unlink($lock);
    }
    @rmdir($lockDir);
}

// === 5. RIPRISTINA .HTACCESS (RIMUOVI REGOLE DEL PLUGIN) ===
$htaccessFile = ABSPATH . '.htaccess';
if (file_exists($htaccessFile)) {
    $content = @file_get_contents($htaccessFile);
    if ($content !== false) {
        // Rimuovi sezioni FP Performance
        $content = preg_replace(
            '/# BEGIN FP Performance Suite.*?# END FP Performance Suite/s',
            '',
            $content
        );
        
        // Pulisci linee vuote multiple
        $content = preg_replace("/\n{3,}/", "\n\n", $content);
        
        @file_put_contents($htaccessFile, $content);
    }
}

// === 6. CLEANUP TEMP FILES ===
$tempDir = sys_get_temp_dir();
$tempFiles = [
    $tempDir . '/fp_ps_ml_sem_*.lock',
    $tempDir . '/fp-perf-suite-*.tmp',
];

foreach ($tempFiles as $pattern) {
    $files = glob($pattern);
    if ($files) {
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}

// === 7. RIMUOVI BACKUP .HTACCESS ===
$backupPattern = ABSPATH . '.htaccess.fp-backup-*';
$backups = glob($backupPattern);
if ($backups) {
    foreach ($backups as $backup) {
        @unlink($backup);
    }
}

// Log finale
if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
    error_log('FP Performance Suite: Plugin disinstallato e cleanup completato');
}
