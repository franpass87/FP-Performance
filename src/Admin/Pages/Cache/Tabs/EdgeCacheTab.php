<?php

namespace FP\PerfSuite\Admin\Pages\Cache\Tabs;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Cache\EdgeCacheManager;
use FP\PerfSuite\Admin\RiskMatrix;

use function __;
use function checked;
use function esc_attr;
use function esc_html_e;
use function selected;
use function submit_button;
use function wp_nonce_field;

/**
 * Render della tab Edge Cache per Cache page
 * 
 * @package FP\PerfSuite\Admin\Pages\Cache\Tabs
 * @author Francesco Passeri
 */
class EdgeCacheTab
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Render della tab Edge Cache
     */
    public function render(): string
    {
        ob_start();
        
        $edgeManager = null;
        $edgeSettings = [
            'enabled' => false,
            'provider' => 'cloudflare',
            'api_key' => '',
            'zone_id' => '',
        ];
        
        $hasError = false;
        $errorMessage = '';
        
        try {
            // Verifica che il servizio sia disponibile nel container
            if (!$this->container->has(EdgeCacheManager::class)) {
                throw new \RuntimeException('EdgeCacheManager service not registered in container');
            }
            
            $edgeManager = $this->container->get(EdgeCacheManager::class);
            
            // Carica impostazioni - usa settings() che ora è disponibile
            if (method_exists($edgeManager, 'settings')) {
                $edgeSettings = $edgeManager->settings();
            } elseif (method_exists($edgeManager, 'getSettings')) {
                $edgeSettings = $edgeManager->getSettings();
            } else {
                // Se non ha metodi per le impostazioni, carica direttamente dall'opzione
                $option = get_option('fp_ps_edge_cache_settings', []);
                $edgeSettings = [
                    'enabled' => !empty($option['enabled'] ?? false),
                    'provider' => $option['provider'] ?? 'cloudflare',
                    'api_key' => $option['api_key'] ?? '',
                    'zone_id' => $option['zone_id'] ?? '',
                ];
            }
            
            // Assicurati che edgeSettings sia un array valido
            if (!is_array($edgeSettings)) {
                $edgeSettings = [];
            }
            
            // Merge con defaults per sicurezza
            $edgeSettings = array_merge([
                'enabled' => false,
                'provider' => 'cloudflare',
                'api_key' => '',
                'zone_id' => '',
            ], $edgeSettings);
            
        } catch (\Throwable $e) {
            $hasError = true;
            $errorMessage = $e->getMessage();
            
            // Log errore per debug
            if (function_exists('error_log')) {
                error_log(sprintf(
                    '[FP-Performance] EdgeCacheTab error: %s in %s:%d',
                    $e->getMessage(),
                    basename($e->getFile()),
                    $e->getLine()
                ));
            }
        }
        
        ?>
        
        <!-- Edge Cache Configuration -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('☁️ Edge Cache', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Configura il caching su CDN edge per performance globali ottimali.', 'fp-performance-suite'); ?>
            </p>
            
            <?php if ($hasError): ?>
            <div class="notice notice-error" style="margin: 20px 0;">
                <p><strong><?php esc_html_e('⚠️ Errore:', 'fp-performance-suite'); ?></strong> 
                <?php echo esc_html($errorMessage); ?>
                <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
                    <br><small><?php esc_html_e('Debug mode attivo - verifica i log per dettagli.', 'fp-performance-suite'); ?></small>
                <?php endif; ?>
                </p>
            </div>
            <?php elseif (!$edgeManager): ?>
            <div class="notice notice-warning" style="margin: 20px 0;">
                <p><strong><?php esc_html_e('⚠️ Attenzione:', 'fp-performance-suite'); ?></strong> <?php esc_html_e('EdgeCacheManager non disponibile. Il form è comunque disponibile ma le impostazioni potrebbero non essere salvate correttamente.', 'fp-performance-suite'); ?></p>
            </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <?php wp_nonce_field('fp_ps_edge_cache', 'fp_ps_edge_cache_nonce'); ?>
                <input type="hidden" name="fp_ps_edge_cache" value="1" />
                <input type="hidden" name="active_tab" value="edge" />
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="edge_cache_enabled">
                                <?php esc_html_e('Abilita Edge Cache', 'fp-performance-suite'); ?>
                                <?php echo RiskMatrix::renderIndicator('edge_cache_enabled'); ?>
                            </label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" id="edge_cache_enabled" value="1" 
                                       <?php checked(!empty($edgeSettings['enabled'])); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('edge_cache_enabled')); ?>">
                                <?php esc_html_e('Abilita edge caching', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Attiva il caching su CDN edge.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Provider CDN', 'fp-performance-suite'); ?></th>
                        <td>
                            <select name="provider">
                                <option value="cloudflare" <?php selected($edgeSettings['provider'] ?? 'cloudflare', 'cloudflare'); ?>>
                                    <?php esc_html_e('Cloudflare', 'fp-performance-suite'); ?>
                                </option>
                                <option value="cloudfront" <?php selected($edgeSettings['provider'] ?? 'cloudflare', 'cloudfront'); ?>>
                                    <?php esc_html_e('AWS CloudFront', 'fp-performance-suite'); ?>
                                </option>
                                <option value="fastly" <?php selected($edgeSettings['provider'] ?? 'cloudflare', 'fastly'); ?>>
                                    <?php esc_html_e('Fastly', 'fp-performance-suite'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Seleziona il provider CDN da utilizzare.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('API Key', 'fp-performance-suite'); ?></th>
                        <td>
                            <input type="password" name="api_key" value="<?php echo esc_attr($edgeSettings['api_key'] ?? ''); ?>" 
                                   class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Chiave API del provider CDN.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Zone ID', 'fp-performance-suite'); ?></th>
                        <td>
                            <input type="text" name="zone_id" value="<?php echo esc_attr($edgeSettings['zone_id'] ?? ''); ?>" 
                                   class="regular-text">
                            <p class="description">
                                <?php esc_html_e('ID della zona CDN (Cloudflare) o Distribution ID (CloudFront).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Salva Configurazione Edge Cache', 'fp-performance-suite')); ?>
            </form>
        </section>

        <!-- Edge Cache Benefits -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('🌍 Benefici Edge Cache', 'fp-performance-suite'); ?></h2>
            
            <div class="fp-ps-grid three" style="margin-top: 20px;">
                <div class="fp-ps-stat-box" style="border-left: 4px solid #667eea;">
                    <div class="stat-label"><?php esc_html_e('Performance Globale', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">🌍</div>
                    <p class="description">
                        <?php esc_html_e('Contenuti serviti da server vicini agli utenti', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #f093fb;">
                    <div class="stat-label"><?php esc_html_e('Riduzione Latenza', 'fp-performance-suite'); ?></div>
                    <div class="stat-value success">50-90%</div>
                    <p class="description">
                        <?php esc_html_e('Riduzione significativa dei tempi di risposta', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #4facfe;">
                    <div class="stat-label"><?php esc_html_e('Scalabilità', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">📈</div>
                    <p class="description">
                        <?php esc_html_e('Gestisce traffico elevato senza sovraccaricare il server', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </section>
        
        <?php
        $output = ob_get_clean();
        
        // FIX: Assicurati che ci sia sempre output, anche in caso di errore
        if (empty($output) && $hasError) {
            $output = '<div class="notice notice-error"><p><strong>' . esc_html__('Errore:', 'fp-performance-suite') . '</strong> ' . esc_html($errorMessage) . '</p></div>';
        }
        
        return $output ?: '';
    }
}

