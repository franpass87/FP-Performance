<?php

namespace FP\PerfSuite\Services\Media\WebP;

use function delete_post_meta;
use function dirname;
use function file_exists;
use function get_attached_file;
use function get_post_meta;
use function is_array;
use function path_join;
use function update_attached_file;
use function update_post_meta;

/**
 * WebP Attachment Processor
 * 
 * Processes individual WordPress attachments and their sizes for WebP conversion
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class WebPAttachmentProcessor
{
    private const CONVERSION_META = '_fp_ps_webp_generated';
    private const SETTINGS_META = '_fp_ps_webp_settings';
    private const WEBP_MIME = 'image/webp';

    private WebPImageConverter $converter;
    private WebPPathHelper $pathHelper;

    public function __construct(WebPImageConverter $converter, WebPPathHelper $pathHelper)
    {
        $this->converter = $converter;
        $this->pathHelper = $pathHelper;
    }

    /**
     * Process attachment for WebP conversion
     * 
     * @param int $attachmentId WordPress attachment ID
     * @param array<string,mixed> $metadata Attachment metadata
     * @param array{quality:int,lossy:bool,keep_original:bool} $settings Conversion settings
     * @return array{metadata:array<string,mixed>,converted:bool}
     */
    public function process(int $attachmentId, array $metadata, array $settings): array
    {
        $converted = false;
        $hasWebp = false;
        $file = get_attached_file($attachmentId);
        $baseDir = '';

        $force = $this->shouldForceConversion($attachmentId, $settings);

        // Process main file
        if ($file && file_exists($file)) {
            $baseDir = dirname($file);
            $webpFile = $this->pathHelper->getWebPPath($file);
            
            if ($this->converter->convert($file, $webpFile, $settings, $force)) {
                $converted = true;
            }
            
            if (file_exists($webpFile)) {
                $hasWebp = true;
                $metadata = $this->updateMainFileMetadata($metadata, $file, $webpFile, $settings);
            }
        }

        // Process image sizes
        if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {
            $result = $this->processSizes($metadata['sizes'], $baseDir, $settings, $force);
            $metadata['sizes'] = $result['sizes'];
            $converted = $converted || $result['converted'];
            $hasWebp = $hasWebp || $result['hasWebp'];
        }

        // Update post meta
        $this->updatePostMeta($attachmentId, $hasWebp, $settings, $metadata);

        return [
            'metadata' => $metadata,
            'converted' => $converted,
        ];
    }

    /**
     * Check if conversion should be forced
     */
    private function shouldForceConversion(int $attachmentId, array $settings): bool
    {
        $settingsSignature = [
            'quality' => (int) $settings['quality'],
            'lossy' => (bool) $settings['lossy'],
        ];
        
        $storedSignature = get_post_meta($attachmentId, self::SETTINGS_META, true);
        
        return !is_array($storedSignature)
            || (int) ($storedSignature['quality'] ?? -1) !== $settingsSignature['quality']
            || (bool) ($storedSignature['lossy'] ?? true) !== $settingsSignature['lossy'];
    }

    /**
     * Update metadata for main file
     * 
     * @param array<string,mixed> $metadata
     * @param string $originalFile
     * @param string $webpFile
     * @param array{quality:int,lossy:bool,keep_original:bool} $settings
     * @return array<string,mixed>
     */
    private function updateMainFileMetadata(array $metadata, string $originalFile, string $webpFile, array $settings): array
    {
        if (!$settings['keep_original']) {
            if (!empty($metadata['file'])) {
                $metadata['file'] = $this->pathHelper->withWebPExtension($metadata['file']);
            }
            if (!empty($metadata['original_image'])) {
                $metadata['original_image'] = $this->pathHelper->withWebPExtension($metadata['original_image']);
            }
            
            $filesize = $this->pathHelper->safeFilesize($webpFile);
            if ($filesize !== null) {
                $metadata['filesize'] = $filesize;
                if (isset($metadata['original_image_filesize'])) {
                    $metadata['original_image_filesize'] = $filesize;
                }
            }
            
            $metadata['mime-type'] = self::WEBP_MIME;
            
            if (file_exists($originalFile)) {
                @unlink($originalFile);
            }
        }

        return $metadata;
    }

    /**
     * Process image sizes
     * 
     * @param array<string,array<string,mixed>> $sizes
     * @param string $baseDir
     * @param array{quality:int,lossy:bool,keep_original:bool} $settings
     * @param bool $force
     * @return array{sizes:array<string,array<string,mixed>>,converted:bool,hasWebp:bool}
     */
    private function processSizes(array $sizes, string $baseDir, array $settings, bool $force): array
    {
        $converted = false;
        $hasWebp = false;

        foreach ($sizes as $sizeKey => $sizeData) {
            if (empty($sizeData['file'])) {
                continue;
            }
            
            $path = $baseDir !== '' ? path_join($baseDir, $sizeData['file']) : '';
            if ($path === '' || !file_exists($path)) {
                continue;
            }
            
            $sizeWebp = $this->pathHelper->getWebPPath($path);
            
            if ($this->converter->convert($path, $sizeWebp, $settings, $force)) {
                $converted = true;
            }
            
            if (file_exists($sizeWebp)) {
                $hasWebp = true;
                
                if (!$settings['keep_original']) {
                    $sizes[$sizeKey]['file'] = $this->pathHelper->withWebPExtension($sizeData['file']);
                    $sizes[$sizeKey]['mime-type'] = self::WEBP_MIME;
                    
                    if (isset($sizes[$sizeKey]['filesize'])) {
                        $sizeFilesize = $this->pathHelper->safeFilesize($sizeWebp);
                        if ($sizeFilesize !== null) {
                            $sizes[$sizeKey]['filesize'] = $sizeFilesize;
                        }
                    }
                    
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
            }
        }

        return [
            'sizes' => $sizes,
            'converted' => $converted,
            'hasWebp' => $hasWebp,
        ];
    }

    /**
     * Update post metadata
     * 
     * @param int $attachmentId
     * @param bool $hasWebp
     * @param array{quality:int,lossy:bool,keep_original:bool} $settings
     * @param array<string,mixed> $metadata
     */
    private function updatePostMeta(int $attachmentId, bool $hasWebp, array $settings, array $metadata): void
    {
        if ($hasWebp) {
            update_post_meta($attachmentId, self::CONVERSION_META, '1');
            update_post_meta($attachmentId, self::SETTINGS_META, [
                'quality' => (int) $settings['quality'],
                'lossy' => (bool) $settings['lossy'],
            ]);
            
            if (!$settings['keep_original'] && !empty($metadata['file'])) {
                update_attached_file($attachmentId, $metadata['file']);
            }
        } else {
            delete_post_meta($attachmentId, self::CONVERSION_META);
            delete_post_meta($attachmentId, self::SETTINGS_META);
        }
    }
}