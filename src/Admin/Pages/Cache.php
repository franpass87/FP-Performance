<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Assets\PredictivePrefetching;

use function __;
use function checked;
use function esc_attr;
use function esc_html_e;
use function esc_textarea;
use function printf;
use function sanitize_text_field;
use function wp_nonce_field;
use function wp_unslash;

class Cache extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-cache';
    }

    public function title(): string
    {
        return __('Cache Optimization', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Cache', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $pageCache = $this->container->get(PageCache::class);
        $headers = $this->container->get(Headers::class);
        $prefetching = $this->container->get(PredictivePrefetching::class);
        $message = '';
        $headerSettings = $headers->settings();

        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_cache_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_cache_nonce']), 'fp-ps-cache')) {
            if (isset($_POST['fp_ps_page_cache'])) {
                $enabledRequested = !empty($_POST['page_cache_enabled']);
                $ttlRequested = (int) ($_POST['page_cache_ttl'] ?? 3600);
                $pageCache->update([
                    'enabled' => $enabledRequested,
                    'ttl' => $ttlRequested,
                ]);
                $message = __('Page cache settings saved.', 'fp-performance-suite');
                $currentSettings = $pageCache->settings();
                if ($enabledRequested && !$currentSettings['enabled']) {
                    $message .= ' ' . __('Caching was disabled because the cache lifetime must be greater than zero.', 'fp-performance-suite');
                } elseif ($enabledRequested && $ttlRequested > 0 && $ttlRequested !== $currentSettings['ttl']) {
                    $message .= ' ' . __('Cache lifetime adjusted to the minimum of 60 seconds.', 'fp-performance-suite');
                }
            }
            if (isset($_POST['fp_ps_browser_cache'])) {
                $headers->update([
                    'enabled' => !empty($_POST['browser_cache_enabled']),
                    'headers' => [
                        'Cache-Control' => sanitize_text_field($_POST['cache_control'] ?? 'public, max-age=31536000'),
                    ],
                    'expires_ttl' => isset($_POST['expires_ttl']) ? (int) $_POST['expires_ttl'] : $headerSettings['expires_ttl'],
                    'htaccess' => wp_unslash($_POST['htaccess_rules'] ?? ''),
                ]);
                $message = __('Browser cache settings saved.', 'fp-performance-suite');
            }
            if (isset($_POST['fp_ps_prefetch'])) {
                $ignorePatterns = isset($_POST['prefetch_ignore_patterns']) 
                    ? array_filter(array_map('trim', explode("\n", wp_unslash($_POST['prefetch_ignore_patterns'])))) 
                    : [];
                
                $prefetching->updateSettings([
                    'enabled' => !empty($_POST['prefetch_enabled']),
                    'strategy' => sanitize_text_field($_POST['prefetch_strategy'] ?? 'hover'),
                    'hover_delay' => isset($_POST['prefetch_delay']) ? (int) $_POST['prefetch_delay'] : 100,
                    'prefetch_limit' => isset($_POST['prefetch_limit']) ? (int) $_POST['prefetch_limit'] : 5,
                    'ignore_patterns' => $ignorePatterns,
                ]);
                $message = __('Predictive prefetching settings saved.', 'fp-performance-suite');
            }
            if (isset($_POST['fp_ps_clear_cache'])) {
                $pageCache->clear();
                $message = __('Page cache cleared.', 'fp-performance-suite');
            }
        }

        $pageSettings = $pageCache->settings();
        $headerSettings = $headers->settings();
        $prefetchSettings = $prefetching->getSettings();
        $status = $pageCache->status();

        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        
        <!-- Status Badge -->
        <div class="fp-ps-feature-status" style="display: flex; align-items: center; gap: 15px; padding: 15px; background: <?php echo $pageSettings['enabled'] ? '#d1fae5' : '#f1f5f9'; ?>; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid <?php echo $pageSettings['enabled'] ? '#10b981' : '#94a3b8'; ?>;">
            <div class="fp-ps-status-badge" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: <?php echo $pageSettings['enabled'] ? '#10b981' : '#94a3b8'; ?>; color: white; border-radius: 6px; font-weight: 600; font-size: 14px;">
                <?php if ($pageSettings['enabled']) : ?>
                    <span style="font-size: 16px;">✅</span> <?php esc_html_e('Attivo', 'fp-performance-suite'); ?>
                <?php else : ?>
                    <span style="font-size: 16px;">⏸️</span> <?php esc_html_e('Non Attivo', 'fp-performance-suite'); ?>
                <?php endif; ?>
            </div>
            <?php if ($pageSettings['enabled']) : ?>
                <div class="fp-ps-status-details" style="flex: 1; color: #065f46; font-size: 14px;">
                    <strong><?php esc_html_e('File in cache:', 'fp-performance-suite'); ?></strong> 
                    <span style="background: #059669; color: white; padding: 2px 8px; border-radius: 4px; font-weight: 600; margin-left: 8px;">
                        <?php echo number_format((int) $status['files']); ?>
                    </span>
                    <span style="margin-left: 15px;">
                        <strong><?php esc_html_e('TTL:', 'fp-performance-suite'); ?></strong> <?php echo esc_html((string) $pageSettings['ttl']); ?>s
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Page Cache', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Serve cached HTML for anonymous visitors using filesystem storage.', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-cache', 'fp_ps_cache_nonce'); ?>
                <input type="hidden" name="fp_ps_page_cache" value="1" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable page cache', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Recommended for shared hosting with limited CPU.', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="page_cache_enabled" value="1" <?php checked($pageSettings['enabled']); ?> data-risk="amber" />
                </label>
                <p>
                    <label for="page_cache_ttl">
                        <?php esc_html_e('Cache lifetime (seconds)', 'fp-performance-suite'); ?>
                        <span class="fp-ps-help-icon" style="cursor: help; color: #3b82f6; margin-left: 5px;" title="<?php esc_attr_e('Determina per quanto tempo le pagine vengono servite dalla cache prima di essere rigenerate. Valori più alti = migliori performance ma contenuti meno aggiornati.', 'fp-performance-suite'); ?>">ℹ️</span>
                    </label>
                    <input type="number" name="page_cache_ttl" id="page_cache_ttl" value="<?php echo esc_attr((string) $pageSettings['ttl']); ?>" min="60" step="60" placeholder="3600" class="regular-text" />
                </p>
                <div class="fp-ps-input-help" style="margin-top: 8px; margin-bottom: 15px;">
                    <p style="background: #dbeafe; border-left: 3px solid #3b82f6; padding: 10px; margin: 10px 0; border-radius: 4px;">
                        💡 <strong><?php esc_html_e('Consigliato: 3600 secondi (1 ora)', 'fp-performance-suite'); ?></strong>
                        <br><small style="color: #64748b;"><?php esc_html_e('Buon equilibrio tra performance e aggiornamenti contenuti', 'fp-performance-suite'); ?></small>
                    </p>
                    <details style="margin-top: 10px; cursor: pointer;">
                        <summary style="font-weight: 600; color: #475569; padding: 8px 0;"><?php esc_html_e('📚 Guida valori in base al tipo di sito', 'fp-performance-suite'); ?></summary>
                        <table style="width: 100%; margin-top: 10px; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                                    <th style="padding: 10px; text-align: left;"><?php esc_html_e('Tipo Sito', 'fp-performance-suite'); ?></th>
                                    <th style="padding: 10px; text-align: left;"><?php esc_html_e('TTL Consigliato', 'fp-performance-suite'); ?></th>
                                    <th style="padding: 10px; text-align: left;"><?php esc_html_e('Motivazione', 'fp-performance-suite'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px;"><strong>🗞️ <?php esc_html_e('Blog/News', 'fp-performance-suite'); ?></strong></td>
                                    <td style="padding: 10px;"><code>1800-3600s</code></td>
                                    <td style="padding: 10px;"><?php esc_html_e('Contenuti aggiornati più volte al giorno', 'fp-performance-suite'); ?></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px;"><strong>🛒 <?php esc_html_e('E-commerce', 'fp-performance-suite'); ?></strong></td>
                                    <td style="padding: 10px;"><code>300-900s</code></td>
                                    <td style="padding: 10px;"><?php esc_html_e('Prezzi, stock e carrello dinamici', 'fp-performance-suite'); ?></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px;"><strong>🏢 <?php esc_html_e('Sito Aziendale', 'fp-performance-suite'); ?></strong></td>
                                    <td style="padding: 10px;"><code>7200-14400s</code></td>
                                    <td style="padding: 10px;"><?php esc_html_e('Contenuti prevalentemente statici', 'fp-performance-suite'); ?></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px;"><strong>📰 <?php esc_html_e('Portale Alto Traffico', 'fp-performance-suite'); ?></strong></td>
                                    <td style="padding: 10px;"><code>3600-7200s</code></td>
                                    <td style="padding: 10px;"><?php esc_html_e('Bilanciamento carico/aggiornamenti', 'fp-performance-suite'); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px;"><strong>🎨 <?php esc_html_e('Portfolio', 'fp-performance-suite'); ?></strong></td>
                                    <td style="padding: 10px;"><code>14400-86400s</code></td>
                                    <td style="padding: 10px;"><?php esc_html_e('Contenuti raramente aggiornati', 'fp-performance-suite'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="background: #fef3c7; border-left: 3px solid #fbbf24; padding: 12px; margin-top: 15px; border-radius: 4px;">
                            <strong style="color: #92400e;">⚠️ <?php esc_html_e('Attenzione', 'fp-performance-suite'); ?></strong>
                            <p style="margin: 8px 0 0 0; color: #78350f; line-height: 1.5; font-size: 13px;">
                                <?php esc_html_e('Valori troppo alti (>86400s) possono mostrare contenuti obsoleti. Valori troppo bassi (<60s) annullano i benefici della cache.', 'fp-performance-suite'); ?>
                            </p>
                        </div>
                    </details>
                </div>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Page Cache', 'fp-performance-suite'); ?></button>
                    <button type="submit" name="fp_ps_clear_cache" value="1" class="button"><?php esc_html_e('Clear Cache', 'fp-performance-suite'); ?></button>
                </p>
                <p class="description"><?php printf(esc_html__('Current cached files: %d', 'fp-performance-suite'), (int) $status['files']); ?></p>
            </form>
        </section>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Browser Cache Headers', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp-ps-cache', 'fp_ps_cache_nonce'); ?>
                <input type="hidden" name="fp_ps_browser_cache" value="1" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable headers', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Adds Cache-Control/Expires headers for static files.', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="browser_cache_enabled" value="1" <?php checked($headerSettings['enabled']); ?> data-risk="green" />
                </label>
                <p>
                    <label for="cache_control"><?php esc_html_e('Cache-Control', 'fp-performance-suite'); ?></label>
                    <input type="text" name="cache_control" id="cache_control" value="<?php echo esc_attr($headerSettings['headers']['Cache-Control']); ?>" class="regular-text" />
                </p>
                <p>
                    <label for="expires_ttl"><?php esc_html_e('Expires header TTL (seconds)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="expires_ttl" id="expires_ttl" value="<?php echo esc_attr((string) $headerSettings['expires_ttl']); ?>" class="regular-text" min="0" step="60" />
                    <span class="description"><?php printf(esc_html__('Current Expires header will resolve to: %s', 'fp-performance-suite'), esc_html($headerSettings['headers']['Expires'])); ?></span>
                </p>
                <p>
                    <label for="htaccess_rules">
                        <?php esc_html_e('.htaccess rules', 'fp-performance-suite'); ?>
                        <span class="fp-ps-help-icon" style="cursor: help; color: #3b82f6; margin-left: 5px;" title="<?php esc_attr_e('Regole Apache personalizzate per il caching. Modifica solo se sai cosa stai facendo.', 'fp-performance-suite'); ?>">ℹ️</span>
                    </label>
                    <textarea name="htaccess_rules" id="htaccess_rules" rows="6" class="large-text code"><?php echo esc_textarea($headerSettings['htaccess']); ?></textarea>
                    <span class="description">
                        <?php esc_html_e('Regole Apache avanzate per il caching.', 'fp-performance-suite'); ?>
                        <a href="https://httpd.apache.org/docs/current/mod/mod_expires.html" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Documentazione Apache', 'fp-performance-suite'); ?></a>
                    </span>
                </p>
                <div style="background: #fef3c7; border-left: 3px solid #fbbf24; padding: 12px; margin: 10px 0; border-radius: 4px;">
                    <strong style="color: #92400e;">⚠️ <?php esc_html_e('Attenzione', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 8px 0 0 0; color: #78350f; line-height: 1.5; font-size: 13px;">
                        <?php esc_html_e('Regole .htaccess errate possono causare errori 500 sul sito. Viene creato un backup automatico prima di ogni modifica.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Headers', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>

        <section class="fp-ps-card">
            <h2>🚀 <?php esc_html_e('Predictive Prefetching', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Precarica intelligentemente le pagine che l\'utente probabilmente visiterà, basato su hover, scroll e viewport. Rende la navigazione quasi istantanea.', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-cache', 'fp_ps_cache_nonce'); ?>
                <input type="hidden" name="fp_ps_prefetch" value="1" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable Predictive Prefetching', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Precarica le pagine prima del click per navigazione istantanea.', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="prefetch_enabled" value="1" <?php checked($prefetchSettings['enabled']); ?> data-risk="green" />
                </label>

                <table class="form-table" style="margin-top: 20px;">
                    <tr>
                        <th scope="row">
                            <label for="prefetch_strategy"><?php esc_html_e('Strategia', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <select name="prefetch_strategy" id="prefetch_strategy" class="regular-text">
                                <option value="hover" <?php selected($prefetchSettings['strategy'], 'hover'); ?>>
                                    <?php esc_html_e('Hover - Precarica al passaggio del mouse (Consigliato)', 'fp-performance-suite'); ?>
                                </option>
                                <option value="viewport" <?php selected($prefetchSettings['strategy'], 'viewport'); ?>>
                                    <?php esc_html_e('Viewport - Precarica link visibili', 'fp-performance-suite'); ?>
                                </option>
                                <option value="aggressive" <?php selected($prefetchSettings['strategy'], 'aggressive'); ?>>
                                    <?php esc_html_e('Aggressive - Precarica tutti i link (Alto uso banda)', 'fp-performance-suite'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('La strategia "hover" offre il miglior rapporto performance/banda.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="prefetch_delay"><?php esc_html_e('Delay (ms)', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="prefetch_delay" id="prefetch_delay" value="<?php echo esc_attr((string) $prefetchSettings['hover_delay']); ?>" min="0" max="2000" step="50" class="small-text" />
                            <span>ms</span>
                            <p class="description">
                                <?php esc_html_e('Ritardo prima del prefetch quando il mouse è su un link (default: 100ms). Previene prefetch accidentali.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="prefetch_limit"><?php esc_html_e('Limite prefetch', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="prefetch_limit" id="prefetch_limit" value="<?php echo esc_attr((string) $prefetchSettings['prefetch_limit']); ?>" min="1" max="20" class="small-text" />
                            <p class="description">
                                <?php esc_html_e('Numero massimo di pagine da precaricare simultaneamente (consigliato: 5). Previene sovraccarico.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="prefetch_ignore_patterns"><?php esc_html_e('Pattern da ignorare', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <textarea name="prefetch_ignore_patterns" id="prefetch_ignore_patterns" rows="5" class="large-text code"><?php echo esc_textarea(implode("\n", $prefetchSettings['ignore_patterns'])); ?></textarea>
                            <p class="description">
                                <?php esc_html_e('URL o pattern da escludere dal prefetch (uno per riga). Esempio: /wp-admin/, /cart/, /checkout/', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px; border-radius: 4px;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;">💡 <?php esc_html_e('Benefici Predictive Prefetching:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #1e293b; line-height: 1.6;">
                        <li><?php esc_html_e('⚡ Navigazione quasi istantanea tra pagine', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('🎯 Riduce il tempo di caricamento percepito a ~0ms', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('🧠 Intelligente: prefetch solo pagine con alta probabilità di click', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('📱 Rispetta Save-Data e connessioni lente automaticamente', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>

                <div style="background: #fef3c7; border-left: 3px solid #fbbf24; padding: 12px; margin: 15px 0; border-radius: 4px;">
                    <strong style="color: #92400e;">💡 <?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 8px 0 0 0; color: #78350f; line-height: 1.5; font-size: 13px;">
                        <?php esc_html_e('Inizia con strategia "hover" e delay 100ms. È il setup più efficiente che non spreca banda.', 'fp-performance-suite'); ?>
                    </p>
                </div>

                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Prefetching Settings', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
