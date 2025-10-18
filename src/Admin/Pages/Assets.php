<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Assets\FontOptimizer;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;
use FP\PerfSuite\Services\Assets\Http2ServerPush;
use FP\PerfSuite\Services\Assets\SmartAssetDelivery;

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
        $fontOptimizer = $this->container->get(FontOptimizer::class);
        $thirdPartyScripts = $this->container->get(ThirdPartyScriptManager::class);
        $http2Push = $this->container->get(Http2ServerPush::class);
        $smartDelivery = $this->container->get(SmartAssetDelivery::class);
        $message = '';
        
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_assets_nonce']), 'fp-ps-assets')) {
            // Determina quale form è stato inviato
            $formType = sanitize_text_field($_POST['form_type'] ?? '');
            
            if ($formType === 'delivery') {
                // Salva solo le impostazioni di delivery
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
                $message = __('Delivery settings saved.', 'fp-performance-suite');
            } elseif ($formType === 'pagespeed') {
                // Salva solo le impostazioni PageSpeed (font)
                $fontOptimizer->updateSettings([
                    'enabled' => !empty($_POST['font_optimizer_enabled']),
                    'optimize_google_fonts' => !empty($_POST['optimize_google_fonts']),
                    'preload_fonts' => !empty($_POST['preload_fonts']),
                    'preconnect_providers' => !empty($_POST['preconnect_providers']),
                ]);
                $message = __('Font Optimizer settings saved.', 'fp-performance-suite');
            } elseif ($formType === 'third_party') {
                // Salva solo le impostazioni degli script di terze parti
                $thirdPartyScripts->update([
                    'enabled' => !empty($_POST['third_party_enabled']),
                    'delay_all' => !empty($_POST['third_party_delay_all']),
                    'delay_timeout' => (int) ($_POST['third_party_timeout'] ?? 5000),
                    'load_on' => sanitize_text_field($_POST['third_party_load_on'] ?? 'interaction'),
                    'scripts' => [
                        'google_analytics' => ['enabled' => !empty($_POST['third_party_ga']), 'delay' => true],
                        'facebook_pixel' => ['enabled' => !empty($_POST['third_party_fb']), 'delay' => true],
                        'google_ads' => ['enabled' => !empty($_POST['third_party_ads']), 'delay' => true],
                        'hotjar' => ['enabled' => !empty($_POST['third_party_hotjar']), 'delay' => true],
                        'intercom' => ['enabled' => !empty($_POST['third_party_intercom']), 'delay' => true],
                        'youtube' => ['enabled' => !empty($_POST['third_party_youtube']), 'delay' => true],
                    ],
                ]);
                $message = __('Third-Party Script settings saved.', 'fp-performance-suite');
            } elseif ($formType === 'http2_push') {
                $http2Push->update([
                    'enabled' => !empty($_POST['http2_push_enabled']),
                    'push_css' => !empty($_POST['http2_push_css']),
                    'push_js' => !empty($_POST['http2_push_js']),
                    'push_fonts' => !empty($_POST['http2_push_fonts']),
                    'push_images' => !empty($_POST['http2_push_images']),
                    'max_resources' => (int) ($_POST['http2_max_resources'] ?? 10),
                    'critical_only' => !empty($_POST['http2_critical_only']),
                ]);
                $message = __('HTTP/2 Server Push settings saved.', 'fp-performance-suite');
            } elseif ($formType === 'smart_delivery') {
                $smartDelivery->update([
                    'enabled' => !empty($_POST['smart_delivery_enabled']),
                    'detect_connection' => !empty($_POST['smart_detect_connection']),
                    'save_data_mode' => !empty($_POST['smart_save_data_mode']),
                    'adaptive_images' => !empty($_POST['smart_adaptive_images']),
                    'adaptive_videos' => !empty($_POST['smart_adaptive_videos']),
                    'quality_slow' => (int) ($_POST['smart_quality_slow'] ?? 50),
                    'quality_moderate' => (int) ($_POST['smart_quality_moderate'] ?? 70),
                    'quality_fast' => (int) ($_POST['smart_quality_fast'] ?? 85),
                ]);
                $message = __('Smart Asset Delivery settings saved.', 'fp-performance-suite');
            }
        }
        $settings = $optimizer->settings();
        $fontSettings = $fontOptimizer->getSettings();
        $thirdPartySettings = $thirdPartyScripts->settings();
        $thirdPartyStatus = $thirdPartyScripts->status();
        $http2Settings = $http2Push->settings();
        $smartDeliverySettings = $smartDelivery->settings();
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Delivery', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="delivery" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Minify HTML output', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Rimuove spazi bianchi e commenti HTML per ridurre la dimensione della pagina.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Potrebbe causare problemi con JavaScript inline o alcuni builder.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚡ Consigliato: Attiva e testa accuratamente tutte le pagine del sito.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="minify_html" value="1" <?php checked($settings['minify_html']); ?> />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Defer JavaScript', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Posticipa l\'esecuzione degli script JavaScript dopo il caricamento della pagina.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Può causare errori con script che dipendono da jQuery o altri script caricati prima.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚡ Consigliato: Migliora significativamente il First Contentful Paint. Testa animazioni e funzionalità interattive.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="defer_js" value="1" <?php checked($settings['defer_js']); ?> data-risk="amber" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Async JavaScript', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Carica gli script in modo asincrono senza bloccare il rendering.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Gli script potrebbero eseguirsi in ordine diverso, causando errori di dipendenza.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚠️ Usa con cautela: Non combinare con Defer. Testa approfonditamente.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="async_js" value="1" <?php checked($settings['async_js']); ?> data-risk="amber" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Combine CSS files', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator red">
                            <div class="fp-ps-risk-tooltip red">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">🔴</span>
                                    <?php esc_html_e('Rischio Alto', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Combina tutti i file CSS in un unico file per ridurre le richieste HTTP.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Alto rischio di rottura del layout, problemi con media queries e specificity CSS.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('❌ Sconsigliato: Usa HTTP/2 invece, che gestisce meglio file multipli. Attiva solo se assolutamente necessario e testa molto bene.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="combine_css" value="1" <?php checked($settings['combine_css']); ?> data-risk="red" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Combine JS files', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator red">
                            <div class="fp-ps-risk-tooltip red">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">🔴</span>
                                    <?php esc_html_e('Rischio Alto', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Combina tutti i file JavaScript in un unico file.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Molto alto rischio di errori JavaScript, problemi di dipendenze e conflitti tra script.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('❌ Sconsigliato: Può rompere funzionalità critiche. Meglio usare il defer invece della combinazione.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="combine_js" value="1" <?php checked($settings['combine_js']); ?> data-risk="red" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Remove emojis script', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Rimuove lo script WordPress per il supporto emoji legacy. I browser moderni li supportano nativamente.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Consigliato: Riduce le richieste HTTP senza rischi. Attiva sempre.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
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
            <h2><?php esc_html_e('Font Optimization', 'fp-performance-suite'); ?> <span class="fp-ps-badge green" style="font-size: 0.7em;">v1.2.0</span></h2>
            <p style="color: #666; margin-bottom: 20px;"><?php esc_html_e('Ottimizza il caricamento dei font per migliorare FCP e CLS', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="pagespeed" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita ottimizzazione font', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Ottimizza il caricamento dei font con display=swap e preconnect.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Migliora FCP e elimina CLS causato dai font. Previene il flash di testo invisibile.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Consigliato: Specialmente per siti con Google Fonts. Impatto PageSpeed: +5-8 punti.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
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
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Impatto previsto su PageSpeed:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('Font Optimization: +5-8 punti Mobile', 'fp-performance-suite'); ?></li>
                    </ul>
                    <p style="margin: 10px 0 0 0; color: #555; font-size: 13px;">
                        <?php esc_html_e('Per ottimizzazioni immagini (Lazy Loading, dimensioni, WebP, AVIF) vai alla sezione', 'fp-performance-suite'); ?> 
                        <a href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-media')); ?>"><?php esc_html_e('Media Optimization', 'fp-performance-suite'); ?></a>.
                    </p>
                </div>
                
                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Font', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2>🔌 <?php esc_html_e('Third-Party Script Manager', 'fp-performance-suite'); ?> <span class="fp-ps-badge green" style="font-size: 0.7em;">v1.2.0</span></h2>
            <p style="color: #666; margin-bottom: 20px;"><?php esc_html_e('Gestisce il caricamento ritardato di script di terze parti (analytics, social, ads) per migliorare i tempi di caricamento iniziali e i Core Web Vitals.', 'fp-performance-suite'); ?></p>
            
            <?php if ($thirdPartyStatus['enabled']): ?>
                <div class="notice notice-info inline" style="margin: 15px 0;">
                    <p>
                        <strong><?php esc_html_e('Stato:', 'fp-performance-suite'); ?></strong>
                        <?php printf(
                            esc_html__('Attivo - Gestione di %d script di terze parti', 'fp-performance-suite'),
                            $thirdPartyStatus['managed_scripts']
                        ); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="third_party" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Third-Party Script Manager', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Ritarda il caricamento di script di terze parti fino all\'interazione dell\'utente o dopo un timeout.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Migliora significativamente TTI, TBT e FCP. Riduce il blocking time iniziale del 40-60%.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Altamente consigliato: Essenziale per siti con analytics e pixel di tracking. Impatto PageSpeed: +8-12 punti.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="third_party_enabled" value="1" <?php checked($thirdPartySettings['enabled']); ?> />
                </label>
                
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;" />
                
                <h3><?php esc_html_e('Impostazioni Generali', 'fp-performance-suite'); ?></h3>
                
                <p>
                    <label for="third_party_load_on"><?php esc_html_e('Carica script al', 'fp-performance-suite'); ?></label>
                    <select name="third_party_load_on" id="third_party_load_on">
                        <option value="interaction" <?php selected($thirdPartySettings['load_on'], 'interaction'); ?>><?php esc_html_e('Prima interazione utente (consigliato)', 'fp-performance-suite'); ?></option>
                        <option value="scroll" <?php selected($thirdPartySettings['load_on'], 'scroll'); ?>><?php esc_html_e('Primo scroll', 'fp-performance-suite'); ?></option>
                        <option value="timeout" <?php selected($thirdPartySettings['load_on'], 'timeout'); ?>><?php esc_html_e('Timeout fisso', 'fp-performance-suite'); ?></option>
                    </select>
                    <span class="description"><?php esc_html_e('Quando caricare gli script ritardati', 'fp-performance-suite'); ?></span>
                </p>
                
                <p>
                    <label for="third_party_timeout"><?php esc_html_e('Timeout fallback (ms)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="third_party_timeout" id="third_party_timeout" value="<?php echo esc_attr((string) $thirdPartySettings['delay_timeout']); ?>" min="1000" max="30000" step="1000" style="width: 100px;" />
                    <span class="description"><?php esc_html_e('Carica gli script dopo questo tempo anche senza interazione', 'fp-performance-suite'); ?></span>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Ritarda tutti gli script (modalità aggressiva)', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Attenzione: ritarda TUTTI gli script tranne quelli di WordPress core. Usare solo se sai cosa stai facendo.', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="third_party_delay_all" value="1" <?php checked($thirdPartySettings['delay_all']); ?> />
                </label>
                
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;" />
                
                <h3><?php esc_html_e('Script Gestiti', 'fp-performance-suite'); ?></h3>
                <p class="description"><?php esc_html_e('Seleziona quali script di terze parti ritardare automaticamente:', 'fp-performance-suite'); ?></p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; margin-top: 15px;">
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>📊 Google Analytics</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('GA4, GTM, Universal Analytics', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_ga" value="1" <?php checked($thirdPartySettings['scripts']['google_analytics']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>👍 Facebook Pixel</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Meta Pixel, FB Events', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_fb" value="1" <?php checked($thirdPartySettings['scripts']['facebook_pixel']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>💰 Google Ads</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('AdWords, AdSense', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_ads" value="1" <?php checked($thirdPartySettings['scripts']['google_ads']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>🔥 Hotjar</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Heatmaps, Recordings', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_hotjar" value="1" <?php checked($thirdPartySettings['scripts']['hotjar']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>💬 Intercom</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Live Chat, Support', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_intercom" value="1" <?php checked($thirdPartySettings['scripts']['intercom']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>▶️ YouTube</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Video embeds, iframe API', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_youtube" value="1" <?php checked($thirdPartySettings['scripts']['youtube']['enabled'] ?? false); ?> />
                    </label>
                </div>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Impatto previsto su PageSpeed:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('Time to Interactive (TTI): -30-50%', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Total Blocking Time (TBT): -40-60%', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('First Contentful Paint (FCP): -10-20%', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Punteggio PageSpeed Mobile: +8-12 punti', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Third-Party Scripts', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2>⚡ <?php esc_html_e('HTTP/2 Server Push', 'fp-performance-suite'); ?> <span class="fp-ps-badge green" style="font-size: 0.7em;">Advanced</span></h2>
            <p style="color: #666; margin-bottom: 20px;"><?php esc_html_e('Push automatico di risorse critiche via HTTP/2 Server Push per eliminare round-trip e accelerare il rendering.', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="http2_push" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita HTTP/2 Server Push', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Invia risorse critiche al browser prima ancora che le richieda, eliminando latenza.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Riduce FCP del 20-30%, elimina round-trip per risorse critiche.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Può sprecare banda se push risorse già in cache. Richiede HTTP/2 attivo sul server.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="http2_push_enabled" value="1" <?php checked($http2Settings['enabled']); ?> />
                </label>
                
                <h3><?php esc_html_e('Tipi di Risorse da Pushare', 'fp-performance-suite'); ?></h3>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Push CSS critici', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="http2_push_css" value="1" <?php checked($http2Settings['push_css']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Push JavaScript critici', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="http2_push_js" value="1" <?php checked($http2Settings['push_js']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Push Fonts', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="http2_push_fonts" value="1" <?php checked($http2Settings['push_fonts']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Push Immagini critiche', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('(sconsigliato: troppo pesanti)', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="http2_push_images" value="1" <?php checked($http2Settings['push_images']); ?> />
                </label>
                
                <p>
                    <label for="http2_max_resources"><?php esc_html_e('Max risorse da pushare', 'fp-performance-suite'); ?></label>
                    <input type="number" name="http2_max_resources" id="http2_max_resources" value="<?php echo esc_attr((string) $http2Settings['max_resources']); ?>" min="1" max="20" style="width: 80px;" />
                    <span class="description"><?php esc_html_e('Consigliato: 5-10', 'fp-performance-suite'); ?></span>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Push solo risorse critiche', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Raccomandato: push solo above-the-fold', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="http2_critical_only" value="1" <?php checked($http2Settings['critical_only']); ?> />
                </label>
                
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600; color: #856404;"><?php esc_html_e('⚠️ Nota importante:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #856404;">
                        <li><?php esc_html_e('Richiede HTTP/2 abilitato sul server', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Testa sempre l\'impatto con strumenti come WebPageTest', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('HTTP/3 renderà questa tecnica meno necessaria', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni HTTP/2 Push', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2>📱 <?php esc_html_e('Smart Asset Delivery', 'fp-performance-suite'); ?> <span class="fp-ps-badge green" style="font-size: 0.7em;">Advanced</span></h2>
            <p style="color: #666; margin-bottom: 20px;"><?php esc_html_e('Adatta automaticamente la qualità e il tipo di assets in base alla connessione dell\'utente (2G, 3G, 4G, Save-Data).', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="smart_delivery" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Smart Delivery', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Rileva automaticamente la velocità di connessione e adatta qualità immagini/video.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Esperienza ottimale per tutti gli utenti, da mobile 2G a fibra ottica. Riduce consumo dati su reti lente.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="smart_delivery_enabled" value="1" <?php checked($smartDeliverySettings['enabled']); ?> />
                </label>
                
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Rileva tipo connessione', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Usa Network Information API', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="smart_detect_connection" value="1" <?php checked($smartDeliverySettings['detect_connection']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Rispetta Save-Data mode', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Riduce qualità se utente ha attivato risparmio dati', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="smart_save_data_mode" value="1" <?php checked($smartDeliverySettings['save_data_mode']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Immagini adaptive', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="smart_adaptive_images" value="1" <?php checked($smartDeliverySettings['adaptive_images']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Video adaptive', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="smart_adaptive_videos" value="1" <?php checked($smartDeliverySettings['adaptive_videos']); ?> />
                </label>
                
                <h3><?php esc_html_e('Qualità per Tipo di Connessione', 'fp-performance-suite'); ?></h3>
                <p>
                    <label for="smart_quality_slow"><?php esc_html_e('Qualità connessione lenta (2G)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="smart_quality_slow" id="smart_quality_slow" value="<?php echo esc_attr((string) $smartDeliverySettings['quality_slow']); ?>" min="20" max="100" style="width: 80px;" />
                    <span class="description"><?php esc_html_e('Default: 50 (bassa qualità, carica veloce)', 'fp-performance-suite'); ?></span>
                </p>
                <p>
                    <label for="smart_quality_moderate"><?php esc_html_e('Qualità connessione moderata (3G)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="smart_quality_moderate" id="smart_quality_moderate" value="<?php echo esc_attr((string) $smartDeliverySettings['quality_moderate']); ?>" min="20" max="100" style="width: 80px;" />
                    <span class="description"><?php esc_html_e('Default: 70 (media qualità)', 'fp-performance-suite'); ?></span>
                </p>
                <p>
                    <label for="smart_quality_fast"><?php esc_html_e('Qualità connessione veloce (4G+)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="smart_quality_fast" id="smart_quality_fast" value="<?php echo esc_attr((string) $smartDeliverySettings['quality_fast']); ?>" min="20" max="100" style="width: 80px;" />
                    <span class="description"><?php esc_html_e('Default: 85 (alta qualità)', 'fp-performance-suite'); ?></span>
                </p>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Benefici Smart Delivery:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('Ottimizzazione automatica per ogni utente', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Riduzione consumo dati su mobile fino al 70%', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Esperienza fluida anche su reti lente', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Supporto Network Information API e Save-Data', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Smart Delivery', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
