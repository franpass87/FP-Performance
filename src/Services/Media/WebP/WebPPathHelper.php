<?php

namespace FP\PerfSuite\Services\Media\WebP;

use function filesize;
use function pathinfo;

/**
 * WebP Path Helper Utilities
 *
 * Helper functions for WebP file path manipulation
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class WebPPathHelper
{
    /**
     * Get WebP path for a given file
     *
     * @param string $file Original file path
     * @return string WebP file path
     */
    public function getWebPPath(string $file): string
    {
        $info = pathinfo($file);
        $dir = $info['dirname'] ?? '';
        $filename = $info['filename'] ?? '';
        return ($dir !== '' ? $dir . '/' : '') . $filename . '.webp';
    }

    /**
     * Convert file path to WebP extension
     *
     * @param string $file Original file path
     * @return string Path with .webp extension
     */
    public function withWebPExtension(string $file): string
    {
        $info = pathinfo($file);
        $dirname = $info['dirname'] ?? '';
        $filename = $info['filename'] ?? '';
        $replacement = $filename . '.webp';

        if ($dirname !== '' && $dirname !== '.') {
            return $dirname . '/' . $replacement;
        }

        return $replacement;
    }

    /**
     * Get file size safely
     *
     * @param string $file File path
     * @return int|null File size in bytes or null on failure
     */
    public function safeFilesize(string $file): ?int
    {
        $size = @filesize($file);
        if ($size === false) {
            return null;
        }
        return (int) $size;
    }
}
