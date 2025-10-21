<?php

namespace FP\PerfSuite\Services\Media\AVIF;

use FP\PerfSuite\Utils\Logger;

/**
 * AVIF Image Converter
 * 
 * Converte immagini in formato AVIF
 *
 * @package FP\PerfSuite\Services\Media\AVIF
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class AVIFImageConverter
{
    private const QUALITY = 80;
    private const MAX_WIDTH = 2560;
    private const MAX_HEIGHT = 2560;

    /**
     * Verifica se AVIF è supportato
     */
    public function isSupported(): bool
    {
        // AVIF richiede PHP >= 8.1 e GD con supporto AVIF
        if (PHP_VERSION_ID < 80100) {
            return false;
        }

        if (!function_exists('imageavif')) {
            return false;
        }

        return true;
    }

    /**
     * Converte un'immagine in AVIF
     */
    public function convert(string $sourcePath, string $destinationPath, ?int $quality = null): bool
    {
        if (!$this->isSupported()) {
            Logger::warning('AVIF not supported on this server');
            return false;
        }

        if (!file_exists($sourcePath)) {
            Logger::error('Source file not found', ['path' => $sourcePath]);
            return false;
        }

        $quality = $quality ?? self::QUALITY;

        try {
            // Carica immagine sorgente
            $image = $this->loadImage($sourcePath);
            if (!$image) {
                return false;
            }

            // Ridimensiona se necessario
            $image = $this->resizeIfNeeded($image);

            // Crea directory destinazione se non esiste
            $destDir = dirname($destinationPath);
            if (!file_exists($destDir)) {
                wp_mkdir_p($destDir);
            }

            // Converti in AVIF
            $result = @imageavif($image, $destinationPath, $quality);
            
            imagedestroy($image);

            if ($result) {
                Logger::debug('AVIF conversion successful', [
                    'source' => basename($sourcePath),
                    'destination' => basename($destinationPath),
                    'size_before' => filesize($sourcePath),
                    'size_after' => filesize($destinationPath),
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Logger::error('AVIF conversion failed', [
                'error' => $e->getMessage(),
                'source' => $sourcePath,
            ]);
            return false;
        }
    }

    /**
     * Carica immagine dalla sorgente
     */
    private function loadImage(string $path)
    {
        $imageInfo = @getimagesize($path);
        if (!$imageInfo) {
            Logger::error('Cannot get image info', ['path' => $path]);
            return false;
        }

        $mimeType = $imageInfo['mime'];

        switch ($mimeType) {
            case 'image/jpeg':
                return @imagecreatefromjpeg($path);
            
            case 'image/png':
                $image = @imagecreatefrompng($path);
                if ($image) {
                    // Preserva trasparenza
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                }
                return $image;
            
            case 'image/gif':
                return @imagecreatefromgif($path);
            
            case 'image/webp':
                return @imagecreatefromwebp($path);
            
            default:
                Logger::warning('Unsupported image type for AVIF', ['mime' => $mimeType]);
                return false;
        }
    }

    /**
     * Ridimensiona se l'immagine è troppo grande
     */
    private function resizeIfNeeded($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= self::MAX_WIDTH && $height <= self::MAX_HEIGHT) {
            return $image;
        }

        // Calcola nuove dimensioni mantenendo aspect ratio
        $ratio = min(self::MAX_WIDTH / $width, self::MAX_HEIGHT / $height);
        $newWidth = (int) ($width * $ratio);
        $newHeight = (int) ($height * $ratio);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserva trasparenza
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);

        imagecopyresampled(
            $resized,
            $image,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        imagedestroy($image);
        
        return $resized;
    }

    /**
     * Converti batch di immagini
     */
    public function convertBatch(array $files, ?int $quality = null): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'skipped' => [],
        ];

        foreach ($files as $sourcePath => $destinationPath) {
            if (!file_exists($sourcePath)) {
                $results['skipped'][] = $sourcePath;
                continue;
            }

            $result = $this->convert($sourcePath, $destinationPath, $quality);
            
            if ($result) {
                $results['success'][] = $sourcePath;
            } else {
                $results['failed'][] = $sourcePath;
            }
        }

        return $results;
    }

    /**
     * Ottiene informazioni sul supporto AVIF
     */
    public function getSupportInfo(): array
    {
        $info = [
            'php_version' => PHP_VERSION,
            'php_version_sufficient' => PHP_VERSION_ID >= 80100,
            'imageavif_exists' => function_exists('imageavif'),
            'gd_info' => [],
        ];

        if (function_exists('gd_info')) {
            $info['gd_info'] = gd_info();
        }

        $info['supported'] = $this->isSupported();

        return $info;
    }

    /**
     * Calcola risparmio potenziale
     */
    public function estimateSavings(string $sourcePath): array
    {
        if (!file_exists($sourcePath)) {
            return [
                'error' => 'File not found',
            ];
        }

        $sourceSize = filesize($sourcePath);
        
        // Stima: AVIF è tipicamente 20-50% più piccolo di WebP
        // e 30-60% più piccolo di JPEG
        $estimatedSize = (int) ($sourceSize * 0.4); // Stima conservativa 40%

        return [
            'source_size' => $sourceSize,
            'estimated_avif_size' => $estimatedSize,
            'estimated_savings' => $sourceSize - $estimatedSize,
            'estimated_savings_percent' => round((($sourceSize - $estimatedSize) / $sourceSize) * 100, 2),
        ];
    }
}

