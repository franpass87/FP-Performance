<?php

namespace FP\PerfSuite\Services\Intelligence;

/**
 * Critical Assets Detector
 * 
 * Sistema intelligente che rileva automaticamente:
 * - CSS critici per il rendering above-the-fold
 * - Immagini critiche (hero, logo, LCP)
 * - JavaScript necessario per il rendering iniziale
 * - Font critici per il caricamento
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CriticalAssetsDetector
{
    /**
     * Pattern comuni per CSS critici
     */
    private const CRITICAL_CSS_PATTERNS = [
        'style.css', 'main.css', 'theme.css', 'global.css',
        'critical.css', 'above-fold.css', 'header.css',
        'elementor-frontend', 'wp-block-library',
    ];

    /**
     * Pattern per immagini critiche
     */
    private const CRITICAL_IMAGE_PATTERNS = [
        'logo', 'header', 'hero', 'banner', 'featured',
        'background', 'cover', 'masthead',
    ];

    /**
     * Dimensioni minime per considerare un'immagine critica
     */
    private const MIN_CRITICAL_IMAGE_SIZE = [
        'width' => 300,
        'height' => 200,
    ];

    /**
     * Rileva automaticamente asset critici
     */
    public function detectCriticalAssets(array $options = []): array
    {
        $detected = [
            'css' => [],
            'js' => [],
            'images' => [],
            'fonts' => [],
            'summary' => [],
        ];

        // Analizza homepage
        $homeUrl = get_home_url();
        $homeAssets = $this->analyzePageAssets($homeUrl);

        // CSS critici
        $detected['css'] = $this->detectCriticalCss($homeAssets);

        // Immagini critiche
        $detected['images'] = $this->detectCriticalImages($homeAssets);

        // JavaScript critici
        $detected['js'] = $this->detectCriticalJs($homeAssets);

        // Font critici
        $detected['fonts'] = $this->detectCriticalFonts($homeAssets);

        // Genera sommario
        $detected['summary'] = $this->generateSummary($detected);

        return $detected;
    }

    /**
     * Analizza asset di una pagina
     */
    private function analyzePageAssets(string $url): array
    {
        $assets = [
            'css' => [],
            'js' => [],
            'images' => [],
            'fonts' => [],
            'html' => '',
        ];

        // Scarica l'HTML della pagina
        $response = wp_remote_get($url, [
            'timeout' => 10,
            'sslverify' => false,
            'user-agent' => 'FP-Performance-Suite-Asset-Detector/1.0',
        ]);

        if (is_wp_error($response)) {
            return $assets;
        }

        $html = wp_remote_retrieve_body($response);
        $assets['html'] = $html;

        // Estrai CSS
        $assets['css'] = $this->extractCssFromHtml($html);

        // Estrai immagini
        $assets['images'] = $this->extractImagesFromHtml($html);

        // Estrai JS
        $assets['js'] = $this->extractJsFromHtml($html);

        // Estrai font
        $assets['fonts'] = $this->extractFontsFromHtml($html);

        return $assets;
    }

    /**
     * Estrai CSS dall'HTML
     */
    private function extractCssFromHtml(string $html): array
    {
        $css = [];

        // Cerca link CSS
        if (preg_match_all('/<link[^>]*rel=["\']stylesheet["\'][^>]*href=["\'](.*?)["\']/i', $html, $matches)) {
            foreach ($matches[1] as $index => $href) {
                $css[] = [
                    'url' => $this->normalizeUrl($href),
                    'position' => $index,
                    'inline' => false,
                ];
            }
        }

        // Cerca style inline
        if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html, $matches)) {
            foreach ($matches[0] as $index => $style) {
                $css[] = [
                    'url' => '',
                    'content' => $matches[1][$index],
                    'position' => 1000 + $index,
                    'inline' => true,
                    'size' => strlen($matches[1][$index]),
                ];
            }
        }

        return $css;
    }

    /**
     * Estrai immagini dall'HTML
     */
    private function extractImagesFromHtml(string $html): array
    {
        $images = [];

        // Cerca img tag
        if (preg_match_all('/<img[^>]*src=["\'](.*?)["\']/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $index => $match) {
                $fullTag = $match[0];
                $src = $match[1];

                // Estrai attributi
                $width = 0;
                $height = 0;
                $alt = '';
                $class = '';

                if (preg_match('/width=["\'](\\d+)["\']/i', $fullTag, $w)) {
                    $width = (int) $w[1];
                }
                if (preg_match('/height=["\'](\\d+)["\']/i', $fullTag, $h)) {
                    $height = (int) $h[1];
                }
                if (preg_match('/alt=["\'](.*?)["\']/i', $fullTag, $a)) {
                    $alt = $a[1];
                }
                if (preg_match('/class=["\'](.*?)["\']/i', $fullTag, $c)) {
                    $class = $c[1];
                }

                $images[] = [
                    'url' => $this->normalizeUrl($src),
                    'position' => $index,
                    'width' => $width,
                    'height' => $height,
                    'alt' => $alt,
                    'class' => $class,
                    'is_above_fold' => $index < 5, // Prime 5 immagini probabilmente above-fold
                ];
            }
        }

        // Cerca background-image in style inline
        if (preg_match_all('/background-image:\s*url\(["\']?(.*?)["\']?\)/i', $html, $matches)) {
            foreach ($matches[1] as $index => $url) {
                $images[] = [
                    'url' => $this->normalizeUrl($url),
                    'position' => 10000 + $index,
                    'type' => 'background',
                    'is_above_fold' => $index < 2,
                ];
            }
        }

        return $images;
    }

    /**
     * Estrai JavaScript dall'HTML
     */
    private function extractJsFromHtml(string $html): array
    {
        $scripts = [];

        // Cerca script tag
        if (preg_match_all('/<script[^>]*src=["\'](.*?)["\']/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $index => $match) {
                $scripts[] = [
                    'url' => $this->normalizeUrl($match[1]),
                    'position' => $index,
                    'inline' => false,
                ];
            }
        }

        return $scripts;
    }

    /**
     * Estrai font dall'HTML
     */
    private function extractFontsFromHtml(string $html): array
    {
        $fonts = [];

        // Cerca preload font
        if (preg_match_all('/<link[^>]*rel=["\']preload["\'][^>]*as=["\']font["\'][^>]*href=["\'](.*?)["\']/i', $html, $matches)) {
            foreach ($matches[1] as $href) {
                $fonts[] = [
                    'url' => $this->normalizeUrl($href),
                    'preloaded' => true,
                ];
            }
        }

        // Cerca link font (Google Fonts, ecc.)
        if (preg_match_all('/<link[^>]*href=["\'](https?:\/\/fonts\\.googleapis\\.com\/.*?)["\']/i', $html, $matches)) {
            foreach ($matches[1] as $href) {
                $fonts[] = [
                    'url' => $href,
                    'provider' => 'google-fonts',
                    'preloaded' => false,
                ];
            }
        }

        // Cerca @font-face nel CSS inline
        if (preg_match_all('/@font-face[^}]*url\(["\']?(.*?)["\']?\)/i', $html, $matches)) {
            foreach ($matches[1] as $url) {
                $fonts[] = [
                    'url' => $this->normalizeUrl($url),
                    'custom' => true,
                ];
            }
        }

        return $fonts;
    }

    /**
     * Rileva CSS critici
     */
    private function detectCriticalCss(array $assets): array
    {
        $critical = [];

        foreach ($assets['css'] as $css) {
            if ($css['inline']) {
                continue; // Skip inline CSS
            }

            $url = $css['url'];
            $isCritical = false;
            $reason = '';
            $confidence = 0.5;

            // Controlla pattern comuni
            foreach (self::CRITICAL_CSS_PATTERNS as $pattern) {
                if (stripos($url, $pattern) !== false) {
                    $isCritical = true;
                    $reason = sprintf(__('Matches critical CSS pattern: %s', 'fp-performance-suite'), $pattern);
                    $confidence = 0.9;
                    break;
                }
            }

            // Primi CSS caricati sono probabilmente critici
            if ($css['position'] < 3) {
                $isCritical = true;
                $reason = __('Loaded early in document (top 3)', 'fp-performance-suite');
                $confidence = max($confidence, 0.85);
            }

            // CSS del tema
            $themeUrl = get_stylesheet_directory_uri();
            if (strpos($url, $themeUrl) !== false) {
                $isCritical = true;
                $reason = __('Theme stylesheet', 'fp-performance-suite');
                $confidence = max($confidence, 0.95);
            }

            if ($isCritical) {
                $critical[] = [
                    'url' => $url,
                    'reason' => $reason,
                    'confidence' => $confidence,
                    'position' => $css['position'],
                ];
            }
        }

        return $critical;
    }

    /**
     * Rileva immagini critiche
     */
    private function detectCriticalImages(array $assets): array
    {
        $critical = [];

        foreach ($assets['images'] as $image) {
            $url = $image['url'];
            $isCritical = false;
            $reason = '';
            $confidence = 0.5;

            // Controlla se è above-the-fold
            if ($image['is_above_fold']) {
                $isCritical = true;
                $reason = __('Appears above the fold', 'fp-performance-suite');
                $confidence = 0.8;
            }

            // Controlla pattern nel nome/path
            foreach (self::CRITICAL_IMAGE_PATTERNS as $pattern) {
                if (stripos($url, $pattern) !== false) {
                    $isCritical = true;
                    $reason = sprintf(__('Matches critical image pattern: %s', 'fp-performance-suite'), $pattern);
                    $confidence = max($confidence, 0.85);
                    break;
                }
            }

            // Controlla dimensioni
            if (isset($image['width']) && isset($image['height'])) {
                if ($image['width'] >= self::MIN_CRITICAL_IMAGE_SIZE['width'] &&
                    $image['height'] >= self::MIN_CRITICAL_IMAGE_SIZE['height']) {
                    $isCritical = true;
                    $reason = sprintf(__('Large image (%dx%d) - likely LCP candidate', 'fp-performance-suite'), $image['width'], $image['height']);
                    $confidence = max($confidence, 0.9);
                }
            }

            // Logo
            if (isset($image['alt']) && stripos($image['alt'], 'logo') !== false) {
                $isCritical = true;
                $reason = __('Logo image', 'fp-performance-suite');
                $confidence = 0.95;
            }

            // Background images sono spesso critiche
            if (isset($image['type']) && $image['type'] === 'background') {
                $isCritical = true;
                $reason = __('Background image - likely hero section', 'fp-performance-suite');
                $confidence = 0.85;
            }

            if ($isCritical) {
                $critical[] = [
                    'url' => $url,
                    'reason' => $reason,
                    'confidence' => $confidence,
                    'position' => $image['position'],
                    'dimensions' => isset($image['width']) ? sprintf('%dx%d', $image['width'], $image['height']) : 'unknown',
                ];
            }
        }

        // Limita a massimo 5 immagini critiche
        usort($critical, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });

        return array_slice($critical, 0, 5);
    }

    /**
     * Rileva JavaScript critici
     */
    private function detectCriticalJs(array $assets): array
    {
        $critical = [];

        foreach ($assets['js'] as $js) {
            if ($js['inline']) {
                continue;
            }

            $url = $js['url'];
            $isCritical = false;
            $reason = '';
            $confidence = 0.5;

            // Pattern critici comuni
            $criticalPatterns = [
                'jquery' => 0.9,
                'polyfill' => 0.85,
                'modernizr' => 0.8,
                'analytics' => 0.3, // Non critico per rendering
            ];

            foreach ($criticalPatterns as $pattern => $conf) {
                if (stripos($url, $pattern) !== false) {
                    $isCritical = $conf >= 0.7;
                    $reason = sprintf(__('Matches JS pattern: %s', 'fp-performance-suite'), $pattern);
                    $confidence = $conf;
                    break;
                }
            }

            // Primi JS caricati
            if ($js['position'] < 2 && stripos($url, 'analytics') === false) {
                $isCritical = true;
                $reason = __('Loaded very early - likely critical', 'fp-performance-suite');
                $confidence = max($confidence, 0.8);
            }

            if ($isCritical) {
                $critical[] = [
                    'url' => $url,
                    'reason' => $reason,
                    'confidence' => $confidence,
                    'position' => $js['position'],
                ];
            }
        }

        return $critical;
    }

    /**
     * Rileva font critici
     */
    private function detectCriticalFonts(array $assets): array
    {
        $critical = [];

        foreach ($assets['fonts'] as $font) {
            $url = $font['url'];
            $reason = '';
            $confidence = 0.7;

            // Font già precaricati
            if (isset($font['preloaded']) && $font['preloaded']) {
                $reason = __('Already preloaded in theme', 'fp-performance-suite');
                $confidence = 0.95;
            }

            // Google Fonts
            if (isset($font['provider']) && $font['provider'] === 'google-fonts') {
                $reason = __('Google Fonts - critical for text rendering', 'fp-performance-suite');
                $confidence = 0.85;
            }

            // Custom fonts
            if (isset($font['custom']) && $font['custom']) {
                $reason = __('Custom font - theme typography', 'fp-performance-suite');
                $confidence = 0.8;
            }

            // Solo font WOFF2 (più moderni e performanti)
            if (strpos($url, '.woff2') !== false) {
                $confidence = min(1.0, $confidence + 0.1);
            }

            $critical[] = [
                'url' => $url,
                'reason' => $reason,
                'confidence' => $confidence,
            ];
        }

        return $critical;
    }

    /**
     * Genera sommario
     */
    private function generateSummary(array $detected): array
    {
        $summary = [
            'total_assets' => 0,
            'by_type' => [],
            'recommendations' => [],
        ];

        // Conta asset per tipo
        foreach (['css', 'js', 'images', 'fonts'] as $type) {
            $count = count($detected[$type]);
            $summary['by_type'][$type] = $count;
            $summary['total_assets'] += $count;
        }

        // Genera raccomandazioni
        if ($summary['by_type']['css'] > 3) {
            $summary['recommendations'][] = [
                'type' => 'warning',
                'message' => __('Troppe CSS critiche rilevate. Considera di combinare alcuni file.', 'fp-performance-suite'),
            ];
        }

        if ($summary['by_type']['images'] > 5) {
            $summary['recommendations'][] = [
                'type' => 'warning',
                'message' => __('Troppe immagini critiche. Precarica solo il logo e l\'immagine hero principale.', 'fp-performance-suite'),
            ];
        }

        if ($summary['total_assets'] < 3) {
            $summary['recommendations'][] = [
                'type' => 'info',
                'message' => __('Ottimo! Pochi asset critici rilevati = caricamento veloce.', 'fp-performance-suite'),
            ];
        }

        if (empty($detected['fonts']) && !empty($detected['css'])) {
            $summary['recommendations'][] = [
                'type' => 'info',
                'message' => __('Nessun font critico rilevato. Se usi font personalizzati, considera di precaricarne uno.', 'fp-performance-suite'),
            ];
        }

        return $summary;
    }

    /**
     * Normalizza URL
     */
    private function normalizeUrl(string $url): string
    {
        // Rimuovi query string per semplicità
        $url = strtok($url, '?');

        // Se è relativo, converte in assoluto
        if (strpos($url, 'http') !== 0) {
            $siteUrl = get_site_url();
            if (strpos($url, '/') === 0) {
                $url = $siteUrl . $url;
            } else {
                $url = $siteUrl . '/' . $url;
            }
        }

        return $url;
    }

    /**
     * Applica automaticamente i suggerimenti
     * 
     * @param bool $dryRun Se true, non salva le modifiche
     * @param \FP\PerfSuite\Services\Assets\Optimizer|null $optimizer Optimizer instance per salvare correttamente
     * @return array Risultati dell'applicazione
     */
    public function autoApplyCriticalAssets(bool $dryRun = true, $optimizer = null): array
    {
        $results = [
            'applied' => 0,
            'skipped' => 0,
            'assets' => [],
        ];

        $detected = $this->detectCriticalAssets();
        $assetsToPreload = [];

        // Raccogli tutti gli asset con confidence >= 0.8
        foreach (['css', 'images', 'fonts'] as $type) {
            foreach ($detected[$type] as $asset) {
                if ($asset['confidence'] >= 0.8) {
                    $assetsToPreload[] = $asset['url'];
                    $results['applied']++;
                    $results['assets'][] = [
                        'type' => $type,
                        'url' => $asset['url'],
                        'reason' => $asset['reason'],
                        'confidence' => $asset['confidence'],
                    ];
                } else {
                    $results['skipped']++;
                }
            }
        }

        // Applica se non dry run
        if (!$dryRun && !empty($assetsToPreload)) {
            if ($optimizer) {
                // Usa l'optimizer per salvare correttamente
                $optimizer->update([
                    'critical_assets_list' => $assetsToPreload,
                    'preload_critical_assets' => true,
                ]);
            } else {
                // Fallback al metodo precedente (deprecato)
                $settings = get_option('fp_ps_assets', []);
                $settings['critical_assets_list'] = $assetsToPreload;
                $settings['preload_critical_assets'] = true;
                update_option('fp_ps_assets', $settings);
            }
        }

        return $results;
    }
}
