<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger as StaticLogger;

/**
 * HTTP/2 Server Push
 * 
 * Implementa HTTP/2 Server Push per asset critici
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Http2ServerPush
{
    private const OPTION_KEY = 'fp_ps_http2_push';
    private array $pushedAssets = [];
    private ?OptionsRepositoryInterface $optionsRepo = null;
    private ?LoggerInterface $logger = null;
    
    /**
     * Costruttore
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Repository opzionale per gestione opzioni
     * @param LoggerInterface|null $logger Logger opzionale per logging
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null, ?LoggerInterface $logger = null)
    {
        $this->optionsRepo = $optionsRepo;
        $this->logger = $logger;
    }
    
    /**
     * Helper per logging con fallback
     * 
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger !== null) {
            $this->logger->$level($message, $context);
        } else {
            StaticLogger::$level($message, $context);
        }
    }
    
    /**
     * Helper per ottenere opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $default Valore di default
     * @return mixed Valore opzione
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
    
    /**
     * Helper per salvare opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $value Valore opzione
     * @param bool $autoload Se autoload
     * @return bool True se salvato con successo
     */
    private function setOption(string $key, $value, bool $autoload = true): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value, $autoload);
        }
        return update_option($key, $value, $autoload);
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

        // Hook per aggiungere header Link - solo nel frontend
        add_action('template_redirect', [$this, 'sendPushHeaders'], 1);

        $this->log('debug', 'HTTP/2 Server Push registered');
    }

    /**
     * Ottiene le impostazioni (alias per compatibilità)
     */
    public function settings(): array
    {
        return $this->getSettings();
    }

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'push_css' => true,
            'push_js' => true,
            'push_fonts' => true,
            'push_images' => false,
            'max_resources' => 10,
            'critical_only' => true,
            'custom_assets' => [],
        ];

        $options = $this->getOption(self::OPTION_KEY, []);
        return wp_parse_args($options, $defaults);
    }

    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = wp_parse_args($settings, $current);

        $result = $this->setOption(self::OPTION_KEY, $updated);
        
        if ($result) {
            $this->log('info', 'HTTP/2 Server Push settings updated', $updated);
        }

        return $result;
    }

    /**
     * Verifica se HTTP/2 è supportato
     */
    public function isHttp2Supported(): bool
    {
        // Controlla se il server supporta HTTP/2
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            return strpos($_SERVER['SERVER_PROTOCOL'], 'HTTP/2') !== false;
        }

        return false;
    }

    /**
     * Invia header di push
     */
    public function sendPushHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        if (!$this->isHttp2Supported()) {
            return;
        }

        $settings = $this->getSettings();
        $assets = $this->getAssetsToPush();

        // Limita numero asset
        $assets = array_slice($assets, 0, $settings['max_resources']);

        foreach ($assets as $asset) {
            $this->pushAsset($asset);
        }

        $this->log('debug', 'HTTP/2 push headers sent', ['count' => count($assets)]);
    }

    /**
     * Ottiene gli asset da pushare
     */
    private function getAssetsToPush(): array
    {
        $settings = $this->getSettings();
        $assets = [];

        // CSS critici
        if ($settings['push_css']) {
            $assets = array_merge($assets, $this->getCriticalCss());
        }

        // JS critici
        if ($settings['push_js']) {
            $assets = array_merge($assets, $this->getCriticalJs());
        }

        // Font
        if ($settings['push_fonts']) {
            $assets = array_merge($assets, $this->getCriticalFonts());
        }

        // Asset custom
        if (!empty($settings['custom_assets'])) {
            $assets = array_merge($assets, $settings['custom_assets']);
        }

        // Applica filtro
        $assets = apply_filters('fp_ps_http2_push_assets', $assets);

        return array_unique($assets, SORT_REGULAR);
    }

    /**
     * Ottiene CSS critici da pushare
     */
    private function getCriticalCss(): array
    {
        $css = [];

        // Stylesheet principale tema
        $stylesheet = get_stylesheet_uri();
        if ($stylesheet) {
            $css[] = [
                'url' => $stylesheet,
                'as' => 'style',
            ];
        }

        // CSS critici registrati
        global $wp_styles;
        
        $criticalHandles = apply_filters('fp_ps_critical_css_handles', [
            'wp-block-library',
            'global-styles',
        ]);

        foreach ($criticalHandles as $handle) {
            if (isset($wp_styles->registered[$handle])) {
                $src = $wp_styles->registered[$handle]->src;
                
                // Solo asset locali
                if ($this->isLocalAsset($src)) {
                    $css[] = [
                        'url' => $src,
                        'as' => 'style',
                    ];
                }
            }
        }

        return $css;
    }

    /**
     * Ottiene JS critici da pushare
     */
    private function getCriticalJs(): array
    {
        $js = [];

        global $wp_scripts;

        $criticalHandles = apply_filters('fp_ps_critical_js_handles', [
            'jquery-core',
            'wp-polyfill',
        ]);

        foreach ($criticalHandles as $handle) {
            if (isset($wp_scripts->registered[$handle])) {
                $src = $wp_scripts->registered[$handle]->src;
                
                if ($this->isLocalAsset($src)) {
                    $js[] = [
                        'url' => $src,
                        'as' => 'script',
                    ];
                }
            }
        }

        return $js;
    }

    /**
     * Ottiene font critici da pushare
     */
    private function getCriticalFonts(): array
    {
        $fonts = [];

        // Cerca font nel tema
        $themeDir = get_template_directory();
        $themeUrl = get_template_directory_uri();

        // Pattern comuni per font
        $fontPaths = [
            '/assets/fonts/',
            '/fonts/',
            '/css/fonts/',
        ];

        foreach ($fontPaths as $path) {
            $fullPath = $themeDir . $path;
            
            if (!file_exists($fullPath)) {
                continue;
            }

            $files = glob($fullPath . '*.{woff2,woff}', GLOB_BRACE);
            
            foreach ($files as $file) {
                $url = str_replace($themeDir, $themeUrl, $file);
                $fonts[] = [
                    'url' => $url,
                    'as' => 'font',
                    'type' => $this->getFontMimeType($file),
                    'crossorigin' => 'anonymous',
                ];
            }
        }

        // Limita a 3 font più importanti
        return array_slice($fonts, 0, 3);
    }

    /**
     * Pusha un asset
     */
    private function pushAsset(array $asset): void
    {
        if (empty($asset['url']) || empty($asset['as'])) {
            return;
        }

        // Evita duplicati
        $key = $asset['url'];
        if (isset($this->pushedAssets[$key])) {
            return;
        }

        $this->pushedAssets[$key] = true;

        // Costruisci header Link
        $linkHeader = sprintf('<%s>; rel=preload; as=%s', $asset['url'], $asset['as']);

        // Aggiungi attributi opzionali
        if (!empty($asset['type'])) {
            $linkHeader .= sprintf('; type=%s', $asset['type']);
        }

        if (!empty($asset['crossorigin'])) {
            $linkHeader .= sprintf('; crossorigin=%s', $asset['crossorigin']);
        }

        // Invia header
        header("Link: {$linkHeader}", false);
    }

    /**
     * Verifica se un asset è locale
     */
    private function isLocalAsset(string $url): bool
    {
        $homeUrl = home_url();
        
        // URL relativo
        if (strpos($url, '//') === false) {
            return true;
        }

        // URL assoluto locale
        return strpos($url, $homeUrl) !== false;
    }

    /**
     * Ottiene mime type per font
     */
    private function getFontMimeType(string $file): string
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        $mimeTypes = [
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'eot' => 'application/vnd.ms-fontobject',
        ];

        return $mimeTypes[$ext] ?? 'font/woff2';
    }

    /**
     * Aggiunge asset custom per push
     */
    public function addCustomAsset(string $url, string $as, array $attributes = []): void
    {
        $settings = $this->getSettings();

        $asset = array_merge(
            ['url' => $url, 'as' => $as],
            $attributes
        );

        $settings['custom_assets'][] = $asset;

        $this->updateSettings($settings);
    }

    /**
     * Rimuove asset custom
     */
    public function removeCustomAsset(string $url): void
    {
        $settings = $this->getSettings();

        $settings['custom_assets'] = array_filter(
            $settings['custom_assets'],
            function ($asset) use ($url) {
                return $asset['url'] !== $url;
            }
        );

        // Reindex array
        $settings['custom_assets'] = array_values($settings['custom_assets']);

        $this->updateSettings($settings);
    }

    /**
     * Ottiene statistiche
     */
    public function getStats(): array
    {
        return [
            'enabled' => !empty($this->getSettings()['enabled']),
            'http2_supported' => $this->isHttp2Supported(),
            'pushed_assets' => count($this->pushedAssets),
            'assets_list' => array_keys($this->pushedAssets),
        ];
    }

    /**
     * Genera report
     */
    public function getReport(): array
    {
        $settings = $this->getSettings();
        $assets = $this->getAssetsToPush();

        return [
            'settings' => $settings,
            'http2_supported' => $this->isHttp2Supported(),
            'assets_to_push' => $assets,
            'assets_count' => count($assets),
            'pushed_count' => count($this->pushedAssets),
        ];
    }

    /**
     * Status
     */
    public function status(): array
    {
        return [
            'enabled' => !empty($this->getSettings()['enabled']),
            'supported' => $this->isHttp2Supported(),
            'settings' => $this->getSettings(),
            'stats' => $this->getStats(),
        ];
    }
}

