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

            // Preload critical fonts
            if ($this->getSetting('preload_fonts', true)) {
                add_action('wp_head', [$this, 'preloadCriticalFonts'], 1);
            }

            // Preconnect to font providers
            if ($this->getSetting('preconnect_providers', true)) {
                add_action('wp_head', [$this, 'addFontProviderPreconnect'], 1);
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
            
            // Rebuild the link tag
            $html = sprintf(
                '<link rel="stylesheet" id="%s-css" href="%s" media="%s" />',
                esc_attr($handle),
                esc_url($href),
                esc_attr($media)
            );
        }

        // Add preconnect for Google Fonts (will be deduplicated by browser)
        // Note: The preconnect method will handle this, but we mark it here
        
        Logger::debug('Optimized Google Fonts link', ['handle' => $handle, 'href' => $href]);

        return $html;
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

        // Only process local CSS files
        $homeUrl = home_url('/');
        if (strpos($href, $homeUrl) === false && strpos($href, '/') !== 0) {
            return $html;
        }

        // Check if this CSS is in our font list or if it's a theme stylesheet
        $fontHandles = $this->getSetting('font_handles', []);
        $isThemeStyle = (strpos($handle, 'theme') !== false || $handle === get_stylesheet());
        
        if (in_array($handle, $fontHandles, true) || $isThemeStyle) {
            // Add data attribute to mark for font-display injection
            $html = str_replace('<link ', '<link data-fp-font-css="true" ', $html);
        }

        return $html;
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

        // Filter and validate
        return array_filter($fonts, function($font) {
            return !empty($font['url']) && $this->isValidFontUrl($font['url']);
        });
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
            'enabled' => true,
            'optimize_google_fonts' => true,
            'add_font_display' => true,
            'preload_fonts' => true,
            'preconnect_providers' => true,
            'use_google_fonts' => true,
            'auto_detect_fonts' => false,
            'critical_fonts' => [],
            'custom_providers' => [],
            'font_handles' => [],
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
            'preload_enabled' => !empty($settings['preload_fonts']),
            'critical_fonts_count' => count($criticalFonts),
            'preconnect_enabled' => !empty($settings['preconnect_providers']),
        ];
    }
}
