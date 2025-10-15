<?php

namespace FP\PerfSuite\Services\Media\AVIF;

use FP\PerfSuite\Utils\Logger;

/**
 * AVIF Image Converter
 *
 * Converts images to AVIF format using GD (PHP 8.1+) or Imagick
 * 
 * AVIF è un formato immagine moderno basato su AV1, più efficiente di WebP.
 * Richiede PHP 8.1+ con GD o Imagick con supporto AVIF.
 *
 * @package FP\PerfSuite\Services\Media\AVIF
 * @author Francesco Passeri
 */
class AVIFImageConverter
{
    private string $method = 'none';

    public function __construct()
    {
        $this->detectMethod();
    }

    /**
     * Detect available conversion method
     */
    private function detectMethod(): void
    {
        // Check GD support (PHP 8.1+)
        if (function_exists('imageavif') && function_exists('imagecreatefromstring')) {
            $this->method = 'gd';
            Logger::debug('AVIF conversion using GD (PHP 8.1+)');
            return;
        }

        // Check Imagick support
        if (extension_loaded('imagick')) {
            try {
                $imagick = new \Imagick();
                $formats = $imagick->queryFormats('AVIF');
                if (!empty($formats)) {
                    $this->method = 'imagick';
                    Logger::debug('AVIF conversion using Imagick');
                    return;
                }
            } catch (\Exception $e) {
                Logger::debug('Imagick AVIF support not available');
            }
        }

        Logger::warning('AVIF conversion not available (requires PHP 8.1+ GD or Imagick with AVIF support)');
    }

    /**
     * Check if AVIF conversion is available
     *
     * @return bool True if AVIF conversion is supported
     */
    public function isAvailable(): bool
    {
        return $this->method !== 'none';
    }

