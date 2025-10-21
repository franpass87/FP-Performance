<?php

namespace FP\PerfSuite\Services\Compatibility;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\Logger;

/**
 * Theme Compatibility Service
 *
 * Automatically applies optimal configurations based on detected theme and page builder
 *
 * @package FP\PerfSuite\Services\Compatibility
 * @author Francesco Passeri
 */
class ThemeCompatibility
{
    private const OPTION = 'fp_ps_theme_compat';
    
    private ThemeDetector $detector;
    private ServiceContainer $container;
    private bool $autoApply = false;
    
    public function __construct(ServiceContainer $container, ?ThemeDetector $detector = null)
    {
        $this->container = $container;
        $this->detector = $detector ?? new ThemeDetector();
    }
    
    public function register(): void
    {
        // Check if auto-apply is enabled
        $settings = $this->settings();
        $this->autoApply = $settings['auto_apply'] ?? false;
        
        // Apply compatibility rules
        add_action('init', [$this, 'applyCompatibilityRules'], 15);
        
        // Admin notice for recommendations - REMOVED: Notice obsoleto con link rotti
        // add_action('admin_notices', [$this, 'showCompatibilityNotice']);
    }
    
    /**
     * Get settings
     *
     * @return array{auto_apply:bool,applied:bool,theme:string,builder:string}
     */
    public function settings(): array
    {
        $defaults = [
            'auto_apply' => false,
            'applied' => false,
            'theme' => '',
            'builder' => '',
            'last_check' => 0,
        ];
        
        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }
    
    /**
     * Update settings
     *
     * @param array $settings New settings
     */
    public function update(array $settings): void
    {
        $current = $this->settings();
        
        $new = [
            'auto_apply' => !empty($settings['auto_apply']),
            'applied' => $settings['applied'] ?? $current['applied'],
            'theme' => $settings['theme'] ?? $current['theme'],
            'builder' => $settings['builder'] ?? $current['builder'],
            'last_check' => time(),
        ];
        
        update_option(self::OPTION, $new);
    }
    
    /**
     * Apply compatibility rules based on detected theme
     * 
     * @param bool $force Force application even if autoApply is disabled
     */
    public function applyCompatibilityRules(bool $force = false): void
    {
        if (!$force && !$this->autoApply) {
            return;
        }
        
        $config = $this->detector->getRecommendedConfig();
        $settings = $this->settings();
        
        // Check if already applied for this theme/builder combo
        $currentCombo = $config['theme']['slug'] . '_' . $config['page_builder']['slug'];
        $appliedCombo = ($settings['theme'] ?? '') . '_' . ($settings['builder'] ?? '');
        
        if ($currentCombo === $appliedCombo && !empty($settings['applied'])) {
            return; // Already applied
        }
        
        Logger::info('Applying theme compatibility rules', [
            'theme' => $config['theme']['name'],
            'builder' => $config['page_builder']['name'],
        ]);
        
        // Apply each service configuration
        foreach ($config['recommendations'] as $service => $recommendation) {
            if (empty($recommendation['enabled'])) {
                continue;
            }
            
            try {
                $this->applyServiceConfig($service, $recommendation);
            } catch (\Exception $e) {
                Logger::error('Failed to apply compatibility for ' . $service, $e);
            }
        }
        
        // Mark as applied
        $this->update([
            'auto_apply' => true,
            'applied' => true,
            'theme' => $config['theme']['slug'],
            'builder' => $config['page_builder']['slug'],
        ]);
        
        Logger::info('Theme compatibility rules applied successfully');
    }
    
    /**
     * Apply configuration for specific service
     *
     * @param string $service Service name
     * @param array $config Service configuration
     */
    private function applyServiceConfig(string $service, array $config): void
    {
        switch ($service) {
            case 'object_cache':
                $this->configureObjectCache($config);
                break;
                
            case 'edge_cache':
                $this->configureEdgeCache($config);
                break;
                
            case 'third_party_scripts':
                $this->configureThirdPartyScripts($config);
                break;
                
            case 'http2_push':
                $this->configureHttp2Push($config);
                break;
                
            case 'smart_delivery':
                $this->configureSmartDelivery($config);
                break;
                
            case 'core_web_vitals':
                $this->configureCoreWebVitals($config);
                break;
                
            case 'avif_converter':
                $this->configureAVIF($config);
                break;
                
            case 'service_worker':
                $this->configureServiceWorker($config);
                break;
        }
    }
    
    /**
     * Configure Object Cache
     */
    private function configureObjectCache(array $config): void
    {
        try {
            $service = $this->container->get(\FP\PerfSuite\Services\Cache\ObjectCacheManager::class);
            $service->update([
                'enabled' => $config['enabled'],
                'driver' => 'auto',
            ]);
        } catch (\Exception $e) {
            // Service might not be available
        }
    }
    
