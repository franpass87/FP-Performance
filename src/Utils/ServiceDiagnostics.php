<?php

namespace FP\PerfSuite\Utils;

/**
 * Diagnostica dei servizi del plugin
 * Verifica che i servizi si attivino correttamente quando le opzioni sono abilitate
 * 
 * @author Francesco Passeri
 */
class ServiceDiagnostics
{
    /**
     * Verifica lo stato di tutti i servizi principali
     * 
     * @return array Array con stato di ogni servizio
     */
    public static function checkAllServices(): array
    {
        return [
            'cache' => self::checkCacheServices(),
            'assets' => self::checkAssetServices(),
            'database' => self::checkDatabaseServices(),
            'mobile' => self::checkMobileServices(),
            'security' => self::checkSecurityServices(),
            'compression' => self::checkCompressionServices(),
            'monitoring' => self::checkMonitoringServices(),
        ];
    }
    
    /**
     * Verifica servizi di cache
     */
    private static function checkCacheServices(): array
    {
        $pageCacheSettings = get_option('fp_ps_page_cache_settings', []);
        $browserCacheSettings = get_option('fp_ps_browser_cache', []);
        $objectCacheSettings = get_option('fp_ps_object_cache', []);
        
        return [
            'page_cache' => [
                'enabled' => !empty($pageCacheSettings['enabled']),
                'option_exists' => $pageCacheSettings !== false,
                'hooks_registered' => self::checkHook('template_redirect', 'PageCache'),
                'settings' => $pageCacheSettings,
            ],
            'browser_cache' => [
                'enabled' => !empty($browserCacheSettings['enabled']),
                'option_exists' => $browserCacheSettings !== false,
                'hooks_registered' => self::checkHook('wp_headers', 'Headers'),
                'settings' => $browserCacheSettings,
            ],
            'object_cache' => [
                'enabled' => !empty($objectCacheSettings['enabled']),
                'option_exists' => $objectCacheSettings !== false,
                'driver' => $objectCacheSettings['driver'] ?? 'none',
            ],
        ];
    }
    
    /**
     * Verifica servizi asset
     */
    private static function checkAssetServices(): array
    {
        $assetSettings = get_option('fp_ps_assets', []);
        
        return [
            'optimizer' => [
                'enabled' => !empty($assetSettings['enabled']) || get_option('fp_ps_asset_optimization_enabled', false),
                'option_exists' => $assetSettings !== false,
                'minify_html' => !empty($assetSettings['minify_html']),
                'defer_js' => !empty($assetSettings['defer_js']),
                'async_css' => !empty($assetSettings['async_css']),
                'hooks_registered' => [
                    'template_redirect' => self::checkHook('template_redirect', 'Optimizer'),
                    'script_loader_tag' => self::checkHook('script_loader_tag', 'Optimizer'),
                    'style_loader_tag' => self::checkHook('style_loader_tag', 'Optimizer'),
                ],
                'settings' => $assetSettings,
            ],
            'lazy_load' => [
                'enabled' => get_option('fp_ps_lazy_load_enabled', false),
            ],
            'font_optimizer' => [
                'enabled' => get_option('fp_ps_font_optimization_enabled', false),
            ],
        ];
    }
    
    /**
     * Verifica servizi database
     */
    private static function checkDatabaseServices(): array
    {
        $dbSettings = get_option('fp_ps_db', []);
        
        return [
            'cleaner' => [
                'enabled' => isset($dbSettings['schedule']) && $dbSettings['schedule'] !== 'manual',
                'schedule' => $dbSettings['schedule'] ?? 'manual',
                'cron_registered' => wp_next_scheduled('fp_ps_db_cleanup'),
                'settings' => $dbSettings,
            ],
            'optimizer' => [
                'enabled' => !empty($dbSettings['enabled']),
                'option_exists' => $dbSettings !== false,
            ],
            'query_cache' => [
                'enabled' => !empty($dbSettings['query_cache_enabled']),
            ],
        ];
    }
    
    /**
     * Verifica servizi mobile
     */
    private static function checkMobileServices(): array
    {
        $mobileSettings = get_option('fp_ps_mobile_optimizer', []);
        $touchSettings = get_option('fp_ps_touch_optimizer', []);
        
        return [
            'mobile_optimizer' => [
                'enabled' => !empty($mobileSettings['enabled']),
                'settings' => $mobileSettings,
            ],
            'touch_optimizer' => [
                'enabled' => !empty($touchSettings['enabled']),
                'settings' => $touchSettings,
            ],
        ];
    }
    