    /**
     * Get current conversion method
     *
     * @return string 'gd', 'imagick', or 'none'
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Convert image to AVIF format
     *
     * @param string $source Source file path
     * @param string $destination Destination AVIF file path
     * @param array $settings Conversion settings
     * @param bool $force Force reconversion even if destination exists
     * @return bool True if conversion successful
     */
    public function convert(string $source, string $destination, array $settings = [], bool $force = false): bool
    {
        if (!$this->isAvailable()) {
            Logger::warning('AVIF conversion not available');
            return false;
        }

        if (!file_exists($source) || !is_readable($source)) {
            Logger::warning('Source file not readable', ['file' => $source]);
            return false;
        }

        if (file_exists($destination) && !$force) {
            return true;
        }

        $quality = (int)($settings['quality'] ?? 75);
        $quality = max(1, min(100, $quality));

        // AVIF quality range: 0-100 (lower is better quality, opposite of JPEG!)
        // We invert it to match user expectations (100 = best quality)
        $avifQuality = 100 - $quality;

        try {
            if ($this->method === 'gd') {
                return $this->convertWithGD($source, $destination, $avifQuality);
            } elseif ($this->method === 'imagick') {
                return $this->convertWithImagick($source, $destination, $avifQuality, $settings);
            }
        } catch (\Exception $e) {
            Logger::error('AVIF conversion failed', [
                'source' => basename($source),
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Convert using GD library (PHP 8.1+)
     *
     * @param string $source Source file
     * @param string $destination Destination file
     * @param int $quality Quality (0-100, lower is better)
     * @return bool True if successful
     */
    private function convertWithGD(string $source, string $destination, int $quality): bool
    {
        $image = $this->loadImageWithGD($source);
        
        if ($image === false) {
            return false;
        }

        // Ensure directory exists
        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Convert to AVIF
        $result = imageavif($image, $destination, $quality);
        imagedestroy($image);

        if ($result) {
            Logger::debug('AVIF created with GD', [
                'source' => basename($source),
                'destination' => basename($destination),
                'quality' => $quality,
                'size' => filesize($destination),
            ]);
        }

        return $result;
    }

    /**
     * Load image with GD based on file type
     *
     * @param string $file File path
     * @return \GdImage|false GD image resource or false
     */
    private function loadImageWithGD(string $file)
    {
        $imageInfo = getimagesize($file);
        
        if ($imageInfo === false) {
            return false;
        }

        $mimeType = $imageInfo['mime'];

        switch ($mimeType) {
            case 'image/jpeg':
                return imagecreatefromjpeg($file);
            case 'image/png':
                return imagecreatefrompng($file);
            case 'image/gif':
                return imagecreatefromgif($file);
            case 'image/webp':
                return imagecreatefromwebp($file);
            default:
                // Try generic loader
                return imagecreatefromstring(file_get_contents($file));
        }
    }

    /**
     * Convert using Imagick library
     *
     * @param string $source Source file
     * @param string $destination Destination file
     * @param int $quality Quality (0-100, lower is better)
     * @param array $settings Additional settings
     * @return bool True if successful
     */
    private function convertWithImagick(string $source, string $destination, int $quality, array $settings): bool
    {
        $imagick = new \Imagick($source);

        // Set format
        $imagick->setImageFormat('avif');

        // Set quality
        $imagick->setImageCompressionQuality($quality);

        // Optional: Set speed vs quality tradeoff (0-10, higher is faster but lower quality)
        $speed = (int)($settings['speed'] ?? 6);
        if (method_exists($imagick, 'setOption')) {
            $imagick->setOption('avif:speed', $speed);
        }

        // Strip metadata to reduce file size (optional)
        if (!empty($settings['strip_metadata'])) {
            $imagick->stripImage();
        }

        // Ensure directory exists
        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Write file
        $result = $imagick->writeImage($destination);
        $fileSize = $result ? filesize($destination) : 0;

        $imagick->clear();
        $imagick->destroy();

        if ($result) {
            Logger::debug('AVIF created with Imagick', [
                'source' => basename($source),
                'destination' => basename($destination),
                'quality' => $quality,
                'speed' => $speed,
                'size' => $fileSize,
            ]);
        }

        return $result;
    }

    /**
     * Batch convert multiple images
     *
     * @param array $files Array of [source => destination] pairs
     * @param array $settings Conversion settings
     * @return array{converted:int,failed:int,errors:array}
     */
    public function batchConvert(array $files, array $settings = []): array
    {
        $converted = 0;
        $failed = 0;
        $errors = [];

        foreach ($files as $source => $destination) {
            if ($this->convert($source, $destination, $settings)) {
                $converted++;
            } else {
                $failed++;
                $errors[] = basename($source);
            }
        }

        return [
            'converted' => $converted,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Get optimal quality based on image type and size
     *
     * @param string $file Image file path
     * @return int Recommended quality (0-100)
     */
    public function getOptimalQuality(string $file): int
    {
        $imageInfo = getimagesize($file);
        
        if ($imageInfo === false) {
            return 75; // Default
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $pixels = $width * $height;

        // Adjust quality based on image size
        if ($pixels > 4000000) { // > 4MP
            return 70; // High compression for large images
        } elseif ($pixels > 1000000) { // > 1MP
            return 75; // Medium compression
        } else {
            return 80; // Lower compression for small images
        }
    }

    /**
     * Compare file sizes between original and AVIF
     *
     * @param string $original Original file path
     * @param string $avif AVIF file path
     * @return array{original:int,avif:int,savings:int,percentage:float}
     */
    public function compareSize(string $original, string $avif): array
    {
        if (!file_exists($original) || !file_exists($avif)) {
            return [
                'original' => 0,
                'avif' => 0,
                'savings' => 0,
                'percentage' => 0.0,
            ];
        }

        $originalSize = filesize($original);
        $avifSize = filesize($avif);
        $savings = $originalSize - $avifSize;
        $percentage = $originalSize > 0 ? ($savings / $originalSize) * 100 : 0;

        return [
            'original' => $originalSize,
            'avif' => $avifSize,
            'savings' => $savings,
            'percentage' => round($percentage, 2),
        ];
    }
}
