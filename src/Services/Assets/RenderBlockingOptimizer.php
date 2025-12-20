<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;

/**
 * Render Blocking Optimizer
 * 
 * Solves Element render delay issues by optimizing font loading,
 * CSS delivery, and critical resource prioritization.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class RenderBlockingOptimizer
{
    private const OPTION = 'fp_ps_render_blocking_optimization';
    
    /**
     * @var OptionsRepositoryInterface|null
     */
    private $optionsRepo;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository instance
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
    }

    /**
     * Register hooks
     */
    public function register(): void
    {
        if (!is_admin() && $this->isEnabled()) {
            // Critical CSS injection for above-the-fold content - PRIORITÀ CRITICA
            add_action('wp_head', [$this, 'injectCriticalCSS'], 3);
            
            // Optimize font loading to prevent render blocking - PRIORITÀ CRITICA
            add_action('wp_head', [$this, 'optimizeFontLoading'], 4);
            
            // Defer non-critical CSS - PRIORITÀ MEDIA per render blocking
            add_filter('style_loader_tag', [$this, 'deferNonCriticalCSS'], 9, 4);
            
            // Preload critical resources - PRIORITÀ MEDIA
            add_action('wp_head', [$this, 'preloadCriticalResources'], 6);
            
            // Add resource hints for faster loading - PRIORITÀ MEDIA
            add_action('wp_head', [$this, 'addResourceHints'], 7);
            
            Logger::debug('RenderBlockingOptimizer registered');
        }
    }

    /**
     * Inject critical CSS to prevent render blocking
     */
    public function injectCriticalCSS(): void
    {
        $criticalCSS = $this->getCriticalCSS();
        
        if (empty($criticalCSS)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Critical CSS -->\n";
        echo '<style id="fp-critical-css">' . $criticalCSS . '</style>' . "\n";
        echo "<!-- End Critical CSS -->\n";
        
        Logger::debug('Critical CSS injected');
    }

    /**
     * Optimize font loading to prevent render blocking
     */
    public function optimizeFontLoading(): void
    {
        $settings = $this->getSettings();
        
        if (!$settings['optimize_fonts']) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Font Loading Optimization -->\n";
        
        // Preconnect to font providers
        $this->addFontPreconnects();
        
        // Preload critical fonts
        $this->preloadCriticalFonts();
        
        // Add font-display optimization
        $this->addFontDisplayOptimization();
        
        echo "<!-- End Font Loading Optimization -->\n";
    }

    /**
     * Defer non-critical CSS
     */
    public function deferNonCriticalCSS(string $html, string $handle, string $href, $media): string
    {
        $settings = $this->getSettings();
        
        if (!$settings['defer_css']) {
            return $html;
        }

        // Skip critical CSS files
        if ($this->isCriticalCSS($handle, $href)) {
            return $html;
        }

        // Skip if already has media="print" (already deferred)
        if (strpos($html, 'media="print"') !== false) {
            return $html;
        }

        // Add defer attributes
        $html = str_replace('rel="stylesheet"', 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', $html);
        $html = str_replace('<link', '<link data-fp-deferred="true"', $html);

        // Add noscript fallback
        $html .= '<noscript><link rel="stylesheet" href="' . esc_url($href) . '" media="' . esc_attr($media) . '"></noscript>';

        Logger::debug('CSS deferred', ['handle' => $handle, 'href' => basename($href)]);

        return $html;
    }

    /**
     * Preload critical resources
     */
    public function preloadCriticalResources(): void
    {
        $resources = $this->getCriticalResources();
        
        if (empty($resources)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Critical Resource Preload -->\n";
        
        foreach ($resources as $resource) {
            $attributes = [
                'rel="preload"',
                'href="' . esc_url($resource['url']) . '"',
                'as="' . esc_attr($resource['as']) . '"'
            ];

            if (!empty($resource['type'])) {
                $attributes[] = 'type="' . esc_attr($resource['type']) . '"';
            }

            if (!empty($resource['crossorigin'])) {
                $attributes[] = 'crossorigin';
            }

            if (!empty($resource['media'])) {
                $attributes[] = 'media="' . esc_attr($resource['media']) . '"';
            }

            echo '<link ' . implode(' ', $attributes) . '>' . "\n";
        }
        
        echo "<!-- End Critical Resource Preload -->\n";
    }

    /**
     * Add resource hints for faster loading
     */
    public function addResourceHints(): void
    {
        $hints = $this->getResourceHints();
        
        if (empty($hints)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Resource Hints -->\n";
        
        foreach ($hints as $hint) {
            $attributes = [
                'rel="' . esc_attr($hint['rel']) . '"',
                'href="' . esc_url($hint['href']) . '"'
            ];

            if (!empty($hint['crossorigin'])) {
                $attributes[] = 'crossorigin';
            }

            echo '<link ' . implode(' ', $attributes) . '>' . "\n";
        }
        
        echo "<!-- End Resource Hints -->\n";
    }

    /**
     * Get critical CSS for above-the-fold content
     */
    private function getCriticalCSS(): string
    {
        $settings = $this->getSettings();
        $criticalCSS = $settings['critical_css'] ?? '';

        // If no custom critical CSS, generate basic one
        if (empty($criticalCSS)) {
            $criticalCSS = $this->generateBasicCriticalCSS();
        }

        return $criticalCSS;
    }

    /**
     * Generate basic critical CSS for common elements
     */
    private function generateBasicCriticalCSS(): string
    {
        return '
            /* Critical CSS for above-the-fold content */
            body { font-family: system-ui, -apple-system, sans-serif; }
            .site-header, header, .header { display: block; }
            .site-main, main, .main { display: block; }
            .hero, .banner, .hero-section { display: block; }
            h1, h2, h3, h4, h5, h6 { font-weight: bold; }
            p { line-height: 1.6; }
            img { max-width: 100%; height: auto; }
            .container, .wrapper { max-width: 1200px; margin: 0 auto; }
            .btn, .button { display: inline-block; padding: 10px 20px; }
        ';
    }

    /**
     * Add font preconnects
     */
    private function addFontPreconnects(): void
    {
        $providers = [
            'https://fonts.googleapis.com',
            'https://fonts.gstatic.com',
            'https://assets.brevo.com',
            'https://use.fontawesome.com',
        ];

        foreach ($providers as $provider) {
            echo '<link rel="preconnect" href="' . esc_url($provider) . '">' . "\n";
        }
    }

    /**
     * Preload critical fonts
     */
    private function preloadCriticalFonts(): void
    {
        $fonts = $this->getCriticalFonts();
        
        foreach ($fonts as $font) {
            $attributes = [
                'rel="preload"',
                'href="' . esc_url($font['url']) . '"',
                'as="font"',
                'type="' . esc_attr($font['type']) . '"'
            ];

            if (!empty($font['crossorigin'])) {
                $attributes[] = 'crossorigin';
            }

            echo '<link ' . implode(' ', $attributes) . '>' . "\n";
        }
    }

    /**
     * Add font-display optimization
     */
    private function addFontDisplayOptimization(): void
    {
        echo '<style>
            @font-face { font-display: swap !important; }
            * { font-display: swap !important; }
        </style>' . "\n";
    }

    /**
     * Check if CSS is critical
     */
    private function isCriticalCSS(string $handle, string $href): bool
    {
        $criticalHandles = [
            'theme-style',
            'style',
            'main-style',
            'critical-css'
        ];

        $criticalPatterns = [
            'style.css',
            'main.css',
            'critical.css',
            'above-the-fold.css'
        ];

        // Check handle
        foreach ($criticalHandles as $criticalHandle) {
            if (strpos($handle, $criticalHandle) !== false) {
                return true;
            }
        }

        // Check URL patterns
        foreach ($criticalPatterns as $pattern) {
            if (strpos($href, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get critical resources for preloading
     */
    private function getCriticalResources(): array
    {
        $settings = $this->getSettings();
        $resources = $settings['critical_resources'] ?? [];

        // Auto-detect critical resources
        $autoDetected = $this->autoDetectCriticalResources();
        $resources = array_merge($resources, $autoDetected);

        return array_filter($resources, function($resource) {
            return !empty($resource['url']) && !empty($resource['as']);
        });
    }

    /**
     * Auto-detect critical resources
     */
    private function autoDetectCriticalResources(): array
    {
        $resources = [];

        // Theme critical fonts
        $themeUri = get_stylesheet_directory_uri();
        $themeDir = get_stylesheet_directory();

        // Check for common font files
        $fontFiles = glob($themeDir . '/fonts/*.{woff2,woff}', GLOB_BRACE);
        if (!empty($fontFiles)) {
            foreach (array_slice($fontFiles, 0, 2) as $fontFile) {
                $basename = basename($fontFile);
                $resources[] = [
                    'url' => $themeUri . '/fonts/' . $basename,
                    'as' => 'font',
                    'type' => $this->getFontMimeType($fontFile),
                    'crossorigin' => false
                ];
            }
        }

        return $resources;
    }

    /**
     * Get critical fonts
     */
    private function getCriticalFonts(): array
    {
        $settings = $this->getSettings();
        return $settings['critical_fonts'] ?? [];
    }

    /**
     * Get resource hints
     */
    private function getResourceHints(): array
    {
        return [
            [
                'rel' => 'dns-prefetch',
                'href' => '//fonts.googleapis.com'
            ],
            [
                'rel' => 'dns-prefetch', 
                'href' => '//fonts.gstatic.com'
            ],
            [
                'rel' => 'dns-prefetch',
                'href' => '//assets.brevo.com'
            ]
        ];
    }

    /**
     * Get font MIME type
     */
    private function getFontMimeType(string $file): string
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        $types = [
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf'
        ];

        return $types[$ext] ?? 'font/woff2';
    }

    /**
     * Check if optimization is enabled
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
            'optimize_fonts' => false,
            'defer_css' => false,
            'critical_css' => '',
            'critical_resources' => [],
            'critical_fonts' => [],
        ];

        $settings = $this->getOption(self::OPTION, []);
        return is_array($settings) ? array_merge($defaults, $settings) : $defaults;
    }

    /**
     * Update settings
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = array_merge($current, $settings);

        $result = $this->setOption(self::OPTION, $updated);

        if ($result) {
            Logger::info('Render blocking optimization settings updated', $updated);
            do_action('fp_ps_render_blocking_optimization_updated', $updated);
        }

        return $result;
    }

    /**
     * Get status for admin display
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        
        return [
            'enabled' => $this->isEnabled(),
            'font_optimization' => !empty($settings['optimize_fonts']),
            'css_deferring' => !empty($settings['defer_css']),
            'critical_css_configured' => !empty($settings['critical_css']),
            'critical_resources_count' => count($settings['critical_resources'] ?? []),
            'critical_fonts_count' => count($settings['critical_fonts'] ?? []),
        ];
    }

    /**
     * Get option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }

    /**
     * Set option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $value Value to set
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value);
        }
        return update_option($key, $value);
    }
}
