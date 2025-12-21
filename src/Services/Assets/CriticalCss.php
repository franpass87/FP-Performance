<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger as StaticLogger;
use FP\PerfSuite\Utils\AssetLockManager;

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
     * Register hooks
     */
    public function register(): void
    {
        if ($this->isEnabled() && !is_admin()) {
            add_action('wp_head', [$this, 'inlineCriticalCss'], 19);
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
        $css = $this->getOption(self::OPTION, '');
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

        // Use asset lock to prevent race conditions
        $result = AssetLockManager::executeWithLock('critical_css', '', function() use ($css) {
            $this->setOption(self::OPTION, $css);
            return true;
        });

        if (!$result) {
            return [
                'success' => false,
                'error' => __('Critical CSS update in progress by another process', 'fp-performance-suite'),
            ];
        }

        // FIX: Reinizializza il servizio per applicare immediatamente le modifiche
        $this->forceInit();

        $this->log('info', 'Critical CSS updated', [
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
     * Forza l'inizializzazione del servizio
     * FIX: Ricarica le impostazioni e reinizializza il servizio
     */
    public function forceInit(): void
    {
        // Rimuovi hook esistenti
        remove_action('wp_head', [$this, 'inlineCriticalCss'], 19);
        
        // Reinizializza
        $this->register();
    }

    /**
     * Clear critical CSS
     */
    public function clear(): void
    {
        delete_option(self::OPTION);
        
        // FIX: Reinizializza il servizio dopo la pulizia
        $this->forceInit();
        
        $this->log('info', 'Critical CSS cleared');
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

        $this->log('debug', 'Critical CSS inlined', ['size' => strlen($css)]);
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

        // SICUREZZA: Aggiungiamo timeout e SSL verification per sicurezza
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'sslverify' => true,
            'user-agent' => 'FP Performance Suite/1.0',
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ]
        ]);

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

        // SICUREZZA: Check for suspicious content con regex sicura
        if (preg_match('/<\?(?:php)?|<script/i', $css)) {
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

        // Remove comments first to avoid issues
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css) ?? $css;

        // Split into rules using a more robust regex that handles nested braces
        // Match selector { properties } including media queries
        if (preg_match_all('/([^{]+)\{([^{}]*(?:\{[^}]*\}[^{}]*)*)\}/s', $css, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $selector = trim($match[1]);
                $properties = trim($match[2]);
                
                if (empty($selector) || empty($properties)) {
                    continue;
                }

                foreach ($aboveFoldPatterns as $pattern) {
                    if (stripos($selector, $pattern) !== false) {
                        // Properly reconstruct the CSS rule
                        $filtered[] = $selector . '{' . $properties . '}';
                        break;
                    }
                }
                
                // Limit to prevent excessive CSS
                if (count($filtered) >= 50) {
                    break;
                }
            }
        }

        return implode("\n", $filtered);
    }

    /**
     * Minify CSS (basic minification)
     */
    private function minifyCss(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css) ?? $css;

        // Remove whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css) ?? $css;
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css) ?? $css;

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
