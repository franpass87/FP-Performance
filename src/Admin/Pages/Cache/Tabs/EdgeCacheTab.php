<?php

namespace FP\PerfSuite\Admin\Pages\Cache\Tabs;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Cache\EdgeCacheManager;

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
        
        try {
            $edgeManager = $this->container->get(EdgeCacheManager::class);
            $edgeSettings = $edgeManager->settings();
        } catch (\Throwable $e) {
            $edgeManager = null;
            $edgeSettings = [];
        }
        
        ?>
        
        <!-- Edge Cache Configuration -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('☁️ Edge Cache', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Configura il caching su CDN edge per performance globali ottimali.', 'fp-performance-suite'); ?>
            </p>
            
            <?php if ($edgeManager): ?>
            
            <form method="post" action="">
                <?php wp_nonce_field('fp_ps_edge_cache', 'fp_ps_edge_cache_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Abilita Edge Cache', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" value="1" 
                                       <?php checked(!empty($edgeSettings['enabled'])); ?>>
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
            
            <?php else: ?>
            
            <div class="notice notice-warning">
                <p><?php esc_html_e('EdgeCacheManager non disponibile. Verifica che il servizio sia registrato correttamente.', 'fp-performance-suite'); ?></p>
            </div>
            
            <?php endif; ?>
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
        return ob_get_clean();
    }
}

