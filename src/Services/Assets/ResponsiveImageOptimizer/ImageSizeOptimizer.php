<?php

namespace FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer;

/**
 * Ottimizza le dimensioni delle immagini
 * 
 * @package FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer
 * @author Francesco Passeri
 */
class ImageSizeOptimizer
{
    private ImageMetadataManager $metadataManager;
    private ImageSizeGenerator $sizeGenerator;

    public function __construct(ImageMetadataManager $metadataManager, ImageSizeGenerator $sizeGenerator)
    {
        $this->metadataManager = $metadataManager;
        $this->sizeGenerator = $sizeGenerator;
    }

    /**
     * Ottiene la dimensione ottimale per un'immagine
     */
    public function getOptimalImageSize(string $originalUrl, int $attachmentId, ?string $requestedSize = null): ?string
    {
        // Get image metadata
        $metadata = wp_get_attachment_metadata($attachmentId);
        if (!is_array($metadata)) {
            return null;
        }

        // Get original dimensions
        $originalWidth = $metadata['width'] ?? 0;
        $originalHeight = $metadata['height'] ?? 0;

        if ($originalWidth <= 0 || $originalHeight <= 0) {
            return null;
        }

        // Get target dimensions
        $targetDimensions = $this->getTargetDimensions($originalUrl, $attachmentId, $requestedSize);
        
        if (!$targetDimensions) {
            return null;
        }

        $targetWidth = $targetDimensions['width'];
        $targetHeight = $targetDimensions['height'];

        // If target is larger than original, use original
        if ($targetWidth >= $originalWidth && $targetHeight >= $originalHeight) {
            return null;
        }

        // Find suitable existing size
        $suitableSize = $this->findSuitableSize($metadata, $targetWidth, $targetHeight);
        
        if ($suitableSize) {
            return $suitableSize;
        }

        // Generate optimal size if needed
        return $this->sizeGenerator->generateOptimalSize($originalUrl, $attachmentId, $targetWidth, $targetHeight);
    }

    /**
     * Ottiene le dimensioni target
     */
    private function getTargetDimensions(string $url, int $attachmentId, ?string $requestedSize = null): ?array
    {
        // If specific size requested, use it
        if ($requestedSize) {
            $sizeData = $this->metadataManager->getImageSizeData($requestedSize);
            if ($sizeData) {
                return [
                    'width' => $sizeData['width'],
                    'height' => $sizeData['height'],
                ];
            }
        }

        // Try to get CSS dimensions
        $cssDimensions = $this->metadataManager->getCssDimensions($url);
        
        if ($cssDimensions) {
            return $cssDimensions;
        }

        // Fallback: use viewport width
        return [
            'width' => min(1920, (int) ($_SERVER['HTTP_X_VIEWPORT_WIDTH'] ?? 1920)),
            'height' => 0, // Maintain aspect ratio
        ];
    }

    /**
     * Trova una dimensione esistente adatta
     */
    private function findSuitableSize(array $metadata, int $targetWidth, int $targetHeight): ?string
    {
        if (empty($metadata['sizes'])) {
            return null;
        }

        $bestSize = null;
        $bestScore = PHP_INT_MAX;

        foreach ($metadata['sizes'] as $sizeName => $sizeData) {
            $width = $sizeData['width'] ?? 0;
            $height = $sizeData['height'] ?? 0;

            if ($width <= 0 || $height <= 0) {
                continue;
            }

            // Calculate how close this size is to target
            $widthDiff = abs($width - $targetWidth);
            $heightDiff = abs($height - $targetHeight);
            $score = $widthDiff + $heightDiff;

            // Prefer sizes that are close but not smaller than target
            if ($width >= $targetWidth && $height >= $targetHeight && $score < $bestScore) {
                $bestSize = $sizeName;
                $bestScore = $score;
            }
        }

        if (null === $bestSize) {
            return null;
        }

        $uploadDir = wp_get_upload_dir();
        $baseUrl   = rtrim($uploadDir['baseurl'], '/');

        $relativeDir = '';
        if (!empty($metadata['file'])) {
            $dir = dirname($metadata['file']);
            if ('.' !== $dir) {
                $relativeDir = '/' . ltrim($dir, '/');
            }
        }

        $sizeFile = $metadata['sizes'][$bestSize]['file'] ?? '';
        if ('' === $sizeFile) {
            return null;
        }

        return $baseUrl . $relativeDir . '/' . ltrim($sizeFile, '/');
    }

    /**
     * Ottiene la dimensione ottimale per una larghezza specifica
     */
    public function getOptimalSizeForWidth(string $url, int $attachmentId, int $width): ?string
    {
        $metadata = wp_get_attachment_metadata($attachmentId);
        if (!is_array($metadata)) {
            return null;
        }

        $originalWidth = $metadata['width'] ?? 0;
        if ($width >= $originalWidth) {
            return null; // Use original
        }

        // Find closest size
        $bestSize = null;
        $bestDiff = PHP_INT_MAX;

        foreach ($metadata['sizes'] as $sizeName => $sizeData) {
            $sizeWidth = $sizeData['width'] ?? 0;
            if ($sizeWidth <= 0) {
                continue;
            }

            $diff = abs($sizeWidth - $width);
            if ($sizeWidth >= $width && $diff < $bestDiff) {
                $bestSize = $sizeName;
                $bestDiff = $diff;
            }
        }

        if ($bestSize) {
            $uploadDir = wp_get_upload_dir();
            $baseUrl = rtrim($uploadDir['baseurl'], '/');
            $relativeDir = '';
            
            if (!empty($metadata['file'])) {
                $dir = dirname($metadata['file']);
                if ('.' !== $dir) {
                    $relativeDir = '/' . ltrim($dir, '/');
                }
            }

            $sizeFile = $metadata['sizes'][$bestSize]['file'] ?? '';
            if ($sizeFile) {
                return $baseUrl . $relativeDir . '/' . ltrim($sizeFile, '/');
            }
        }

        // Generate if needed
        $originalHeight = $metadata['height'] ?? 0;
        if ($originalHeight > 0) {
            $height = (int) (($width / $originalWidth) * $originalHeight);
            return $this->sizeGenerator->generateOptimalSize($url, $attachmentId, $width, $height);
        }

        return null;
    }
}















