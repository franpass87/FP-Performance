<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

/**
 * Auto Font Optimizer
 * 
 * Sistema di auto-rilevamento che identifica automaticamente i font problematici
 * e applica le ottimizzazioni senza configurazione manuale.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class AutoFontOptimizer
{
    private const OPTION = 'fp_ps_auto_font_optimization';

    /**
     * Register hooks
     */
    public function register(): void
    {
        if (!is_admin() && $this->isEnabled()) {
            // Auto-rilevamento e ottimizzazione font
            add_action('wp_head', [$this, 'autoDetectAndOptimizeFonts'], 1);
            
            // Iniezione automatica font-display CSS
            add_action('wp_head', [$this, 'injectAutoFontDisplayCSS'], 5);
            
            // Preload automatico font critici
            add_action('wp_head', [$this, 'autoPreloadCriticalFonts'], 2);
            
            // Preconnect automatico provider
            add_action('wp_head', [$this, 'autoAddFontProviderPreconnect'], 3);
            
            // Ottimizzazione automatica Google Fonts
            add_filter('style_loader_tag', [$this, 'autoOptimizeGoogleFonts'], 10, 4);
            
            // Ottimizzazione automatica font locali
            add_filter('style_loader_tag', [$this, 'autoOptimizeLocalFonts'], 10, 4);
            
            Logger::debug('AutoFontOptimizer registered');
        }
    }

    /**
     * Auto-rilevamento e ottimizzazione font
     */
    public function autoDetectAndOptimizeFonts(): void
    {
        $detectedFonts = $this->detectProblematicFonts();
        
        if (empty($detectedFonts)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Auto Font Detection & Optimization -->\n";
        
        foreach ($detectedFonts as $font) {
            $this->applyFontOptimization($font);
        }
        
        echo "<!-- End Auto Font Detection & Optimization -->\n";
        
        Logger::debug('Auto-detected and optimized fonts', [
            'count' => count($detectedFonts),
            'fonts' => array_column($detectedFonts, 'name')
        ]);
    }

    /**
     * Iniezione automatica font-display CSS
     */
    public function injectAutoFontDisplayCSS(): void
    {
        $detectedFonts = $this->detectProblematicFonts();
        
        if (empty($detectedFonts)) {
            return;
        }

        $css = $this->generateAutoFontDisplayCSS($detectedFonts);
        
        if (!empty($css)) {
            echo "\n<!-- FP Performance Suite - Auto Font Display Fix -->\n";
            echo '<style id="fp-auto-font-display-fix">' . $css . '</style>' . "\n";
            echo "<!-- End Auto Font Display Fix -->\n";
            
            Logger::debug('Injected auto font-display CSS');
        }
    }

    /**
     * Preload automatico font critici
     */
    public function autoPreloadCriticalFonts(): void
    {
        $criticalFonts = $this->detectCriticalFonts();
        
        if (empty($criticalFonts)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Auto Critical Font Preload -->\n";
        
        foreach ($criticalFonts as $font) {
            $type = $font['type'] ?? 'font/woff2';
            $crossorigin = !empty($font['crossorigin']) ? ' crossorigin' : '';
            $fetchpriority = !empty($font['priority']) ? ' fetchpriority="' . esc_attr($font['priority']) . '"' : '';

            printf(
                '<link rel="preload" href="%s" as="font" type="%s"%s%s />' . "\n",
                esc_url($font['url']),
                esc_attr($type),
                $crossorigin,
                $fetchpriority
            );
        }
        
        echo "<!-- End Auto Critical Font Preload -->\n";
        
        Logger::debug('Auto-preloaded critical fonts', ['count' => count($criticalFonts)]);
    }

    /**
     * Preconnect automatico provider
     */
    public function autoAddFontProviderPreconnect(): void
    {
        $providers = $this->detectFontProviders();
        
        if (empty($providers)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Auto Font Provider Preconnect -->\n";
        
        foreach ($providers as $provider) {
            $crossorigin = !empty($provider['crossorigin']) ? ' crossorigin' : '';
            
            printf(
                '<link rel="preconnect" href="%s"%s />' . "\n",
                esc_url($provider['url']),
                $crossorigin
            );
        }
        
        echo "<!-- End Auto Font Provider Preconnect -->\n";
        
        Logger::debug('Auto-added font provider preconnect', ['count' => count($providers)]);
    }

    /**
     * Ottimizzazione automatica Google Fonts
     */
    public function autoOptimizeGoogleFonts(string $html, string $handle, string $href, $media): string
    {
        // Controlla se è un Google Font
        if (strpos($href, 'fonts.googleapis.com') === false) {
            return $html;
        }

        // Aggiunge display=swap se non presente
        if (strpos($href, 'display=') === false) {
            $separator = strpos($href, '?') !== false ? '&' : '?';
            $href = $href . $separator . 'display=swap';
        }

        // Aggiunge text parameter per ridurre dimensioni
        if (strpos($href, 'text=') === false) {
            $separator = strpos($href, '?') !== false ? '&' : '?';
            $href = $href . $separator . 'text=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        }

        // Rebuild del link ottimizzato
        $html = sprintf(
            '<link rel="stylesheet" id="%s-css" href="%s" media="%s" />',
            esc_attr($handle),
            esc_url($href),
            esc_attr($media)
        );

        // Logger::debug('Auto-optimized Google Fonts', ['handle' => $handle, 'href' => $href]);

        return $html;
    }

    /**
     * Ottimizzazione automatica font locali
     */
    public function autoOptimizeLocalFonts(string $html, string $handle, string $href, $media): string
    {
        // Controlla se è un font locale che necessita ottimizzazione
        if (!$this->isLocalFontNeedingOptimization($handle, $href)) {
            return $html;
        }

        // Aggiunge attributi per l'ottimizzazione
        $html = str_replace('<link ', '<link data-fp-auto-font="true" ', $html);
        
        // Logger::debug('Auto-marked local font for optimization', [
        //     'handle' => $handle,
        //     'href' => $href
        // ]);

        return $html;
    }

    /**
     * Rileva automaticamente i font problematici
     */
    private function detectProblematicFonts(): array
    {
        $problematicFonts = [];

        // Analizza gli stili caricati
        global $wp_styles;
        if (!empty($wp_styles)) {
            foreach ($wp_styles->done as $handle) {
                $src = $wp_styles->registered[$handle]->src ?? '';
                
                if ($this->isProblematicFont($handle, $src)) {
                    $problematicFonts[] = [
                        'name' => $this->extractFontName($handle, $src),
                        'url' => $src,
                        'type' => $this->getFontTypeFromUrl($src),
                        'handle' => $handle,
                        'priority' => $this->getFontPriority($handle, $src),
                        'crossorigin' => $this->needsCrossorigin($src)
                    ];
                }
            }
        }

        // Rileva font nel tema
        $themeFonts = $this->detectThemeFonts();
        $problematicFonts = array_merge($problematicFonts, $themeFonts);

        // Rileva font inline nel CSS
        $inlineFonts = $this->detectInlineFonts();
        $problematicFonts = array_merge($problematicFonts, $inlineFonts);

        return array_unique($problematicFonts, SORT_REGULAR);
    }

    /**
     * Rileva font critici per preload
     */
    private function detectCriticalFonts(): array
    {
        $criticalFonts = [];
        $detectedFonts = $this->detectProblematicFonts();

        foreach ($detectedFonts as $font) {
            // Solo font con priorità alta o media
            if (in_array($font['priority'], ['high', 'medium'], true)) {
                $criticalFonts[] = $font;
            }
        }

        return $criticalFonts;
    }

    /**
     * Rileva provider di font
     */
    private function detectFontProviders(): array
    {
        $providers = [];
        $detectedFonts = $this->detectProblematicFonts();

        foreach ($detectedFonts as $font) {
            $domain = parse_url($font['url'], PHP_URL_HOST);
            
            if ($domain && !in_array($domain, array_column($providers, 'domain'), true)) {
                $providers[] = [
                    'url' => 'https://' . $domain,
                    'domain' => $domain,
                    'crossorigin' => $this->needsCrossorigin($font['url'])
                ];
            }
        }

        return $providers;
    }

    /**
     * Controlla se un font è problematico
     */
    private function isProblematicFont(string $handle, string $url): bool
    {
        // Handle problematici comuni
        $problematicHandles = [
            'fontawesome', 'fa', 'gillsans', 'gill-sans', 'theme-font', 
            'main-font', 'primary-font', 'body-font', 'heading-font'
        ];

        if (in_array($handle, $problematicHandles, true)) {
            return true;
        }

        // Pattern URL problematici
        $problematicPatterns = [
            'fontawesome', 'gillsans', 'gill-sans', 'useanyfont',
            'fa-brands', 'fa-solid', 'webfont', 'font-awesome'
        ];

        foreach ($problematicPatterns as $pattern) {
            if (strpos(strtolower($url), $pattern) !== false) {
                return true;
            }
        }

        // Font di terze parti noti
        $thirdPartyProviders = [
            'fonts.googleapis.com', 'fonts.gstatic.com', 'use.fontawesome.com',
            'kit.fontawesome.com', 'assets.brevo.com', 'cdnjs.cloudflare.com'
        ];

        foreach ($thirdPartyProviders as $provider) {
            if (strpos($url, $provider) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Controlla se è un font locale che necessita ottimizzazione
     */
    private function isLocalFontNeedingOptimization(string $handle, string $url): bool
    {
        // Font locali del tema
        if (strpos($url, get_stylesheet_directory_uri()) !== false) {
            return true;
        }

        // Font con estensioni comuni
        $fontExtensions = ['woff2', 'woff', 'ttf', 'otf', 'eot'];
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        
        return in_array($extension, $fontExtensions, true);
    }

    /**
     * Rileva font nel tema
     */
    private function detectThemeFonts(): array
    {
        $themeFonts = [];
        $themeDir = get_stylesheet_directory();
        $themeUrl = get_stylesheet_directory_uri();

        // Cerca nelle directory comuni dei font
        $fontDirs = ['/fonts/', '/assets/fonts/', '/css/fonts/', '/font/'];
        
        foreach ($fontDirs as $dir) {
            $path = $themeDir . $dir;
            if (is_dir($path)) {
                $files = glob($path . '*.{woff2,woff,ttf,otf}', GLOB_BRACE);
                if (!empty($files)) {
                    foreach (array_slice($files, 0, 5) as $file) { // Max 5 font
                        $basename = basename($file);
                        $themeFonts[] = [
                            'name' => $this->extractFontNameFromFile($basename),
                            'url' => $themeUrl . $dir . $basename,
                            'type' => $this->getFontTypeFromFile($file),
                            'handle' => 'theme-font-' . sanitize_title($basename),
                            'priority' => 'medium',
                            'crossorigin' => false
                        ];
                    }
                    break; // Solo prima directory trovata
                }
            }
        }

        return $themeFonts;
    }

    /**
     * Rileva font inline nel CSS
     */
    private function detectInlineFonts(): array
    {
        $inlineFonts = [];
        
        // Cerca @font-face nel CSS inline
        $output = ob_get_contents();
        if (preg_match_all('/@font-face\s*\{[^}]*url\(["\']?([^"\']+)["\']?\)[^}]*\}/i', $output, $matches)) {
            foreach ($matches[1] as $index => $fontUrl) {
                if ($this->isValidFontUrl($fontUrl)) {
                    $inlineFonts[] = [
                        'name' => 'Inline Font ' . ($index + 1),
                        'url' => $fontUrl,
                        'type' => $this->getFontTypeFromUrl($fontUrl),
                        'handle' => 'inline-font-' . $index,
                        'priority' => 'low',
                        'crossorigin' => $this->needsCrossorigin($fontUrl)
                    ];
                }
            }
        }

        return $inlineFonts;
    }

    /**
     * Estrae il nome del font
     */
    private function extractFontName(string $handle, string $url): string
    {
        // Prova a estrarre dal handle
        if (strpos($handle, 'font') !== false) {
            return ucwords(str_replace(['-', '_'], ' ', $handle));
        }

        // Prova a estrarre dall'URL
        $filename = basename(parse_url($url, PHP_URL_PATH));
        $name = pathinfo($filename, PATHINFO_FILENAME);
        
        return ucwords(str_replace(['-', '_'], ' ', $name));
    }

    /**
     * Estrae il nome del font dal file
     */
    private function extractFontNameFromFile(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        return ucwords(str_replace(['-', '_'], ' ', $name));
    }

    /**
     * Ottiene il tipo MIME del font dall'URL
     */
    private function getFontTypeFromUrl(string $url): string
    {
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        return $this->getFontTypeFromExtension($ext);
    }

    /**
     * Ottiene il tipo MIME del font dal file
     */
    private function getFontTypeFromFile(string $file): string
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return $this->getFontTypeFromExtension($ext);
    }

    /**
     * Ottiene il tipo MIME dall'estensione
     */
    private function getFontTypeFromExtension(string $ext): string
    {
        $types = [
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'eot' => 'application/vnd.ms-fontobject',
        ];

        return $types[$ext] ?? 'font/woff2';
    }

    /**
     * Ottiene la priorità del font
     */
    private function getFontPriority(string $handle, string $url): string
    {
        // Handle critici
        $criticalHandles = ['theme-font', 'main-font', 'primary-font', 'body-font'];
        if (in_array($handle, $criticalHandles, true)) {
            return 'high';
        }

        // Font di terze parti
        if (strpos($url, 'fonts.googleapis.com') !== false || 
            strpos($url, 'fonts.gstatic.com') !== false) {
            return 'high';
        }

        // Font locali del tema
        if (strpos($url, get_stylesheet_directory_uri()) !== false) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Controlla se necessita crossorigin
     */
    private function needsCrossorigin(string $url): bool
    {
        $crossoriginDomains = [
            'fonts.googleapis.com', 'fonts.gstatic.com', 'use.fontawesome.com',
            'kit.fontawesome.com', 'assets.brevo.com', 'cdnjs.cloudflare.com'
        ];

        foreach ($crossoriginDomains as $domain) {
            if (strpos($url, $domain) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Applica ottimizzazione al font
     */
    private function applyFontOptimization(array $font): void
    {
        // Log del font rilevato
        Logger::debug('Auto-detected problematic font', [
            'name' => $font['name'],
            'url' => $font['url'],
            'priority' => $font['priority']
        ]);
    }

    /**
     * Genera CSS font-display automatico
     */
    private function generateAutoFontDisplayCSS(array $fonts): string
    {
        $css = [];

        // CSS specifico per ogni font rilevato
        foreach ($fonts as $font) {
            $css[] = sprintf(
                '@font-face { font-family: "%s"; font-display: swap !important; }',
                $font['name']
            );
        }

        // Fallback generico
        $css[] = '@font-face { font-display: swap !important; }';

        // CSS per prevenire FOIT
        $css[] = '/* Auto Font Display Fix - Prevent FOIT */';
        $css[] = 'body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }';

        return implode("\n", $css);
    }

    /**
     * Valida URL del font
     */
    private function isValidFontUrl(string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        // Controlla se è un URL valido o un percorso
        if (!filter_var($url, FILTER_VALIDATE_URL) && strpos($url, '/') !== 0) {
            return false;
        }

        // Controlla estensione
        $validExts = ['woff2', 'woff', 'ttf', 'otf', 'eot'];
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        
        return in_array($ext, $validExts, true);
    }

    /**
     * Controlla se l'ottimizzazione automatica è abilitata
     */
    public function isEnabled(): bool
    {
        $settings = $this->getSettings();
        return !empty($settings['enabled']);
    }

    /**
     * Ottiene tutte le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => true, // Abilitato di default
            'auto_detect_fonts' => true,
            'auto_preload_critical' => true,
            'auto_inject_font_display' => true,
            'auto_preconnect_providers' => true,
            'auto_optimize_google_fonts' => true,
            'auto_optimize_local_fonts' => true,
        ];

        $settings = get_option(self::OPTION, []);
        return is_array($settings) ? array_merge($defaults, $settings) : $defaults;
    }

    /**
     * Ottiene lo stato per la visualizzazione admin
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        $detectedFonts = $this->detectProblematicFonts();
        $criticalFonts = $this->detectCriticalFonts();
        $providers = $this->detectFontProviders();
        
        return [
            'enabled' => $this->isEnabled(),
            'auto_detection_active' => !empty($settings['auto_detect_fonts']),
            'auto_preload_active' => !empty($settings['auto_preload_critical']),
            'auto_font_display_active' => !empty($settings['auto_inject_font_display']),
            'auto_preconnect_active' => !empty($settings['auto_preconnect_providers']),
            'detected_fonts_count' => count($detectedFonts),
            'critical_fonts_count' => count($criticalFonts),
            'providers_count' => count($providers),
        ];
    }
}
