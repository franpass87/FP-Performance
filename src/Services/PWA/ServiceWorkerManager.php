<?php

namespace FP\PerfSuite\Services\PWA;

use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Logger;

/**
 * Service Worker Manager
 * 
 * Gestisce Service Worker per funzionalità PWA
 *
 * @package FP\PerfSuite\Services\PWA
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ServiceWorkerManager
{
    private const OPTION_KEY = 'fp_ps_service_worker';
    private const SW_FILENAME = 'fp-sw.js';

    private Fs $fs;

    public function __construct(Fs $fs)
    {
        $this->fs = $fs;
    }

    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $settings = $this->getSettings();

        if (empty($settings['enabled'])) {
            return;
        }

        // Genera Service Worker
        add_action('init', [$this, 'generateServiceWorker']);

        // Registra Service Worker nel frontend
        add_action('wp_footer', [$this, 'enqueueRegistrationScript'], 999);

        // Manifest PWA
        if (!empty($settings['generate_manifest'])) {
            add_action('wp_head', [$this, 'addManifestLink']);
            add_action('init', [$this, 'serveManifest']);
        }

        Logger::debug('Service Worker Manager registered');
    }

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'cache_strategy' => 'network-first',
            'cache_images' => true,
            'cache_css' => true,
            'cache_js' => true,
            'cache_fonts' => true,
            'cache_ttl' => 86400, // 1 giorno
            'offline_page' => false,
            'generate_manifest' => true,
            'app_name' => get_bloginfo('name'),
            'app_short_name' => get_bloginfo('name'),
            'theme_color' => '#ffffff',
            'background_color' => '#ffffff',
        ];

        $options = get_option(self::OPTION_KEY, []);
        return wp_parse_args($options, $defaults);
    }

    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = wp_parse_args($settings, $current);

        // Rigenera Service Worker
        $this->generateServiceWorker();

        $result = update_option(self::OPTION_KEY, $updated);
        
        if ($result) {
            Logger::info('Service Worker settings updated', $updated);
        }

        return $result;
    }

    /**
     * Genera file Service Worker
     */
    public function generateServiceWorker(): void
    {
        $settings = $this->getSettings();
        
        $swContent = $this->getServiceWorkerTemplate();
        
        // Sostituisci variabili
        $replacements = [
            '{{CACHE_NAME}}' => 'fp-cache-v' . time(),
            '{{CACHE_STRATEGY}}' => $settings['cache_strategy'],
            '{{CACHE_IMAGES}}' => $settings['cache_images'] ? 'true' : 'false',
            '{{CACHE_CSS}}' => $settings['cache_css'] ? 'true' : 'false',
            '{{CACHE_JS}}' => $settings['cache_js'] ? 'true' : 'false',
            '{{CACHE_FONTS}}' => $settings['cache_fonts'] ? 'true' : 'false',
            '{{CACHE_TTL}}' => $settings['cache_ttl'],
            '{{SITE_URL}}' => home_url(),
        ];

        foreach ($replacements as $placeholder => $value) {
            $swContent = str_replace($placeholder, $value, $swContent);
        }

        // Scrivi file nella root con file lock
        $swPath = ABSPATH . self::SW_FILENAME;
        $result = $this->safeServiceWorkerWrite($swPath, $swContent);
        
        if ($result) {
            Logger::info('Service Worker generated', ['path' => $swPath]);
        } else {
            Logger::warning('Service Worker generation failed - file locked');
        }
    }

    /**
     * Scrive il Service Worker in modo sicuro con file lock
     * 
     * @param string $filePath Path del file Service Worker
     * @param string $content Contenuto da scrivere
     * @return bool True se scrittura riuscita
     */
    private function safeServiceWorkerWrite(string $filePath, string $content): bool
    {
        $lockFile = $filePath . '.lock';
        $lock = fopen($lockFile, 'c+');
        
        if (!$lock) {
            Logger::error('Failed to create Service Worker lock file', ['file' => $lockFile]);
            return false;
        }
        
        // Acquire exclusive lock (non-blocking)
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            Logger::debug('Service Worker file locked by another process');
            return false; // Another process is writing
        }
        
        try {
            // Write Service Worker file safely
            $result = file_put_contents($filePath, $content, LOCK_EX);
            
            if ($result === false) {
                Logger::error('Failed to write Service Worker file');
                return false;
            }
            
            Logger::debug('Service Worker written safely');
            return true;
        } finally {
            // Always release lock
            flock($lock, LOCK_UN);
            fclose($lock);
            @unlink($lockFile);
        }
    }

    /**
     * Template Service Worker
     */
    private function getServiceWorkerTemplate(): string
    {
        return <<<'JAVASCRIPT'
const CACHE_NAME = '{{CACHE_NAME}}';
const CACHE_STRATEGY = '{{CACHE_STRATEGY}}';
const CACHE_TTL = {{CACHE_TTL}};
const SITE_URL = '{{SITE_URL}}';

// Risorse da cachare immediatamente
const PRECACHE_URLS = [
    '/',
    '/wp-content/themes/',
];

// Install event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

// Activate event
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames
                        .filter(name => name !== CACHE_NAME)
                        .map(name => caches.delete(name))
                );
            })
            .then(() => self.clients.claim())
    );
});

