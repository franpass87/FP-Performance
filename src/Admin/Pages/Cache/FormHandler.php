<?php

namespace FP\PerfSuite\Admin\Pages\Cache;

use FP\PerfSuite\Admin\Form\AbstractFormHandler;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Assets\PredictivePrefetching;
use FP\PerfSuite\Services\Security\HtaccessSecurity;
use FP\PerfSuite\Services\PWA\ServiceWorkerManager;
use FP\PerfSuite\Services\Cache\EdgeCacheManager;
use FP\PerfSuite\Services\Assets\ExternalResourceCacheManager;
use FP\PerfSuite\Admin\NoticeManager;
use FP\PerfSuite\Utils\ErrorHandler;

/**
 * Gestisce le submission dei form per Cache page
 * 
 * @package FP\PerfSuite\Admin\Pages\Cache
 * @author Francesco Passeri
 */
class FormHandler extends AbstractFormHandler
{

    /**
     * Gestisce tutte le submission dei form
     * 
     * @param string $activeTab Tab attivo (opzionale, per backward compatibility)
     * @return string Messaggio di risultato
     */
    public function handle(string $activeTab = ''): string
    {
        if (!$this->isPost()) {
            return '';
        }

        // Se activeTab non specificato, cerca di determinarlo dal POST
        if (empty($activeTab)) {
            // Verifica quale form è stato inviato
            if (isset($_POST['fp_ps_edge_cache'])) {
                $activeTab = 'edge';
            } elseif (isset($_POST['fp_ps_page_cache'])) {
                $activeTab = 'page';
            } elseif (isset($_POST['fp_ps_browser_cache'])) {
                $activeTab = 'browser';
            } elseif (isset($_POST['fp_ps_pwa'])) {
                $activeTab = 'pwa';
            } elseif (isset($_POST['fp_ps_external_cache'])) {
                $activeTab = 'external';
            } else {
                $activeTab = $this->sanitizeInput('active_tab', 'text') ?? 'page';
            }
        }

        $message = '';

        try {
            switch ($activeTab) {
                case 'page':
                    $message = $this->handlePageCacheForm();
                    break;
                case 'browser':
                    $message = $this->handleBrowserCacheForm();
                    break;
                case 'pwa':
                    $message = $this->handlePWAForm();
                    break;
                case 'edge':
                    $message = $this->handleEdgeCacheForm();
                    break;
                case 'external':
                    $message = $this->handleExternalCacheForm();
                    break;
            }
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Cache form handling');
        }

        return $message;
    }