    /**
     * Configure Edge Cache
     */
    private function configureEdgeCache(array $config): void
    {
        try {
            $service = $this->container->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
            $service->update([
                'enabled' => false, // Don't auto-enable without credentials
                'auto_purge' => true,
            ]);
        } catch (\Exception $e) {
            // Service might not be available
        }
    }
    
    /**
     * Configure Third-Party Scripts
     */
    private function configureThirdPartyScripts(array $config): void
    {
        try {
            $service = $this->container->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class);
            $service->update([
                'enabled' => $config['enabled'],
                'delay_all' => $config['delay_all'] ?? false,
                'load_on' => 'interaction',
            ]);
        } catch (\Exception $e) {
            // Service might not be available
        }
    }
    
    /**
     * Configure HTTP/2 Push
     */
    private function configureHttp2Push(array $config): void
    {
        try {
            $service = $this->container->get(\FP\PerfSuite\Services\Assets\Http2ServerPush::class);
            $service->update([
                'enabled' => $config['enabled'],
                'push_js' => $config['push_js'] ?? false,
                'push_css' => true,
                'push_fonts' => true,
            ]);
        } catch (\Exception $e) {
            // Service might not be available
        }
    }
    
    /**
     * Configure Smart Delivery
     */
    private function configureSmartDelivery(array $config): void
    {
        try {
            $service = $this->container->get(\FP\PerfSuite\Services\Assets\SmartAssetDelivery::class);
            $service->update([
                'enabled' => $config['enabled'],
                'save_data_mode' => true,
                'adaptive_images' => true,
            ]);
        } catch (\Exception $e) {
            // Service might not be available
        }
    }
    
    /**
     * Configure Core Web Vitals
     */
    private function configureCoreWebVitals(array $config): void
    {
        try {
            $service = $this->container->get(\FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor::class);
            $service->update([
                'enabled' => $config['enabled'],
                'sample_rate' => 0.5,
                'track_lcp' => true,
                'track_fid' => true,
                'track_cls' => true,
                'alert_threshold_cls' => $config['alert_threshold_cls'] ?? 0.1,
            ]);
        } catch (\Exception $e) {
            // Service might not be available
        }
    }
    
    /**
     * Configure AVIF
     */
    private function configureAVIF(array $config): void
    {
        try {
            $service = $this->container->get(\FP\PerfSuite\Services\Media\AVIFConverter::class);
            $service->update([
                'enabled' => $config['enabled'],
                'auto_deliver' => $config['auto_deliver'] ?? false,
                'quality' => 75,
            ]);
        } catch (\Exception $e) {
            // Service might not be available
        }
    }
    
    /**
     * Configure Service Worker
     */
    private function configureServiceWorker(array $config): void
    {
        try {
            $service = $this->container->get(\FP\PerfSuite\Services\PWA\ServiceWorkerManager::class);
            $service->update([
                'enabled' => $config['enabled'],
            ]);
        } catch (\Exception $e) {
            // Service might not be available
        }
    }
    
    /**
     * Show admin notice with compatibility recommendations
     * 
     * @deprecated Notice rimosso - i suggerimenti sono ora integrati inline nelle pagine Cache e Assets
     */
    public function showCompatibilityNotice(): void
    {
        // Metodo deprecato - notice rimosso per evitare link rotti
        // I suggerimenti sono ora mostrati come badge contestuali nelle pagine Cache e Assets
        return;
    }
    
    /**
     * Show Salient-specific notice
     * 
     * @deprecated Metodo rimosso - notice obsoleto con link rotti
     */
    private function showSalientNotice(array $config): void
    {
        // Metodo deprecato - notice rimosso per evitare link rotti
        // I suggerimenti sono ora mostrati come badge contestuali nelle pagine Cache e Assets
        return;
    }
    
    /**
     * Show generic compatibility notice
     * 
     * @deprecated Metodo rimosso - notice obsoleto con link rotti
     */
    private function showGenericNotice(array $config): void
    {
        // Metodo deprecato - notice rimosso per evitare link rotti
        // I suggerimenti sono ora mostrati come badge contestuali nelle pagine Cache e Assets
        return;
    }
    
    /**
     * Get compatibility status
     *
     * @return array{detected:array,applied:bool,auto_apply:bool}
     */
    public function status(): array
    {
        $settings = $this->settings();
        $config = $this->detector->getRecommendedConfig();
        
        return [
            'detected' => $config,
            'applied' => !empty($settings['applied']),
            'auto_apply' => !empty($settings['auto_apply']),
        ];
    }
}
