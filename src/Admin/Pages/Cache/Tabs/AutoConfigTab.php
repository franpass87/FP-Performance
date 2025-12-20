<?php

namespace FP\PerfSuite\Admin\Pages\Cache\Tabs;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator;

use function __;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_js;
use function submit_button;
use function wp_nonce_field;

/**
 * Render della tab Auto Config per Cache page
 * 
 * @package FP\PerfSuite\Admin\Pages\Cache\Tabs
 * @author Francesco Passeri
 */
class AutoConfigTab
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Render della tab Auto Config
     */
    public function render(): string
    {
        ob_start();
        
        try {
            $autoConfigurator = $this->container->get(PageCacheAutoConfigurator::class);
            $config = $autoConfigurator->getConfiguration();
        } catch (\Throwable $e) {
            $autoConfigurator = null;
            $config = [];
            $recommendations = [];
        }
        
        if ($autoConfigurator) {
            try {
                $recommendations = $autoConfigurator->getRecommendations();
            } catch (\Throwable $e) {
                $recommendations = [];
            }
        }
        
        ?>
        
        <!-- Auto Configuration -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('🤖 Auto Configurazione Cache', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Configurazione automatica della cache basata sui plugin e contenuti del tuo sito.', 'fp-performance-suite'); ?>
            </p>
            
            <?php if ($autoConfigurator): ?>
            
            <div class="fp-ps-grid two" style="margin-top: 20px;">
                <div class="fp-ps-stat-box">
                    <h3><?php esc_html_e('🔍 Analisi Sito', 'fp-performance-suite'); ?></h3>
                    <p class="description">
                        <?php esc_html_e('Il sistema analizza automaticamente i plugin attivi e il contenuto per suggerire le migliori configurazioni di cache.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box">
                    <h3><?php esc_html_e('⚡ Raccomandazioni', 'fp-performance-suite'); ?></h3>
                    <p class="description">
                        <?php esc_html_e('Ricevi suggerimenti personalizzati per ottimizzare le performance del tuo sito specifico.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
            
            <?php if (!empty($recommendations)): ?>
            
            <section class="fp-ps-card" style="margin-top: 20px;">
                <h3><?php esc_html_e('📋 Raccomandazioni Attuali', 'fp-performance-suite'); ?></h3>
                
                <?php foreach ($recommendations as $recommendation): ?>
                <div class="fp-ps-recommendation" style="border-left: 4px solid #2271b1; padding: 15px; margin: 15px 0; background: #f8f9fa; border-radius: 4px;">
                    <h4 style="margin: 0 0 10px 0; color: #2271b1;">
                        <?php echo esc_html($recommendation['icon'] ?? '💡'); ?> 
                        <?php echo esc_html($recommendation['title'] ?? ''); ?>
                    </h4>
                    <p style="margin: 0; color: #666;">
                        <?php echo esc_html($recommendation['description'] ?? ''); ?>
                    </p>
                    
                    <?php if (isset($recommendation['exclusions']) && !empty($recommendation['exclusions'])): ?>
                    <details style="margin-top: 10px;">
                        <summary style="cursor: pointer; font-weight: 600;"><?php esc_html_e('Visualizza esclusioni suggerite', 'fp-performance-suite'); ?></summary>
                        <ul style="margin: 10px 0 0 20px;">
                            <?php foreach ($recommendation['exclusions'] as $exclusion): ?>
                            <li><code><?php echo esc_html($exclusion); ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <form method="post" action="" style="margin-top: 20px;">
                    <?php wp_nonce_field('fp_ps_auto_config', 'fp_ps_auto_config_nonce'); ?>
                    <input type="hidden" name="action" value="apply_auto_config">
                    
                    <p>
                        <label>
                            <input type="checkbox" name="confirm_auto_config" value="1" required>
                            <?php esc_html_e('Confermo di voler applicare la configurazione automatica', 'fp-performance-suite'); ?>
                        </label>
                    </p>
                    
                    <?php submit_button(__('Applica Configurazione Automatica', 'fp-performance-suite'), 'primary', 'apply_auto_config', false, ['onclick' => 'return confirm("' . esc_js(__('Questo modificherà le impostazioni di cache attuali. Continuare?', 'fp-performance-suite')) . '")']); ?>
                </form>
            </section>
            
            <?php else: ?>
            
            <div class="notice notice-info">
                <p><?php esc_html_e('Nessuna raccomandazione disponibile al momento. Il sistema analizzerà il tuo sito per fornire suggerimenti personalizzati.', 'fp-performance-suite'); ?></p>
            </div>
            
            <?php endif; ?>
            
            <?php else: ?>
            
            <div class="notice notice-warning">
                <p><?php esc_html_e('PageCacheAutoConfigurator non disponibile. Verifica che il servizio sia registrato correttamente.', 'fp-performance-suite'); ?></p>
            </div>
            
            <?php endif; ?>
        </section>

        <!-- Auto Config Benefits -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('🎯 Benefici Auto Config', 'fp-performance-suite'); ?></h2>
            
            <div class="fp-ps-grid three" style="margin-top: 20px;">
                <div class="fp-ps-stat-box" style="border-left: 4px solid #667eea;">
                    <div class="stat-label"><?php esc_html_e('Configurazione Intelligente', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">🧠</div>
                    <p class="description">
                        <?php esc_html_e('Analisi automatica dei plugin e contenuti', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #f093fb;">
                    <div class="stat-label"><?php esc_html_e('Zero Configurazione', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">⚡</div>
                    <p class="description">
                        <?php esc_html_e('Applicazione automatica delle migliori pratiche', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #4facfe;">
                    <div class="stat-label"><?php esc_html_e('Personalizzato', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">🎯</div>
                    <p class="description">
                        <?php esc_html_e('Configurazioni specifiche per il tuo sito', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </section>
        
        <?php
        return ob_get_clean();
    }
}

