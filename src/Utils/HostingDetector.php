<?php

/**
 * Hosting Detector Utility
 * 
 * Rileva automaticamente il tipo di hosting e fornisce raccomandazioni
 * specifiche per ottimizzare il plugin in base all'ambiente
 * 
 * @package FP\PerfSuite\Utils
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 * @since 1.6.0
 */

namespace FP\PerfSuite\Utils;

class HostingDetector
{
    /**
     * Cache del risultato del rilevamento
     */
    private static ?bool $isSharedCache = null;
    
    /**
     * Cache delle capabilities
     */
    private static ?array $capabilitiesCache = null;
    
    /**
     * Rileva se l'ambiente è shared hosting
     * 
     * @return bool True se shared hosting, false se VPS/Dedicated
     */
    public static function isSharedHosting(): bool
    {
        // Usa cache se disponibile
        if (self::$isSharedCache !== null) {
            return self::$isSharedCache;
        }
        
        $indicators = 0;
        $totalChecks = 0;
        
        // 1. Check Memory Limit (shared tipicamente <= 256MB)
        $totalChecks++;
        $memoryLimit = self::parseMemoryLimit(ini_get('memory_limit'));
        if ($memoryLimit > 0 && $memoryLimit <= 256) {
            $indicators++;
        }
        
        // 2. Check Max Execution Time (shared tipicamente <= 30s)
        $totalChecks++;
        $maxTime = (int) ini_get('max_execution_time');
        if ($maxTime > 0 && $maxTime <= 30) {
            $indicators++;
        }
        
        // 3. Check .htaccess writable
        $totalChecks++;
        $htaccessPath = ABSPATH . '.htaccess';
        if (file_exists($htaccessPath) && !is_writable($htaccessPath)) {
            $indicators++;
        }
        
        // 4. Check disabled functions (shared spesso disabilita exec, shell_exec, etc.)
        $totalChecks++;
        $disabledFunctions = ini_get('disable_functions');
        $criticalFunctions = ['exec', 'shell_exec', 'proc_open', 'popen'];
        $disabledCount = 0;
        foreach ($criticalFunctions as $func) {
            if (stripos($disabledFunctions, $func) !== false) {
                $disabledCount++;
            }
        }
        if ($disabledCount >= 2) {
            $indicators++;
        }
        
        // 5. Check allow_url_fopen (shared spesso lo disabilita)
        $totalChecks++;
        if (!ini_get('allow_url_fopen')) {
            $indicators++;
        }
        
        // 6. Check upload_max_filesize (shared tipicamente <= 32MB)
        $totalChecks++;
        $uploadMax = self::parseMemoryLimit(ini_get('upload_max_filesize'));
        if ($uploadMax > 0 && $uploadMax <= 32) {
            $indicators++;
        }
        
        // 7. Check post_max_size (shared tipicamente <= 32MB)
        $totalChecks++;
        $postMax = self::parseMemoryLimit(ini_get('post_max_size'));
        if ($postMax > 0 && $postMax <= 32) {
            $indicators++;
        }
        
        // Se >= 50% degli indicatori sono positivi, è probabilmente shared hosting
        $isShared = ($indicators / $totalChecks) >= 0.5;
        
        // Cache il risultato
        self::$isSharedCache = $isShared;
        
        // Log per debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            Logger::debug(sprintf(
                'Hosting Detection: %s (%d/%d indicators)',
                $isShared ? 'SHARED' : 'VPS/Dedicated',
                $indicators,
                $totalChecks
            ));
        }
        
        return $isShared;
    }
    
    /**
     * Ottiene il preset raccomandato basato sull'ambiente
     * 
     * @return string Nome del preset raccomandato
     */
    public static function getRecommendedPreset(): string
    {
        if (self::isSharedHosting()) {
            return 'shared-hosting';
        }
        
        // Se ha Redis/Memcached, può usare preset più aggressivo
        if (self::hasObjectCache()) {
            return 'aggressive';
        }
        
        return 'balanced';
    }
    
    /**
     * Ottiene memory limit raccomandato per il plugin
     * 
     * @return string Memory limit (es. '256M', '512M')
     */
    public static function getRecommendedMemoryLimit(): string
    {
        return self::isSharedHosting() ? '256M' : '512M';
    }
    
    /**
     * Ottiene execution time limit raccomandato per il plugin
     * 
     * @return int Secondi
     */
    public static function getRecommendedTimeLimit(): int
    {
        return self::isSharedHosting() ? 30 : 60;
    }
    
    /**
     * Verifica se object cache (Redis/Memcached/APCu) è disponibile
     * 
     * @return bool
     */
    public static function hasObjectCache(): bool
    {
        // Redis
        if (class_exists('Redis') || extension_loaded('redis')) {
            return true;
        }
        
        // Memcached
        if (class_exists('Memcached') || extension_loaded('memcached')) {
            return true;
        }
        
        // APCu
        if (function_exists('apcu_fetch') && ini_get('apc.enabled')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Verifica se l'ambiente supporta operazioni pesanti
     * 
     * @return bool
     */
    public static function canHandleHeavyOperations(): bool
    {
        $caps = self::getCapabilities();
        
        // Richiede almeno 512MB RAM e 60s execution time
        return $caps['memory_mb'] >= 512 && $caps['max_execution_time'] >= 60;
    }
    
    /**
     * Ottiene le capabilities dell'ambiente
     * 
     * @return array Capabilities dettagliate
     */
    public static function getCapabilities(): array
    {
        if (self::$capabilitiesCache !== null) {
            return self::$capabilitiesCache;
        }
        
        // FIX: Calcola prima i valori base per evitare ricorsione infinita
        $memoryMb = self::parseMemoryLimit(ini_get('memory_limit'));
        $maxExecutionTime = (int) ini_get('max_execution_time');
        
        // Calcola can_handle_heavy_ops direttamente qui per evitare ricorsione
        $canHandleHeavyOps = ($memoryMb >= 512 && $maxExecutionTime >= 60);
        
        $caps = [
            'is_shared' => self::isSharedHosting(),
            'memory_mb' => $memoryMb,
            'max_execution_time' => $maxExecutionTime,
            'upload_max_mb' => self::parseMemoryLimit(ini_get('upload_max_filesize')),
            'post_max_mb' => self::parseMemoryLimit(ini_get('post_max_size')),
            'has_object_cache' => self::hasObjectCache(),
            'allow_url_fopen' => (bool) ini_get('allow_url_fopen'),
            'htaccess_writable' => is_writable(ABSPATH . '.htaccess'),
            'wp_content_writable' => is_writable(WP_CONTENT_DIR),
            'disabled_functions' => array_map('trim', explode(',', ini_get('disable_functions'))),
            'recommended_preset' => self::getRecommendedPreset(),
            'can_handle_heavy_ops' => $canHandleHeavyOps, // FIX: Usa valore calcolato localmente
        ];
        
        self::$capabilitiesCache = $caps;
        
        return $caps;
    }
    
    /**
     * Ottiene informazioni hosting per display admin
     * 
     * @return array Info formattate per UI
     */
    public static function getHostingInfo(): array
    {
        $caps = self::getCapabilities();
        
        return [
            'type' => $caps['is_shared'] ? 'Shared Hosting' : 'VPS/Dedicated',
            'type_badge' => $caps['is_shared'] ? 'warning' : 'success',
            'memory' => $caps['memory_mb'] . ' MB',
            'execution_time' => $caps['max_execution_time'] . 's',
            'object_cache' => $caps['has_object_cache'] ? 'Disponibile' : 'Non disponibile',
            'recommended_preset' => $caps['recommended_preset'],
            'can_use_ml' => $caps['can_handle_heavy_ops'] ? 'Sì' : 'No (risorse insufficienti)',
            'can_optimize_images' => $caps['memory_mb'] >= 256 ? 'Sì' : 'No (memoria insufficiente)',
            'warnings' => self::getWarnings($caps),
        ];
    }
    
    /**
     * Ottiene warnings basati sulle capabilities
     * 
     * @param array $caps Capabilities
     * @return array Lista warnings
     */
    private static function getWarnings(array $caps): array
    {
        $warnings = [];
        
        if ($caps['is_shared']) {
            $warnings[] = 'Shared hosting rilevato: alcune funzionalità avanzate saranno disabilitate per sicurezza';
        }
        
        if (!$caps['has_object_cache']) {
            $warnings[] = 'Object cache non disponibile: considera Redis o Memcached per performance ottimali';
        }
        
        if ($caps['memory_mb'] < 256) {
            $warnings[] = 'Memoria limitata (' . $caps['memory_mb'] . 'MB): disabilita servizi ML e ottimizzazioni immagini pesanti';
        }
        
        if ($caps['max_execution_time'] < 30) {
            $warnings[] = 'Execution time limitato (' . $caps['max_execution_time'] . 's): operazioni batch potrebbero fallire';
        }
        
        if (!$caps['htaccess_writable']) {
            $warnings[] = 'File .htaccess non scrivibile: regole di cache non possono essere applicate automaticamente';
        }
        
        if (!$caps['allow_url_fopen']) {
            $warnings[] = 'allow_url_fopen disabilitato: alcune funzionalità remote potrebbero non funzionare';
        }
        
        return $warnings;
    }
    
    /**
     * Parse memory limit string in MB
     * 
     * @param string $size Size string (es. '256M', '1G', '512000')
     * @return int Size in MB
     */
    private static function parseMemoryLimit(string $size): int
    {
        if (empty($size) || $size === '-1') {
            return -1; // Unlimited
        }
        
        $size = trim($size);
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
                break;
            case 'm':
                // Already in MB
                break;
            case 'k':
                $value = (int) ($value / 1024);
                break;
            default:
                // Assume bytes
                $value = (int) ($value / 1048576);
        }
        
        return $value;
    }
    
    /**
     * Reset cache (utile per testing)
     */
    public static function resetCache(): void
    {
        self::$isSharedCache = null;
        self::$capabilitiesCache = null;
    }
    
    /**
     * Verifica se un servizio specifico dovrebbe essere abilitato
     * 
     * @param string $service Nome del servizio
     * @return bool True se può essere abilitato
     */
    public static function canEnableService(string $service): bool
    {
        $caps = self::getCapabilities();
        
        // Servizi sempre sicuri
        $safeSevices = [
            'PageCache',
            'Headers',
            'CompressionManager',
            'LazyLoadManager',
            'MobileOptimizer',
        ];
        
        if (in_array($service, $safeSevices, true)) {
            return true;
        }
        
        // Servizi che richiedono risorse moderate
        $moderateServices = [
            'Optimizer',
            'CSSOptimizer',
            'ScriptOptimizer',
            'DatabaseOptimizer',
        ];
        
        if (in_array($service, $moderateServices, true)) {
            return $caps['memory_mb'] >= 256;
        }
        
        // Servizi pesanti (ML, Image Processing, ecc.)
        $heavyServices = [
            'MLPredictor',
            'AutoTuner',
            'ImageOptimizer',
            'CriticalCssAutomation',
            'JavaScriptTreeShaker',
        ];
        
        if (in_array($service, $heavyServices, true)) {
            return $caps['can_handle_heavy_ops'];
        }
        
        // Servizi che richiedono .htaccess writable
        if ($service === 'HtaccessSecurity') {
            return $caps['htaccess_writable'];
        }
        
        // Servizi che richiedono object cache
        if ($service === 'ObjectCacheManager') {
            return $caps['has_object_cache'];
        }
        
        // Default: permetti su non-shared
        return !$caps['is_shared'];
    }
}

