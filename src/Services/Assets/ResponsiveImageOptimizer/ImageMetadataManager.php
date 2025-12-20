<?php

namespace FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer;

use function wp_get_upload_dir;
use function wp_get_attachment_metadata;
use function wp_update_attachment_metadata;
use function wp_get_image_mime;
use function wp_cache_get;
use function wp_cache_set;

/**
 * Gestisce metadata e cache delle immagini
 * 
 * @package FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer
 * @author Francesco Passeri
 */
class ImageMetadataManager
{
    private const CACHE_GROUP = 'fp_responsive_images';
    private const CACHE_EXPIRATION = 3600; // 1 hour

    /**
     * Cache locale per mapping URL -> attachment ID
     */
    private array $attachmentIdCache = [];

    /**
     * Ottiene l'ID attachment da URL
     */
    public function getAttachmentIdFromUrl(string $url): int
    {
        $normalizedUrl = $this->normalizeImageUrl($url);
        
        if (isset($this->attachmentIdCache[$normalizedUrl])) {
            return $this->attachmentIdCache[$normalizedUrl];
        }

        // Try cache first
        $cached = wp_cache_get($normalizedUrl, self::CACHE_GROUP);
        if ($cached !== false) {
            $this->attachmentIdCache[$normalizedUrl] = (int) $cached;
            return (int) $cached;
        }

        // Query database
        global $wpdb;
        
        $uploadDir = wp_get_upload_dir();
        $baseUrl = $uploadDir['baseurl'];
        
        if (strpos($normalizedUrl, $baseUrl) === false) {
            return 0;
        }

        $relativePath = str_replace($baseUrl, '', $normalizedUrl);
        $relativePath = ltrim($relativePath, '/');

        $attachmentId = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_wp_attached_file' 
             AND meta_value = %s 
             LIMIT 1",
            $relativePath
        ));

        if (!$attachmentId) {
            // Try with year/month structure
            $attachmentId = $wpdb->get_var($wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} 
                 WHERE meta_key = '_wp_attached_file' 
                 AND meta_value LIKE %s 
                 LIMIT 1",
                '%/' . basename($relativePath)
            ));
        }

        $attachmentId = (int) $attachmentId;

        // Cache result
        wp_cache_set($normalizedUrl, $attachmentId, self::CACHE_GROUP, self::CACHE_EXPIRATION);
        $this->attachmentIdCache[$normalizedUrl] = $attachmentId;

        return $attachmentId;
    }

    /**
     * Normalizza URL immagine
     */
    public function normalizeImageUrl(string $url): string
    {
        // Remove query strings and fragments
        $url = strtok($url, '?');
        $url = strtok($url, '#');
        
        return $url;
    }

    /**
     * Ottiene dimensioni CSS per un'immagine
     */
    public function getCssDimensions(string $url): ?array
    {
        // Try to get from JavaScript-detected dimensions
        $cacheKey = 'css_dimensions_' . md5($url);
        $dimensions = wp_cache_get($cacheKey, self::CACHE_GROUP);
        
        if ($dimensions !== false && is_array($dimensions)) {
            return $dimensions;
        }

        return null;
    }

    /**
     * Ottiene dati dimensione immagine
     */
    public function getImageSizeData(string $size): ?array
    {
        $sizes = $this->getImageSizes();
        return $sizes[$size] ?? null;
    }

    /**
     * Ottiene tutte le dimensioni immagine disponibili
     */
    public function getImageSizes(): array
    {
        return get_intermediate_image_sizes();
    }

    /**
     * Aggiorna metadata attachment con nuova dimensione
     */
    public function updateAttachmentMetadata(int $attachmentId, int $width, int $height, string $fileName): void
    {
        $metadata = wp_get_attachment_metadata($attachmentId);
        
        if (!is_array($metadata)) {
            return;
        }

        // Add new size to metadata
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
     * Costruisce il path assoluto da metadata
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
}