    /**
     * Gestisce form Page Cache tab
     */
    private function handlePageCacheForm(): string
    {
        if (!$this->verifyNonce('fp_ps_cache_nonce', 'fp-ps-cache')) {
            return '';
        }

        try {
            $pageCache = $this->container->get(PageCache::class);
            $headers = $this->container->get(Headers::class);
            $prefetching = $this->container->get(PredictivePrefetching::class);
            $headerSettings = $headers->settings();

            // Page Cache
            if (isset($_POST['fp_ps_page_cache'])) {
                $enabledRequested = isset($_POST['page_cache_enabled']) ? ($this->sanitizeInput('page_cache_enabled', 'bool') ?? false) : false;
                $ttlRequested = $this->sanitizeInput('page_cache_ttl', 'int') ?? 3600;
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
                NoticeManager::success($message);
                return $message;
            }

            // Browser Cache
            if (isset($_POST['fp_ps_browser_cache'])) {
                $headers->update([
                    'enabled' => isset($_POST['browser_cache_enabled']) ? ($this->sanitizeInput('browser_cache_enabled', 'bool') ?? false) : false,
                    'headers' => [
                        'Cache-Control' => $this->sanitizeInput('cache_control', 'text') ?? 'public, max-age=31536000',
                    ],
                    'expires_ttl' => $this->sanitizeInput('expires_ttl', 'int') ?? $headerSettings['expires_ttl'],
                    'htaccess' => $this->sanitizeInput('htaccess_rules', 'textarea') ?? '',
                ]);
                NoticeManager::success(__('Browser cache settings saved.', 'fp-performance-suite'));
                return __('Browser cache settings saved.', 'fp-performance-suite');
            }

            // Predictive Prefetching
            if (isset($_POST['fp_ps_prefetch'])) {
                $ignorePatternsRaw = $this->sanitizeInput('prefetch_ignore_patterns', 'textarea') ?? '';
                $ignorePatterns = !empty($ignorePatternsRaw)
                    ? array_filter(array_map('trim', explode("\n", $ignorePatternsRaw)))
                    : [];
                
                $prefetching->updateSettings([
                    'enabled' => isset($_POST['prefetch_enabled']) ? ($this->sanitizeInput('prefetch_enabled', 'bool') ?? false) : false,
                    'strategy' => $this->sanitizeInput('prefetch_strategy', 'text') ?? 'hover',
                    'hover_delay' => $this->sanitizeInput('prefetch_delay', 'int') ?? 100,
                    'prefetch_limit' => $this->sanitizeInput('prefetch_limit', 'int') ?? 5,
                    'ignore_patterns' => $ignorePatterns,
                ]);
                NoticeManager::success(__('Predictive prefetching settings saved.', 'fp-performance-suite'));
                return __('Predictive prefetching settings saved.', 'fp-performance-suite');
            }

            // Clear Cache
            if (isset($_POST['fp_ps_clear_cache'])) {
                $pageCache->clear();
                NoticeManager::success(__('Page cache cleared.', 'fp-performance-suite'));
                return __('Page cache cleared.', 'fp-performance-suite');
            }

            // Cache Rules
            if (isset($_POST['fp_ps_cache_rules'])) {
                $securityService = $this->container->get(HtaccessSecurity::class);
                $currentSettings = $securityService->settings();
                
                $newSettings = $currentSettings;
                $newSettings['cache_rules'] = [
                    'enabled' => isset($_POST['cache_rules_enabled']) ? ($this->sanitizeInput('cache_rules_enabled', 'bool') ?? false) : false,
                    'html_cache' => isset($_POST['html_cache']) ? ($this->sanitizeInput('html_cache', 'bool') ?? false) : false,
                    'fonts_cache' => isset($_POST['fonts_cache']) ? ($this->sanitizeInput('fonts_cache', 'bool') ?? false) : false,
                    'fonts_max_age' => max(0, $this->sanitizeInput('fonts_max_age', 'int') ?? 31536000),
                    'images_max_age' => max(0, $this->sanitizeInput('images_max_age', 'int') ?? 31536000),
                    'css_js_max_age' => max(0, $this->sanitizeInput('css_js_max_age', 'int') ?? 2592000),
                ];
                
                $securityService->update($newSettings);
                NoticeManager::success(__('Cache rules settings saved.', 'fp-performance-suite'));
                return __('Cache rules settings saved.', 'fp-performance-suite');
            }

        } catch (\Throwable $e) {
            return $this->handleError($e, 'Page cache form');
        }

        return '';
    }

    /**
     * Gestisce form Browser Cache tab
     */
    private function handleBrowserCacheForm(): string
    {
        if (!$this->verifyNonce('fp_ps_cache_nonce', 'fp-ps-cache')) {
            return '';
        }

        try {
            $headers = $this->container->get(Headers::class);
            $headerSettings = $headers->settings();

            $headers->update([
                'enabled' => isset($_POST['browser_cache_enabled']) ? ($this->sanitizeInput('browser_cache_enabled', 'bool') ?? false) : false,
                'headers' => [
                    'Cache-Control' => $this->sanitizeInput('cache_control', 'text') ?? 'public, max-age=31536000',
                ],
                'expires_ttl' => $this->sanitizeInput('expires_ttl', 'int') ?? $headerSettings['expires_ttl'],
                'htaccess' => $this->sanitizeInput('htaccess_rules', 'textarea') ?? '',
            ]);
            NoticeManager::success(__('Browser cache settings saved.', 'fp-performance-suite'));
            return $this->successMessage(__('Browser cache settings saved.', 'fp-performance-suite'));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Browser cache form');
        }
    }

