<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Assets\PredictivePrefetching;
use FP\PerfSuite\Services\Security\HtaccessSecurity;
use FP\PerfSuite\Services\PWA\ServiceWorkerManager;
use FP\PerfSuite\Services\Cache\EdgeCacheManager;
use FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator;
use FP\PerfSuite\Services\Assets\ExternalResourceCacheManager;

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
        // Determina la tab attiva
        $activeTab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'page';
        $validTabs = ['page', 'browser', 'pwa', 'edge', 'auto', 'external', 'intelligence', 'exclusions'];
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'page';
        }
        
        // Gestione dei form submissions
        $message = $this->handleFormSubmissions($activeTab);
        
        ob_start();
        ?>
        
        <!-- INTRO BOX -->
        <div class="fp-ps-page-intro" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin: 0 0 15px 0; color: white; font-size: 28px;">
                🚀 <?php esc_html_e('Cache Management', 'fp-performance-suite'); ?>
            </h2>
            <p style="margin: 0; font-size: 16px; line-height: 1.6; opacity: 0.95;">
                <?php esc_html_e('Gestisci la cache del sito per migliorare drasticamente le prestazioni. Configura page cache, browser cache, PWA e Edge cache.', 'fp-performance-suite'); ?>
            </p>
        </div>
        
        <?php
        // Render tabs navigation
        $this->renderTabsNavigation($activeTab);
        
        // Render content based on active tab
        switch ($activeTab) {
            case 'browser':
                echo $this->renderBrowserCacheTab($message);
                break;
            case 'pwa':
                echo $this->renderPWATab();
                break;
            case 'edge':
                echo $this->renderEdgeCacheTab();
                break;
            case 'auto':
                echo $this->renderAutoConfigTab();
                break;
            case 'external':
                echo $this->renderExternalCacheTab($message);
                break;
            case 'intelligence':
                echo $this->renderIntelligenceTab();
                break;
            case 'exclusions':
                echo $this->renderExclusionsTab();
                break;
            default:
                echo $this->renderPageCacheTab();
                break;
        }
        
        return (string) ob_get_clean();
    }

    private function handleFormSubmissions(string $activeTab): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return '';
        }

        $pageCache = $this->container->get(PageCache::class);
        $headers = $this->container->get(Headers::class);
        $prefetching = $this->container->get(PredictivePrefetching::class);
        $message = '';
        $headerSettings = $headers->settings();

        // Page Cache form submission
        if ($activeTab === 'page' && isset($_POST['fp_ps_cache_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_cache_nonce']), 'fp-ps-cache')) {
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
            if (isset($_POST['fp_ps_cache_rules'])) {
                $securityService = $this->container->get(HtaccessSecurity::class);
                $currentSettings = $securityService->settings();
                
                $newSettings = $currentSettings;
                $newSettings['cache_rules'] = [
                    'enabled' => !empty($_POST['cache_rules_enabled']),
                    'html_cache' => !empty($_POST['html_cache']),
                    'fonts_cache' => !empty($_POST['fonts_cache']),
                    'fonts_max_age' => max(0, (int)($_POST['fonts_max_age'] ?? 31536000)),
                    'images_max_age' => max(0, (int)($_POST['images_max_age'] ?? 31536000)),
                    'css_js_max_age' => max(0, (int)($_POST['css_js_max_age'] ?? 2592000)),
                ];
                
                $securityService->update($newSettings);
                $message = __('Cache rules settings saved.', 'fp-performance-suite');
            }
        }

        // PWA form submission
        if ($activeTab === 'pwa' && isset($_POST['fp_ps_pwa_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_pwa_nonce']), 'fp_ps_pwa')) {
            try {
                $pwaManager = $this->container->get(ServiceWorkerManager::class);
                
                $settings = [
                    'enabled' => !empty($_POST['enabled']),
                    'cache_strategy' => sanitize_text_field($_POST['cache_strategy'] ?? 'cache_first'),
                    'cache_duration' => (int) ($_POST['cache_duration'] ?? 86400),
                    'offline_fallback' => !empty($_POST['offline_fallback']),
                ];
                
                $result = $pwaManager->updateSettings($settings);
                
                if ($result) {
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-success is-dismissible"><p>' . 
                             esc_html__('Configurazione PWA salvata con successo!', 'fp-performance-suite') . 
                             '</p></div>';
                    });
                }
            } catch (\Exception $e) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="notice notice-error is-dismissible"><p>' . 
                         esc_html__('Errore nel salvare la configurazione PWA: ', 'fp-performance-suite') . 
                         esc_html($e->getMessage()) . '</p></div>';
                });
            }
        }

        // Browser Cache form submission
        if ($activeTab === 'browser' && isset($_POST['fp_ps_cache_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_cache_nonce']), 'fp-ps-cache')) {
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
        }

        // Edge Cache form submission
        if ($activeTab === 'edge' && isset($_POST['fp_ps_edge_cache_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_edge_cache_nonce']), 'fp_ps_edge_cache')) {
            try {
                $edgeManager = $this->container->get(EdgeCacheManager::class);
                
                $settings = [
                    'enabled' => !empty($_POST['enabled']),
                    'provider' => sanitize_text_field($_POST['provider'] ?? 'cloudflare'),
                    'api_key' => sanitize_text_field($_POST['api_key'] ?? ''),
                    'zone_id' => sanitize_text_field($_POST['zone_id'] ?? ''),
                ];
                
                $result = $edgeManager->updateSettings($settings);
                
                if ($result) {
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-success is-dismissible"><p>' . 
                             esc_html__('Configurazione Edge Cache salvata con successo!', 'fp-performance-suite') . 
                             '</p></div>';
                    });
                }
            } catch (\Exception $e) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="notice notice-error is-dismissible"><p>' . 
                         esc_html__('Errore nel salvare la configurazione Edge Cache: ', 'fp-performance-suite') . 
                         esc_html($e->getMessage()) . '</p></div>';
                });
            }
        }
        
        // External Cache form submission
        if ($activeTab === 'external' && isset($_POST['fp_external_cache_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_external_cache_nonce']), 'fp_external_cache_save')) {
            $cacheManager = new ExternalResourceCacheManager();
            $settings = [
                'enabled' => !empty($_POST['enabled']),
                'js_ttl' => (int) ($_POST['js_ttl'] ?? 31536000),
                'css_ttl' => (int) ($_POST['css_ttl'] ?? 31536000),
                'font_ttl' => (int) ($_POST['font_ttl'] ?? 31536000),
                'image_ttl' => (int) ($_POST['image_ttl'] ?? 31536000),
                'aggressive_mode' => !empty($_POST['aggressive_mode']),
                'preload_critical' => !empty($_POST['preload_critical']),
                'cache_control_headers' => !empty($_POST['cache_control_headers']),
                'custom_domains' => array_filter(array_map('sanitize_text_field', $_POST['custom_domains'] ?? [])),
                'exclude_domains' => array_filter(array_map('sanitize_text_field', $_POST['exclude_domains'] ?? [])),
            ];
            
            $cacheManager->updateSettings($settings);
            $message = __('External cache settings saved.', 'fp-performance-suite');
        }
        
        return $message;
    }

    private function renderTabsNavigation(string $activeTab): void
    {
        $baseUrl = admin_url('admin.php?page=fp-performance-suite-cache');
        ?>
        <div class="nav-tab-wrapper" style="margin-bottom: 20px;">
            <a href="<?php echo esc_url($baseUrl . '&tab=page'); ?>" 
               class="nav-tab <?php echo $activeTab === 'page' ? 'nav-tab-active' : ''; ?>">
                📄 <?php esc_html_e('Page Cache', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=browser'); ?>" 
               class="nav-tab <?php echo $activeTab === 'browser' ? 'nav-tab-active' : ''; ?>">
                🌐 <?php esc_html_e('Browser Cache', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=pwa'); ?>" 
               class="nav-tab <?php echo $activeTab === 'pwa' ? 'nav-tab-active' : ''; ?>">
                📱 <?php esc_html_e('PWA', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=edge'); ?>" 
               class="nav-tab <?php echo $activeTab === 'edge' ? 'nav-tab-active' : ''; ?>">
                ☁️ <?php esc_html_e('Edge Cache', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=auto'); ?>" 
               class="nav-tab <?php echo $activeTab === 'auto' ? 'nav-tab-active' : ''; ?>">
                🤖 <?php esc_html_e('Auto Config', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=external'); ?>" 
               class="nav-tab <?php echo $activeTab === 'external' ? 'nav-tab-active' : ''; ?>">
                🌐 <?php esc_html_e('External Cache', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=intelligence'); ?>" 
               class="nav-tab <?php echo $activeTab === 'intelligence' ? 'nav-tab-active' : ''; ?>">
                🧠 <?php esc_html_e('Intelligence', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=exclusions'); ?>" 
               class="nav-tab <?php echo $activeTab === 'exclusions' ? 'nav-tab-active' : ''; ?>">
                🎯 <?php esc_html_e('Smart Exclusions', 'fp-performance-suite'); ?>
            </a>
        </div>
        <?php
    }

    private function renderPageCacheTab(): string
    {
        ob_start();
        
        $pageCache = $this->container->get(PageCache::class);
        $headers = $this->container->get(Headers::class);
        $prefetching = $this->container->get(PredictivePrefetching::class);
        
        $pageSettings = $pageCache->settings();
        $headerSettings = $headers->settings();
        $prefetchSettings = $prefetching->getSettings();
        $status = $pageCache->status();
        
        // Recupera le cache rules dal servizio Security
        $securityService = $this->container->get(HtaccessSecurity::class);
        $securitySettings = $securityService->settings();
        $headerSettings['cache_rules'] = $securitySettings['cache_rules'] ?? [
            'enabled' => false,
            'html_cache' => false,
            'fonts_cache' => true,
            'fonts_max_age' => 31536000,
            'images_max_age' => 31536000,
            'css_js_max_age' => 2592000,
        ];

        ?>
        
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
                    <input type="checkbox" name="prefetch_enabled" value="1" <?php checked($prefetchSettings['enabled'] ?? false); ?> data-risk="green" />
                </label>

                <table class="form-table" style="margin-top: 20px;">
                    <tr>
                        <th scope="row">
                            <label for="prefetch_strategy"><?php esc_html_e('Strategia', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <select name="prefetch_strategy" id="prefetch_strategy" class="regular-text">
                                <option value="hover" <?php selected($prefetchSettings['strategy'] ?? 'hover', 'hover'); ?>>
                                    <?php esc_html_e('Hover - Precarica al passaggio del mouse (Consigliato)', 'fp-performance-suite'); ?>
                                </option>
                                <option value="viewport" <?php selected($prefetchSettings['strategy'] ?? 'hover', 'viewport'); ?>>
                                    <?php esc_html_e('Viewport - Precarica link visibili', 'fp-performance-suite'); ?>
                                </option>
                                <option value="aggressive" <?php selected($prefetchSettings['strategy'] ?? 'hover', 'aggressive'); ?>>
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
                            <input type="number" name="prefetch_delay" id="prefetch_delay" value="<?php echo esc_attr((string) ($prefetchSettings['hover_delay'] ?? 100)); ?>" min="0" max="2000" step="50" class="small-text" />
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
                            <input type="number" name="prefetch_limit" id="prefetch_limit" value="<?php echo esc_attr((string) ($prefetchSettings['prefetch_limit'] ?? 5)); ?>" min="1" max="20" class="small-text" />
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
                            <textarea name="prefetch_ignore_patterns" id="prefetch_ignore_patterns" rows="5" class="large-text code"><?php echo esc_textarea(implode("\n", $prefetchSettings['ignore_patterns'] ?? [])); ?></textarea>
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

        <section class="fp-ps-card">
            <h2>⏱️ <?php esc_html_e('Regole di Cache Ottimizzate', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Definisci tempi di cache precisi per HTML, font, immagini, CSS/JS tramite regole .htaccess ottimizzate.', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-cache', 'fp_ps_cache_nonce'); ?>
                <input type="hidden" name="fp_ps_cache_rules" value="1" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Cache Rules', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Applica regole .htaccess ottimizzate per il caching dei file statici.', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="cache_rules_enabled" value="1" <?php checked($headerSettings['cache_rules']['enabled'] ?? false); ?> data-risk="green" />
                </label>
                
                <div style="margin-left: 20px; margin-top: 15px;">
                    <p>
                        <label>
                            <input type="checkbox" name="html_cache" value="1" <?php checked($headerSettings['cache_rules']['html_cache'] ?? false); ?> />
                            <?php esc_html_e('Cache HTML', 'fp-performance-suite'); ?>
                        </label>
                        <span class="description" style="display: block; margin-left: 24px; color: #d63638;"><?php esc_html_e('❌ Sconsigliato: meglio no-cache per contenuti dinamici', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <p>
                        <label>
                            <input type="checkbox" name="fonts_cache" value="1" <?php checked($headerSettings['cache_rules']['fonts_cache'] ?? true); ?> />
                            <?php esc_html_e('Cache Font (woff2, woff, ttf, otf)', 'fp-performance-suite'); ?>
                        </label>
                    </p>
                    <p style="margin-left: 24px;">
                        <label for="fonts_max_age"><?php esc_html_e('Durata cache font (secondi)', 'fp-performance-suite'); ?></label><br>
                        <input type="number" name="fonts_max_age" id="fonts_max_age" value="<?php echo esc_attr($headerSettings['cache_rules']['fonts_max_age'] ?? 31536000); ?>" min="0" class="small-text">
                        <span class="description"><?php esc_html_e('(default: 31536000 = 1 anno)', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <p style="margin-left: 24px;">
                        <label for="images_max_age"><?php esc_html_e('Durata cache immagini (secondi)', 'fp-performance-suite'); ?></label><br>
                        <input type="number" name="images_max_age" id="images_max_age" value="<?php echo esc_attr($headerSettings['cache_rules']['images_max_age'] ?? 31536000); ?>" min="0" class="small-text">
                        <span class="description"><?php esc_html_e('(default: 31536000 = 1 anno)', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <p style="margin-left: 24px;">
                        <label for="css_js_max_age"><?php esc_html_e('Durata cache CSS/JS (secondi)', 'fp-performance-suite'); ?></label><br>
                        <input type="number" name="css_js_max_age" id="css_js_max_age" value="<?php echo esc_attr($headerSettings['cache_rules']['css_js_max_age'] ?? 2592000); ?>" min="0" class="small-text">
                        <span class="description"><?php esc_html_e('(default: 2592000 = 1 mese)', 'fp-performance-suite'); ?></span>
                    </p>
                </div>

                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px; border-radius: 4px;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;">💡 <?php esc_html_e('Benefici Cache Rules:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #1e293b; line-height: 1.6;">
                        <li><?php esc_html_e('⚡ Riduce i tempi di caricamento per visitatori ricorrenti', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('📦 Diminuisce il carico sul server per file statici', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('🎯 Ottimizzazione specifica per tipo di file (font, immagini, CSS/JS)', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('🔧 Regole .htaccess automatiche e sicure', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>

                <div style="background: #fef3c7; border-left: 3px solid #fbbf24; padding: 12px; margin: 15px 0; border-radius: 4px;">
                    <strong style="color: #92400e;">⚠️ <?php esc_html_e('Attenzione', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 8px 0 0 0; color: #78350f; line-height: 1.5; font-size: 13px;">
                        <?php esc_html_e('Le regole vengono applicate automaticamente al file .htaccess. Viene creato un backup prima di ogni modifica.', 'fp-performance-suite'); ?>
                    </p>
                </div>

                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Cache Rules', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    private function renderBrowserCacheTab(string $message = ''): string
    {
        ob_start();
        
        $headers = $this->container->get(Headers::class);
        $headerSettings = $headers->settings();
        
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
                            <label>
                                <input type="checkbox" name="browser_cache_enabled" value="1" 
                                       <?php checked(!empty($headerSettings['enabled'])); ?>>
                                <?php esc_html_e('Abilita cache browser', 'fp-performance-suite'); ?>
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
        return (string) ob_get_clean();
    }

    private function renderPWATab(): string
    {
        ob_start();
        
        try {
            $pwaManager = $this->container->get(ServiceWorkerManager::class);
            $pwaSettings = $pwaManager->getSettings();
        } catch (\Exception $e) {
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
                        <th scope="row"><?php esc_html_e('Abilita PWA', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" value="1" 
                                       <?php checked(!empty($pwaSettings['enabled'])); ?>>
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
        return (string) ob_get_clean();
    }

    private function renderEdgeCacheTab(): string
    {
        ob_start();
        
        try {
            $edgeManager = $this->container->get(EdgeCacheManager::class);
            $edgeSettings = $edgeManager->getSettings();
        } catch (\Exception $e) {
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
        return (string) ob_get_clean();
    }

    private function renderAutoConfigTab(): string
    {
        ob_start();
        
        try {
            $autoConfigurator = $this->container->get(PageCacheAutoConfigurator::class);
            $recommendations = $autoConfigurator->getRecommendations();
        } catch (\Exception $e) {
            $autoConfigurator = null;
            $recommendations = [];
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
        return (string) ob_get_clean();
    }

    /**
     * Renderizza il tab External Cache
     */
    private function renderExternalCacheTab(string $message = ''): string
    {
        $cacheManager = new \FP\PerfSuite\Services\Assets\ExternalResourceCacheManager();
        $settings = $cacheManager->getSettings();
        $stats = $cacheManager->getCacheStats();
        $resources = $cacheManager->detectExternalResources();
        
        ob_start();
        ?>
        
        <?php if (!empty($message)): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- External Cache Status Cards -->
        <div class="fp-status-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1rem 0;">
            <div class="fp-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1rem;">
                <h3 style="margin: 0 0 0.5rem 0; color: #333;">📊 Risorse Totali</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #0073aa;"><?php echo $stats['total_resources']; ?></div>
            </div>
            
            <div class="fp-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1rem;">
                <h3 style="margin: 0 0 0.5rem 0; color: #333;">✅ In Cache</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #46b450;"><?php echo $stats['cached_resources']; ?></div>
            </div>
            
            <div class="fp-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1rem;">
                <h3 style="margin: 0 0 0.5rem 0; color: #333;">📈 Ratio Cache</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #00a0d2;"><?php echo $stats['cache_ratio']; ?>%</div>
            </div>
        </div>

        <!-- External Resources Table -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('🔍 Risorse Esterne Rilevate', 'fp-performance-suite'); ?></h2>
            
            <?php if (empty($resources['js']) && empty($resources['css']) && empty($resources['fonts'])): ?>
                <p><?php esc_html_e('Nessuna risorsa esterna rilevata. Visita il frontend del sito per rilevare le risorse.', 'fp-performance-suite'); ?></p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="widefat" style="margin-top: 1rem;">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Tipo', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Handle', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Dominio', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('URL', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Stato Cache', 'fp-performance-suite'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resources as $type => $typeResources): ?>
                                <?php foreach ($typeResources as $resource): ?>
                                    <tr>
                                        <td>
                                            <span class="fp-badge fp-badge-<?php echo $type; ?>" style="
                                                display: inline-block; 
                                                padding: 0.25rem 0.5rem; 
                                                border-radius: 4px; 
                                                font-size: 0.8rem; 
                                                font-weight: bold;
                                                background: <?php echo $type === 'js' ? '#0073aa' : ($type === 'css' ? '#46b450' : '#00a0d2'); ?>;
                                                color: white;
                                            ">
                                                <?php echo strtoupper($type); ?>
                                            </span>
                                        </td>
                                        <td><code><?php echo esc_html($resource['handle']); ?></code></td>
                                        <td><strong><?php echo esc_html($resource['domain']); ?></strong></td>
                                        <td>
                                            <a href="<?php echo esc_url($resource['src']); ?>" target="_blank" style="color: #0073aa;">
                                                <?php echo esc_html($this->truncateUrl($resource['src'], 50)); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($cacheManager->shouldCacheResource($resource['src'], $type)): ?>
                                                <span style="color: #46b450;">✅ Cached</span>
                                            <?php else: ?>
                                                <span style="color: #dc3232;">❌ Not Cached</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- External Cache Settings Form -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('⚙️ Configurazione Cache Esterna', 'fp-performance-suite'); ?></h2>
            
            <form method="post" action="">
                <?php wp_nonce_field('fp_external_cache_save', 'fp_external_cache_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enabled"><?php esc_html_e('Abilita Cache Esterna', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($settings['enabled']); ?> />
                            <p class="description">
                                <?php esc_html_e('Abilita la gestione automatica degli header di cache per risorse esterne.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="js_ttl"><?php esc_html_e('TTL JavaScript (secondi)', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="js_ttl" name="js_ttl" value="<?php echo $settings['js_ttl']; ?>" 
                                   min="3600" max="63072000" step="3600" style="width: 200px;" />
                            <p class="description">
                                <?php esc_html_e('Durata cache per file JavaScript esterni. Valore consigliato: 31536000 (1 anno).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="css_ttl"><?php esc_html_e('TTL CSS (secondi)', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="css_ttl" name="css_ttl" value="<?php echo $settings['css_ttl']; ?>" 
                                   min="3600" max="63072000" step="3600" style="width: 200px;" />
                            <p class="description">
                                <?php esc_html_e('Durata cache per file CSS esterni. Valore consigliato: 31536000 (1 anno).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="font_ttl"><?php esc_html_e('TTL Font (secondi)', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="font_ttl" name="font_ttl" value="<?php echo $settings['font_ttl']; ?>" 
                                   min="3600" max="63072000" step="3600" style="width: 200px;" />
                            <p class="description">
                                <?php esc_html_e('Durata cache per font esterni. Valore consigliato: 31536000 (1 anno).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="aggressive_mode"><?php esc_html_e('Modalità Aggressiva', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="aggressive_mode" name="aggressive_mode" value="1" <?php checked($settings['aggressive_mode']); ?> />
                            <p class="description">
                                <?php esc_html_e('Abilita preload automatico per risorse critiche e header di cache più aggressivi.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="preload_critical"><?php esc_html_e('Preload Risorse Critiche', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="preload_critical" name="preload_critical" value="1" <?php checked($settings['preload_critical']); ?> />
                            <p class="description">
                                <?php esc_html_e('Aggiunge header Link preload per risorse critiche identificate automaticamente.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="cache_control_headers"><?php esc_html_e('Header Cache-Control', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="cache_control_headers" name="cache_control_headers" value="1" <?php checked($settings['cache_control_headers']); ?> />
                            <p class="description">
                                <?php esc_html_e('Aggiunge header Cache-Control personalizzati per migliorare la compatibilità con i browser.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <div class="fp-domain-management" style="margin: 2rem 0;">
                    <h3><?php esc_html_e('🌐 Gestione Domini', 'fp-performance-suite'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="custom_domains"><?php esc_html_e('Domini Personalizzati', 'fp-performance-suite'); ?></label>
                            </th>
                            <td>
                                <textarea id="custom_domains" name="custom_domains[]" rows="3" cols="50" 
                                          placeholder="esempio.com&#10;cdn.example.com&#10;fonts.googleapis.com"><?php 
                                    echo esc_textarea(implode("\n", $settings['custom_domains'])); 
                                ?></textarea>
                                <p class="description">
                                    <?php esc_html_e('Un dominio per riga. Se specificato, solo questi domini verranno cachati.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="exclude_domains"><?php esc_html_e('Domini Esclusi', 'fp-performance-suite'); ?></label>
                            </th>
                            <td>
                                <textarea id="exclude_domains" name="exclude_domains[]" rows="3" cols="50" 
                                          placeholder="ads.example.com&#10;tracking.example.com"><?php 
                                    echo esc_textarea(implode("\n", $settings['exclude_domains'])); 
                                ?></textarea>
                                <p class="description">
                                    <?php esc_html_e('Domini da escludere dalla cache. Un dominio per riga.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="fp-actions" style="margin: 2rem 0;">
                    <?php submit_button(__('💾 Salva Impostazioni', 'fp-performance-suite'), 'primary', 'submit', false); ?>
                    
                    <button type="button" class="button" onclick="location.reload();" style="margin-left: 1rem;">
                        <?php esc_html_e('🔄 Rileva Risorse', 'fp-performance-suite'); ?>
                    </button>
                    
                    <button type="button" class="button" onclick="if(confirm('Sei sicuro?')) { window.location.href = '<?php echo admin_url('admin.php?page=fp-performance-suite-cache&tab=external&action=clear-cache'); ?>'; }" style="margin-left: 1rem;">
                        <?php esc_html_e('🗑️ Pulisci Cache', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
        </section>
        
        <?php
        return ob_get_clean();
    }


    /**
     * Tronca un URL per la visualizzazione
     */
    private function truncateUrl(string $url, int $length): string
    {
        return strlen($url) > $length ? substr($url, 0, $length) . '...' : $url;
    }
    
    /**
     * Render Intelligence tab
     */
    private function renderIntelligenceTab(): string
    {
        ob_start();
        
        // Include Intelligence Dashboard content
        try {
            $intelligencePage = $this->container->get(\FP\PerfSuite\Admin\Pages\IntelligenceDashboard::class);
            // Estrae solo il contenuto senza il wrapper della pagina
            $content = $intelligencePage->content();
            
            // Rimuove l'intro box se presente (perché abbiamo già quello della pagina Cache)
            $content = preg_replace('/<div class="fp-ps-page-intro".*?<\/div>/s', '', $content);
            
            echo $content;
        } catch (\Exception $e) {
            ?>
            <div class="notice notice-error">
                <p><?php echo esc_html(sprintf(__('Errore nel caricamento di Intelligence: %s', 'fp-performance-suite'), $e->getMessage())); ?></p>
            </div>
            <?php
        }
        
        return (string) ob_get_clean();
    }
    
    /**
     * Render Smart Exclusions tab
     */
    private function renderExclusionsTab(): string
    {
        ob_start();
        
        // Include Smart Exclusions content
        try {
            $exclusionsPage = $this->container->get(\FP\PerfSuite\Admin\Pages\Exclusions::class);
            // Estrae solo il contenuto senza il wrapper della pagina
            $content = $exclusionsPage->content();
            
            // Rimuove l'intro box se presente
            $content = preg_replace('/<div class="fp-ps-page-intro".*?<\/div>/s', '', $content);
            
            echo $content;
        } catch (\Exception $e) {
            ?>
            <div class="notice notice-error">
                <p><?php echo esc_html(sprintf(__('Errore nel caricamento di Smart Exclusions: %s', 'fp-performance-suite'), $e->getMessage())); ?></p>
            </div>
            <?php
        }
        
        return (string) ob_get_clean();
    }
}
