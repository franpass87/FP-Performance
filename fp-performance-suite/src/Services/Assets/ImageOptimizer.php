<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

/**
 * Image Optimizer
 *
 * Ensures images have explicit width/height attributes to prevent
 * Cumulative Layout Shift (CLS) and improve PageSpeed scores.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ImageOptimizer
{
    private const OPTION = 'fp_ps_image_optimization';

    /**
     * Register hooks
     */
    public function register(): void
    {
        if (!is_admin() && $this->isEnabled()) {
            // Force width/height attributes on images
            if ($this->getSetting('force_dimensions', true)) {
                add_filter('wp_get_attachment_image_attributes', [$this, 'addImageDimensions'], 10, 3);
                add_filter('the_content', [$this, 'addDimensionsToContentImages'], 999);
            }

            // Add aspect-ratio CSS support
            if ($this->getSetting('add_aspect_ratio', true)) {
                add_action('wp_head', [$this, 'addAspectRatioStyles'], 1);
            }

            Logger::debug('ImageOptimizer registered');
        }
    }

    /**
     * Add width and height to attachment images
     */
    public function addImageDimensions(array $attr, $attachment, $size): array
    {
        // Skip if already has both dimensions
        if (isset($attr['width'], $attr['height'])) {
            return $attr;
        }

        // Get attachment metadata
        $metadata = wp_get_attachment_metadata($attachment->ID ?? 0);
        
        if (!is_array($metadata)) {
            return $attr;
        }

        // Handle different size requests
        if (is_string($size)) {
            // Named size (thumbnail, medium, large, etc.)
            if (isset($metadata['sizes'][$size])) {
                $sizeData = $metadata['sizes'][$size];
                $attr['width'] = $sizeData['width'] ?? null;
                $attr['height'] = $sizeData['height'] ?? null;
            } else {
                // Full size
                $attr['width'] = $metadata['width'] ?? null;
                $attr['height'] = $metadata['height'] ?? null;
            }
        } elseif (is_array($size) && count($size) === 2) {
            // Custom size array [width, height]
            $attr['width'] = $size[0];
            $attr['height'] = $size[1];
        } else {
            // Fallback to full size
            $attr['width'] = $metadata['width'] ?? null;
            $attr['height'] = $metadata['height'] ?? null;
        }

        // Remove if we couldn't determine dimensions
        if (empty($attr['width']) || empty($attr['height'])) {
            unset($attr['width'], $attr['height']);
        }

        return $attr;
    }

    /**
     * Add dimensions to images in post content
     */
    public function addDimensionsToContentImages(string $content): string
    {
        if (empty($content) || strpos($content, '<img') === false) {
            return $content;
        }

        // Match img tags without both width and height
        $content = preg_replace_callback(
            '/<img([^>]+)>/i',
            [$this, 'processSingleImage'],
            $content
        );

        return $content;
    }

    /**
     * Process single image tag
     */
    private function processSingleImage(array $matches): string
    {
        $imgTag = $matches[0];
        $attrs = $matches[1];

        // Check if already has both dimensions
        $hasWidth = preg_match('/\swidth\s*=\s*["\']?\d+["\']?/i', $attrs);
        $hasHeight = preg_match('/\sheight\s*=\s*["\']?\d+["\']?/i', $attrs);

        if ($hasWidth && $hasHeight) {
            return $imgTag;
        }

        // Try to extract src to get attachment ID
        if (preg_match('/\ssrc\s*=\s*["\']([^"\']+)["\']/i', $attrs, $srcMatch)) {
            $src = $srcMatch[1];
            $attachmentId = $this->getAttachmentIdFromUrl($src);

            if ($attachmentId > 0) {
                $metadata = wp_get_attachment_metadata($attachmentId);
                
                if (is_array($metadata) && isset($metadata['width'], $metadata['height'])) {
                    $width = $metadata['width'];
                    $height = $metadata['height'];

                    // Try to match size from URL
                    if (preg_match('/-(\d+)x(\d+)\.(jpg|jpeg|png|gif|webp)$/i', $src, $sizeMatch)) {
                        $width = $sizeMatch[1];
                        $height = $sizeMatch[2];
                    }

                    // Add missing attributes
                    if (!$hasWidth) {
                        $attrs .= sprintf(' width="%d"', $width);
                    }
                    if (!$hasHeight) {
                        $attrs .= sprintf(' height="%d"', $height);
                    }

                    return '<img' . $attrs . '>';
                }
            }
        }

        return $imgTag;
    }

    /**
     * Get attachment ID from image URL
     */
    private function getAttachmentIdFromUrl(string $url): int
    {
        global $wpdb;

        // Remove size suffix for lookup
        $url = preg_replace('/-\d+x\d+(?=\.[a-z]{3,4}$)/i', '', $url);

        // Query database
        $attachmentId = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
            WHERE meta_key = '_wp_attached_file' 
            AND meta_value LIKE %s 
            LIMIT 1",
            '%' . $wpdb->esc_like(basename($url))
        ));

        return $attachmentId ? (int) $attachmentId : 0;
    }

    /**
     * Add aspect-ratio CSS for better CLS prevention
     */
    public function addAspectRatioStyles(): void
    {
        ?>
        <style id="fp-image-aspect-ratio">
        /* FP Performance Suite - Image Aspect Ratio Support */
        img[width][height] {
            height: auto;
            aspect-ratio: attr(width) / attr(height);
        }
        @supports not (aspect-ratio: 1 / 1) {
            img[width][height] {
                height: auto;
            }
        }
        </style>
        <?php
    }

    /**
     * Check if image optimization is enabled
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
            'force_dimensions' => true,
            'add_aspect_ratio' => true,
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
            Logger::info('Image optimization settings updated', $updated);
            do_action('fp_ps_image_optimization_updated', $updated);
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
            'force_dimensions' => !empty($settings['force_dimensions']),
            'aspect_ratio' => !empty($settings['add_aspect_ratio']),
        ];
    }
}
