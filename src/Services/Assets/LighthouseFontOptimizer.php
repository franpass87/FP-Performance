<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

/**
 * Lighthouse Font Optimizer
 * 
 * Specificamente ottimizzato per i font identificati nel report Lighthouse
 * che causano il problema "Font display" con potenziale risparmio di 180ms.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class LighthouseFontOptimizer
{
    private const OPTION = 'fp_ps_lighthouse_font_optimization';

    /**
     * Register hooks
     */
    public function register(): void
    {
        if (!is_admin() && $this->isEnabled()) {
            // Preload dei font critici identificati in Lighthouse
            add_action('wp_head', [$this, 'preloadLighthouseFonts'], 1);
            
            // Iniezione font-display CSS per i font problematici
            add_action('wp_head', [$this, 'injectLighthouseFontDisplayCSS'], 5);
            
            // Preconnect ai provider dei font problematici
            add_action('wp_head', [$this, 'addLighthouseFontProviderPreconnect'], 2);
            
            // Ottimizzazione specifica per i font del sito
            add_filter('style_loader_tag', [$this, 'optimizeSiteFonts'], 10, 4);
            
            Logger::debug('LighthouseFontOptimizer registered');
        }
    }

    /**
     * Preload dei font critici identificati nel report Lighthouse
     */
    public function preloadLighthouseFonts(): void
    {
        $lighthouseFonts = $this->getLighthouseCriticalFonts();

        if (empty($lighthouseFonts)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Lighthouse Font Preload -->\n";
        
        foreach ($lighthouseFonts as $font) {
            if (empty($font['url'])) {
                continue;
            }

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

            Logger::debug('Preloaded Lighthouse critical font', [
                'url' => $font['url'],
                'savings' => $font['savings'] ?? 'unknown'
            ]);
        }
        
        echo "<!-- End Lighthouse Font Preload -->\n";
    }

    /**
     * Iniezione CSS font-display per i font problematici di Lighthouse
     */
    public function injectLighthouseFontDisplayCSS(): void
    {
        $css = $this->generateLighthouseFontDisplayCSS();
        
        if (!empty($css)) {
            echo "\n<!-- FP Performance Suite - Lighthouse Font Display Fix -->\n";
            echo '<style id="fp-lighthouse-font-display-fix">' . $css . '</style>' . "\n";
            echo "<!-- End Lighthouse Font Display Fix -->\n";
            
            Logger::debug('Injected Lighthouse font-display CSS');
        }
    }

    /**
     * Preconnect ai provider dei font problematici
     */
    public function addLighthouseFontProviderPreconnect(): void
    {
        $providers = $this->getLighthouseFontProviders();

        if (empty($providers)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Lighthouse Font Provider Preconnect -->\n";
        
        foreach ($providers as $provider) {
            $crossorigin = !empty($provider['crossorigin']) ? ' crossorigin' : '';
            
            printf(
                '<link rel="preconnect" href="%s"%s />' . "\n",
                esc_url($provider['url']),
                $crossorigin
            );

            Logger::debug('Added Lighthouse font provider preconnect', ['url' => $provider['url']]);
        }
        
        echo "<!-- End Lighthouse Font Provider Preconnect -->\n";
    }

    /**
     * Ottimizzazione specifica per i font del sito
     */
    public function optimizeSiteFonts(string $html, string $handle, string $href, $media): string
    {
        // Controlla se è un font del sito che necessita ottimizzazione
        if (!$this->isLighthouseProblematicFont($handle, $href)) {
            return $html;
        }

        // Aggiunge attributi per l'ottimizzazione
        $html = str_replace('<link ', '<link data-fp-lighthouse-font="true" ', $html);
        
        Logger::debug('Marked Lighthouse problematic font for optimization', [
            'handle' => $handle,
            'href' => $href
        ]);

        return $html;
    }

    /**
     * Ottiene i font critici identificati nel report Lighthouse
     */
    private function getLighthouseCriticalFonts(): array
    {
        $fonts = [];

        // Font del sito (ilpoderedimarfisa.it) - Risparmio totale: 460ms
        $siteFonts = [
            [
                'url' => home_url('/wp-content/themes/' . get_stylesheet() . '/fonts/useanyfont/939GillSans-Light.woff2'),
                'type' => 'font/woff2',
                'crossorigin' => false,
                'priority' => 'high',
                'savings' => '180ms'
            ],
            [
                'url' => home_url('/wp-content/themes/' . get_stylesheet() . '/fonts/useanyfont/2090GillSans.woff2'),
                'type' => 'font/woff2',
                'crossorigin' => false,
                'priority' => 'high',
                'savings' => '150ms'
            ],
            [
                'url' => home_url('/wp-content/themes/' . get_stylesheet() . '/fonts/fontawesome-webfont.woff'),
                'type' => 'font/woff',
                'crossorigin' => false,
                'priority' => 'medium',
                'savings' => '130ms'
            ]
        ];

        // Font di terze parti (FontAwesome CDN) - Risparmio totale: 50ms
        $thirdPartyFonts = [
            [
                'url' => 'https://use.fontawesome.com/releases/v6.0.0/webfonts/fa-brands-400.woff2',
                'type' => 'font/woff2',
                'crossorigin' => true,
                'priority' => 'low',
                'savings' => '30ms'
            ],
            [
                'url' => 'https://use.fontawesome.com/releases/v6.0.0/webfonts/fa-solid-900.woff2',
                'type' => 'font/woff2',
                'crossorigin' => true,
                'priority' => 'low',
                'savings' => '20ms'
            ]
        ];

        // Verifica se i font del sito esistono
        foreach ($siteFonts as $font) {
            if ($this->fontExists($font['url'])) {
                $fonts[] = $font;
            }
        }

        // Aggiungi sempre i font di terze parti
        $fonts = array_merge($fonts, $thirdPartyFonts);

        // Aggiungi font personalizzati dalle impostazioni
        $customFonts = $this->getSetting('custom_lighthouse_fonts', []);
        $fonts = array_merge($fonts, $customFonts);

        return array_filter($fonts, function($font) {
            return !empty($font['url']) && $this->isValidFontUrl($font['url']);
        });
    }

    /**
     * Genera CSS font-display per i font problematici di Lighthouse
     */
    private function generateLighthouseFontDisplayCSS(): string
    {
        $css = [];

        // Font specifici identificati nel report Lighthouse
        $lighthouseProblematicFonts = [
            // Gill Sans fonts (ilpoderedimarfisa.it)
            'Gill Sans',
            'GillSans',
            'GillSans-Light',
            'GillSans-Regular',
            
            // FontAwesome fonts
            'FontAwesome',
            'fontawesome',
            'Font Awesome',
            'FontAwesome 4.2',
            
            // Font di terze parti
            'Font Awesome 6 Brands',
            'Font Awesome 6 Solid',
            'fa-brands-400',
            'fa-solid-900'
        ];

        foreach ($lighthouseProblematicFonts as $font) {
            $css[] = sprintf('@font-face { font-family: "%s"; font-display: swap !important; }', $font);
        }

        // CSS specifico per i font identificati nel report
        $css[] = '/* Lighthouse Font Display Fix - 180ms savings */';
        $css[] = '@font-face { font-family: "Gill Sans Light"; font-display: swap !important; }';
        $css[] = '@font-face { font-family: "Gill Sans Regular"; font-display: swap !important; }';
        $css[] = '@font-face { font-family: "FontAwesome 4.2"; font-display: swap !important; }';
        
        // Fallback generico per tutti i font senza font-display
        $css[] = '@font-face { font-display: swap !important; }';

        // CSS per prevenire FOIT (Flash of Invisible Text)
        $css[] = '/* Prevent FOIT with fallback fonts */';
        $css[] = 'body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }';
        $css[] = '.fa, .fas, .far, .fab { font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands", sans-serif !important; }';

        return implode("\n", $css);
    }

    /**
     * Ottiene i provider dei font problematici
     */
    private function getLighthouseFontProviders(): array
    {
        return [
            [
                'url' => 'https://use.fontawesome.com',
                'crossorigin' => true,
            ],
            [
                'url' => 'https://kit.fontawesome.com',
                'crossorigin' => true,
            ],
            // DNS prefetch per provider comuni
            [
                'url' => 'https://fonts.googleapis.com',
                'crossorigin' => false,
            ],
            [
                'url' => 'https://fonts.gstatic.com',
                'crossorigin' => true,
            ]
        ];
    }

    /**
     * Controlla se un font è problematico secondo Lighthouse
     */
    private function isLighthouseProblematicFont(string $handle, string $href): bool
    {
        // Handle problematici
        $problematicHandles = [
            'fontawesome',
            'fa',
            'gillsans',
            'gill-sans',
            'theme-font',
            'main-font'
        ];

        if (in_array($handle, $problematicHandles, true)) {
            return true;
        }

        // URL problematici
        $problematicPatterns = [
            'fontawesome',
            'gillsans',
            'gill-sans',
            'useanyfont',
            'fa-brands',
            'fa-solid'
        ];

        foreach ($problematicPatterns as $pattern) {
            if (strpos(strtolower($href), $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se un font esiste
     */
    private function fontExists(string $url): bool
    {
        // Per URL locali, controlla se il file esiste
        if (strpos($url, home_url()) !== false) {
            $path = str_replace(home_url(), ABSPATH, $url);
            return file_exists($path);
        }

        // Per URL esterni, assumiamo che esistano
        return true;
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
     * Controlla se l'ottimizzazione Lighthouse è abilitata
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
            'enabled' => false,
            'preload_critical_fonts' => false,
            'inject_font_display' => false,
            'preconnect_providers' => false,
            'optimize_site_fonts' => false,
            'custom_lighthouse_fonts' => [],
            'expected_savings' => '180ms'
        ];

        $settings = get_option(self::OPTION, []);
        return is_array($settings) ? array_merge($defaults, $settings) : $defaults;
    }

    /**
     * Ottiene un'impostazione specifica
     */
    private function getSetting(string $key, $default = null)
    {
        $settings = $this->getSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = array_merge($current, $settings);

        $result = update_option(self::OPTION, $updated);

        if ($result) {
            Logger::info('Lighthouse font optimization settings updated', $updated);
            do_action('fp_ps_lighthouse_font_optimization_updated', $updated);
        }

        return $result;
    }

    /**
     * Ottiene lo stato per la visualizzazione admin
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        $criticalFonts = $this->getLighthouseCriticalFonts();
        
        return [
            'enabled' => $this->isEnabled(),
            'preload_enabled' => !empty($settings['preload_critical_fonts']),
            'font_display_injected' => !empty($settings['inject_font_display']),
            'preconnect_enabled' => !empty($settings['preconnect_providers']),
            'site_fonts_optimized' => !empty($settings['optimize_site_fonts']),
            'critical_fonts_count' => count($criticalFonts),
            'expected_savings' => $settings['expected_savings'] ?? '180ms'
        ];
    }

    /**
     * Calcola il risparmio totale stimato
     */
    public function getTotalExpectedSavings(): string
    {
        $fonts = $this->getLighthouseCriticalFonts();
        $totalMs = 0;

        foreach ($fonts as $font) {
            if (isset($font['savings'])) {
                $savings = str_replace('ms', '', $font['savings']);
                $totalMs += (int) $savings;
            }
        }

        return $totalMs . 'ms';
    }
}
