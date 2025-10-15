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
        
        // Admin notice for recommendations
        add_action('admin_notices', [$this, 'showCompatibilityNotice']);
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
     */
    public function applyCompatibilityRules(): void
    {
        if (!$this->autoApply) {
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
                Logger::error('Failed to apply compatibility for ' . $service, [
                    'error' => $e->getMessage(),
                ]);
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
     */
    public function showCompatibilityNotice(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Show only once
        if (get_transient('fp_ps_compat_notice_shown')) {
            return;
        }
        
        $config = $this->detector->getRecommendedConfig();
        $settings = $this->settings();
        
        // Don't show if auto-apply is enabled and already applied
        if ($this->autoApply && !empty($settings['applied'])) {
            return;
        }
        
        // Special notice for Salient
        if ($this->detector->isSalient()) {
            $this->showSalientNotice($config);
            return;
        }
        
        // Generic notice
        $this->showGenericNotice($config);
    }
    
    /**
     * Show Salient-specific notice
     */
    private function showSalientNotice(array $config): void
    {
        $builder = $config['page_builder']['name'];
        $settings = $this->settings();
        
        ?>
        <div class="notice notice-info is-dismissible fp-ps-compat-notice">
            <h3>🎨 FP Performance Suite - Rilevato Salient + <?php echo esc_html($builder); ?></h3>
            <p>
                <strong>Configurazione ottimizzata disponibile!</strong><br>
                Abbiamo rilevato che stai usando <strong>Salient</strong> con <strong><?php echo esc_html($builder); ?></strong>.
                Possiamo configurare automaticamente il plugin per massime performance.
            </p>
            <p><strong>Servizi raccomandati:</strong></p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li>✅ <strong>Object Cache</strong> - Riduce query database del 70%</li>
                <li>✅ <strong>Core Web Vitals Monitor</strong> - Monitora CLS (critico per animazioni Salient)</li>
                <li>✅ <strong>Third-Party Scripts</strong> - Ritarda Analytics/Pixel senza bloccare Salient</li>
                <li>✅ <strong>Smart Delivery</strong> - Ottimizza per mobile e connessioni lente</li>
                <li>✅ <strong>HTTP/2 Push</strong> - Push font icons Salient</li>
                <li>⚠️ <strong>AVIF</strong> - Da testare (può avere problemi con slider/lightbox)</li>
            </ul>
            <p>
                <a href="<?php echo admin_url('admin.php?page=fp-performance-compatibility'); ?>" class="button button-primary">
                    📋 Visualizza Raccomandazioni Dettagliate
                </a>
                <a href="#" class="button fp-ps-apply-compat" data-nonce="<?php echo wp_create_nonce('fp_ps_apply_compat'); ?>">
                    ⚡ Applica Configurazione Automatica
                </a>
                <a href="#" class="button fp-ps-dismiss-compat">
                    Ricordamelo dopo
                </a>
            </p>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('.fp-ps-apply-compat').on('click', function(e) {
                e.preventDefault();
                if (!confirm('Applicare la configurazione ottimizzata per Salient + <?php echo esc_js($builder); ?>?\n\nQuesto aggiornerà le impostazioni dei servizi.')) {
                    return;
                }
                
                var $btn = $(this).prop('disabled', true).text('⏳ Applicando...');
                
                $.post(ajaxurl, {
                    action: 'fp_ps_apply_compatibility',
                    nonce: $(this).data('nonce')
                }, function(response) {
                    if (response.success) {
                        $btn.text('✅ Applicato!').removeClass('button').addClass('button-primary');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert('Errore: ' + (response.data || 'Sconosciuto'));
                        $btn.prop('disabled', false).text('⚡ Applica Configurazione Automatica');
                    }
                });
            });
            
            $('.fp-ps-dismiss-compat').on('click', function(e) {
                e.preventDefault();
                $('.fp-ps-compat-notice').fadeOut();
                $.post(ajaxurl, {
                    action: 'fp_ps_dismiss_compat_notice'
                });
            });
        });
        </script>
        <?php
        
        set_transient('fp_ps_compat_notice_shown', true, WEEK_IN_SECONDS);
    }
    
    /**
     * Show generic compatibility notice
     */
    private function showGenericNotice(array $config): void
    {
        $theme = $config['theme']['name'];
        $builder = $config['page_builder']['name'];
        
        ?>
        <div class="notice notice-info is-dismissible">
            <h3>🚀 FP Performance Suite - Configurazione Disponibile</h3>
            <p>
                Tema rilevato: <strong><?php echo esc_html($theme); ?></strong><br>
                Page Builder: <strong><?php echo esc_html($builder); ?></strong>
            </p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=fp-performance-compatibility'); ?>" class="button button-primary">
                    Visualizza Raccomandazioni
                </a>
            </p>
        </div>
        <?php
        
        set_transient('fp_ps_compat_notice_shown', true, WEEK_IN_SECONDS);
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
