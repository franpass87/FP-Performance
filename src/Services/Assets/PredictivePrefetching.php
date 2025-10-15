<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

use function add_action;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * Predictive Prefetching
 *
 * Intelligently prefetches likely next pages based on user behavior
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class PredictivePrefetching
{
    private const OPTION = 'fp_ps_predictive_prefetch';
    private const STATS_OPTION = 'fp_ps_prefetch_stats';

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Inject prefetch script
        add_action('wp_footer', [$this, 'injectPrefetchScript'], 999);
        
        // Register REST endpoint for tracking
        add_action('rest_api_init', [$this, 'registerRestRoute']);
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,strategy:string,prefetch_on_hover:bool,prefetch_on_visible:bool,delay:int,max_prefetch:int,exclude_patterns:array}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'strategy' => 'hover', // hover, visible, viewport, mouse-tracking
            'prefetch_on_hover' => true,
            'prefetch_on_visible' => false,
            'delay' => 100, // ms delay before prefetch
            'max_prefetch' => 3, // Max simultaneous prefetches
            'exclude_patterns' => [
                '/wp-admin/',
                '/wp-login.php',
                '.pdf',
                '.zip',
                '.exe',
            ],
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

        $delay = isset($settings['delay']) ? (int)$settings['delay'] : $current['delay'];
        $delay = max(0, min(2000, $delay));

        $maxPrefetch = isset($settings['max_prefetch']) ? (int)$settings['max_prefetch'] : $current['max_prefetch'];
        $maxPrefetch = max(1, min(10, $maxPrefetch));

        $new = [
            'enabled' => !empty($settings['enabled']),
            'strategy' => $settings['strategy'] ?? $current['strategy'],
            'prefetch_on_hover' => isset($settings['prefetch_on_hover']) ? !empty($settings['prefetch_on_hover']) : $current['prefetch_on_hover'],
            'prefetch_on_visible' => isset($settings['prefetch_on_visible']) ? !empty($settings['prefetch_on_visible']) : $current['prefetch_on_visible'],
            'delay' => $delay,
            'max_prefetch' => $maxPrefetch,
            'exclude_patterns' => $settings['exclude_patterns'] ?? $current['exclude_patterns'],
        ];

        update_option(self::OPTION, $new);
    }

    /**
     * Inject prefetch script
     */
    public function injectPrefetchScript(): void
    {
        $settings = $this->settings();
        $strategy = $settings['strategy'];
        $delay = $settings['delay'];
        $maxPrefetch = $settings['max_prefetch'];
        $excludePatterns = json_encode($settings['exclude_patterns']);

        ?>
        <script>
        (function() {
            'use strict';
            
            var prefetchedUrls = new Set();
            var prefetchCount = 0;
            var maxPrefetch = <?php echo $maxPrefetch; ?>;
            var delay = <?php echo $delay; ?>;
            var excludePatterns = <?php echo $excludePatterns; ?>;
            
            // Check if URL should be excluded
            function shouldExclude(url) {
                for (var i = 0; i < excludePatterns.length; i++) {
                    if (url.includes(excludePatterns[i])) {
                        return true;
                    }
                }
                return false;
            }
            
            // Prefetch URL
            function prefetchUrl(url) {
                if (prefetchedUrls.has(url) || prefetchCount >= maxPrefetch) {
                    return;
                }
                
                if (shouldExclude(url)) {
                    return;
                }
                
                // Create prefetch link
                var link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url;
                link.as = 'document';
                
                link.onload = function() {
                    console.log('[FP Prefetch] Prefetched:', url);
                    prefetchCount--;
                };
                
                link.onerror = function() {
                    console.error('[FP Prefetch] Failed:', url);
                    prefetchCount--;
                };
                
                document.head.appendChild(link);
                prefetchedUrls.add(url);
                prefetchCount++;
            }
            
            // Get all internal links
            function getInternalLinks() {
                var links = document.querySelectorAll('a[href]');
                var internal = [];
                
                for (var i = 0; i < links.length; i++) {
                    var link = links[i];
                    var href = link.href;
                    
                    // Check if internal link
                    if (href && href.startsWith(window.location.origin) && !shouldExclude(href)) {
                        internal.push(link);
                    }
                }
                
                return internal;
            }
            
            <?php if ($settings['prefetch_on_hover']): ?>
            // Hover strategy
            var hoverTimeout;
            
            document.addEventListener('mouseover', function(e) {
                var link = e.target.closest('a[href]');
                if (!link) return;
                
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(function() {
                    prefetchUrl(link.href);
                }, delay);
            }, true);
            
            document.addEventListener('mouseout', function(e) {
                clearTimeout(hoverTimeout);
            }, true);
            <?php endif; ?>
            
            <?php if ($settings['prefetch_on_visible']): ?>
            // Intersection Observer strategy
            if ('IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var link = entry.target;
                            setTimeout(function() {
                                prefetchUrl(link.href);
                            }, delay);
                            observer.unobserve(link);
                        }
                    });
                }, {
                    rootMargin: '50px'
                });
                
                var links = getInternalLinks();
                links.forEach(function(link) {
                    observer.observe(link);
                });
            }
            <?php endif; ?>
            
            <?php if ($strategy === 'mouse-tracking'): ?>
            // Mouse movement prediction (experimental)
            var mouseVelocity = { x: 0, y: 0 };
            var lastMouse = { x: 0, y: 0, time: Date.now() };
            
            document.addEventListener('mousemove', function(e) {
                var now = Date.now();
                var dt = now - lastMouse.time;
                
                if (dt > 0) {
                    mouseVelocity.x = (e.clientX - lastMouse.x) / dt;
                    mouseVelocity.y = (e.clientY - lastMouse.y) / dt;
                }
                
                lastMouse = { x: e.clientX, y: e.clientY, time: now };
                
                // Predict which link mouse is heading towards
                var links = getInternalLinks();
                var bestLink = null;
                var bestScore = -1;
                
                links.forEach(function(link) {
                    var rect = link.getBoundingClientRect();
                    var centerX = rect.left + rect.width / 2;
                    var centerY = rect.top + rect.height / 2;
                    
                    var dx = centerX - e.clientX;
                    var dy = centerY - e.clientY;
                    
                    // Calculate if mouse velocity points towards link
                    var dotProduct = dx * mouseVelocity.x + dy * mouseVelocity.y;
                    
                    if (dotProduct > bestScore) {
                        bestScore = dotProduct;
                        bestLink = link;
                    }
                });
                
                if (bestLink && bestScore > 0.5) {
                    setTimeout(function() {
                        prefetchUrl(bestLink.href);
                    }, delay);
                }
            });
            <?php endif; ?>
            
            console.log('[FP Prefetch] Initialized with strategy:', '<?php echo $strategy; ?>');
        })();
        </script>
        <?php
    }

    /**
     * Register REST API route
     */
    public function registerRestRoute(): void
    {
        register_rest_route('fp-ps/v1', '/prefetch/track', [
            'methods' => 'POST',
            'callback' => [$this, 'trackPrefetch'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Track prefetch success
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response Response
     */
    public function trackPrefetch($request): \WP_REST_Response
    {
        $data = $request->get_json_params();
        
        if (empty($data['url'])) {
            return new \WP_REST_Response(['error' => 'Invalid data'], 400);
        }

        $url = esc_url_raw($data['url']);
        $success = !empty($data['success']);

        // Store stats
        $this->updateStats($url, $success);

        return new \WP_REST_Response(['success' => true], 200);
    }

    /**
     * Update prefetch statistics
     *
     * @param string $url URL
     * @param bool $success Whether prefetch was successful
     */
    private function updateStats(string $url, bool $success): void
    {
        $stats = get_option(self::STATS_OPTION, []);

        if (!isset($stats[$url])) {
            $stats[$url] = [
                'attempts' => 0,
                'successes' => 0,
            ];
        }

        $stats[$url]['attempts']++;
        if ($success) {
            $stats[$url]['successes']++;
        }

        // Keep only top 100 URLs
        if (count($stats) > 100) {
            arsort($stats);
            $stats = array_slice($stats, 0, 100, true);
        }

        update_option(self::STATS_OPTION, $stats, false);
    }

    /**
     * Get prefetch statistics
     *
     * @return array Statistics
     */
    public function getStats(): array
    {
        return get_option(self::STATS_OPTION, []);
    }

    /**
     * Get status
     *
     * @return array{enabled:bool,strategy:string,tracked_urls:int}
     */
    public function status(): array
    {
        $settings = $this->settings();
        $stats = $this->getStats();

        return [
            'enabled' => $settings['enabled'],
            'strategy' => $settings['strategy'],
            'tracked_urls' => count($stats),
        ];
    }
}