// Fetch event
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

    event.respondWith(handleFetch(request));
});

async function handleFetch(request) {
    const url = new URL(request.url);
    
    // Determina strategia basata sul tipo di risorsa
    if (shouldCache(url.pathname)) {
        if (CACHE_STRATEGY === 'cache-first') {
            return cacheFirst(request);
        } else {
            return networkFirst(request);
        }
    }

    return fetch(request);
}

function shouldCache(pathname) {
    const ext = pathname.split('.').pop();
    
    if ({{CACHE_IMAGES}} && /\.(jpg|jpeg|png|gif|webp|svg|ico)$/i.test(pathname)) {
        return true;
    }
    
    if ({{CACHE_CSS}} && /\.css$/i.test(pathname)) {
        return true;
    }
    
    if ({{CACHE_JS}} && /\.js$/i.test(pathname)) {
        return true;
    }
    
    if ({{CACHE_FONTS}} && /\.(woff|woff2|ttf|otf|eot)$/i.test(pathname)) {
        return true;
    }
    
    return false;
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        return cached || new Response('Offline', { status: 503 });
    }
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        return cached || new Response('Offline', { status: 503 });
    }
}
JAVASCRIPT;
    }

    /**
     * Script registrazione Service Worker
     */
    public function enqueueRegistrationScript(): void
    {
        if (is_admin()) {
            return;
        }

        $swUrl = home_url(self::SW_FILENAME);
        ?>
        <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo esc_js($swUrl); ?>')
                    .then(function(registration) {
                        console.log('Service Worker registered:', registration);
                    })
                    .catch(function(error) {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }
        </script>
        <?php
    }

    /**
     * Aggiunge link manifest
     */
    public function addManifestLink(): void
    {
        echo '<link rel="manifest" href="' . esc_url(home_url('fp-manifest.json')) . '">';
    }

    /**
     * Serve manifest.json
     */
    public function serveManifest(): void
    {
        if (!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] !== '/fp-manifest.json') {
            return;
        }

        header('Content-Type: application/json');
        echo wp_json_encode($this->getManifest());
        exit;
    }

    /**
     * Genera manifest PWA
     */
    private function getManifest(): array
    {
        $settings = $this->getSettings();

        return [
            'name' => $settings['app_name'],
            'short_name' => $settings['app_short_name'],
            'description' => get_bloginfo('description'),
            'start_url' => home_url('/'),
            'display' => 'standalone',
            'background_color' => $settings['background_color'],
            'theme_color' => $settings['theme_color'],
            'icons' => $this->getManifestIcons(),
        ];
    }

    /**
     * Ottiene icone per manifest
     */
    private function getManifestIcons(): array
    {
        $icons = [];
        
        // Site icon (favicon)
        $siteIcon = get_site_icon_url();
        if ($siteIcon) {
            $icons[] = [
                'src' => $siteIcon,
                'sizes' => '512x512',
                'type' => 'image/png',
            ];
        }

        return $icons;
    }

    /**
     * Rimuove Service Worker
     */
    public function uninstall(): bool
    {
        $swPath = ABSPATH . self::SW_FILENAME;
        
        if (file_exists($swPath)) {
            return @unlink($swPath);
        }

        return true;
    }

    /**
     * Status
     */
    public function status(): array
    {
        $swPath = ABSPATH . self::SW_FILENAME;

        return [
            'enabled' => !empty($this->getSettings()['enabled']),
            'sw_exists' => file_exists($swPath),
            'sw_url' => home_url(self::SW_FILENAME),
            'manifest_url' => home_url('fp-manifest.json'),
            'settings' => $this->getSettings(),
        ];
    }
}

