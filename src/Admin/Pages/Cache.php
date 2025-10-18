<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Cache\ObjectCacheManager;

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
        $objectCache = $this->container->get(ObjectCacheManager::class);
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
            if (isset($_POST['fp_ps_clear_cache'])) {
                $pageCache->clear();
                $message = __('Page cache cleared.', 'fp-performance-suite');
            }
        }

        $pageSettings = $pageCache->settings();
        $headerSettings = $headers->settings();
        $objectCacheSettings = $objectCache->settings();
        $objectCacheStatus = $objectCache->testConnection();
        $status = $pageCache->status();

        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
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
            <h2>🗄️ <?php esc_html_e('Object Cache (Redis/Memcached)', 'fp-performance-suite'); ?></h2>
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
        <?php
        return (string) ob_get_clean();
    }
}
