<?php

namespace FP\PerfSuite\Services\PWA;

use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\Fs;

use function add_action;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * Service Worker Manager
 *
 * Manages service worker generation and registration for PWA functionality
 *
 * @package FP\PerfSuite\Services\PWA
 * @author Francesco Passeri
 */
class ServiceWorkerManager
{
    private const OPTION = 'fp_ps_service_worker';
    private const SW_FILE = 'fp-sw.js';
    private const MANIFEST_FILE = 'fp-manifest.json';

    private Fs $fs;

    public function __construct(?Fs $fs = null)
    {
        $this->fs = $fs ?? new Fs();
    }

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Generate service worker file
        add_action('init', [$this, 'maybeGenerateServiceWorker']);
        
        // Register service worker
        add_action('wp_footer', [$this, 'registerServiceWorker'], 999);
        
        // Add manifest link
        add_action('wp_head', [$this, 'addManifestLink'], 1);
        
        // Generate manifest file
        $this->maybeGenerateManifest();
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,cache_strategy:string,cache_assets:bool,cache_pages:bool,cache_api:bool,offline_page:bool,update_interval:int,max_cache_size:int}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'cache_strategy' => 'network_first', // cache_first, network_first, stale_while_revalidate
            'cache_assets' => true,
            'cache_pages' => true,
            'cache_api' => false,
            'offline_page' => true,
            'update_interval' => 86400, // 24 hours
            'max_cache_size' => 50, // MB
            'app_name' => get_bloginfo('name'),
            'app_short_name' => get_bloginfo('name'),
            'app_description' => get_bloginfo('description'),
            'theme_color' => '#ffffff',
            'background_color' => '#ffffff',
        ];

        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    /**
     * Update settings
     *
     * @param array $settings New settings
     */
    public function update(array $settings): void
    {
        $current = $this->settings();

        $updateInterval = isset($settings['update_interval']) ? (int)$settings['update_interval'] : $current['update_interval'];
        $updateInterval = max(3600, min(604800, $updateInterval));

        $maxCacheSize = isset($settings['max_cache_size']) ? (int)$settings['max_cache_size'] : $current['max_cache_size'];
        $maxCacheSize = max(10, min(500, $maxCacheSize));

        $new = [
            'enabled' => !empty($settings['enabled']),
            'cache_strategy' => $settings['cache_strategy'] ?? $current['cache_strategy'],
            'cache_assets' => isset($settings['cache_assets']) ? !empty($settings['cache_assets']) : $current['cache_assets'],
            'cache_pages' => isset($settings['cache_pages']) ? !empty($settings['cache_pages']) : $current['cache_pages'],
            'cache_api' => isset($settings['cache_api']) ? !empty($settings['cache_api']) : $current['cache_api'],
            'offline_page' => isset($settings['offline_page']) ? !empty($settings['offline_page']) : $current['offline_page'],
            'update_interval' => $updateInterval,
            'max_cache_size' => $maxCacheSize,
            'app_name' => $settings['app_name'] ?? $current['app_name'],
            'app_short_name' => $settings['app_short_name'] ?? $current['app_short_name'],
            'app_description' => $settings['app_description'] ?? $current['app_description'],
            'theme_color' => $settings['theme_color'] ?? $current['theme_color'],
            'background_color' => $settings['background_color'] ?? $current['background_color'],
        ];

        update_option(self::OPTION, $new);

        // Regenerate files
        $this->generateServiceWorker(true);
        $this->generateManifest(true);
    }

    /**
     * Maybe generate service worker file
     */
    public function maybeGenerateServiceWorker(): void
    {
        $swPath = ABSPATH . self::SW_FILE;
        
        // Generate if doesn't exist or is older than update interval
        if (!file_exists($swPath) || (time() - filemtime($swPath)) > $this->settings()['update_interval']) {
            $this->generateServiceWorker();
        }
    }

    /**
     * Generate service worker JavaScript file
     *
     * @param bool $force Force regeneration
     * @return bool True if generated
     */
    public function generateServiceWorker(bool $force = false): bool
    {
        $swPath = ABSPATH . self::SW_FILE;
        
        if (!$force && file_exists($swPath)) {
            return true;
        }

        $settings = $this->settings();
        $version = time(); // Cache version
        
        $swContent = $this->getServiceWorkerTemplate($settings, $version);

        if (!file_put_contents($swPath, $swContent)) {
            Logger::error('Failed to generate service worker file');
            return false;
        }

        Logger::info('Service worker generated', ['version' => $version]);
        return true;
    }

    /**
     * Get service worker template
     *
     * @param array $settings Settings
     * @param int $version Cache version
     * @return string Service worker JavaScript
     */
    private function getServiceWorkerTemplate(array $settings, int $version): string
    {
        $cacheName = 'fp-ps-cache-v' . $version;
        $strategy = $settings['cache_strategy'];
        $maxSize = $settings['max_cache_size'] * 1024 * 1024; // Convert MB to bytes

        return <<<JS
// FP Performance Suite Service Worker
// Generated: {date('Y-m-d H:i:s')}

const CACHE_NAME = '$cacheName';
const MAX_CACHE_SIZE = $maxSize;

// Assets to precache
const PRECACHE_URLS = [
    '/',
    '/wp-includes/css/dist/block-library/style.min.css',
];

// Install event - precache assets
self.addEventListener('install', event => {
    console.log('[FP SW] Installing service worker');
    
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('[FP SW] Precaching assets');
            return cache.addAll(PRECACHE_URLS);
        }).then(() => {
            return self.skipWaiting();
        })
    );
});

