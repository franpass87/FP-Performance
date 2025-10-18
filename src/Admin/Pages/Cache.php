<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Cache\ObjectCacheManager;
use FP\PerfSuite\Services\Cache\EdgeCacheManager;
use FP\PerfSuite\Services\Compatibility\ThemeDetector;
use FP\PerfSuite\Admin\ThemeHints;

use function __;
use function checked;
use function esc_attr;
use function esc_html_e;
use function esc_textarea;
use function printf;
use function sanitize_email;
use function sanitize_text_field;
use function wp_nonce_field;
use function wp_unslash;

class Cache extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-cache';
    }

    public function title(): string
    {
        return __('Cache & Edge', 'fp-performance-suite');
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
        $objectCache = $this->container->get(ObjectCacheManager::class);
        $edgeCache = $this->container->get(EdgeCacheManager::class);
        $cacheAutoConfig = $this->container->get(\FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator::class);
        $message = '';
        $headerSettings = $headers->settings();
        
        // Theme-specific hints
        $themeDetector = $this->container->get(ThemeDetector::class);
        $hints = new ThemeHints($themeDetector);

        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_cache_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_cache_nonce']), 'fp-ps-cache')) {
            if (isset($_POST['fp_ps_page_cache'])) {
                $enabledRequested = !empty($_POST['page_cache_enabled']);
                $ttlRequested = (int) ($_POST['page_cache_ttl'] ?? 3600);
                
                // Parse exclusions
                $excludeUrls = !empty($_POST['cache_exclude_urls']) ? wp_unslash($_POST['cache_exclude_urls']) : '';
                $excludeQueryStrings = !empty($_POST['cache_exclude_query_strings']) ? wp_unslash($_POST['cache_exclude_query_strings']) : '';
                
                // Cache warming settings
                $enableWarming = !empty($_POST['cache_warming_enabled']);
                $warmingUrls = !empty($_POST['cache_warming_urls']) ? wp_unslash($_POST['cache_warming_urls']) : '';
                $warmingSchedule = sanitize_text_field($_POST['cache_warming_schedule'] ?? 'hourly');
                
                $pageCache->update([
                    'enabled' => $enabledRequested,
                    'ttl' => $ttlRequested,
                    'exclude_urls' => $excludeUrls,
                    'exclude_query_strings' => $excludeQueryStrings,
                    'warming_enabled' => $enableWarming,
                    'warming_urls' => $warmingUrls,
                    'warming_schedule' => $warmingSchedule,
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
            if (isset($_POST['fp_ps_object_cache'])) {
                $objectCache->update([
                    'enabled' => !empty($_POST['object_cache_enabled']),
                    'driver' => sanitize_text_field($_POST['object_cache_driver'] ?? 'auto'),
                    'host' => sanitize_text_field($_POST['object_cache_host'] ?? '127.0.0.1'),
                    'port' => (int) ($_POST['object_cache_port'] ?? 6379),
                    'password' => sanitize_text_field($_POST['object_cache_password'] ?? ''),
                    'prefix' => sanitize_text_field($_POST['object_cache_prefix'] ?? 'fp_ps_'),
                ]);
                $message = __('Object cache settings saved.', 'fp-performance-suite');
            }
            if (isset($_POST['fp_ps_edge_cache'])) {
                $edgeCacheData = [
                    'enabled' => !empty($_POST['edge_cache_enabled']),
                    'provider' => sanitize_text_field($_POST['edge_cache_provider'] ?? 'none'),
                    'auto_purge' => !empty($_POST['edge_cache_auto_purge']),
                ];
                
                // Cloudflare settings
                if (!empty($_POST['cloudflare_api_token'])) {
                    $edgeCacheData['cloudflare'] = [
                        'api_token' => sanitize_text_field($_POST['cloudflare_api_token']),
                        'zone_id' => sanitize_text_field($_POST['cloudflare_zone_id'] ?? ''),
                        'email' => sanitize_email($_POST['cloudflare_email'] ?? ''),
                    ];
                }
                
                // Fastly settings
                if (!empty($_POST['fastly_api_key'])) {
                    $edgeCacheData['fastly'] = [
                        'api_key' => sanitize_text_field($_POST['fastly_api_key']),
                        'service_id' => sanitize_text_field($_POST['fastly_service_id'] ?? ''),
                    ];
                }
                
                // CloudFront settings
                if (!empty($_POST['cloudfront_access_key'])) {
                    $edgeCacheData['cloudfront'] = [
                        'access_key_id' => sanitize_text_field($_POST['cloudfront_access_key']),
                        'secret_access_key' => sanitize_text_field($_POST['cloudfront_secret_key'] ?? ''),
                        'distribution_id' => sanitize_text_field($_POST['cloudfront_distribution_id'] ?? ''),
                        'region' => sanitize_text_field($_POST['cloudfront_region'] ?? 'us-east-1'),
                    ];
                }
                
                $edgeCache->update($edgeCacheData);
                $message = __('Edge cache settings saved.', 'fp-performance-suite');
            }
            if (isset($_POST['fp_ps_clear_cache'])) {
                $pageCache->clear();
                $message = __('Page cache cleared.', 'fp-performance-suite');
            }
            if (isset($_POST['fp_ps_auto_config_scan'])) {
                $cacheAutoConfig->analyzeSite();
                $message = __('Analisi completata! Suggerimenti aggiornati.', 'fp-performance-suite');
            }
            if (isset($_POST['fp_ps_auto_config_apply'])) {
                $results = $cacheAutoConfig->applyAutoConfiguration(false);
                if ($results['applied']) {
                    $message = sprintf(
                        __('Configurazione automatica applicata! %d URL da escludere, %d URL per warming, %d parametri query.', 'fp-performance-suite'),
                        $results['changes']['exclude_urls_count'],
                        $results['changes']['warming_urls_count'],
                        $results['changes']['query_params_count']
                    );
                } else {
                    $message = __('Errore durante l\'applicazione della configurazione: ', 'fp-performance-suite') . implode(', ', $results['errors']);
                }
            }
        }

        $pageSettings = $pageCache->settings();
        $headerSettings = $headers->settings();
        $objectCacheSettings = $objectCache->settings();
        $objectCacheStatus = $objectCache->testConnection();
        $status = $pageCache->status();

        ob_start();
        
        // Ottieni suggerimenti auto-configurazione
        $autoConfigSuggestions = $cacheAutoConfig->getSuggestions();
        $autoConfigStats = $cacheAutoConfig->getStats();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        
        <!-- Auto-Configuration Section -->
        <section class="fp-ps-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
            <h2 style="color: white; margin-top: 0;">
                🤖 <?php esc_html_e('Configurazione Automatica Intelligente', 'fp-performance-suite'); ?>
            </h2>
            <p style="color: rgba(255,255,255,0.9); font-size: 15px;">
                <?php esc_html_e('Sistema AI che rileva, consiglia e applica automaticamente le migliori impostazioni di cache per il tuo sito.', 'fp-performance-suite'); ?>
            </p>
            
            <div style="background: rgba(255,255,255,0.15); border-radius: 8px; padding: 20px; margin: 20px 0; backdrop-filter: blur(10px);">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; color: #ffd700;">
                            <?php echo esc_html($autoConfigStats['suggestions_count']['exclude_urls']); ?>
                        </div>
                        <div style="font-size: 13px; opacity: 0.9; margin-top: 5px;">
                            <?php esc_html_e('URL da Escludere', 'fp-performance-suite'); ?>
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; color: #ffd700;">
                            <?php echo esc_html($autoConfigStats['suggestions_count']['warming_urls']); ?>
                        </div>
                        <div style="font-size: 13px; opacity: 0.9; margin-top: 5px;">
                            <?php esc_html_e('URL per Warming', 'fp-performance-suite'); ?>
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; color: #ffd700;">
                            <?php echo esc_html($autoConfigStats['suggestions_count']['query_params']); ?>
                        </div>
                        <div style="font-size: 13px; opacity: 0.9; margin-top: 5px;">
                            <?php esc_html_e('Parametri Query', 'fp-performance-suite'); ?>
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; color: #ffd700;">
                            <?php echo esc_html($autoConfigSuggestions['optimal_ttl']['ttl'] / 3600); ?>h
                        </div>
                        <div style="font-size: 13px; opacity: 0.9; margin-top: 5px;">
                            <?php esc_html_e('TTL Consigliato', 'fp-performance-suite'); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('fp-ps-cache', 'fp_ps_cache_nonce'); ?>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <button type="submit" name="fp_ps_auto_config_scan" value="1" class="button button-secondary" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.5); color: white; padding: 10px 20px; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                        🔍 <?php esc_html_e('Analizza Sito', 'fp-performance-suite'); ?>
                    </button>
                    <button type="submit" name="fp_ps_auto_config_apply" value="1" class="button button-primary" style="background: #ffd700; border: none; color: #1e3a8a; padding: 10px 20px; font-weight: 700; box-shadow: 0 4px 15px rgba(255,215,0,0.4);">
                        ✨ <?php esc_html_e('Applica Configurazione Automatica', 'fp-performance-suite'); ?>
                    </button>
                    <button type="button" class="button" onclick="document.getElementById('fp-ps-auto-config-details').style.display = document.getElementById('fp-ps-auto-config-details').style.display === 'none' ? 'block' : 'none';" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white;">
                        📋 <?php esc_html_e('Mostra Dettagli', 'fp-performance-suite'); ?>
                    </button>
                </div>
                
                <?php if ($autoConfigStats['auto_applied']): ?>
                <p style="margin-top: 15px; opacity: 0.9; font-size: 13px;">
                    ✓ <?php printf(
                        esc_html__('Ultima applicazione: %s fa', 'fp-performance-suite'),
                        human_time_diff($autoConfigStats['applied_at'], time())
                    ); ?>
                </p>
                <?php endif; ?>
            </form>
            
            <!-- Dettagli Suggerimenti -->
            <div id="fp-ps-auto-config-details" style="display: none; margin-top: 25px; background: rgba(255,255,255,0.95); color: #333; border-radius: 8px; padding: 20px;">
                <h3 style="margin-top: 0; color: #667eea;">
                    <?php esc_html_e('Dettagli Suggerimenti', 'fp-performance-suite'); ?>
                </h3>
                
                <!-- Cache Abilitata -->
                <div style="margin-bottom: 20px; padding: 15px; background: #f0f9ff; border-left: 4px solid #0ea5e9; border-radius: 4px;">
                    <strong><?php esc_html_e('Cache Abilitata:', 'fp-performance-suite'); ?></strong>
                    <?php echo $autoConfigSuggestions['cache_enabled']['enabled'] ? '✅ Sì' : '❌ No'; ?>
                    <br>
                    <span style="font-size: 13px; color: #666;">
                        <?php echo esc_html($autoConfigSuggestions['cache_enabled']['reason']); ?>
                        (<?php printf(esc_html__('Confidenza: %d%%', 'fp-performance-suite'), $autoConfigSuggestions['cache_enabled']['confidence'] * 100); ?>)
                    </span>
                </div>
                
                <!-- TTL Ottimale -->
                <div style="margin-bottom: 20px; padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
                    <strong><?php esc_html_e('TTL Ottimale:', 'fp-performance-suite'); ?></strong>
                    <?php echo esc_html($autoConfigSuggestions['optimal_ttl']['ttl']); ?> secondi
                    (<?php echo esc_html($autoConfigSuggestions['optimal_ttl']['ttl'] / 3600); ?> ore)
                    <br>
                    <span style="font-size: 13px; color: #666;">
                        <?php echo esc_html($autoConfigSuggestions['optimal_ttl']['reason']); ?>
                    </span>
                </div>
                
                <!-- URL da Escludere -->
                <?php if (!empty($autoConfigSuggestions['exclude_urls'])): ?>
                <div style="margin-bottom: 20px;">
                    <strong><?php esc_html_e('URL da Escludere dalla Cache:', 'fp-performance-suite'); ?></strong>
                    <ul style="margin: 10px 0; padding-left: 20px; max-height: 200px; overflow-y: auto;">
                        <?php foreach (array_slice($autoConfigSuggestions['exclude_urls'], 0, 10) as $item): ?>
                        <li style="margin: 5px 0; font-size: 13px;">
                            <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 3px;"><?php echo esc_html($item['url']); ?></code>
                            <br>
                            <span style="color: #666; font-size: 12px;">
                                <?php echo esc_html($item['reason']); ?>
                                (<?php printf(esc_html__('Confidenza: %d%%', 'fp-performance-suite'), $item['confidence'] * 100); ?>)
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (count($autoConfigSuggestions['exclude_urls']) > 10): ?>
                    <p style="font-size: 13px; color: #666; font-style: italic;">
                        <?php printf(esc_html__('... e altri %d URL', 'fp-performance-suite'), count($autoConfigSuggestions['exclude_urls']) - 10); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- URL per Warming -->
                <?php if (!empty($autoConfigSuggestions['warming_urls'])): ?>
                <div style="margin-bottom: 20px;">
                    <strong><?php esc_html_e('URL per Cache Warming:', 'fp-performance-suite'); ?></strong>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <?php foreach ($autoConfigSuggestions['warming_urls'] as $item): ?>
                        <li style="margin: 5px 0; font-size: 13px;">
                            <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 3px;"><?php echo esc_html($item['url']); ?></code>
                            <br>
                            <span style="color: #666; font-size: 12px;">
                                <?php echo esc_html($item['reason']); ?>
                                (<?php printf(esc_html__('Priorità: %d', 'fp-performance-suite'), $item['priority']); ?>)
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Parametri Query -->
                <?php if (!empty($autoConfigSuggestions['exclude_query_params'])): ?>
                <div style="margin-bottom: 20px;">
                    <strong><?php esc_html_e('Parametri Query da Escludere:', 'fp-performance-suite'); ?></strong>
                    <div style="margin: 10px 0; display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach (array_slice($autoConfigSuggestions['exclude_query_params'], 0, 20) as $item): ?>
                        <span style="background: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-family: monospace;">
                            <?php echo esc_html($item['param']); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($autoConfigSuggestions['exclude_query_params']) > 20): ?>
                    <p style="font-size: 13px; color: #666; font-style: italic;">
                        <?php printf(esc_html__('... e altri %d parametri', 'fp-performance-suite'), count($autoConfigSuggestions['exclude_query_params']) - 20); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Cache Warming -->
                <div style="margin-bottom: 20px; padding: 15px; background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 4px;">
                    <strong><?php esc_html_e('Cache Warming:', 'fp-performance-suite'); ?></strong>
                    <?php echo $autoConfigSuggestions['warming_enabled']['enabled'] ? '✅ Consigliato' : '❌ Non necessario'; ?>
                    <br>
                    <span style="font-size: 13px; color: #666;">
                        <?php echo esc_html($autoConfigSuggestions['warming_enabled']['reason']); ?>
                    </span>
                    <br>
                    <strong><?php esc_html_e('Frequenza consigliata:', 'fp-performance-suite'); ?></strong>
                    <?php echo esc_html($autoConfigSuggestions['warming_schedule']['schedule']); ?>
                    <br>
                    <span style="font-size: 13px; color: #666;">
                        <?php echo esc_html($autoConfigSuggestions['warming_schedule']['reason']); ?>
                    </span>
                </div>
            </div>
        </section>
        
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
                    <label for="page_cache_ttl"><?php esc_html_e('Cache lifetime (seconds)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="page_cache_ttl" id="page_cache_ttl" value="<?php echo esc_attr((string) $pageSettings['ttl']); ?>" min="60" step="60" />
                </p>
                <p>
                    <label for="cache_exclude_urls"><?php esc_html_e('Exclude URLs from cache', 'fp-performance-suite'); ?></label>
                    <textarea name="cache_exclude_urls" id="cache_exclude_urls" rows="4" class="large-text" placeholder="<?php esc_attr_e('/cart/\n/checkout/\n/my-account/*', 'fp-performance-suite'); ?>"><?php echo esc_textarea($pageSettings['exclude_urls'] ?? ''); ?></textarea>
                    <span class="description"><?php esc_html_e('One URL pattern per line. Supports wildcards (*). Example: /cart/, /checkout/*, /?add-to-cart=*', 'fp-performance-suite'); ?></span>
                </p>
                <p>
                    <label for="cache_exclude_query_strings"><?php esc_html_e('Exclude query string parameters', 'fp-performance-suite'); ?></label>
                    <input type="text" name="cache_exclude_query_strings" id="cache_exclude_query_strings" value="<?php echo esc_attr($pageSettings['exclude_query_strings'] ?? ''); ?>" class="large-text" placeholder="<?php esc_attr_e('fbclid, gclid, utm_source, utm_medium', 'fp-performance-suite'); ?>" />
                    <span class="description"><?php esc_html_e('Comma-separated list of query parameters to ignore (pages with these params will still be cached).', 'fp-performance-suite'); ?></span>
                </p>
                
                <h3 style="margin-top: 30px;"><?php esc_html_e('Cache Warming', 'fp-performance-suite'); ?></h3>
                <p class="description"><?php esc_html_e('Automatically pre-generate cache for important pages to ensure they are always fast.', 'fp-performance-suite'); ?></p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable automatic cache warming', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="cache_warming_enabled" value="1" <?php checked($pageSettings['warming_enabled'] ?? false); ?> />
                </label>
                
                <p>
                    <label for="cache_warming_urls"><?php esc_html_e('URLs to warm (one per line)', 'fp-performance-suite'); ?></label>
                    <textarea name="cache_warming_urls" id="cache_warming_urls" rows="5" class="large-text" placeholder="<?php esc_attr_e('https://example.com/\nhttps://example.com/about/\nhttps://example.com/products/', 'fp-performance-suite'); ?>"><?php echo esc_textarea($pageSettings['warming_urls'] ?? ''); ?></textarea>
                    <span class="description"><?php esc_html_e('Full URLs of pages to pre-cache. These pages will be requested automatically to keep cache warm.', 'fp-performance-suite'); ?></span>
                </p>
                
                <p>
                    <label for="cache_warming_schedule"><?php esc_html_e('Warming schedule', 'fp-performance-suite'); ?></label>
                    <select name="cache_warming_schedule" id="cache_warming_schedule">
                        <option value="hourly" <?php selected($pageSettings['warming_schedule'] ?? 'hourly', 'hourly'); ?>><?php esc_html_e('Every hour', 'fp-performance-suite'); ?></option>
                        <option value="twicedaily" <?php selected($pageSettings['warming_schedule'] ?? 'hourly', 'twicedaily'); ?>><?php esc_html_e('Twice daily', 'fp-performance-suite'); ?></option>
                        <option value="daily" <?php selected($pageSettings['warming_schedule'] ?? 'hourly', 'daily'); ?>><?php esc_html_e('Once daily', 'fp-performance-suite'); ?></option>
                    </select>
                    <span class="description"><?php esc_html_e('How often to warm the cache for specified URLs.', 'fp-performance-suite'); ?></span>
                </p>
                
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
                    <label for="htaccess_rules"><?php esc_html_e('.htaccess rules', 'fp-performance-suite'); ?></label>
                    <textarea name="htaccess_rules" id="htaccess_rules" rows="6" class="large-text code"><?php echo esc_textarea($headerSettings['htaccess']); ?></textarea>
                </p>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Headers', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <section class="fp-ps-card">
            <h2>
                🗄️ <?php esc_html_e('Object Cache (Redis/Memcached)', 'fp-performance-suite'); ?>
                <?php echo $hints->renderInlineHint('object_cache'); ?>
            </h2>
            <p><?php esc_html_e('Attiva la cache persistente degli oggetti con Redis o Memcached per ridurre drasticamente le query al database e migliorare le performance del sito.', 'fp-performance-suite'); ?></p>
            
            <?php if ($objectCacheStatus['success']): ?>
                <div class="notice notice-success inline" style="margin: 15px 0;">
                    <p>
                        <strong><?php esc_html_e('Connessione Attiva:', 'fp-performance-suite'); ?></strong>
                        <?php printf(
                            esc_html__('Connesso a %s con successo!', 'fp-performance-suite'),
                            '<strong>' . esc_html(strtoupper($objectCacheStatus['driver'])) . '</strong>'
                        ); ?>
                    </p>
                </div>
            <?php elseif ($objectCacheSettings['enabled']): ?>
                <div class="notice notice-warning inline" style="margin: 15px 0;">
                    <p>
                        <strong><?php esc_html_e('Attenzione:', 'fp-performance-suite'); ?></strong>
                        <?php esc_html_e('Object Cache è abilitato ma la connessione non è riuscita. Verifica le impostazioni.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <?php wp_nonce_field('fp-ps-cache', 'fp_ps_cache_nonce'); ?>
                <input type="hidden" name="fp_ps_object_cache" value="1" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Object Cache', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Memorizza gli oggetti WordPress in Redis o Memcached invece del database, riducendo le query del 60-80%.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Riduzione drastica delle query database, tempi di risposta più veloci, scalabilità migliorata.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Altamente consigliato: Essenziale per siti con alto traffico. Richiede Redis o Memcached installato sul server.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="object_cache_enabled" value="1" <?php checked($objectCacheSettings['enabled']); ?> />
                </label>
                
                <p>
                    <label for="object_cache_driver"><?php esc_html_e('Driver', 'fp-performance-suite'); ?></label>
                    <select name="object_cache_driver" id="object_cache_driver">
                        <option value="auto" <?php selected($objectCacheSettings['driver'], 'auto'); ?>><?php esc_html_e('Auto (Rileva automaticamente)', 'fp-performance-suite'); ?></option>
                        <option value="redis" <?php selected($objectCacheSettings['driver'], 'redis'); ?>>Redis</option>
                        <option value="memcached" <?php selected($objectCacheSettings['driver'], 'memcached'); ?>>Memcached</option>
                    </select>
                    <span class="description"><?php esc_html_e('Seleziona il backend di caching da utilizzare', 'fp-performance-suite'); ?></span>
                </p>
                
                <p>
                    <label for="object_cache_host"><?php esc_html_e('Host', 'fp-performance-suite'); ?></label>
                    <input type="text" name="object_cache_host" id="object_cache_host" value="<?php echo esc_attr($objectCacheSettings['host']); ?>" class="regular-text" />
                    <span class="description"><?php esc_html_e('Indirizzo del server (default: 127.0.0.1)', 'fp-performance-suite'); ?></span>
                </p>
                
                <p>
                    <label for="object_cache_port"><?php esc_html_e('Porta', 'fp-performance-suite'); ?></label>
                    <input type="number" name="object_cache_port" id="object_cache_port" value="<?php echo esc_attr((string) $objectCacheSettings['port']); ?>" class="small-text" min="1" max="65535" />
                    <span class="description"><?php esc_html_e('Porta di connessione (Redis default: 6379, Memcached: 11211)', 'fp-performance-suite'); ?></span>
                </p>
                
                <p>
                    <label for="object_cache_password"><?php esc_html_e('Password (opzionale)', 'fp-performance-suite'); ?></label>
                    <input type="password" name="object_cache_password" id="object_cache_password" value="<?php echo esc_attr($objectCacheSettings['password']); ?>" class="regular-text" autocomplete="off" />
                    <span class="description"><?php esc_html_e('Password per Redis se richiesta', 'fp-performance-suite'); ?></span>
                </p>
                
                <p>
                    <label for="object_cache_prefix"><?php esc_html_e('Prefisso chiavi', 'fp-performance-suite'); ?></label>
                    <input type="text" name="object_cache_prefix" id="object_cache_prefix" value="<?php echo esc_attr($objectCacheSettings['prefix']); ?>" class="regular-text" />
                    <span class="description"><?php esc_html_e('Prefisso per le chiavi cache (utile per multi-sito)', 'fp-performance-suite'); ?></span>
                </p>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin: 15px 0;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Requisiti:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('Redis o Memcached deve essere installato e in esecuzione sul server', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('L\'estensione PHP corrispondente (php-redis o php-memcached) deve essere attiva', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Contatta il tuo hosting provider se non sai come installare questi servizi', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Object Cache', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2>
                🌐 <?php esc_html_e('Edge Cache (CDN/WAF)', 'fp-performance-suite'); ?>
                <?php echo $hints->renderInlineHint('edge_cache'); ?>
            </h2>
            <p><?php esc_html_e('Integrazione con cache edge di Cloudflare, Fastly o CloudFront per purge automatico e gestione cache distribuita.', 'fp-performance-suite'); ?></p>
            
            <?php 
            $edgeSettings = $edgeCache->settings();
            $edgeStatus = $edgeCache->status();
            ?>
            
            <?php if ($edgeStatus['enabled'] && $edgeStatus['connected']): ?>
                <div class="notice notice-success inline" style="margin: 15px 0;">
                    <p>
                        <strong><?php esc_html_e('Connessione Attiva:', 'fp-performance-suite'); ?></strong>
                        <?php printf(
                            esc_html__('Connesso a %s con successo!', 'fp-performance-suite'),
                            '<strong>' . esc_html(strtoupper($edgeSettings['provider'])) . '</strong>'
                        ); ?>
                    </p>
                </div>
            <?php elseif ($edgeSettings['enabled']): ?>
                <div class="notice notice-warning inline" style="margin: 15px 0;">
                    <p>
                        <strong><?php esc_html_e('Attenzione:', 'fp-performance-suite'); ?></strong>
                        <?php esc_html_e('Edge Cache è abilitato ma la connessione non è riuscita. Verifica le credenziali API.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <?php wp_nonce_field('fp-ps-cache', 'fp_ps_cache_nonce'); ?>
                <input type="hidden" name="fp_ps_edge_cache" value="1" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Edge Cache', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Gestisce la cache distribuita su edge server per tempi di risposta ultra-veloci.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Cache globalmente distribuita, TTFB ridotto del 60-80%, purge automatico su aggiornamenti.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="edge_cache_enabled" value="1" <?php checked($edgeSettings['enabled']); ?> />
                </label>
                
                <p>
                    <label for="edge_cache_provider"><?php esc_html_e('Provider', 'fp-performance-suite'); ?></label>
                    <select name="edge_cache_provider" id="edge_cache_provider">
                        <option value="none" <?php selected($edgeSettings['provider'], 'none'); ?>><?php esc_html_e('Nessuno', 'fp-performance-suite'); ?></option>
                        <option value="cloudflare" <?php selected($edgeSettings['provider'], 'cloudflare'); ?>>Cloudflare</option>
                        <option value="fastly" <?php selected($edgeSettings['provider'], 'fastly'); ?>>Fastly</option>
                        <option value="cloudfront" <?php selected($edgeSettings['provider'], 'cloudfront'); ?>>AWS CloudFront</option>
                    </select>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Auto-purge su aggiornamenti', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Purga automaticamente la cache edge quando pubblichi/aggiorni contenuti', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="edge_cache_auto_purge" value="1" <?php checked($edgeSettings['auto_purge']); ?> />
                </label>
                
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;" />
                
                <!-- Cloudflare Settings -->
                <div id="cloudflare-settings" style="<?php echo $edgeSettings['provider'] === 'cloudflare' ? '' : 'display:none;'; ?>">
                    <h3><?php esc_html_e('Cloudflare Settings', 'fp-performance-suite'); ?></h3>
                    <p>
                        <label for="cloudflare_api_token"><?php esc_html_e('API Token', 'fp-performance-suite'); ?></label>
                        <input type="password" name="cloudflare_api_token" id="cloudflare_api_token" value="<?php echo esc_attr($edgeSettings['cloudflare']['api_token']); ?>" class="regular-text" autocomplete="off" />
                        <span class="description"><?php esc_html_e('Token API con permessi Zone:Cache Purge', 'fp-performance-suite'); ?></span>
                    </p>
                    <p>
                        <label for="cloudflare_zone_id"><?php esc_html_e('Zone ID', 'fp-performance-suite'); ?></label>
                        <input type="text" name="cloudflare_zone_id" id="cloudflare_zone_id" value="<?php echo esc_attr($edgeSettings['cloudflare']['zone_id']); ?>" class="regular-text" />
                    </p>
                    <p>
                        <label for="cloudflare_email"><?php esc_html_e('Email (opzionale)', 'fp-performance-suite'); ?></label>
                        <input type="email" name="cloudflare_email" id="cloudflare_email" value="<?php echo esc_attr($edgeSettings['cloudflare']['email']); ?>" class="regular-text" />
                    </p>
                </div>
                
                <!-- Fastly Settings -->
                <div id="fastly-settings" style="<?php echo $edgeSettings['provider'] === 'fastly' ? '' : 'display:none;'; ?>">
                    <h3><?php esc_html_e('Fastly Settings', 'fp-performance-suite'); ?></h3>
                    <p>
                        <label for="fastly_api_key"><?php esc_html_e('API Key', 'fp-performance-suite'); ?></label>
                        <input type="password" name="fastly_api_key" id="fastly_api_key" value="<?php echo esc_attr($edgeSettings['fastly']['api_key']); ?>" class="regular-text" autocomplete="off" />
                    </p>
                    <p>
                        <label for="fastly_service_id"><?php esc_html_e('Service ID', 'fp-performance-suite'); ?></label>
                        <input type="text" name="fastly_service_id" id="fastly_service_id" value="<?php echo esc_attr($edgeSettings['fastly']['service_id']); ?>" class="regular-text" />
                    </p>
                </div>
                
                <!-- CloudFront Settings -->
                <div id="cloudfront-settings" style="<?php echo $edgeSettings['provider'] === 'cloudfront' ? '' : 'display:none;'; ?>">
                    <h3><?php esc_html_e('AWS CloudFront Settings', 'fp-performance-suite'); ?></h3>
                    <p>
                        <label for="cloudfront_access_key"><?php esc_html_e('Access Key ID', 'fp-performance-suite'); ?></label>
                        <input type="text" name="cloudfront_access_key" id="cloudfront_access_key" value="<?php echo esc_attr($edgeSettings['cloudfront']['access_key_id']); ?>" class="regular-text" />
                    </p>
                    <p>
                        <label for="cloudfront_secret_key"><?php esc_html_e('Secret Access Key', 'fp-performance-suite'); ?></label>
                        <input type="password" name="cloudfront_secret_key" id="cloudfront_secret_key" value="<?php echo esc_attr($edgeSettings['cloudfront']['secret_access_key']); ?>" class="regular-text" autocomplete="off" />
                    </p>
                    <p>
                        <label for="cloudfront_distribution_id"><?php esc_html_e('Distribution ID', 'fp-performance-suite'); ?></label>
                        <input type="text" name="cloudfront_distribution_id" id="cloudfront_distribution_id" value="<?php echo esc_attr($edgeSettings['cloudfront']['distribution_id']); ?>" class="regular-text" />
                    </p>
                    <p>
                        <label for="cloudfront_region"><?php esc_html_e('Region', 'fp-performance-suite'); ?></label>
                        <input type="text" name="cloudfront_region" id="cloudfront_region" value="<?php echo esc_attr($edgeSettings['cloudfront']['region']); ?>" class="regular-text" />
                        <span class="description"><?php esc_html_e('Default: us-east-1', 'fp-performance-suite'); ?></span>
                    </p>
                </div>
                
                <script>
                document.getElementById('edge_cache_provider').addEventListener('change', function() {
                    document.getElementById('cloudflare-settings').style.display = this.value === 'cloudflare' ? 'block' : 'none';
                    document.getElementById('fastly-settings').style.display = this.value === 'fastly' ? 'block' : 'none';
                    document.getElementById('cloudfront-settings').style.display = this.value === 'cloudfront' ? 'block' : 'none';
                });
                </script>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin: 15px 0;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Benefici Edge Cache:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('Cache distribuita globalmente su centinaia di PoP', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('TTFB ridotto del 60-80% per utenti internazionali', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Purge automatico quando aggiorni contenuti', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Protezione DDoS e sicurezza inclusa', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Edge Cache', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <?php echo ThemeHints::renderTooltipScript(); ?>
        <?php
        return (string) ob_get_clean();
    }
}
