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

        // Log detailed warning based on what's missing
        $this->logAvailabilityWarning();
    }

    /**
     * Log detailed warning about AVIF availability
     */
    private function logAvailabilityWarning(): void
    {
        $phpVersion = PHP_VERSION;
        $phpMajor = (int)PHP_MAJOR_VERSION;
        $phpMinor = (int)PHP_MINOR_VERSION;

        $reasons = [];

        // Check PHP version
        if ($phpMajor < 8 || ($phpMajor === 8 && $phpMinor < 1)) {
            $reasons[] = "PHP version $phpVersion (requires 8.1+ for GD support)";
        } else {
            // PHP 8.1+ but GD doesn't have AVIF
            if (!function_exists('imageavif')) {
                $reasons[] = 'GD extension missing imageavif() function';
            }
        }

        // Check Imagick
        if (!extension_loaded('imagick')) {
            $reasons[] = 'Imagick extension not loaded';
        } else {
            $reasons[] = 'Imagick loaded but AVIF format not supported';
        }

        $message = 'AVIF conversion not available';
        if (!empty($reasons)) {
            $message .= ': ' . implode(', ', $reasons);
        }

        Logger::warning($message);
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
     * Check AVIF format conversion availability and requirements
     *
     * @return array{
     *     available: bool,
     *     method: string,
     *     php_version: string,
     *     php_meets_requirement: bool,
     *     gd_loaded: bool,
     *     gd_has_avif: bool,
     *     imagick_loaded: bool,
     *     imagick_has_avif: bool,
     *     recommendations: array
     * }
     */
    public function checkAvailability(): array
    {
        $phpVersion = PHP_VERSION;
        $phpMajor = (int)PHP_MAJOR_VERSION;
        $phpMinor = (int)PHP_MINOR_VERSION;
        $phpMeetsRequirement = $phpMajor > 8 || ($phpMajor === 8 && $phpMinor >= 1);

        $gdLoaded = extension_loaded('gd');
        $gdHasAvif = function_exists('imageavif');

        $imagickLoaded = extension_loaded('imagick');
        $imagickHasAvif = false;

        if ($imagickLoaded) {
            try {
                $imagick = new \Imagick();
                $formats = $imagick->queryFormats('AVIF');
                $imagickHasAvif = !empty($formats);
            } catch (\Exception $e) {
                // Imagick loaded but can't query formats
            }
        }

        $recommendations = [];

        if (!$this->isAvailable()) {
            if (!$phpMeetsRequirement) {
                $recommendations[] = "Aggiorna PHP alla versione 8.1 o superiore (attuale: $phpVersion)";
            } elseif ($gdLoaded && !$gdHasAvif) {
                $recommendations[] = 'Ricompila GD con supporto AVIF o usa Imagick';
            }

            if (!$imagickLoaded) {
                $recommendations[] = 'Installa l\'estensione Imagick come alternativa';
            } elseif (!$imagickHasAvif) {
                $recommendations[] = 'Aggiorna ImageMagick con supporto AVIF (libheif)';
            }
        }

        return [
            'available' => $this->isAvailable(),
            'method' => $this->method,
            'php_version' => $phpVersion,
            'php_meets_requirement' => $phpMeetsRequirement,
            'gd_loaded' => $gdLoaded,
            'gd_has_avif' => $gdHasAvif,
            'imagick_loaded' => $imagickLoaded,
            'imagick_has_avif' => $imagickHasAvif,
            'recommendations' => $recommendations,
        ];
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
            Logger::error('AVIF conversion failed', $e, [
                'source' => basename($source),
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
