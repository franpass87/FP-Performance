<?php

namespace FP\PerfSuite\Admin\Pages\Cache\Tabs;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Admin\RiskMatrix;

use function __;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function wp_nonce_field;
use function submit_button;

/**
 * Render della tab Browser Cache per Cache page
 * 
 * @package FP\PerfSuite\Admin\Pages\Cache\Tabs
 * @author Francesco Passeri
 */
class BrowserCacheTab
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Render della tab Browser Cache
     */
    public function render(string $message = ''): string
    {
        ob_start();
        
        try {
            $headers = $this->container->get(Headers::class);
            $headerSettings = $headers->settings();
        } catch (\Throwable $e) {
            return '<div class="notice notice-error"><p>' . esc_html($e->getMessage()) . '</p></div>';
        }
        
        ?>
        
        <?php if (!empty($message)): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Browser Cache Configuration -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('🌐 Browser Cache', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Configura le regole di cache per i browser dei visitatori.', 'fp-performance-suite'); ?>
            </p>
            
            <form method="post" action="">
                <?php wp_nonce_field('fp-ps-cache', 'fp_ps_cache_nonce'); ?>
                <input type="hidden" name="fp_ps_browser_cache" value="1" />
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Abilita Browser Cache', 'fp-performance-suite'); ?></th>
                        <td>
                            <label class="fp-ps-toggle">
                                <span class="info">
                                    <strong><?php esc_html_e('Abilita cache browser', 'fp-performance-suite'); ?></strong>
                                    <?php echo RiskMatrix::renderIndicator('browser_cache_enabled'); ?>
                                </span>
                                <input type="checkbox" name="browser_cache_enabled" value="1" 
                                       <?php checked(!empty($headerSettings['enabled'])); ?>
                                       data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('browser_cache_enabled')); ?>" />
                            </label>
                            <p class="description">
                                <?php esc_html_e('Imposta header HTTP per il caching dei file statici.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Cache-Control', 'fp-performance-suite'); ?></th>
                        <td>
                            <input type="text" name="cache_control" value="<?php echo esc_attr($headerSettings['headers']['Cache-Control'] ?? 'public, max-age=31536000'); ?>" 
                                   class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Header Cache-Control per i file statici.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('TTL Expires', 'fp-performance-suite'); ?></th>
                        <td>
                            <input type="number" name="expires_ttl" value="<?php echo esc_attr($headerSettings['expires_ttl'] ?? 31536000); ?>" 
                                   min="0" class="small-text">
                            <p class="description">
                                <?php esc_html_e('Durata cache in secondi (default: 31536000 = 1 anno).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Regole .htaccess', 'fp-performance-suite'); ?>
                            <span class="fp-ps-help-icon" style="cursor: help; color: #3b82f6; margin-left: 5px;" title="<?php esc_attr_e('Regole Apache personalizzate per il caching. Modifica solo se sai cosa stai facendo.', 'fp-performance-suite'); ?>">ℹ️</span>
                        </th>
                        <td>
                            <textarea name="htaccess_rules" rows="6" class="large-text code"><?php echo esc_textarea($headerSettings['htaccess'] ?? ''); ?></textarea>
                            <p class="description">
                                <?php esc_html_e('Regole Apache avanzate per il caching.', 'fp-performance-suite'); ?>
                                <a href="https://httpd.apache.org/docs/current/mod/mod_expires.html" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Documentazione Apache', 'fp-performance-suite'); ?></a>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <div style="background: #fef3c7; border-left: 3px solid #fbbf24; padding: 12px; margin: 10px 0; border-radius: 4px;">
                    <strong style="color: #92400e;">⚠️ <?php esc_html_e('Attenzione', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 8px 0 0 0; color: #78350f; line-height: 1.5; font-size: 13px;">
                        <?php esc_html_e('Regole .htaccess errate possono causare errori 500 sul sito. Viene creato un backup automatico prima di ogni modifica.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <?php submit_button(__('Salva Browser Cache', 'fp-performance-suite')); ?>
            </form>
        </section>
        
        <?php
        return ob_get_clean();
    }
}

