<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

use function wp_verify_nonce;
use function wp_unslash;

/**
 * AJAX Handler for Responsive Image Optimization
 *
 * Handles JavaScript dimension detection requests and optimizes images
 * based on actual display dimensions.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ResponsiveImageAjaxHandler
{
    private const CACHE_GROUP = 'fp_responsive_images';
    private const CACHE_EXPIRATION = 3600;

    /**
     * Cache locale per ID allegati calcolati durante la richiesta.
     *
     * @var array<string,int>
     */
    private array $attachmentIdCache = [];

    /**
     * Register AJAX handlers
     */
    public function register(): void
    {
        add_action('wp_ajax_fp_ps_optimize_image', [$this, 'handleOptimizeImage']);
        add_action('wp_ajax_nopriv_fp_ps_optimize_image', [$this, 'handleOptimizeImage']);
    }

    /**
     * Handle image optimization request
     */
    public function handleOptimizeImage(): void
    {
        // Verify nonce for security
        if (!wp_verify_nonce(wp_unslash($_POST['nonce'] ?? ''), 'fp_ps_optimize_image')) {
            wp_die('Security check failed', 'Unauthorized', ['response' => 403]);
        }

        $url = sanitize_url($_POST['url'] ?? '');
        $width = (int) ($_POST['width'] ?? 0);
        $height = (int) ($_POST['height'] ?? 0);

        if (empty($url) || $width <= 0 || $height <= 0) {
            wp_die('Invalid parameters', 'Bad Request', ['response' => 400]);
        }

        // Get attachment ID
        $attachmentId = $this->getAttachmentIdFromUrl($url);
        
        if ($attachmentId <= 0) {
            wp_die('Image not found', 'Not Found', ['response' => 404]);
        }

        // Check if optimization is needed
        $optimizedUrl = $this->optimizeImageForDimensions($url, $attachmentId, $width, $height);
        
        if ($optimizedUrl && $optimizedUrl !== $url) {
            // Return optimized URL
            wp_send_json_success([
                'original_url' => $url,
                'optimized_url' => $optimizedUrl,
                'dimensions' => $width . 'x' . $height
            ]);
        } else {
            // No optimization needed
            wp_send_json_success([
                'original_url' => $url,
                'optimized_url' => $url,
                'dimensions' => $width . 'x' . $height,
                'message' => 'No optimization needed'
            ]);
        }
    }

    /**
     * Optimize image for specific dimensions
     */
    private function optimizeImageForDimensions(string $url, int $attachmentId, int $width, int $height): ?string
    {
        // Get image metadata
        $metadata = wp_get_attachment_metadata($attachmentId);
        if (!is_array($metadata)) {
            return null;
        }

        $originalWidth = $metadata['width'] ?? 0;
        $originalHeight = $metadata['height'] ?? 0;

        // If target dimensions are larger than original, use original
        if ($width >= $originalWidth && $height >= $originalHeight) {
            return null;
        }

        // Check if we already have a suitable size
        $existingSize = $this->findExistingSize($metadata, $width, $height);
        if ($existingSize) {
            return $existingSize;
        }

        // Generate new size if needed
        return $this->generateOptimizedSize($url, $attachmentId, $width, $height);
    }

    /**
     * Find existing size that matches requirements
     */
    private function findExistingSize(array $metadata, int $width, int $height): ?string
    {
        if (empty($metadata['sizes'])) {
            return null;
        }

        $bestSize = null;
        $bestScore = PHP_INT_MAX;

        foreach ($metadata['sizes'] as $sizeName => $sizeData) {
            $sizeWidth = $sizeData['width'] ?? 0;
            $sizeHeight = $sizeData['height'] ?? 0;

            if ($sizeWidth <= 0 || $sizeHeight <= 0) {
                continue;
            }

            // Calculate score (lower is better)
            $widthDiff = abs($sizeWidth - $width);
            $heightDiff = abs($sizeHeight - $height);
            $score = $widthDiff + $heightDiff;

            // Prefer sizes that are close but not smaller than target
            if ($sizeWidth >= $width && $sizeHeight >= $height && $score < $bestScore) {
                $bestSize = $sizeName;
                $bestScore = $score;
            }
        }

        if ($bestSize) {
            $uploadDir = wp_get_upload_dir();
            $baseUrl = $uploadDir['baseurl'];
            $sizeData = $metadata['sizes'][$bestSize];
            
            return $baseUrl . '/' . dirname($metadata['file']) . '/' . $sizeData['file'];
        }

        return null;
    }

    /**
     * Generate optimized size
     */
    private function generateOptimizedSize(string $url, int $attachmentId, int $width, int $height): ?string
    {
        // Get original file path
        $uploadDir = wp_get_upload_dir();
        $baseUrl = $uploadDir['baseurl'];
        
        if (strpos($url, $baseUrl) === false) {
            return null;
        }

        $relativePath = str_replace($baseUrl, '', $url);
        $originalPath = $uploadDir['basedir'] . $relativePath;

        if (!file_exists($originalPath)) {
            return null;
        }

        // Generate new filename
        $pathInfo = pathinfo($originalPath);
        $newFileName = $pathInfo['filename'] . '-opt-' . $width . 'x' . $height . '.' . $pathInfo['extension'];
        $newPath = $pathInfo['dirname'] . '/' . $newFileName;
        $newUrl = $baseUrl . str_replace($uploadDir['basedir'], '', $newPath);

        // Check if already exists
        if (file_exists($newPath)) {
            return $newUrl;
        }

        // Generate the new size
        if ($this->createOptimizedImage($originalPath, $newPath, $width, $height)) {
            // Update attachment metadata
            $this->updateAttachmentMetadata($attachmentId, $width, $height, $newFileName);
            
            Logger::info('Generated optimized image', [
                'original' => basename($originalPath),
                'optimized' => basename($newPath),
                'dimensions' => $width . 'x' . $height
            ]);
            
            return $newUrl;
        }

        return null;
    }

    /**
     * Create optimized image using WordPress image editor
     */
    private function createOptimizedImage(string $originalPath, string $newPath, int $width, int $height): bool
    {
        $editor = wp_get_image_editor($originalPath);
        
        if (is_wp_error($editor)) {
            Logger::error('Failed to load image editor', [
                'path' => $originalPath,
                'error' => $editor->get_error_message()
            ]);
            return false;
        }

        // Set quality
        $quality = apply_filters('fp_ps_responsive_image_quality', 85);
        $editor->set_quality($quality);

        // Resize image
        $editor->resize($width, $height, true);
        $result = $editor->save($newPath);

        if (is_wp_error($result)) {
            Logger::error('Failed to save optimized image', [
                'path' => $newPath,
                'error' => $result->get_error_message()
            ]);
            return false;
        }

        return true;
    }

    /**
     * Update attachment metadata with new optimized size
     */
    private function updateAttachmentMetadata(int $attachmentId, int $width, int $height, string $fileName): void
    {
        $metadata = wp_get_attachment_metadata($attachmentId);
        
        if (!is_array($metadata)) {
            return;
        }

        // Add optimized size to metadata
        $sizeName = 'fp_optimized_' . $width . 'x' . $height;
        $metadata['sizes'][$sizeName] = [
            'file' => $fileName,
            'width' => $width,
            'height' => $height,
            'mime-type' => wp_get_image_mime($this->buildAbsolutePathFromMetadata($metadata, $fileName)) ?: 'image/jpeg'
        ];

        wp_update_attachment_metadata($attachmentId, $metadata);
    }

    /**
     * Helper per costruire il percorso assoluto di un file derivato.
     */
    private function buildAbsolutePathFromMetadata(array $metadata, string $fileName): string
    {
        $uploadDir = wp_get_upload_dir();
        $baseDir   = rtrim($uploadDir['basedir'], DIRECTORY_SEPARATOR);

        $relativeDir = '';
        if (!empty($metadata['file'])) {
            $dir = dirname($metadata['file']);
            if ('.' !== $dir) {
                $relativeDir = DIRECTORY_SEPARATOR . ltrim($dir, DIRECTORY_SEPARATOR);
            }
        }

        return $baseDir . $relativeDir . DIRECTORY_SEPARATOR . ltrim($fileName, DIRECTORY_SEPARATOR);
    }

    /**
     * Get attachment ID from URL
     */
    private function getAttachmentIdFromUrl(string $url): int
    {
        global $wpdb;

        if (!isset($wpdb)) {
            return 0;
        }

        $normalizedUrl = $this->normalizeImageUrl($url);

        if (isset($this->attachmentIdCache[$normalizedUrl])) {
            return $this->attachmentIdCache[$normalizedUrl];
        }

        $cacheKey = 'attachment_id_' . md5($normalizedUrl);
        $cached = wp_cache_get($cacheKey, self::CACHE_GROUP);

        if (false !== $cached) {
            $this->attachmentIdCache[$normalizedUrl] = (int) $cached;
            return (int) $cached;
        }

        $attachmentId = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
            WHERE meta_key = '_wp_attached_file' 
            AND meta_value LIKE %s 
            LIMIT 1",
            '%' . $wpdb->esc_like(basename($normalizedUrl))
        ));

        $attachmentId = $attachmentId ? (int) $attachmentId : 0;

        $this->attachmentIdCache[$normalizedUrl] = $attachmentId;
        wp_cache_set($cacheKey, $attachmentId, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return $attachmentId;
    }

    /**
     * Normalizza l'URL rimuovendo suffissi di dimensioni generate.
     */
    private function normalizeImageUrl(string $url): string
    {
        return preg_replace('/-\d+x\d+(?=\.[a-z]{3,4}$)/i', '', $url) ?? $url;
    }
}

