<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

use function add_filter;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * Smart Asset Delivery
 *
 * Network-aware asset delivery with adaptive quality based on connection speed
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class SmartAssetDelivery
{
    private const OPTION = 'fp_ps_smart_delivery';

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Filter image sources for adaptive quality
        add_filter('wp_get_attachment_image_src', [$this, 'filterImageQuality'], 10, 4);
        
        // Add Client Hints
        add_action('send_headers', [$this, 'sendClientHints']);
        
        // Inject adaptive loading script
        add_action('wp_head', [$this, 'injectAdaptiveScript'], 1);
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,detect_connection:bool,save_data_mode:bool,adaptive_images:bool,adaptive_videos:bool,quality_slow:int,quality_moderate:int,quality_fast:int}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'detect_connection' => true,
            'save_data_mode' => true,
            'adaptive_images' => true,
            'adaptive_videos' => true,
            'quality_slow' => 50,      // < 1 Mbps (slow-2g, 2g)
            'quality_moderate' => 70,  // 1-5 Mbps (3g)
            'quality_fast' => 85,      // > 5 Mbps (4g)
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

        $qualitySlow = isset($settings['quality_slow']) ? (int)$settings['quality_slow'] : $current['quality_slow'];
        $qualitySlow = max(20, min(100, $qualitySlow));

        $qualityModerate = isset($settings['quality_moderate']) ? (int)$settings['quality_moderate'] : $current['quality_moderate'];
        $qualityModerate = max(20, min(100, $qualityModerate));

        $qualityFast = isset($settings['quality_fast']) ? (int)$settings['quality_fast'] : $current['quality_fast'];
        $qualityFast = max(20, min(100, $qualityFast));

        $new = [
            'enabled' => !empty($settings['enabled']),
            'detect_connection' => isset($settings['detect_connection']) ? !empty($settings['detect_connection']) : $current['detect_connection'],
            'save_data_mode' => isset($settings['save_data_mode']) ? !empty($settings['save_data_mode']) : $current['save_data_mode'],
            'adaptive_images' => isset($settings['adaptive_images']) ? !empty($settings['adaptive_images']) : $current['adaptive_images'],
            'adaptive_videos' => isset($settings['adaptive_videos']) ? !empty($settings['adaptive_videos']) : $current['adaptive_videos'],
            'quality_slow' => $qualitySlow,
            'quality_moderate' => $qualityModerate,
            'quality_fast' => $qualityFast,
        ];

        update_option(self::OPTION, $new);
    }

    /**
     * Filter image quality based on connection
     *
     * @param array|false $image Image data
     * @param int $attachment_id Attachment ID
     * @param string|int[] $size Image size
     * @param bool $icon Whether icon
     * @return array|false Modified image data
     */
    public function filterImageQuality($image, int $attachment_id, $size, bool $icon)
    {
        if (!is_array($image) || empty($image[0])) {
            return $image;
        }

        $settings = $this->settings();
        
        if (!$settings['adaptive_images']) {
            return $image;
        }

        $connectionType = $this->detectConnectionType();

        // Add connection type as query parameter for server-side processing
        $image[0] = add_query_arg('fp_connection', $connectionType, $image[0]);

        return $image;
    }

    /**
     * Detect connection type from headers
     *
     * @return string Connection type (slow, moderate, fast)
     */
    private function detectConnectionType(): string
    {
        // Check Save-Data header
        if (!empty($_SERVER['HTTP_SAVE_DATA'])) {
            return 'slow';
        }

        // Check Network Information API hint
        $ect = $_SERVER['HTTP_ECT'] ?? '';
        
        if ($ect) {
            if (in_array($ect, ['slow-2g', '2g'], true)) {
                return 'slow';
            } elseif ($ect === '3g') {
                return 'moderate';
            } elseif ($ect === '4g') {
                return 'fast';
            }
        }

        // Default to fast if unknown
        return 'fast';
    }

    /**
     * Send Client Hints headers
     */
    public function sendClientHints(): void
    {
        if (headers_sent()) {
            return;
        }

        // Request permission for Client Hints
        header('Accept-CH: ECT, Save-Data, Downlink, RTT, Viewport-Width, Width', false);
        header('Accept-CH-Lifetime: 86400', false); // 24 hours
    }

    /**
     * Inject adaptive loading script
     */
    public function injectAdaptiveScript(): void
    {
        $settings = $this->settings();
        ?>
        <script>
        (function() {
            'use strict';
            
            // Detect connection and store in sessionStorage
            if ('connection' in navigator || 'mozConnection' in navigator || 'webkitConnection' in navigator) {
                var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                
                var connectionData = {
                    effectiveType: connection.effectiveType || 'unknown',
                    downlink: connection.downlink || 0,
                    rtt: connection.rtt || 0,
                    saveData: connection.saveData || false,
                };
                
                sessionStorage.setItem('fp_connection_data', JSON.stringify(connectionData));
                
                console.log('[FP Smart Delivery] Connection detected:', connectionData);
                
                // Add class to body for CSS targeting
                var connectionClass = 'connection-' + connectionData.effectiveType;
                document.documentElement.classList.add(connectionClass);
                
                if (connectionData.saveData) {
                    document.documentElement.classList.add('save-data-mode');
                }
                
                <?php if ($settings['save_data_mode']): ?>
                // Save Data mode optimizations
                if (connectionData.saveData) {
                    // Disable autoplay videos
                    document.addEventListener('DOMContentLoaded', function() {
                        var videos = document.querySelectorAll('video[autoplay]');
                        videos.forEach(function(video) {
                            video.removeAttribute('autoplay');
                            video.setAttribute('data-fp-autoplay-disabled', 'true');
                        });
                    });
                }
                <?php endif; ?>
                
                <?php if ($settings['adaptive_images']): ?>
                // Lazy load images on slow connections
                if (connectionData.effectiveType === 'slow-2g' || connectionData.effectiveType === '2g') {
                    document.addEventListener('DOMContentLoaded', function() {
                        var images = document.querySelectorAll('img:not([loading])');
                        images.forEach(function(img) {
                            img.setAttribute('loading', 'lazy');
                        });
                    });
                }
                <?php endif; ?>
                
                // Listen for connection changes
                connection.addEventListener('change', function() {
                    console.log('[FP Smart Delivery] Connection changed:', connection.effectiveType);
                    
                    // Reload page if connection improved significantly
                    var stored = JSON.parse(sessionStorage.getItem('fp_connection_data'));
                    if (stored && stored.effectiveType !== connection.effectiveType) {
                        var wasQuota = ['slow-2g', '2g'].includes(stored.effectiveType);
                        var isFast = ['3g', '4g'].includes(connection.effectiveType);
                        
                        if (wasQuota && isFast) {
                            console.log('[FP Smart Delivery] Connection improved, page reload suggested');
                            // Could auto-reload here if desired
                        }
                    }
                    
                    sessionStorage.setItem('fp_connection_data', JSON.stringify(connectionData));
                });
            }
            
            // Set cookie for server-side detection
            if ('connection' in navigator) {
                var ect = navigator.connection.effectiveType || 'unknown';
                document.cookie = 'fp_ect=' + ect + '; path=/; max-age=86400; SameSite=Lax';
            }
        })();
        </script>
        
        <style>
        /* Adaptive styles based on connection */
        .connection-slow-2g img,
        .connection-2g img {
            /* Force lower quality on slow connections */
            image-rendering: -webkit-optimize-contrast;
        }
        
        .save-data-mode {
            /* Reduce visual complexity in save-data mode */
        }
        
        .save-data-mode .background-video,
        .save-data-mode .hero-animation {
            display: none !important;
        }
        </style>
        <?php
    }

    /**
     * Get recommended quality for current connection
     *
     * @param string $connectionType Connection type
     * @return int Quality (0-100)
     */
    public function getRecommendedQuality(string $connectionType = ''): int
    {
        if (empty($connectionType)) {
            $connectionType = $this->detectConnectionType();
        }

        $settings = $this->settings();

        switch ($connectionType) {
            case 'slow':
                return $settings['quality_slow'];
            case 'moderate':
                return $settings['quality_moderate'];
            case 'fast':
            default:
                return $settings['quality_fast'];
        }
    }

    /**
     * Check if Save-Data mode is active
     *
     * @return bool True if Save-Data is enabled
     */
    public function isSaveDataMode(): bool
    {
        return !empty($_SERVER['HTTP_SAVE_DATA']) || !empty($_COOKIE['fp_save_data']);
    }

    /**
     * Get connection information
     *
     * @return array{type:string,save_data:bool,downlink:float,rtt:int}
     */
    public function getConnectionInfo(): array
    {
        return [
            'type' => $this->detectConnectionType(),
            'save_data' => $this->isSaveDataMode(),
            'downlink' => isset($_SERVER['HTTP_DOWNLINK']) ? (float)$_SERVER['HTTP_DOWNLINK'] : 0,
            'rtt' => isset($_SERVER['HTTP_RTT']) ? (int)$_SERVER['HTTP_RTT'] : 0,
        ];
    }

    /**
     * Get status
     *
     * @return array{enabled:bool,connection_type:string,save_data:bool}
     */
    public function status(): array
    {
        $settings = $this->settings();
        $info = $this->getConnectionInfo();

        return [
            'enabled' => $settings['enabled'],
            'connection_type' => $info['type'],
            'save_data' => $info['save_data'],
        ];
    }
}
