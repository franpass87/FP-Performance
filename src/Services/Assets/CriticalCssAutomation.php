<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

use function add_action;
use function get_option;
use function update_option;
use function wp_parse_args;
use function wp_remote_get;
use function wp_remote_post;

/**
 * Critical CSS Automation
 *
 * Automatically extracts and inlines critical CSS for above-the-fold content
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class CriticalCssAutomation
{
    private const OPTION = 'fp_ps_critical_css_auto';
    private const CACHE_KEY = 'fp_ps_critical_css_cache';

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Generate critical CSS on theme/plugin activation
        add_action('after_switch_theme', [$this, 'regenerateCriticalCss']);
        add_action('activated_plugin', [$this, 'regenerateCriticalCss']);

        // Schedule periodic regeneration
        if (!wp_next_scheduled('fp_ps_regenerate_critical_css')) {
            wp_schedule_event(time(), 'weekly', 'fp_ps_regenerate_critical_css');
        }

        add_action('fp_ps_regenerate_critical_css', [$this, 'regenerateCriticalCss']);
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,method:string,api_key:string,viewports:array,pages:array,auto_regenerate:bool}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'method' => 'internal', // internal, criticalcss.com, penthouse
            'api_key' => '',
            'viewports' => [
                'mobile' => ['width' => 375, 'height' => 667],
                'desktop' => ['width' => 1920, 'height' => 1080],
            ],
            'pages' => ['home', 'single', 'archive'],
            'auto_regenerate' => true,
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

        $new = [
            'enabled' => !empty($settings['enabled']),
            'method' => $settings['method'] ?? $current['method'],
            'api_key' => $settings['api_key'] ?? $current['api_key'],
            'viewports' => $settings['viewports'] ?? $current['viewports'],
            'pages' => $settings['pages'] ?? $current['pages'],
            'auto_regenerate' => isset($settings['auto_regenerate']) ? !empty($settings['auto_regenerate']) : $current['auto_regenerate'],
        ];

        update_option(self::OPTION, $new);
    }

    /**
     * Regenerate critical CSS
     */
    public function regenerateCriticalCss(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        Logger::info('Regenerating critical CSS');

        $criticalCss = [];

        foreach ($settings['pages'] as $pageType) {
            $url = $this->getPageUrl($pageType);
            
            if (!$url) {
                continue;
            }

            $css = $this->extractCriticalCss($url, $settings);

            if ($css) {
                $criticalCss[$pageType] = $css;
                Logger::info('Critical CSS generated', ['page_type' => $pageType, 'size' => strlen($css)]);
            }
        }

        // Cache the results
        update_option(self::CACHE_KEY, $criticalCss);

        do_action('fp_ps_critical_css_regenerated', $criticalCss);
    }

    /**
     * Extract critical CSS for a URL
     *
     * @param string $url Page URL
     * @param array $settings Extraction settings
     * @return string|null Critical CSS or null on failure
     */
    private function extractCriticalCss(string $url, array $settings): ?string
    {
        $method = $settings['method'];

        try {
            if ($method === 'internal') {
                return $this->extractInternal($url, $settings);
            } elseif ($method === 'criticalcss.com') {
                return $this->extractViaCriticalCssCom($url, $settings);
            }
        } catch (\Exception $e) {
            Logger::error('Critical CSS extraction failed', [
                'url' => $url,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Internal critical CSS extraction (simplified)
     *
     * @param string $url Page URL
     * @param array $settings Settings
     * @return string|null Critical CSS
     */
    private function extractInternal(string $url, array $settings): ?string
    {
        // Fetch page HTML
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            Logger::error('Failed to fetch page', ['url' => $url, 'error' => $response->get_error_message()]);
            return null;
        }

        $html = wp_remote_retrieve_body($response);

        // Extract all CSS links
        preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);
        
        $criticalCss = '';

        foreach ($matches[1] as $cssUrl) {
            // Make URL absolute
            if (strpos($cssUrl, 'http') !== 0) {
                $cssUrl = home_url($cssUrl);
            }

            // Fetch CSS
            $cssResponse = wp_remote_get($cssUrl, ['timeout' => 10, 'sslverify' => false]);
            
            if (is_wp_error($cssResponse)) {
                continue;
            }

            $css = wp_remote_retrieve_body($cssResponse);

            // Extract above-the-fold CSS (simplified heuristic)
            // In production, you'd use a proper CSS parser
            $critical = $this->extractAboveFoldCss($css, $html);
            
            $criticalCss .= $critical . "\n";
        }

        // Minify
        $criticalCss = $this->minifyCss($criticalCss);

        return $criticalCss;
    }

    /**
     * Extract above-the-fold CSS (simplified heuristic)
     *
     * @param string $css Full CSS
     * @param string $html Page HTML
     * @return string Critical CSS
     */
    private function extractAboveFoldCss(string $css, string $html): string
    {
        // This is a simplified implementation
        // In production, use a proper CSS parser and DOM analyzer
        
        $critical = '';

        // Extract selectors that match elements in HTML
        preg_match_all('/([^{]+)\{([^}]+)\}/s', $css, $rules);

        foreach ($rules[0] as $index => $rule) {
            $selector = trim($rules[1][$index]);
            $properties = trim($rules[2][$index]);

            // Skip @media queries for large screens
            if (strpos($selector, '@media') !== false && strpos($selector, 'min-width: 1024px') !== false) {
                continue;
            }

            // Include common critical selectors
            if ($this->isCriticalSelector($selector)) {
                $critical .= $selector . '{' . $properties . '}';
                continue;
            }

            // Check if selector matches elements in HTML (simplified)
            $cleanSelector = preg_replace('/[^a-zA-Z0-9\-_#.]/', '', explode(' ', $selector)[0]);
            
            if (!empty($cleanSelector) && stripos($html, $cleanSelector) !== false) {
                $critical .= $selector . '{' . $properties . '}';
            }
        }

        return $critical;
    }

    /**
     * Check if selector is critical
     *
     * @param string $selector CSS selector
     * @return bool True if critical
     */
    private function isCriticalSelector(string $selector): bool
    {
        $criticalSelectors = [
            'body', 'html', 'header', 'nav', 'h1', 'h2', 'h3',
            '.header', '.navbar', '.menu', '.logo', '.hero',
            '#header', '#nav', '#menu', '#logo',
        ];

        foreach ($criticalSelectors as $critical) {
            if (stripos($selector, $critical) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract via CriticalCSS.com API
     *
     * @param string $url Page URL
     * @param array $settings Settings
     * @return string|null Critical CSS
     */
    private function extractViaCriticalCssCom(string $url, array $settings): ?string
    {
        if (empty($settings['api_key'])) {
            Logger::warning('CriticalCSS.com API key not configured');
            return null;
        }

        $apiUrl = 'https://api.criticalcss.com/generate';

        $response = wp_remote_post($apiUrl, [
            'timeout' => 60,
            'headers' => [
                'Authorization' => 'Bearer ' . $settings['api_key'],
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'url' => $url,
                'width' => $settings['viewports']['desktop']['width'],
                'height' => $settings['viewports']['desktop']['height'],
            ]),
        ]);

        if (is_wp_error($response)) {
            Logger::error('CriticalCSS.com API error', ['error' => $response->get_error_message()]);
            return null;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        return $data['criticalCss'] ?? null;
    }

    /**
     * Minify CSS
     *
     * @param string $css CSS to minify
     * @return string Minified CSS
     */
    private function minifyCss(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
        
        return trim($css);
    }

    /**
     * Get page URL for type
     *
     * @param string $pageType Page type (home, single, archive)
     * @return string|null Page URL
     */
    private function getPageUrl(string $pageType): ?string
    {
        switch ($pageType) {
            case 'home':
                return home_url('/');
                
            case 'single':
                $post = get_posts(['numberposts' => 1, 'post_type' => 'post']);
                return !empty($post) ? get_permalink($post[0]) : null;
                
            case 'page':
                $page = get_posts(['numberposts' => 1, 'post_type' => 'page']);
                return !empty($page) ? get_permalink($page[0]) : null;
                
            case 'archive':
                $categories = get_categories(['number' => 1]);
                return !empty($categories) ? get_category_link($categories[0]->term_id) : null;
                
            default:
                return null;
        }
    }

    /**
     * Get cached critical CSS
     *
     * @param string $pageType Page type
     * @return string|null Critical CSS
     */
    public function getCriticalCss(string $pageType = 'home'): ?string
    {
        $cache = get_option(self::CACHE_KEY, []);
        return $cache[$pageType] ?? null;
    }

    /**
     * Get all cached critical CSS
     *
     * @return array Cached critical CSS by page type
     */
    public function getAllCriticalCss(): array
    {
        return get_option(self::CACHE_KEY, []);
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        delete_option(self::CACHE_KEY);
        Logger::info('Critical CSS cache cleared');
    }

    /**
     * Get status
     *
     * @return array{enabled:bool,method:string,cached_pages:int,last_generated:int|null}
     */
    public function status(): array
    {
        $settings = $this->settings();
        $cache = $this->getAllCriticalCss();

        return [
            'enabled' => $settings['enabled'],
            'method' => $settings['method'],
            'cached_pages' => count($cache),
            'last_generated' => get_option('fp_ps_critical_css_last_generated', null),
        ];
    }
}