    /**
     * Gestisce form PWA tab
     */
    private function handlePWAForm(): string
    {
        if (!$this->verifyNonce('fp_ps_pwa_nonce', 'fp_ps_pwa')) {
            return '';
        }

        try {
            $pwaManager = $this->container->get(ServiceWorkerManager::class);
            
            $settings = [
                'enabled' => isset($_POST['enabled']) ? ($this->sanitizeInput('enabled', 'bool') ?? false) : false,
                'cache_strategy' => $this->sanitizeInput('cache_strategy', 'text') ?? 'cache_first',
                'cache_duration' => $this->sanitizeInput('cache_duration', 'int') ?? 86400,
                'offline_fallback' => isset($_POST['offline_fallback']) ? ($this->sanitizeInput('offline_fallback', 'bool') ?? false) : false,
            ];
            
            $pwaManager->updateSettings($settings);
            NoticeManager::success(__('Configurazione PWA salvata con successo!', 'fp-performance-suite'));
            return $this->successMessage(__('Configurazione PWA salvata con successo!', 'fp-performance-suite'));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'PWA form');
        }
    }

    /**
     * Gestisce form Edge Cache tab
     */
    private function handleEdgeCacheForm(): string
    {
        if (!$this->verifyNonce('fp_ps_edge_cache_nonce', 'fp_ps_edge_cache')) {
            return '';
        }

        try {
            $edgeManager = $this->container->get(EdgeCacheManager::class);
            
            $settings = [
                'enabled' => isset($_POST['enabled']) ? ($this->sanitizeInput('enabled', 'bool') ?? false) : false,
                'provider' => $this->sanitizeInput('provider', 'text') ?? 'cloudflare',
                'api_key' => $this->sanitizeInput('api_key', 'text') ?? '',
                'zone_id' => $this->sanitizeInput('zone_id', 'text') ?? '',
            ];
            
            $edgeManager->updateSettings($settings);
            NoticeManager::success(__('Configurazione Edge Cache salvata con successo!', 'fp-performance-suite'));
            return $this->successMessage(__('Configurazione Edge Cache salvata con successo!', 'fp-performance-suite'));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Edge cache form');
        }
    }

    /**
     * Gestisce form External Cache tab
     */
    private function handleExternalCacheForm(): string
    {
        if (!$this->verifyNonce('fp_external_cache_nonce', 'fp_external_cache_save')) {
            return '';
        }

        try {
            $cacheManager = $this->container->get(ExternalResourceCacheManager::class);
            $settings = [
                'enabled' => isset($_POST['enabled']) ? ($this->sanitizeInput('enabled', 'bool') ?? false) : false,
                'js_ttl' => $this->sanitizeInput('js_ttl', 'int') ?? 31536000,
                'css_ttl' => $this->sanitizeInput('css_ttl', 'int') ?? 31536000,
                'font_ttl' => $this->sanitizeInput('font_ttl', 'int') ?? 31536000,
                'image_ttl' => $this->sanitizeInput('image_ttl', 'int') ?? 31536000,
                'aggressive_mode' => isset($_POST['aggressive_mode']) ? ($this->sanitizeInput('aggressive_mode', 'bool') ?? false) : false,
                'preload_critical' => isset($_POST['preload_critical']) ? ($this->sanitizeInput('preload_critical', 'bool') ?? false) : false,
                'cache_control_headers' => isset($_POST['cache_control_headers']) ? ($this->sanitizeInput('cache_control_headers', 'bool') ?? false) : false,
                'custom_domains' => $this->sanitizeInput('custom_domains', 'array') ?? [],
                'exclude_domains' => $this->sanitizeInput('exclude_domains', 'array') ?? [],
            ];
            
            // Filtra array vuoti
            $settings['custom_domains'] = array_filter($settings['custom_domains']);
            $settings['exclude_domains'] = array_filter($settings['exclude_domains']);
            
            $cacheManager->updateSettings($settings);
            NoticeManager::success(__('External cache settings saved.', 'fp-performance-suite'));
            return $this->successMessage(__('External cache settings saved.', 'fp-performance-suite'));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'External cache form');
        }
    }
}
