// Activate event - clean old caches
self.addEventListener('activate', event => {
    console.log('[FP SW] Activating service worker');
    
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.filter(name => {
                    return name.startsWith('fp-ps-cache-') && name !== CACHE_NAME;
                }).map(name => {
                    console.log('[FP SW] Deleting old cache:', name);
                    return caches.delete(name);
                })
            );
        }).then(() => {
            return self.clients.claim();
        })
    );
});

// Fetch event - apply caching strategy
self.addEventListener('fetch', event => {
    const request = event.request;
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip admin and login pages
    if (request.url.includes('/wp-admin/') || request.url.includes('/wp-login.php')) {
        return;
    }
    
    event.respondWith(
        handleFetch(request)
    );
});

async function handleFetch(request) {
    const url = new URL(request.url);
    
    // Determine strategy based on resource type
    if (isAsset(url)) {
        return cacheFirst(request);
    } else if (isPage(url)) {
        return {strategy}Strategy(request);
    } else {
        return networkOnly(request);
    }
}

function isAsset(url) {
    const assetExtensions = ['.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.webp', '.avif', '.woff', '.woff2', '.svg'];
    return assetExtensions.some(ext => url.pathname.endsWith(ext));
}

function isPage(url) {
    return url.origin === location.origin && !url.pathname.includes('/wp-json/');
}

// Cache-first strategy
async function cacheFirst(request) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);
    
    if (cached) {
        return cached;
    }
    
    try {
        const response = await fetch(request);
        if (response.ok) {
            await enforceMaxCacheSize();
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        return new Response('Offline', { status: 503 });
    }
}

// Network-first strategy
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            await enforceMaxCacheSize();
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cache = await caches.open(CACHE_NAME);
        const cached = await cache.match(request);
        return cached || new Response('Offline', { status: 503 });
    }
}

// Stale-while-revalidate strategy
async function staleWhileRevalidate(request) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);
    
    const fetchPromise = fetch(request).then(response => {
        if (response.ok) {
            enforceMaxCacheSize().then(() => {
                cache.put(request, response.clone());
            });
        }
        return response;
    });
    
    return cached || fetchPromise;
}

// Network-only strategy
async function networkOnly(request) {
    return fetch(request);
}

// Enforce maximum cache size
async function enforceMaxCacheSize() {
    const cache = await caches.open(CACHE_NAME);
    const keys = await cache.keys();
    
    let totalSize = 0;
    const sizes = await Promise.all(
        keys.map(async request => {
            const response = await cache.match(request);
            const blob = await response.blob();
            return { request, size: blob.size };
        })
    );
    
    // Sort by size descending
    sizes.sort((a, b) => b.size - a.size);
    
    for (const item of sizes) {
        totalSize += item.size;
        if (totalSize > MAX_CACHE_SIZE) {
            console.log('[FP SW] Removing from cache to enforce size limit:', item.request.url);
            await cache.delete(item.request);
        }
    }
}

