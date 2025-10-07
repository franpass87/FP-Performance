<?php

namespace FP\PerfSuite\Services\Media\WebP;

use FP\PerfSuite\Utils\Logger;
use function file_exists;
use function filemtime;
use function function_exists;
use function imagealphablending;
use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagedestroy;
use function imagepalettetotruecolor;
use function imagesavealpha;
use function imagewebp;
use function in_array;
use function pathinfo;
use function strtolower;

/**
 * WebP Image Conversion Engine
 * 
 * Handles the actual image conversion to WebP format using Imagick or GD
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class WebPImageConverter
{
    /** @var array<int, string> */
    private const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /**
     * Convert an image file to WebP format
     * 
     * @param string $sourceFile Source image path
     * @param string $targetFile Target WebP path
     * @param array{quality:int,lossy:bool} $settings Conversion settings
     * @param bool $force Force reconversion even if target exists
     * @return bool True if conversion was successful
     */
    public function convert(string $sourceFile, string $targetFile, array $settings, bool $force = false): bool
    {
        if (!$this->isConvertible($sourceFile)) {
            return false;
        }

        if (!$this->needsConversion($sourceFile, $targetFile, $force)) {
            return false;
        }

        // Try Imagick first (better quality)
        if ($this->convertWithImagick($sourceFile, $targetFile, $settings)) {
            $this->logSuccess($sourceFile);
            return true;
        }

        // Fallback to GD
        if ($this->convertWithGD($sourceFile, $targetFile, $settings)) {
            $this->logSuccess($sourceFile);
            return true;
        }

        return false;
    }

    /**
     * Check if file can be converted to WebP
     * 
     * @param string $file File path
     * @return bool
     */
    public function isConvertible(string $file): bool
    {
        $info = pathinfo($file);
        $ext = strtolower($info['extension'] ?? '');
        return in_array($ext, self::SUPPORTED_EXTENSIONS, true);
    }

    /**
     * Check if conversion is needed
     * 
     * @param string $sourceFile Source file path
     * @param string $targetFile Target WebP path
     * @param bool $force Force conversion
     * @return bool
     */
    private function needsConversion(string $sourceFile, string $targetFile, bool $force): bool
    {
        if ($force) {
            return true;
        }

        if (!file_exists($targetFile)) {
            return true;
        }

        $existingTime = @filemtime($targetFile);
        $sourceTime = @filemtime($sourceFile);

        if ($existingTime === false || $sourceTime === false) {
            return true;
        }

        // Reconvert if source is newer
        return $sourceTime > $existingTime;
    }

    /**
     * Convert using Imagick extension
     * 
     * @param string $sourceFile
     * @param string $targetFile
     * @param array{quality:int,lossy:bool} $settings
     * @return bool
     */
    private function convertWithImagick(string $sourceFile, string $targetFile, array $settings): bool
    {
        if (!class_exists('Imagick')) {
            return false;
        }

        try {
            $imagick = new \Imagick($sourceFile);
            
            if ($settings['lossy']) {
                $imagick->setImageCompressionQuality($settings['quality']);
            }
            
            $imagick->setImageFormat('webp');
            $result = (bool) $imagick->writeImage($targetFile);
            
            return $result;
        } catch (\ImagickException $e) {
            Logger::error('Imagick failed to convert to WebP', $e);
            return false;
        } catch (\Throwable $e) {
            Logger::error('Imagick runtime error during WebP conversion', $e);
            return false;
        } finally {
            if (isset($imagick) && $imagick instanceof \Imagick) {
                $imagick->clear();
                $imagick->destroy();
            }
        }
    }

    /**
     * Convert using GD extension
     * 
     * @param string $sourceFile
     * @param string $targetFile
     * @param array{quality:int,lossy:bool} $settings
     * @return bool
     */
    private function convertWithGD(string $sourceFile, string $targetFile, array $settings): bool
    {
        if (!function_exists('imagewebp')) {
            return false;
        }

        $info = pathinfo($sourceFile);
        $ext = strtolower($info['extension'] ?? '');

        $image = $this->createImageResource($sourceFile, $ext);
        
        if ($image === null) {
            return false;
        }

        // Convert palette to truecolor for better quality
        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($image);
        }

        // Handle PNG transparency
        if ($ext === 'png') {
            if (function_exists('imagealphablending')) {
                imagealphablending($image, false);
            }
            if (function_exists('imagesavealpha')) {
                imagesavealpha($image, true);
            }
        }

        $converted = imagewebp($image, $targetFile, $settings['quality']);
        imagedestroy($image);

        if (!$converted) {
            Logger::warning("GD failed to convert to WebP: {$sourceFile}");
        }

        return $converted;
    }

    /**
     * Create GD image resource from file
     * 
     * @param string $file File path
     * @param string $extension File extension
     * @return resource|null
     */
    private function createImageResource(string $file, string $extension)
    {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                if (function_exists('imagecreatefromjpeg')) {
                    $resource = @imagecreatefromjpeg($file);
                    return $resource !== false ? $resource : null;
                }
                break;
            case 'png':
                if (function_exists('imagecreatefrompng')) {
                    $resource = @imagecreatefrompng($file);
                    return $resource !== false ? $resource : null;
                }
                break;
        }
        return null;
    }

    /**
     * Log successful conversion
     */
    private function logSuccess(string $file): void
    {
        Logger::debug('WebP conversion successful', ['file' => basename($file)]);
        do_action('fp_ps_webp_converted', $file);
    }

    /**
     * Get supported file extensions
     * 
     * @return array<int, string>
     */
    public function getSupportedExtensions(): array
    {
        return self::SUPPORTED_EXTENSIONS;
    }
}