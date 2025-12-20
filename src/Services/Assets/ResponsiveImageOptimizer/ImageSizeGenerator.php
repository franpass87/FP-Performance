<?php

namespace FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer;

use FP\PerfSuite\Utils\Logger;

/**
 * Genera nuove dimensioni di immagini
 * 
 * @package FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer
 * @author Francesco Passeri
 */
class ImageSizeGenerator
{
    private ImageMetadataManager $metadataManager;

    public function __construct(ImageMetadataManager $metadataManager)
    {
        $this->metadataManager = $metadataManager;
    }

    /**
     * Genera una dimensione ottimale se necessario
     */
    public function generateOptimalSize(string $originalUrl, int $attachmentId, int $width, int $height): ?string
    {
        // Get original file path
        $uploadDir = wp_get_upload_dir();
        $baseUrl = $uploadDir['baseurl'];
        
        if (strpos($originalUrl, $baseUrl) === false) {
            return null;
        }

        $relativePath = str_replace($baseUrl, '', $originalUrl);
        $originalPath = $uploadDir['basedir'] . $relativePath;

        if (!file_exists($originalPath)) {
            return null;
        }

        // Generate new size name
        $pathInfo = pathinfo($originalPath);
        $newFileName = $pathInfo['filename'] . '-' . $width . 'x' . $height . '.' . $pathInfo['extension'];
        $newPath = $pathInfo['dirname'] . '/' . $newFileName;
        $newUrl = $baseUrl . str_replace($uploadDir['basedir'], '', $newPath);

        // Check if already exists
        if (file_exists($newPath)) {
            return $newUrl;
        }

        // Generate the new size
        if ($this->generateImageSize($originalPath, $newPath, $width, $height)) {
            // Update attachment metadata
            $this->metadataManager->updateAttachmentMetadata($attachmentId, $width, $height, $newFileName);
            
            return $newUrl;
        }

        return null;
    }

    /**
     * Genera una dimensione immagine usando WordPress image editor
     */
    private function generateImageSize(string $originalPath, string $newPath, int $width, int $height): bool
    {
        $editor = wp_get_image_editor($originalPath);
        
        if (is_wp_error($editor)) {
            Logger::error('Failed to load image editor', ['path' => $originalPath, 'error' => $editor->get_error_message()]);
            return false;
        }

        $editor->resize($width, $height, true);
        $result = $editor->save($newPath);

        if (is_wp_error($result)) {
            Logger::error('Failed to save resized image', ['path' => $newPath, 'error' => $result->get_error_message()]);
            return false;
        }

        Logger::debug('Generated new image size', [
            'original' => basename($originalPath),
            'new' => basename($newPath),
            'dimensions' => $width . 'x' . $height
        ]);

        return true;
    }
}