console.log('[FP SW] Service worker loaded');
JS;
    }

    /**
     * Register service worker in frontend
     */
    public function registerServiceWorker(): void
    {
        $swUrl = home_url('/' . self::SW_FILE);
        ?>
        <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo esc_url($swUrl); ?>')
                    .then(function(registration) {
                        console.log('[FP Performance] Service worker registered:', registration.scope);
                    })
                    .catch(function(error) {
                        console.error('[FP Performance] Service worker registration failed:', error);
                    });
            });
        }
        </script>
        <?php
    }

    /**
     * Maybe generate manifest file
     */
    public function maybeGenerateManifest(): void
    {
        $manifestPath = ABSPATH . self::MANIFEST_FILE;
        
        if (!file_exists($manifestPath)) {
            $this->generateManifest();
        }
    }

    /**
     * Generate web app manifest file
     *
     * @param bool $force Force regeneration
     * @return bool True if generated
     */
    public function generateManifest(bool $force = false): bool
    {
        $manifestPath = ABSPATH . self::MANIFEST_FILE;
        
        if (!$force && file_exists($manifestPath)) {
            return true;
        }

        $settings = $this->settings();

        $manifest = [
            'name' => $settings['app_name'],
            'short_name' => $settings['app_short_name'],
            'description' => $settings['app_description'],
            'start_url' => home_url('/'),
            'display' => 'standalone',
            'background_color' => $settings['background_color'],
            'theme_color' => $settings['theme_color'],
            'icons' => $this->getManifestIcons(),
        ];

        $manifestJson = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (!file_put_contents($manifestPath, $manifestJson)) {
            Logger::error('Failed to generate manifest file');
            return false;
        }

        Logger::info('Web app manifest generated');
        return true;
    }

    /**
     * Get manifest icons
     *
     * @return array Icons array
     */
    private function getManifestIcons(): array
    {
        $icons = [];

        // Check for site icon
        $siteIconId = get_option('site_icon');
        if ($siteIconId) {
            $sizes = [192, 512];
            foreach ($sizes as $size) {
                $icon = wp_get_attachment_image_src($siteIconId, [$size, $size]);
                if ($icon) {
                    $icons[] = [
                        'src' => $icon[0],
                        'sizes' => $size . 'x' . $size,
                        'type' => 'image/png',
                    ];
                }
            }
        }

        return $icons;
    }

    /**
     * Add manifest link to head
     */
    public function addManifestLink(): void
    {
        $manifestUrl = home_url('/' . self::MANIFEST_FILE);
        echo '<link rel="manifest" href="' . esc_url($manifestUrl) . '">' . "\n";
    }

    /**
     * Unregister service worker (remove files)
     *
     * @return bool True if unregistered
     */
    public function unregister(): bool
    {
        $swPath = ABSPATH . self::SW_FILE;
        $manifestPath = ABSPATH . self::MANIFEST_FILE;

        $success = true;

        if (file_exists($swPath)) {
            $success = unlink($swPath) && $success;
        }

        if (file_exists($manifestPath)) {
            $success = unlink($manifestPath) && $success;
        }

        if ($success) {
            Logger::info('Service worker unregistered');
        }

        return $success;
    }

    /**
     * Get status
     *
     * @return array{enabled:bool,sw_exists:bool,manifest_exists:bool,sw_size:int}
     */
    public function status(): array
    {
        $settings = $this->settings();
        $swPath = ABSPATH . self::SW_FILE;
        $manifestPath = ABSPATH . self::MANIFEST_FILE;

        return [
            'enabled' => $settings['enabled'],
            'sw_exists' => file_exists($swPath),
            'manifest_exists' => file_exists($manifestPath),
            'sw_size' => file_exists($swPath) ? filesize($swPath) : 0,
        ];
    }
}
