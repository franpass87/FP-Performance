<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Assets\LazyLoadManager;
use FP\PerfSuite\Services\Assets\FontOptimizer;
use FP\PerfSuite\Services\Assets\ImageOptimizer;

use function __;
use function array_filter;
use function array_map;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function explode;
use function implode;
use function trim;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;

class Assets extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-assets';
    }

    public function title(): string
    {
        return __('Assets Optimization', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return $this->requiredCapability();
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Assets', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $optimizer = $this->container->get(Optimizer::class);
        $lazyLoad = $this->container->get(LazyLoadManager::class);
        $fontOptimizer = $this->container->get(FontOptimizer::class);
        $imageOptimizer = $this->container->get(ImageOptimizer::class);
        $message = '';
        
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_assets_nonce']), 'fp-ps-assets')) {
            $dnsPrefetch = array_filter(array_map('trim', explode("\n", wp_unslash($_POST['dns_prefetch'] ?? ''))));
            $preload = array_filter(array_map('trim', explode("\n", wp_unslash($_POST['preload'] ?? ''))));
            $optimizer->update([
                'minify_html' => !empty($_POST['minify_html']),
                'defer_js' => !empty($_POST['defer_js']),
                'async_js' => !empty($_POST['async_js']),
                'remove_emojis' => !empty($_POST['remove_emojis']),
                'dns_prefetch' => $dnsPrefetch,
                'preload' => $preload,
                'heartbeat_admin' => (int) ($_POST['heartbeat_admin'] ?? 60),
                'combine_css' => !empty($_POST['combine_css']),
                'combine_js' => !empty($_POST['combine_js']),
            ]);
            
            // PageSpeed Optimization settings
            $lazyLoad->updateSettings([
                'enabled' => !empty($_POST['lazy_load_enabled']),
                'images' => !empty($_POST['lazy_load_images']),
                'iframes' => !empty($_POST['lazy_load_iframes']),
                'skip_first' => (int) ($_POST['lazy_load_skip_first'] ?? 1),
            ]);
            
            $fontOptimizer->updateSettings([
                'enabled' => !empty($_POST['font_optimizer_enabled']),
                'optimize_google_fonts' => !empty($_POST['optimize_google_fonts']),
                'preload_fonts' => !empty($_POST['preload_fonts']),
                'preconnect_providers' => !empty($_POST['preconnect_providers']),
            ]);
            
            $imageOptimizer->updateSettings([
                'enabled' => !empty($_POST['image_optimizer_enabled']),
                'force_dimensions' => !empty($_POST['force_dimensions']),
                'add_aspect_ratio' => !empty($_POST['add_aspect_ratio']),
            ]);
            
            $message = __('Asset settings saved.', 'fp-performance-suite');
        }
        $settings = $optimizer->settings();
        $lazyLoadSettings = $lazyLoad->getSettings();
        $fontSettings = $fontOptimizer->getSettings();
        $imageSettings = $imageOptimizer->getSettings();
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Delivery', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Minify HTML output', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber" title="<?php esc_attr_e('Rischio medio - Testare prima di attivare', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="minify_html" value="1" <?php checked($settings['minify_html']); ?> />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Defer JavaScript', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber" title="<?php esc_attr_e('Rischio medio - Testare prima di attivare', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="defer_js" value="1" <?php checked($settings['defer_js']); ?> data-risk="amber" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Async JavaScript', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber" title="<?php esc_attr_e('Rischio medio - Testare prima di attivare', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="async_js" value="1" <?php checked($settings['async_js']); ?> data-risk="amber" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Combine CSS files', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator red" title="<?php esc_attr_e('Rischio alto - Potrebbe causare problemi', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="combine_css" value="1" <?php checked($settings['combine_css']); ?> data-risk="red" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Combine JS files', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator red" title="<?php esc_attr_e('Rischio alto - Potrebbe causare problemi', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="combine_js" value="1" <?php checked($settings['combine_js']); ?> data-risk="red" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Remove emojis script', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green" title="<?php esc_attr_e('Rischio basso - Sicuro da attivare', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="remove_emojis" value="1" <?php checked($settings['remove_emojis']); ?> data-risk="green" />
                </label>
                <p>
                    <label for="heartbeat_admin"><?php esc_html_e('Heartbeat interval (seconds)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="heartbeat_admin" id="heartbeat_admin" value="<?php echo esc_attr((string) $settings['heartbeat_admin']); ?>" min="15" step="5" />
                </p>
                <p>
                    <label for="dns_prefetch"><?php esc_html_e('DNS Prefetch domains (one per line)', 'fp-performance-suite'); ?></label>
                    <textarea name="dns_prefetch" id="dns_prefetch" rows="4" class="large-text code"><?php echo esc_textarea(implode("\n", $settings['dns_prefetch'])); ?></textarea>
                </p>
                <p>
                    <label for="preload"><?php esc_html_e('Preload resources (full URLs)', 'fp-performance-suite'); ?></label>
                    <textarea name="preload" id="preload" rows="4" class="large-text code"><?php echo esc_textarea(implode("\n", $settings['preload'])); ?></textarea>
                </p>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Asset Settings', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2><?php esc_html_e('PageSpeed Optimization', 'fp-performance-suite'); ?> <span class="fp-ps-badge green" style="font-size: 0.7em;">v1.2.0</span></h2>
            <p style="color: #666; margin-bottom: 20px;"><?php esc_html_e('Ottimizzazioni specifiche per migliorare il punteggio Google PageSpeed Insights', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                
                <h3><?php esc_html_e('Lazy Loading', 'fp-performance-suite'); ?></h3>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Lazy Loading', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green" title="<?php esc_attr_e('Rischio basso - Migliora LCP e TTI', 'fp-performance-suite'); ?>"><?php esc_html_e('RACCOMANDATO', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="lazy_load_enabled" value="1" <?php checked($lazyLoadSettings['enabled']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Lazy load immagini', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="lazy_load_images" value="1" <?php checked($lazyLoadSettings['images']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Lazy load iframe (YouTube, etc.)', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="lazy_load_iframes" value="1" <?php checked($lazyLoadSettings['iframes']); ?> />
                </label>
                <p style="margin-left: 30px;">
                    <label for="lazy_load_skip_first"><?php esc_html_e('Salta le prime N immagini (hero images)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="lazy_load_skip_first" id="lazy_load_skip_first" value="<?php echo esc_attr((string) $lazyLoadSettings['skip_first']); ?>" min="0" max="5" style="width: 80px;" />
                    <span class="description"><?php esc_html_e('Consigliato: 1-2 per evitare di lazy-loadare immagini above-the-fold', 'fp-performance-suite'); ?></span>
                </p>
                
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;" />
                
                <h3><?php esc_html_e('Ottimizzazione Font', 'fp-performance-suite'); ?></h3>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita ottimizzazione font', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green" title="<?php esc_attr_e('Rischio basso - Migliora FCP e CLS', 'fp-performance-suite'); ?>"><?php esc_html_e('RACCOMANDATO', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="font_optimizer_enabled" value="1" <?php checked($fontSettings['enabled']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Ottimizza Google Fonts (display=swap)', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="optimize_google_fonts" value="1" <?php checked($fontSettings['optimize_google_fonts']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Preload font critici', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="preload_fonts" value="1" <?php checked($fontSettings['preload_fonts']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Preconnect a font providers', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="preconnect_providers" value="1" <?php checked($fontSettings['preconnect_providers']); ?> />
                </label>
                
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;" />
                
                <h3><?php esc_html_e('Ottimizzazione Immagini', 'fp-performance-suite'); ?></h3>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita ottimizzazione immagini', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green" title="<?php esc_attr_e('Rischio basso - Previene CLS', 'fp-performance-suite'); ?>"><?php esc_html_e('RACCOMANDATO', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="image_optimizer_enabled" value="1" <?php checked($imageSettings['enabled']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Forza dimensioni esplicite (width/height)', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="force_dimensions" value="1" <?php checked($imageSettings['force_dimensions']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Aggiungi CSS aspect-ratio', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="add_aspect_ratio" value="1" <?php checked($imageSettings['add_aspect_ratio']); ?> />
                </label>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Impatto previsto su PageSpeed:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('Lazy Loading: +10-15 punti Mobile', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Font Optimization: +5-8 punti Mobile', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Image Optimization: +3-5 punti Mobile', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni PageSpeed', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
