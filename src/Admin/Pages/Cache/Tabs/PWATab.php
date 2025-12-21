<?php

namespace FP\PerfSuite\Admin\Pages\Cache\Tabs;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\PWA\ServiceWorkerManager;
use FP\PerfSuite\Admin\RiskMatrix;

use function __;
use function checked;
use function esc_attr;
use function esc_html_e;
use function selected;
use function submit_button;
use function wp_nonce_field;

/**
 * Render della tab PWA per Cache page
 * 
 * @package FP\PerfSuite\Admin\Pages\Cache\Tabs
 * @author Francesco Passeri
 */
class PWATab
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Render della tab PWA
     */
    public function render(): string
    {
        ob_start();
        
        try {
            $pwaManager = $this->container->get(ServiceWorkerManager::class);
            $pwaSettings = $pwaManager->getSettings();
        } catch (\Throwable $e) {
            $pwaManager = null;
            $pwaSettings = [];
        }
        
        ?>
        
        <!-- PWA Configuration -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('📱 PWA (Progressive Web App)', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Trasforma il tuo sito in una Progressive Web App con Service Worker e caching offline.', 'fp-performance-suite'); ?>
            </p>
            
            <?php if ($pwaManager): ?>
            
            <form method="post" action="">
                <?php wp_nonce_field('fp_ps_pwa', 'fp_ps_pwa_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="pwa_enabled">
                                <?php esc_html_e('Abilita PWA', 'fp-performance-suite'); ?>
                                <?php echo RiskMatrix::renderIndicator('pwa_enabled'); ?>
                            </label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" id="pwa_enabled" value="1" 
                                       <?php checked(!empty($pwaSettings['enabled'])); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('pwa_enabled')); ?>">
                                <?php esc_html_e('Abilita Progressive Web App', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Attiva Service Worker e funzionalità PWA.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Strategia Cache', 'fp-performance-suite'); ?></th>
                        <td>
                            <select name="cache_strategy">
                                <option value="cache_first" <?php selected($pwaSettings['cache_strategy'] ?? 'cache_first', 'cache_first'); ?>>
                                    <?php esc_html_e('Cache First', 'fp-performance-suite'); ?>
                                </option>
                                <option value="network_first" <?php selected($pwaSettings['cache_strategy'] ?? 'cache_first', 'network_first'); ?>>
                                    <?php esc_html_e('Network First', 'fp-performance-suite'); ?>
                                </option>
                                <option value="stale_while_revalidate" <?php selected($pwaSettings['cache_strategy'] ?? 'cache_first', 'stale_while_revalidate'); ?>>
                                    <?php esc_html_e('Stale While Revalidate', 'fp-performance-suite'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Strategia di caching per le risorse.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Durata Cache', 'fp-performance-suite'); ?></th>
                        <td>
                            <input type="number" name="cache_duration" value="<?php echo esc_attr($pwaSettings['cache_duration'] ?? 86400); ?>" 
                                   min="0" class="small-text">
                            <p class="description">
                                <?php esc_html_e('Durata cache in secondi (default: 86400 = 24 ore).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Fallback Offline', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="offline_fallback" value="1" 
                                       <?php checked(!empty($pwaSettings['offline_fallback'])); ?>>
                                <?php esc_html_e('Abilita fallback offline', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Mostra una pagina offline quando non c\'è connessione.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Salva Configurazione PWA', 'fp-performance-suite')); ?>
            </form>
            
            <?php else: ?>
            
            <div class="notice notice-warning">
                <p><?php esc_html_e('ServiceWorkerManager non disponibile. Verifica che il servizio sia registrato correttamente.', 'fp-performance-suite'); ?></p>
            </div>
            
            <?php endif; ?>
        </section>

        <!-- PWA Benefits -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('📈 Benefici PWA', 'fp-performance-suite'); ?></h2>
            
            <div class="fp-ps-grid three" style="margin-top: 20px;">
                <div class="fp-ps-stat-box" style="border-left: 4px solid #667eea;">
                    <div class="stat-label"><?php esc_html_e('Performance Offline', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">⚡</div>
                    <p class="description">
                        <?php esc_html_e('Funziona anche senza connessione internet', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #f093fb;">
                    <div class="stat-label"><?php esc_html_e('Installazione App', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">📱</div>
                    <p class="description">
                        <?php esc_html_e('Installabile come app nativa sui dispositivi', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #4facfe;">
                    <div class="stat-label"><?php esc_html_e('Cache Intelligente', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">🧠</div>
                    <p class="description">
                        <?php esc_html_e('Caching automatico delle risorse più utilizzate', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </section>
        
        <?php
        return ob_get_clean();
    }
}

