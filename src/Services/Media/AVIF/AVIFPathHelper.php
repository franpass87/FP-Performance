<?php

namespace FP\PerfSuite\Services\Media\AVIF;

use FP\PerfSuite\Utils\Logger;

/**
 * AVIF Path Helper
 * 
 * Gestisce i percorsi dei file AVIF
 *
 * @package FP\PerfSuite\Services\Media\AVIF
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class AVIFPathHelper
{
    private const AVIF_DIRECTORY = 'fp-avif';

    /**
     * Ottiene il path AVIF per un'immagine
     */
    public function getAVIFPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);
        $uploadDir = wp_upload_dir();
        
        // Crea path relativo all'upload dir
        $relativePath = str_replace($uploadDir['basedir'], '', $originalPath);
        $relativePath = ltrim($relativePath, '/\\');
        
        // Nuova struttura: uploads/fp-avif/[path]/[filename].avif
        $avifPath = $uploadDir['basedir'] . '/' . self::AVIF_DIRECTORY . '/' . 
                    $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.avif';
        
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $avifPath);
    }

    /**
     * Ottiene l'URL AVIF per un'immagine
     */
    public function getAVIFUrl(string $originalUrl): string
    {
        $uploadDir = wp_upload_dir();
        
        // Estrai path relativo
        $relativePath = str_replace($uploadDir['baseurl'], '', $originalUrl);
        $relativePath = ltrim($relativePath, '/');
        
        $pathInfo = pathinfo($relativePath);
        
        // Nuovo URL
        $avifUrl = $uploadDir['baseurl'] . '/' . self::AVIF_DIRECTORY . '/' . 
                   $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.avif';
        
        return str_replace('\\', '/', $avifUrl);
    }

    /**
     * Verifica se un file AVIF esiste
     */
    public function avifExists(string $originalPath): bool
    {
        $avifPath = $this->getAVIFPath($originalPath);
        return file_exists($avifPath);
    }

    /**
     * Ottiene il path AVIF da un attachment ID
     */
    public function getAVIFPathFromAttachment(int $attachmentId): ?string
    {
        $originalPath = get_attached_file($attachmentId);
        if (!$originalPath) {
            return null;
        }

        return $this->getAVIFPath($originalPath);
    }

    /**
     * Ottiene l'URL AVIF da un attachment ID
     */
    public function getAVIFUrlFromAttachment(int $attachmentId): ?string
    {
        $originalUrl = wp_get_attachment_url($attachmentId);
        if (!$originalUrl) {
            return null;
        }

        return $this->getAVIFUrl($originalUrl);
    }

    /**
     * Verifica se un attachment ha un file AVIF
     */
    public function attachmentHasAVIF(int $attachmentId): bool
    {
        $avifPath = $this->getAVIFPathFromAttachment($attachmentId);
        return $avifPath && file_exists($avifPath);
    }

    /**
     * Ottiene la directory AVIF base
     */
    public function getAVIFDirectory(): string
    {
        $uploadDir = wp_upload_dir();
        return $uploadDir['basedir'] . '/' . self::AVIF_DIRECTORY;
    }

    /**
     * Crea la struttura directory per AVIF
     */
    public function ensureDirectoryExists(string $avifPath): bool
    {
        $dir = dirname($avifPath);
        
        if (file_exists($dir)) {
            return true;
        }

        return wp_mkdir_p($dir);
    }

    /**
     * Elimina il file AVIF per un path
     */
    public function deleteAVIF(string $originalPath): bool
    {
        $avifPath = $this->getAVIFPath($originalPath);
        
        if (!file_exists($avifPath)) {
            return true;
        }

        return @unlink($avifPath);
    }

    /**
     * Elimina AVIF da attachment ID
     */
    public function deleteAVIFFromAttachment(int $attachmentId): bool
    {
        $avifPath = $this->getAVIFPathFromAttachment($attachmentId);
        
        if (!$avifPath || !file_exists($avifPath)) {
            return true;
        }

        return @unlink($avifPath);
    }

    /**
     * Ottiene tutti i file AVIF
     */
    public function getAllAVIFFiles(): array
    {
        $avifDir = $this->getAVIFDirectory();
        
        if (!file_exists($avifDir)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($avifDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'avif') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Calcola lo spazio utilizzato dai file AVIF
     */
    public function getTotalAVIFSize(): int
    {
        $files = $this->getAllAVIFFiles();
        $totalSize = 0;

        foreach ($files as $file) {
            if (file_exists($file)) {
                $totalSize += filesize($file);
            }
        }

        return $totalSize;
    }

    /**
     * Conta i file AVIF
     */
    public function countAVIFFiles(): int
    {
        return count($this->getAllAVIFFiles());
    }

    /**
     * Pulisce la directory AVIF
     */
    public function cleanAVIFDirectory(): bool
    {
        $avifDir = $this->getAVIFDirectory();
        
        if (!file_exists($avifDir)) {
            return true;
        }

        return $this->deleteDirectory($avifDir);
    }

    /**
     * Elimina una directory ricorsivamente
     */
    private function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        return @rmdir($dir);
    }

    /**
     * Ottiene statistiche AVIF
     */
    public function getStats(): array
    {
        return [
            'count' => $this->countAVIFFiles(),
            'total_size' => $this->getTotalAVIFSize(),
            'total_size_formatted' => size_format($this->getTotalAVIFSize()),
            'directory' => $this->getAVIFDirectory(),
        ];
    }

    /**
     * Register the service
     */
    public function register(): void
    {
        // AVIFPathHelper is a utility class that doesn't need WordPress hooks
        // It's used by other services for AVIF path manipulation
        Logger::debug('AVIF Path Helper registered');
    }
}