    /**
     * Verifica servizi security
     */
    private static function checkSecurityServices(): array
    {
        $securitySettings = get_option('fp_ps_htaccess_security', []);
        
        return [
            'htaccess_security' => [
                'enabled' => !empty($securitySettings['enabled']),
                'security_headers' => $securitySettings['security_headers']['enabled'] ?? false,
                'xmlrpc_disabled' => $securitySettings['xmlrpc_disabled'] ?? false,
                'hotlink_protection' => $securitySettings['hotlink_protection']['enabled'] ?? false,
                'settings' => $securitySettings,
            ],
        ];
    }
    
    /**
     * Verifica servizi compression
     */
    private static function checkCompressionServices(): array
    {
        return [
            'compression' => [
                'enabled' => get_option('fp_ps_compression_enabled', false),
                'gzip' => get_option('fp_ps_compression_deflate_enabled', false),
                'brotli' => get_option('fp_ps_compression_brotli_enabled', false),
            ],
        ];
    }
    
    /**
     * Verifica servizi monitoring
     */
    private static function checkMonitoringServices(): array
    {
        return [
            'performance_monitor' => [
                'enabled' => get_option('fp_ps_performance_monitoring_enabled', false),
            ],
            'core_web_vitals' => [
                'enabled' => get_option('fp_ps_core_web_vitals_enabled', false),
            ],
        ];
    }
    
    /**
     * Controlla se un hook è registrato per una classe specifica
     * 
     * @param string $hook Nome dell'hook
     * @param string $className Parte del nome della classe da cercare
     * @return bool
     */
    private static function checkHook(string $hook, string $className): bool
    {
        global $wp_filter;
        
        if (!isset($wp_filter[$hook])) {
            return false;
        }
        
        foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $function = $callback['function'];
                
                // Controlla array callbacks [object, method]
                if (is_array($function) && is_object($function[0])) {
                    $objectClass = get_class($function[0]);
                    if (strpos($objectClass, $className) !== false) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Genera report completo in formato HTML
     * 
     * @return string HTML del report
     */
    public static function generateHtmlReport(): string
    {
        $diagnostics = self::checkAllServices();
        
        ob_start();
        ?>
        <div class="fp-ps-diagnostics-report">
            <h2>🔍 Diagnostica Servizi FP Performance Suite</h2>
            
            <?php foreach ($diagnostics as $category => $services): ?>
                <div class="fp-ps-diagnostics-category">
                    <h3><?php echo esc_html(ucfirst($category)); ?></h3>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th>Servizio</th>
                                <th>Stato</th>
                                <th>Dettagli</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $serviceName => $serviceData): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($serviceName); ?></strong></td>
                                    <td>
                                        <?php if (!empty($serviceData['enabled'])): ?>
                                            <span style="color: green;">✅ Attivo</span>
                                        <?php else: ?>
                                            <span style="color: red;">❌ Disattivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        unset($serviceData['settings']); // Rimuovi settings per leggibilità
                                        echo '<pre style="font-size: 11px; max-height: 100px; overflow: auto;">';
                                        print_r($serviceData);
                                        echo '</pre>';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
            
            <div class="fp-ps-diagnostics-summary">
                <h3>📊 Riepilogo</h3>
                <?php
                $totalServices = 0;
                $activeServices = 0;
                
                foreach ($diagnostics as $category => $services) {
                    foreach ($services as $service) {
                        $totalServices++;
                        if (!empty($service['enabled'])) {
                            $activeServices++;
                        }
                    }
                }
                ?>
                <p><strong>Servizi Totali:</strong> <?php echo $totalServices; ?></p>
                <p><strong>Servizi Attivi:</strong> <?php echo $activeServices; ?></p>
                <p><strong>Servizi Disattivi:</strong> <?php echo $totalServices - $activeServices; ?></p>
            </div>
        </div>
        
        <style>
            .fp-ps-diagnostics-report {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .fp-ps-diagnostics-category {
                margin-bottom: 30px;
            }
            .fp-ps-diagnostics-category h3 {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 10px 15px;
                border-radius: 4px;
                margin-bottom: 10px;
            }
            .fp-ps-diagnostics-summary {
                background: #f0f9ff;
                border-left: 4px solid #0ea5e9;
                padding: 15px;
                border-radius: 4px;
            }
        </style>
        <?php
        return ob_get_clean();
    }
}

