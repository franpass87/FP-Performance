<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

/**
 * Critical Path Optimizer
 * 
 * Specifically addresses the 6,414ms critical path latency issue
 * by implementing advanced font loading strategies.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CriticalPathOptimizer
{
    private const OPTION = 'fp_ps_critical_path_optimization';

    /**
     * Register hooks
     */
    public function register(): void
    {
        if (!is_admin() && $this->isEnabled()) {
            // High priority font preloading
            add_action('wp_head', [$this, 'preloadCriticalFonts'], 1);
            
            // Preconnect to font providers
            add_action('wp_head', [$this, 'addFontProviderPreconnect'], 1);
            
            // Optimize Google Fonts loading
            add_filter('style_loader_tag', [$this, 'optimizeGoogleFontsLoading'], 10, 4);
            
            // Add font-display CSS injection
            add_action('wp_head', [$this, 'injectFontDisplayCSS'], 5);
            
            // Add resource hints for font files
            add_action('wp_head', [$this, 'addResourceHints'], 2);
            
            Logger::debug('CriticalPathOptimizer registered');
        }
    }

    /**
     * Preload critical fonts to reduce critical path latency
     */
    public function preloadCriticalFonts(): void
    {
        $criticalFonts = $this->getCriticalFonts();

        if (empty($criticalFonts)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Critical Path Font Preload -->\n";
        
        foreach ($criticalFonts as $font) {
            if (empty($font['url'])) {
                continue;
            }

            $type = $font['type'] ?? 'font/woff2';
            $crossorigin = !empty($font['crossorigin']) ? ' crossorigin' : '';

            printf(
                '<link rel="preload" href="%s" as="font" type="%s"%s />' . "\n",
                esc_url($font['url']),
                esc_attr($type),
                $crossorigin
            );

            Logger::debug('Preloaded critical path font', ['url' => $font['url']]);
        }
        
        echo "<!-- End Critical Path Font Preload -->\n";
    }

    /**
     * Add preconnect for font providers
     */
    public function addFontProviderPreconnect(): void
    {
        $providers = $this->getFontProviders();

        if (empty($providers)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Font Provider Preconnect -->\n";
        
        foreach ($providers as $provider) {
            $crossorigin = !empty($provider['crossorigin']) ? ' crossorigin' : '';
            
            printf(
                '<link rel="preconnect" href="%s"%s />' . "\n",
                esc_url($provider['url']),
                $crossorigin
            );

            Logger::debug('Added font provider preconnect', ['url' => $provider['url']]);
        }
        
        echo "<!-- End Font Provider Preconnect -->\n";
    }

    /**
     * Optimize Google Fonts loading to reduce critical path
     */
    public function optimizeGoogleFontsLoading(string $html, string $handle, string $href, $media): string
    {
        // Check if it's a Google Fonts link
        if (strpos($href, 'fonts.googleapis.com') === false) {
            return $html;
        }

        // Add display=swap parameter
        if (strpos($href, 'display=') === false) {
            $separator = strpos($href, '?') !== false ? '&' : '?';
            $href = $href . $separator . 'display=swap';
        }

        // Add text parameter to reduce font file size
        if (strpos($href, 'text=') === false) {
            $separator = strpos($href, '?') !== false ? '&' : '?';
            $href = $href . $separator . 'text=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        }

        // For critical fonts, convert to preload
        if ($this->isCriticalFont($handle, $href)) {
            $this->preloadGoogleFontDirectly($href);
            
            // Return optimized link
            $html = sprintf(
                '<link rel="stylesheet" id="%s-css" href="%s" media="%s" />',
                esc_attr($handle),
                esc_url($href),
                esc_attr($media)
            );
        }

        Logger::debug('Optimized Google Fonts for critical path', ['handle' => $handle, 'href' => $href]);

        return $html;
    }

    /**
     * Inject font-display CSS to fix FOIT/FOUT issues
     */
    public function injectFontDisplayCSS(): void
    {
        $css = $this->generateFontDisplayCSS();
        
        if (!empty($css)) {
            echo "\n<!-- FP Performance Suite - Font Display Fix -->\n";
            echo '<style id="fp-font-display-fix">' . $css . '</style>' . "\n";
            echo "<!-- End Font Display Fix -->\n";
            
            Logger::debug('Injected font-display CSS for critical path');
        }
    }

    /**
     * Add resource hints for font files
     */
    public function addResourceHints(): void
    {
        $hints = $this->getResourceHints();

        if (empty($hints)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Resource Hints -->\n";
        
        foreach ($hints as $hint) {
            $attributes = [];
            
            if (!empty($hint['rel'])) {
                $attributes[] = 'rel="' . esc_attr($hint['rel']) . '"';
            }
            
            if (!empty($hint['href'])) {
                $attributes[] = 'href="' . esc_url($hint['href']) . '"';
            }
            
            if (!empty($hint['as'])) {
                $attributes[] = 'as="' . esc_attr($hint['as']) . '"';
            }
            
            if (!empty($hint['type'])) {
                $attributes[] = 'type="' . esc_attr($hint['type']) . '"';
            }
            
            if (!empty($hint['crossorigin'])) {
                $attributes[] = 'crossorigin';
            }

            printf('<link %s />' . "\n", implode(' ', $attributes));
        }
        
        echo "<!-- End Resource Hints -->\n";
    }

    /**
     * Get critical fonts for preloading
     */
    private function getCriticalFonts(): array
    {
        $fonts = $this->getSetting('critical_fonts', []);

        // Add fonts identified in the Lighthouse report
        $lighthouseFonts = [
            // Google Fonts problematic fonts (6,414ms critical path)
            [
                'url' => 'https://fonts.gstatic.com/s/memvya/v44/memvYaGs1.woff2',
                'type' => 'font/woff2',
                'crossorigin' => true,
            ],
            [
                'url' => 'https://fonts.gstatic.com/s/rp2tp2ywx/v17/rP2tp2ywx.woff2',
                'type' => 'font/woff2',
                'crossorigin' => true,
            ],
            // Brevo fonts (130ms savings)
            [
                'url' => 'https://assets.brevo.com/fonts/3ef7cf1.woff2',
                'type' => 'font/woff2',
                'crossorigin' => true,
            ],
            [
                'url' => 'https://assets.brevo.com/fonts/7529907.woff2',
                'type' => 'font/woff2',
                'crossorigin' => true,
            ],
            // FontAwesome from villadianella.it
            [
                'url' => home_url('/wp-content/themes/' . get_stylesheet() . '/fonts/fontawesome-webfont.woff'),
                'type' => 'font/woff',
                'crossorigin' => false,
            ],
        ];

        $fonts = array_merge($fonts, $lighthouseFonts);

        // Filter and validate
        return array_filter($fonts, function($font) {
            return !empty($font['url']) && $this->isValidFontUrl($font['url']);
        });
    }

    /**
     * Get font providers for preconnect
     */
    private function getFontProviders(): array
    {
        return [
            [
                'url' => 'https://fonts.googleapis.com',
                'crossorigin' => false,
            ],
            [
                'url' => 'https://fonts.gstatic.com',
                'crossorigin' => true,
            ],
            [
                'url' => 'https://assets.brevo.com',
                'crossorigin' => true,
            ],
        ];
    }

    /**
     * Check if this is a critical font
     */
    private function isCriticalFont(string $handle, string $href): bool
    {
        // Critical font handles
        $criticalHandles = [
            'theme-font',
            'main-font',
            'primary-font',
            'body-font',
            'heading-font',
            'google-fonts'
        ];

        if (in_array($handle, $criticalHandles, true)) {
            return true;
        }

        // Check for Google Fonts
        if (strpos($href, 'fonts.googleapis.com') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Preload Google Font directly
     */
    private function preloadGoogleFontDirectly(string $href): void
    {
        // Extract font family from URL
        $fontFamily = $this->extractFontFamilyFromUrl($href);
        
        if ($fontFamily) {
            // This is a simplified approach - in practice you'd need to parse the CSS
            // to get the actual font file URLs
            $fontUrl = $this->getGoogleFontFileUrl($fontFamily);
            
            if ($fontUrl) {
                printf(
                    '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin />' . "\n",
                    esc_url($fontUrl)
                );
            }
        }
    }

    /**
     * Extract font family from Google Fonts URL
     */
    private function extractFontFamilyFromUrl(string $url): ?string
    {
        $parsed = parse_url($url);
        if (empty($parsed['query'])) {
            return null;
        }

        parse_str($parsed['query'], $params);
        return $params['family'] ?? null;
    }

    /**
     * Get Google Font file URL
     */
    private function getGoogleFontFileUrl(string $fontFamily): ?string
    {
        // This is a simplified implementation
        $fontName = str_replace(' ', '+', $fontFamily);
        return "https://fonts.gstatic.com/s/{$fontName}/v1/{$fontName}.woff2";
    }

    /**
     * Generate font-display CSS
     */
    private function generateFontDisplayCSS(): string
    {
        $css = [];

        // Fix for problematic fonts
        $problematicFonts = [
            'Brevo',
            'brevo',
            'FontAwesome',
            'fontawesome',
            'Font Awesome',
            'Open Sans',
            'Roboto',
            'Lato',
            'Montserrat',
            'Source Sans Pro',
            'Poppins',
            'Nunito',
            'Inter'
        ];

        foreach ($problematicFonts as $font) {
            $css[] = sprintf('@font-face { font-family: "%s"; font-display: swap !important; }', $font);
        }

        // Generic fallback
        $css[] = '@font-face { font-display: swap !important; }';

        return implode("\n", $css);
    }

    /**
     * Get resource hints
     */
    private function getResourceHints(): array
    {
        return [
            [
                'rel' => 'dns-prefetch',
                'href' => 'https://fonts.googleapis.com'
            ],
            [
                'rel' => 'dns-prefetch',
                'href' => 'https://fonts.gstatic.com'
            ],
            [
                'rel' => 'dns-prefetch',
                'href' => 'https://assets.brevo.com'
            ],
        ];
    }

    /**
     * Validate font URL
     */
    private function isValidFontUrl(string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        // Check if it's a valid URL or path
        if (!filter_var($url, FILTER_VALIDATE_URL) && strpos($url, '/') !== 0) {
            return false;
        }

        // Check extension
        $validExts = ['woff2', 'woff', 'ttf', 'otf', 'eot'];
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        
        return in_array($ext, $validExts, true);
    }

    /**
     * Check if critical path optimization is enabled
     */
    public function isEnabled(): bool
    {
        $settings = $this->getSettings();
        return !empty($settings['enabled']);
    }

    /**
     * Get all settings
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'preload_critical_fonts' => false,
            'preconnect_providers' => false,
            'optimize_google_fonts' => false,
            'inject_font_display' => false,
            'add_resource_hints' => false,
            'critical_fonts' => [],
        ];

        $settings = get_option(self::OPTION, []);
        return is_array($settings) ? array_merge($defaults, $settings) : $defaults;
    }

    /**
     * Get specific setting
     */
    private function getSetting(string $key, $default = null)
    {
        $settings = $this->getSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Update settings
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = array_merge($current, $settings);

        $result = update_option(self::OPTION, $updated);

        if ($result) {
            Logger::info('Critical path optimization settings updated', $updated);
            do_action('fp_ps_critical_path_optimization_updated', $updated);
        }

        return $result;
    }

    /**
     * Get status for admin display
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        $criticalFonts = $this->getCriticalFonts();
        
        return [
            'enabled' => $this->isEnabled(),
            'preload_enabled' => !empty($settings['preload_critical_fonts']),
            'preconnect_enabled' => !empty($settings['preconnect_providers']),
            'google_fonts_optimized' => !empty($settings['optimize_google_fonts']),
            'font_display_injected' => !empty($settings['inject_font_display']),
            'resource_hints_enabled' => !empty($settings['add_resource_hints']),
            'critical_fonts_count' => count($criticalFonts),
        ];
    }
}
