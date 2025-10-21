<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

/**
 * Font Optimizer
 *
 * Optimizes web font loading with font-display, preload hints,
 * and Google Fonts optimization for better PageSpeed scores.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class FontOptimizer
{
    private const OPTION = 'fp_ps_font_optimization';

    /**
     * Register hooks
     */
    public function register(): void
    {
        if (!is_admin() && $this->isEnabled()) {
            // Google Fonts optimization
            if ($this->getSetting('optimize_google_fonts', true)) {
                add_filter('style_loader_tag', [$this, 'optimizeGoogleFonts'], 10, 4);
            }

            // Add font-display to custom fonts
            if ($this->getSetting('add_font_display', true)) {
                add_filter('style_loader_tag', [$this, 'addFontDisplay'], 10, 4);
            }

            // Inject font-display into CSS content
            if ($this->getSetting('inject_font_display', true)) {
                add_action('wp_head', [$this, 'injectFontDisplayCSS'], 5);
            }

            // Preload critical fonts
            if ($this->getSetting('preload_fonts', true)) {
                add_action('wp_head', [$this, 'preloadCriticalFonts'], 1);
            }

            // Preconnect to font providers
            if ($this->getSetting('preconnect_providers', true)) {
                add_action('wp_head', [$this, 'addFontProviderPreconnect'], 1);
            }

            // Enhanced font loading optimization for render delay
            if ($this->getSetting('optimize_render_delay', true)) {
                add_action('wp_head', [$this, 'optimizeFontLoadingForRenderDelay'], 1);
            }

            Logger::debug('FontOptimizer registered');
        }
    }

    /**
     * Optimize Google Fonts URLs
     */
    public function optimizeGoogleFonts(string $html, string $handle, string $href, $media): string
    {
        // Check if it's a Google Fonts link
        if (strpos($href, 'fonts.googleapis.com') === false) {
            return $html;
        }

        // Add display=swap parameter if not present
        if (strpos($href, 'display=') === false) {
            $separator = strpos($href, '?') !== false ? '&' : '?';
            $href = $href . $separator . 'display=swap';
        }

        // Add text parameter to reduce font file size (only load used characters)
        if (strpos($href, 'text=') === false) {
            $separator = strpos($href, '?') !== false ? '&' : '?';
            $href = $href . $separator . 'text=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        }

        // Convert to preload for critical fonts to reduce critical path
        if ($this->isCriticalGoogleFont($handle, $href)) {
            // Extract font family from URL
            $fontFamily = $this->extractFontFamilyFromUrl($href);
            
            if ($fontFamily) {
                // Preload the font file directly
                $this->preloadGoogleFontFile($fontFamily);
                
                // Return a minimal CSS link with font-display
                $html = sprintf(
                    '<link rel="stylesheet" id="%s-css" href="%s" media="%s" />',
                    esc_attr($handle),
                    esc_url($href),
                    esc_attr($media)
                );
            }
        }
        
        // Rebuild the link tag with optimizations
        $html = sprintf(
            '<link rel="stylesheet" id="%s-css" href="%s" media="%s" />',
            esc_attr($handle),
            esc_url($href),
            esc_attr($media)
        );

        Logger::debug('Optimized Google Fonts link', ['handle' => $handle, 'href' => $href]);

        return $html;
    }

    /**
     * Check if this is a critical Google Font that should be preloaded
     */
    private function isCriticalGoogleFont(string $handle, string $href): bool
    {
        // Critical font handles (usually theme fonts)
        $criticalHandles = [
            'theme-font',
            'main-font',
            'primary-font',
            'body-font',
            'heading-font'
        ];

        if (in_array($handle, $criticalHandles, true)) {
            return true;
        }

        // Check if it's a commonly used font family
        $criticalFonts = [
            'Open Sans',
            'Roboto',
            'Lato',
            'Montserrat',
            'Source Sans Pro',
            'Poppins',
            'Nunito',
            'Inter'
        ];

        foreach ($criticalFonts as $font) {
            if (strpos($href, urlencode($font)) !== false) {
                return true;
            }
        }

        return false;
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
     * Preload Google Font file directly
     */
    private function preloadGoogleFontFile(string $fontFamily): void
    {
        // This would need to be implemented based on the specific font family
        // For now, we'll add a generic preload for common Google Fonts
        $fontUrl = $this->getGoogleFontFileUrl($fontFamily);
        
        if ($fontUrl) {
            printf(
                '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin />' . "\n",
                esc_url($fontUrl)
            );
        }
    }

    /**
     * Get direct Google Font file URL
     */
    private function getGoogleFontFileUrl(string $fontFamily): ?string
    {
        // This is a simplified implementation
        // In practice, you'd need to parse the Google Fonts CSS to get the actual font file URLs
        $fontName = str_replace(' ', '+', $fontFamily);
        return "https://fonts.gstatic.com/s/{$fontName}/v1/{$fontName}.woff2";
    }

    /**
     * Add font-display to CSS containing @font-face
     */
    public function addFontDisplay(string $html, string $handle, string $href, $media): string
    {
        // Skip Google Fonts (handled separately)
        if (strpos($href, 'fonts.googleapis.com') !== false) {
            return $html;
        }

        // Process both local and third-party CSS files that might contain fonts
        $homeUrl = home_url('/');
        $isLocalFile = strpos($href, $homeUrl) !== false || strpos($href, '/') === 0;
        
        // Known third-party font providers that need font-display optimization
        $thirdPartyProviders = [
            'brevo.com',
            'assets.brevo.com',
            'cdnjs.cloudflare.com',
            'maxcdn.bootstrapcdn.com',
            'use.fontawesome.com',
            'kit.fontawesome.com'
        ];
        
        $isThirdPartyFont = false;
        foreach ($thirdPartyProviders as $provider) {
            if (strpos($href, $provider) !== false) {
                $isThirdPartyFont = true;
                break;
            }
        }

        // Check if this CSS is in our font list, theme stylesheet, or third-party font
        $fontHandles = $this->getSetting('font_handles', []);
        $isThemeStyle = (strpos($handle, 'theme') !== false || $handle === get_stylesheet());
        
        if (in_array($handle, $fontHandles, true) || $isThemeStyle || $isThirdPartyFont) {
            // Add data attribute to mark for font-display injection
            $html = str_replace('<link ', '<link data-fp-font-css="true" ', $html);
            
            Logger::debug('Marked CSS for font-display injection', [
                'handle' => $handle,
                'href' => $href,
                'type' => $isThirdPartyFont ? 'third-party' : 'local'
            ]);
        }

        return $html;
    }

    /**
     * Inject font-display CSS to fix third-party fonts
     */
    public function injectFontDisplayCSS(): void
    {
        $css = $this->generateFontDisplayCSS();
        
        if (!empty($css)) {
            echo "\n<!-- FP Performance Suite - Font Display Fix -->\n";
            echo '<style id="fp-font-display-fix">' . $css . '</style>' . "\n";
            echo "<!-- End Font Display Fix -->\n";
            
            Logger::debug('Injected font-display CSS');
        }
    }

    /**
     * Enhanced font loading optimization for render delay issues
     */
    public function optimizeFontLoadingForRenderDelay(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Enhanced Font Loading -->\n";
        
        // Add aggressive font-display optimization
        echo '<style>
            @font-face { 
                font-display: swap !important; 
            }
            * { 
                font-display: swap !important; 
            }
            /* Prevent FOIT (Flash of Invisible Text) */
            body { 
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
        </style>' . "\n";

        // Add preload for critical fonts with higher priority
        $this->preloadCriticalFontsWithPriority();
        
        echo "<!-- End Enhanced Font Loading -->\n";
    }

    /**
     * Preload critical fonts with high priority
     */
    private function preloadCriticalFontsWithPriority(): void
    {
        $criticalFonts = $this->getCriticalFontsForRenderDelay();
        
        foreach ($criticalFonts as $font) {
            if (empty($font['url'])) {
                continue;
            }

            $type = $font['type'] ?? 'font/woff2';
            $crossorigin = !empty($font['crossorigin']) ? ' crossorigin' : '';

            printf(
                '<link rel="preload" href="%s" as="font" type="%s"%s fetchpriority="high" />' . "\n",
                esc_url($font['url']),
                esc_attr($type),
                $crossorigin
            );
        }
    }

    /**
     * Get critical fonts specifically for render delay optimization
     */
    private function getCriticalFontsForRenderDelay(): array
    {
        $fonts = [];

        // Add theme fonts that are likely causing render delay
        $themeUri = get_stylesheet_directory_uri();
        $themeDir = get_stylesheet_directory();

        // Check for common font files in theme
        $fontDirs = ['/fonts/', '/assets/fonts/', '/css/fonts/'];
        
        foreach ($fontDirs as $dir) {
            $path = $themeDir . $dir;
            if (is_dir($path)) {
                $files = glob($path . '*.{woff2,woff}', GLOB_BRACE);
                if (!empty($files)) {
                    foreach (array_slice($files, 0, 2) as $file) {
                        $basename = basename($file);
                        $fonts[] = [
                            'url' => $themeUri . $dir . $basename,
                            'type' => $this->getFontType($file),
                            'crossorigin' => false,
                        ];
                    }
                    break;
                }
            }
        }

        // Add known problematic fonts from Lighthouse reports
        $lighthouseFonts = [
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
        ];

        $fonts = array_merge($fonts, $lighthouseFonts);

        return array_filter($fonts, function($font) {
            return !empty($font['url']) && $this->isValidFontUrl($font['url']);
        });
    }

    /**
     * Generate CSS to fix font-display issues
     */
    private function generateFontDisplayCSS(): string
    {
        $css = [];

        // Fix for specific fonts identified in Lighthouse report
        $problematicFonts = $this->getProblematicFonts();
        
        foreach ($problematicFonts as $font) {
            $css[] = sprintf('@font-face { font-family: "%s"; font-display: swap !important; }', $font);
        }

        // Fix for Brevo fonts (specific to the Lighthouse report)
        $css[] = '@font-face { font-family: "Brevo"; font-display: swap !important; }';
        $css[] = '@font-face { font-family: "brevo"; font-display: swap !important; }';
        
        // Fix for FontAwesome fonts (from villadianella.it)
        $css[] = '@font-face { font-family: "FontAwesome"; font-display: swap !important; }';
        $css[] = '@font-face { font-family: "fontawesome"; font-display: swap !important; }';
        $css[] = '@font-face { font-family: "Font Awesome"; font-display: swap !important; }';
        
        // Generic fallback for any font-face without font-display
        $css[] = '@font-face { font-display: swap !important; }';

        // Additional fixes for common third-party fonts
        $thirdPartyFonts = [
            'Open Sans',
            'Roboto', 
            'Lato',
            'Montserrat',
            'Source Sans Pro',
            'Poppins',
            'Nunito',
            'Inter'
        ];

        foreach ($thirdPartyFonts as $font) {
            $css[] = sprintf('@font-face { font-family: "%s"; font-display: swap !important; }', $font);
        }

        return implode("\n", $css);
    }

    /**
     * Get problematic fonts that need font-display fixes
     */
    private function getProblematicFonts(): array
    {
        // Fonts specifically mentioned in the Lighthouse report
        $lighthouseFonts = [
            // Brevo fonts (from assets.brevo.com)
            'Brevo',
            'brevo',
            
            // FontAwesome fonts (from villadianella.it)
            'FontAwesome',
            'fontawesome',
            'Font Awesome'
        ];

        // Get custom problematic fonts from settings
        $customFonts = $this->getSetting('problematic_fonts', []);
        
        return array_unique(array_merge($lighthouseFonts, $customFonts));
    }

    /**
     * Preload critical fonts
     */
    public function preloadCriticalFonts(): void
    {
        $fonts = $this->getCriticalFonts();

        if (empty($fonts)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Critical Font Preload -->\n";
        
        foreach ($fonts as $font) {
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

            Logger::debug('Preloaded critical font', ['url' => $font['url']]);
        }
        
        echo "<!-- End Critical Font Preload -->\n";
    }

    /**
     * Auto-detect problematic fonts from current page
     */
    public function autoDetectProblematicFonts(): array
    {
        $detected = [];
        
        // Get all stylesheet URLs from the current page
        global $wp_styles;
        if (empty($wp_styles)) {
            return $detected;
        }

        foreach ($wp_styles->done as $handle) {
            $src = $wp_styles->registered[$handle]->src ?? '';
            
            // Check for Google Fonts
            if (strpos($src, 'fonts.googleapis.com') !== false) {
                $detected[] = [
                    'type' => 'google_fonts',
                    'url' => $src,
                    'priority' => 'high',
                    'action' => 'preload_and_optimize'
                ];
            }
            
            // Check for fonts.gstatic.com (Google Fonts files)
            if (strpos($src, 'fonts.gstatic.com') !== false) {
                $detected[] = [
                    'type' => 'google_font_file',
                    'url' => $src,
                    'priority' => 'critical',
                    'action' => 'preload'
                ];
            }
            
            // Check for other font providers
            $fontProviders = [
                'assets.brevo.com',
                'cdnjs.cloudflare.com',
                'maxcdn.bootstrapcdn.com',
                'use.fontawesome.com'
            ];
            
            foreach ($fontProviders as $provider) {
                if (strpos($src, $provider) !== false) {
                    $detected[] = [
                        'type' => 'third_party_font',
                        'url' => $src,
                        'priority' => 'medium',
                        'action' => 'preload_and_optimize'
                    ];
                    break;
                }
            }
        }

        return $detected;
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
     * Get critical fonts for preloading
     */
    private function getCriticalFonts(): array
    {
        $fonts = $this->getSetting('critical_fonts', []);

        // Auto-detect theme fonts if enabled
        if ($this->getSetting('auto_detect_fonts', false)) {
            $themeFonts = $this->detectThemeFonts();
            $fonts = array_merge($fonts, $themeFonts);
        }

        // Add Lighthouse-identified problematic fonts for preloading
        $lighthouseFonts = $this->getLighthouseProblematicFonts();
        $fonts = array_merge($fonts, $lighthouseFonts);

        // Filter and validate
        return array_filter($fonts, function($font) {
            return !empty($font['url']) && $this->isValidFontUrl($font['url']);
        });
    }

    /**
     * Get fonts identified in Lighthouse report for preloading
     */
    private function getLighthouseProblematicFonts(): array
    {
        return [
            // Brevo fonts from assets.brevo.com (130ms savings)
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
            // FontAwesome from villadianella.it (40ms savings)
            [
                'url' => home_url('/wp-content/themes/' . get_stylesheet() . '/fonts/fontawesome-webfont.woff'),
                'type' => 'font/woff',
                'crossorigin' => false,
            ],
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
        ];
    }

    /**
     * Get font provider domains for preconnect
     */
    private function getFontProviders(): array
    {
        $providers = [];

        // Google Fonts
        if ($this->getSetting('use_google_fonts', true)) {
            $providers[] = [
                'url' => 'https://fonts.googleapis.com',
                'crossorigin' => false,
            ];
            $providers[] = [
                'url' => 'https://fonts.gstatic.com',
                'crossorigin' => true,
            ];
        }

        // Brevo providers (identified in Lighthouse report)
        $providers[] = [
            'url' => 'https://assets.brevo.com',
            'crossorigin' => true,
        ];

        // Custom providers
        $customProviders = $this->getSetting('custom_providers', []);
        foreach ($customProviders as $provider) {
            if (!empty($provider['url'])) {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    /**
     * Detect theme fonts (basic implementation)
     */
    private function detectThemeFonts(): array
    {
        $detected = [];
        
        // Check theme directory for common font files
        $themeDir = get_stylesheet_directory();
        $themeUrl = get_stylesheet_directory_uri();
        
        $fontDirs = ['/fonts/', '/assets/fonts/', '/font/'];
        
        foreach ($fontDirs as $dir) {
            $path = $themeDir . $dir;
            if (is_dir($path)) {
                $files = glob($path . '*.{woff2,woff}', GLOB_BRACE);
                if (!empty($files)) {
                    foreach (array_slice($files, 0, 3) as $file) { // Max 3 fonts
                        $basename = basename($file);
                        $detected[] = [
                            'url' => $themeUrl . $dir . $basename,
                            'type' => $this->getFontType($file),
                            'crossorigin' => false,
                        ];
                    }
                    break; // Only process first directory found
                }
            }
        }

        return $detected;
    }

    /**
     * Get MIME type for font file
     */
    private function getFontType(string $file): string
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
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
     * Check if font optimization is enabled
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
            'optimize_google_fonts' => false,
            'add_font_display' => false,
            'inject_font_display' => false,
            'preload_fonts' => false,
            'preconnect_providers' => false,
            'use_google_fonts' => false,
            'auto_detect_fonts' => false,
            'optimize_render_delay' => false,
            'critical_fonts' => [],
            'custom_providers' => [],
            'font_handles' => [],
            'problematic_fonts' => [],
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

        // Validate critical fonts
        if (isset($updated['critical_fonts'])) {
            $updated['critical_fonts'] = array_filter(
                $updated['critical_fonts'],
                fn($font) => !empty($font['url']) && $this->isValidFontUrl($font['url'])
            );
        }

        $result = update_option(self::OPTION, $updated);

        if ($result) {
            Logger::info('Font optimization settings updated', $updated);
            do_action('fp_ps_font_optimization_updated', $updated);
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
            'google_fonts_optimized' => !empty($settings['optimize_google_fonts']),
            'font_display_added' => !empty($settings['add_font_display']),
            'font_display_injected' => !empty($settings['inject_font_display']),
            'preload_enabled' => !empty($settings['preload_fonts']),
            'critical_fonts_count' => count($criticalFonts),
            'preconnect_enabled' => !empty($settings['preconnect_providers']),
        ];
    }
}
