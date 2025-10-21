<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

use function add_action;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * HTTP/2 Server Push Manager
 *
 * Automatically pushes critical assets via HTTP/2 Server Push
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class Http2ServerPush
{
    private const OPTION = 'fp_ps_http2_push';
    private array $pushedResources = [];

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Push critical resources early
        add_action('wp_head', [$this, 'pushCriticalResources'], 1);
        
        // Send Link headers for pushed resources
        add_action('send_headers', [$this, 'sendLinkHeaders']);
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,push_css:bool,push_js:bool,push_fonts:bool,push_images:bool,max_resources:int,critical_only:bool}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'push_css' => true,
            'push_js' => true,
            'push_fonts' => true,
            'push_images' => false,
            'max_resources' => 10,
            'critical_only' => true,
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

        $maxResources = isset($settings['max_resources']) ? (int)$settings['max_resources'] : $current['max_resources'];
        $maxResources = max(1, min(20, $maxResources));

        $new = [
            'enabled' => !empty($settings['enabled']),
            'push_css' => isset($settings['push_css']) ? !empty($settings['push_css']) : $current['push_css'],
            'push_js' => isset($settings['push_js']) ? !empty($settings['push_js']) : $current['push_js'],
            'push_fonts' => isset($settings['push_fonts']) ? !empty($settings['push_fonts']) : $current['push_fonts'],
            'push_images' => isset($settings['push_images']) ? !empty($settings['push_images']) : $current['push_images'],
            'max_resources' => $maxResources,
            'critical_only' => isset($settings['critical_only']) ? !empty($settings['critical_only']) : $current['critical_only'],
        ];

        update_option(self::OPTION, $new);
    }

    /**
     * Push critical resources
     */
    public function pushCriticalResources(): void
    {
        global $wp_scripts, $wp_styles;

        $settings = $this->settings();

        // Collect resources to push
        $resources = [];

        // Push CSS
        if ($settings['push_css'] && $wp_styles instanceof \WP_Styles) {
            foreach ($wp_styles->queue as $handle) {
                if (count($resources) >= $settings['max_resources']) {
                    break;
                }

                if (!isset($wp_styles->registered[$handle])) {
                    continue;
                }

                $style = $wp_styles->registered[$handle];
                
                // Skip if not critical and critical_only is enabled
                if ($settings['critical_only'] && !$this->isCriticalResource($handle, 'style')) {
                    continue;
                }

                $src = $this->getResourceUrl($style->src);
                if ($src) {
                    $resources[] = [
                        'url' => $src,
                        'as' => 'style',
                        'type' => 'text/css',
                    ];
                }
            }
        }

        // Push JavaScript
        if ($settings['push_js'] && $wp_scripts instanceof \WP_Scripts) {
            foreach ($wp_scripts->queue as $handle) {
                if (count($resources) >= $settings['max_resources']) {
                    break;
                }

                if (!isset($wp_scripts->registered[$handle])) {
                    continue;
                }

                $script = $wp_scripts->registered[$handle];
                
                if ($settings['critical_only'] && !$this->isCriticalResource($handle, 'script')) {
                    continue;
                }

                $src = $this->getResourceUrl($script->src);
                if ($src) {
                    $resources[] = [
                        'url' => $src,
                        'as' => 'script',
                        'type' => 'text/javascript',
                    ];
                }
            }
        }

        // Push fonts (from theme/plugin directories)
        if ($settings['push_fonts']) {
            $fonts = $this->detectCriticalFonts();
            foreach ($fonts as $font) {
                if (count($resources) >= $settings['max_resources']) {
                    break;
                }
                $resources[] = $font;
            }
        }

        // Store for header sending
        $this->pushedResources = $resources;

        // Apply filter for customization
        $this->pushedResources = apply_filters('fp_ps_http2_push_resources', $this->pushedResources, $settings);

        Logger::debug('HTTP/2 Server Push prepared', [
            'count' => count($this->pushedResources),
            'resources' => array_map(function($r) { return basename($r['url']); }, $this->pushedResources),
        ]);
    }

    /**
     * Send Link headers for HTTP/2 push
     */
    public function sendLinkHeaders(): void
    {
        if (empty($this->pushedResources)) {
            return;
        }

        // Check if headers already sent
        if (headers_sent()) {
            Logger::warning('HTTP/2 push headers not sent - headers already sent');
            return;
        }

        foreach ($this->pushedResources as $resource) {
            $header = sprintf(
                'Link: <%s>; rel=preload; as=%s',
                $resource['url'],
                $resource['as']
            );

            // Add type if specified
            if (!empty($resource['type'])) {
                $header .= sprintf('; type=%s', $resource['type']);
            }

            // Add crossorigin for fonts
            if ($resource['as'] === 'font') {
                $header .= '; crossorigin';
            }

            // Add nopush attribute for HTTP/1.1 clients
            // Server will automatically push for HTTP/2 clients
            header($header, false);
        }

        Logger::info('HTTP/2 Server Push headers sent', [
            'count' => count($this->pushedResources),
        ]);
    }

    /**
     * Check if resource is critical
     *
     * @param string $handle Resource handle
     * @param string $type 'script' or 'style'
     * @return bool True if critical
     */
    private function isCriticalResource(string $handle, string $type): bool
    {
        // Common critical resource handles
        $criticalHandles = [
            // WordPress core
            'wp-polyfill', 'wp-block-library', 'wp-block-library-theme',
            
            // Common themes
            'theme-style', 'main-stylesheet', 'global-styles',
            
            // Common plugins
            'elementor-frontend', 'contact-form-7',
        ];

        // Allow filtering
        $criticalHandles = apply_filters('fp_ps_http2_critical_handles', $criticalHandles, $type);

        return in_array($handle, $criticalHandles, true);
    }

    /**
     * Get absolute resource URL
     *
     * @param string $src Resource source
     * @return string|null Absolute URL or null
     */
    private function getResourceUrl(string $src): ?string
    {
        if (empty($src)) {
            return null;
        }

        // Already absolute
        if (strpos($src, 'http://') === 0 || strpos($src, 'https://') === 0) {
            return $src;
        }

        // Relative to site URL
        if (strpos($src, '/') === 0) {
            return home_url($src);
        }

        // Relative to wp-includes or wp-content
        if (strpos($src, 'wp-includes/') === 0 || strpos($src, 'wp-content/') === 0) {
            return site_url($src);
        }

        return null;
    }

    /**
     * Detect critical fonts to push
     *
     * @return array Array of font resources
     */
    private function detectCriticalFonts(): array
    {
        $fonts = [];

        // Look for font preload links in the document
        // This is a simplified version - in production, you might want to parse CSS
        $themeDir = get_stylesheet_directory_uri();
        
        // Common font paths
        $commonFontPaths = [
            '/fonts/main.woff2',
            '/assets/fonts/main.woff2',
            '/fonts/primary.woff2',
        ];

        foreach ($commonFontPaths as $path) {
            $url = $themeDir . $path;
            
            // Check if file exists (we can't easily check remote URLs without performance hit)
            // So we rely on filtering or configuration
            
            $fonts[] = [
                'url' => $url,
                'as' => 'font',
                'type' => 'font/woff2',
            ];
        }

        // Allow themes/plugins to register their critical fonts
        return apply_filters('fp_ps_http2_critical_fonts', $fonts);
    }

    /**
     * Manually add resource to push
     *
     * @param string $url Resource URL
     * @param string $as Resource type (script, style, font, image, etc.)
     * @param string $type MIME type
     */
    public function addResource(string $url, string $as, string $type = ''): void
    {
        $this->pushedResources[] = [
            'url' => $url,
            'as' => $as,
            'type' => $type,
        ];
    }

    /**
     * Get pushed resources
     *
     * @return array List of pushed resources
     */
    public function getPushedResources(): array
    {
        return $this->pushedResources;
    }

    /**
     * Check if HTTP/2 is available
     *
     * @return bool True if HTTP/2 is supported
     */
    public function isHttp2Available(): bool
    {
        // Check server protocol
        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? '';
        
        if (strpos($protocol, 'HTTP/2') !== false) {
            return true;
        }

        // Check via headers (some servers report differently)
        if (function_exists('apache_response_headers')) {
            $headers = apache_response_headers();
            if (isset($headers['X-HTTP-Version']) && strpos($headers['X-HTTP-Version'], '2') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get status information
     *
     * @return array{enabled:bool,http2_available:bool,resources_count:int}
     */
    public function status(): array
    {
        $settings = $this->settings();

        return [
            'enabled' => $settings['enabled'],
            'http2_available' => $this->isHttp2Available(),
            'resources_count' => count($this->pushedResources),
        ];
    }
}
