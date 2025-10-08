<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

/**
 * Critical CSS management service
 *
 * Allows administrators to define critical CSS that should be inlined
 * for above-the-fold content optimization.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CriticalCss
{
    private const OPTION = 'fp_ps_critical_css';
    private const MAX_SIZE = 50000; // 50KB max

    /**
     * Register hooks
     */
    public function register(): void
    {
        if ($this->isEnabled() && !is_admin()) {
            add_action('wp_head', [$this, 'inlineCriticalCss'], 1);
        }
    }

    /**
     * Check if critical CSS is enabled and available
     */
    public function isEnabled(): bool
    {
        $css = $this->get();
        return !empty($css);
    }

    /**
     * Get stored critical CSS
     */
    public function get(): string
    {
        $css = get_option(self::OPTION, '');
        return is_string($css) ? trim($css) : '';
    }

    /**
     * Update critical CSS
     *
     * @param string $css The critical CSS to store
     * @return array Result with success/error
     */
    public function update(string $css): array
    {
        $css = trim($css);

        // Validate size
        if (strlen($css) > self::MAX_SIZE) {
            return [
                'success' => false,
                'error' => sprintf(
                    __('Critical CSS is too large (max %s KB)', 'fp-performance-suite'),
                    number_format(self::MAX_SIZE / 1024, 0)
                ),
            ];
        }

        // Basic CSS validation
        if (!empty($css) && !$this->isValidCss($css)) {
            return [
                'success' => false,
                'error' => __('Invalid CSS syntax detected', 'fp-performance-suite'),
            ];
        }

        update_option(self::OPTION, $css);

        Logger::info('Critical CSS updated', [
            'size' => strlen($css),
            'enabled' => !empty($css),
        ]);

        do_action('fp_ps_critical_css_updated', $css);

        return [
            'success' => true,
            'size' => strlen($css),
            'enabled' => !empty($css),
        ];
    }

    /**
     * Clear critical CSS
     */
    public function clear(): void
    {
        delete_option(self::OPTION);
        Logger::info('Critical CSS cleared');
        do_action('fp_ps_critical_css_cleared');
    }

    /**
     * Inline critical CSS in head
     */
    public function inlineCriticalCss(): void
    {
        $css = $this->get();

        if (empty($css)) {
            return;
        }

        // Allow filtering before output
        $css = apply_filters('fp_ps_critical_css_output', $css);

        echo "\n<!-- FP Performance Suite - Critical CSS -->\n";
        echo '<style id="fp-critical-css">' . "\n";
        echo $css;
        echo "\n</style>\n";
        echo "<!-- End Critical CSS -->\n";

        Logger::debug('Critical CSS inlined', ['size' => strlen($css)]);
    }

    /**
     * Generate critical CSS from current page (basic implementation)
     *
     * This is a placeholder for more advanced implementations using
     * services like critical.css, penthouse, or puppeteer
     *
     * @param string $url URL to analyze
     * @return array Result with CSS or error
     */
    public function generate(string $url): array
    {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'success' => false,
                'error' => __('Invalid URL', 'fp-performance-suite'),
            ];
        }

        // Fetch page content
        $response = wp_remote_get($url, ['timeout' => 30]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => $response->get_error_message(),
            ];
        }

        $html = wp_remote_retrieve_body($response);

        // Extract inline styles and linked stylesheets
        $criticalCss = $this->extractCriticalStyles($html, $url);

        return [
            'success' => true,
            'css' => $criticalCss,
            'size' => strlen($criticalCss),
            'note' => __('This is a basic extraction. For production, consider using specialized tools.', 'fp-performance-suite'),
        ];
    }

    /**
     * Basic validation for CSS syntax
     */
    private function isValidCss(string $css): bool
    {
        // Check for balanced braces
        $openBraces = substr_count($css, '{');
        $closeBraces = substr_count($css, '}');

        if ($openBraces !== $closeBraces) {
            return false;
        }

        // Check for suspicious content (PHP tags, script tags)
        if (preg_match('/<\?(php)?|\<script/i', $css)) {
            return false;
        }

        return true;
    }

    /**
     * Extract critical styles from HTML (basic implementation)
     */
    private function extractCriticalStyles(string $html, string $baseUrl): string
    {
        $criticalCss = [];

        // Extract inline styles from <style> tags
        if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html, $matches)) {
            foreach ($matches[1] as $css) {
                $criticalCss[] = $css;
            }
        }

        // Extract styles from <link> tags (simplified - in production use proper CSS parser)
        if (preg_match_all('/<link[^>]*rel=["\']stylesheet["\'][^>]*href=["\'](.*?)["\']/i', $html, $matches)) {
            foreach (array_slice($matches[1], 0, 2) as $href) { // Only first 2 stylesheets
                $cssUrl = $this->resolveUrl($href, $baseUrl);
                $cssContent = $this->fetchCss($cssUrl);
                if ($cssContent) {
                    // Extract only selectors for above-the-fold elements (simplified)
                    $filtered = $this->filterAboveFoldCss($cssContent);
                    $criticalCss[] = $filtered;
                }
            }
        }

        $combined = implode("\n\n", $criticalCss);

        // Minify
        return $this->minifyCss($combined);
    }

    /**
     * Resolve relative URLs
     */
    private function resolveUrl(string $url, string $baseUrl): string
    {
        if (strpos($url, 'http') === 0) {
            return $url;
        }

        $base = parse_url($baseUrl);
        $scheme = $base['scheme'] ?? 'https';
        $host = $base['host'] ?? '';

        if (strpos($url, '//') === 0) {
            return $scheme . ':' . $url;
        }

        if (strpos($url, '/') === 0) {
            return $scheme . '://' . $host . $url;
        }

        return $baseUrl . '/' . $url;
    }

    /**
     * Fetch CSS content from URL
     */
    private function fetchCss(string $url): ?string
    {
        $response = wp_remote_get($url, ['timeout' => 10]);

        if (is_wp_error($response)) {
            return null;
        }

        return wp_remote_retrieve_body($response);
    }

    /**
     * Filter CSS to include only above-the-fold styles (basic heuristic)
     */
    private function filterAboveFoldCss(string $css): string
    {
        // Keep only common above-the-fold selectors (very simplified)
        $aboveFoldPatterns = [
            'body', 'html', 'header', 'nav', '.header', '#header',
            'h1', 'h2', '.hero', '.banner', '.main-menu', '.logo'
        ];

        $filtered = [];

        // Split into rules (very basic)
        $rules = preg_split('/}/', $css);

        foreach ($rules as $rule) {
            $rule = trim($rule);
            if (empty($rule)) {
                continue;
            }

            foreach ($aboveFoldPatterns as $pattern) {
                if (stripos($rule, $pattern) !== false) {
                    $filtered[] = $rule . '}';
                    break;
                }
            }
        }

        return implode("\n", array_slice($filtered, 0, 50)); // Max 50 rules
    }

    /**
     * Minify CSS (basic minification)
     */
    private function minifyCss(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // Remove whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);

        return trim($css);
    }

    /**
     * Get status information
     */
    public function status(): array
    {
        $css = $this->get();
        $size = strlen($css);

        return [
            'enabled' => !empty($css),
            'size' => $size,
            'size_kb' => round($size / 1024, 2),
            'max_size_kb' => self::MAX_SIZE / 1024,
            'usage_percent' => $size > 0 ? round(($size / self::MAX_SIZE) * 100, 1) : 0,
        ];
    }
}
