<?php

namespace FP\PerfSuite\Services\Media\AVIF;

/**
 * AVIF Path Helper
 *
 * Helper class for AVIF file path operations
 *
 * @package FP\PerfSuite\Services\Media\AVIF
 * @author Francesco Passeri
 */
class AVIFPathHelper
{
    /**
     * Get AVIF path for given image path
     *
     * @param string $path Original image path
     * @return string AVIF path (e.g., image.jpg -> image.jpg.avif)
     */
    public function getAVIFPath(string $path): string
    {
        return $path . '.avif';
    }

    /**
     * Check if file is already AVIF
     *
     * @param string $path File path
     * @return bool True if file ends with .avif
     */
    public function isAVIF(string $path): bool
    {
        return substr($path, -5) === '.avif';
    }

    /**
     * Get original path from AVIF path
     *
     * @param string $avifPath AVIF file path
     * @return string Original path
     */
    public function getOriginalPath(string $avifPath): string
    {
        if ($this->isAVIF($avifPath)) {
            return substr($avifPath, 0, -5);
        }
        return $avifPath;
    }

    /**
     * Check if AVIF version exists for given file
     *
     * @param string $path Original file path
     * @return bool True if AVIF version exists
     */
    public function avifExists(string $path): bool
    {
        $avifPath = $this->getAVIFPath($path);
        return file_exists($avifPath);
    }

    /**
     * Get file size comparison
     *
     * @param string $originalPath Original file path
     * @return array{original:int,avif:int,exists:bool} File sizes
     */
    public function getSizeComparison(string $originalPath): array
    {
        $avifPath = $this->getAVIFPath($originalPath);
        
        return [
            'original' => file_exists($originalPath) ? filesize($originalPath) : 0,
            'avif' => file_exists($avifPath) ? filesize($avifPath) : 0,
            'exists' => file_exists($avifPath),
        ];
    }

    /**
     * Delete AVIF file for given original
     *
     * @param string $originalPath Original file path
     * @return bool True if deleted or doesn't exist
     */
    public function deleteAVIF(string $originalPath): bool
    {
        $avifPath = $this->getAVIFPath($originalPath);
        
        if (!file_exists($avifPath)) {
            return true;
        }

        return unlink($avifPath);
    }
}
