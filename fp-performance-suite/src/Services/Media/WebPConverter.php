<?php

namespace FP\PerfSuite\Services\Media;

use FP\PerfSuite\Utils\Fs;
use WP_Query;
use function delete_post_meta;
use function error_log;
use function get_attached_file;
use function get_post_meta;
use function path_join;
use function update_attached_file;
use function update_post_meta;
use function wp_get_attachment_metadata;
use function wp_update_attachment_metadata;

class WebPConverter
{
    private const OPTION = 'fp_ps_webp';
    private const CONVERSION_META = '_fp_ps_webp_generated';
    private const SETTINGS_META = '_fp_ps_webp_settings';
    private const WEBP_MIME = 'image/webp';
    private Fs $fs;

    public function __construct(Fs $fs)
    {
        $this->fs = $fs;
    }

    public function register(): void
    {
        if (!$this->settings()['enabled']) {
            return;
        }

        add_filter('wp_generate_attachment_metadata', [$this, 'generateWebp'], 10, 2);
        add_filter('wp_update_attachment_metadata', [$this, 'generateWebp'], 10, 2);
    }

    /**
     * @return array{enabled:bool,quality:int,keep_original:bool,lossy:bool}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'quality' => 82,
            'keep_original' => true,
            'lossy' => true,
        ];
        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    public function update(array $settings): void
    {
        $current = $this->settings();
        $quality = isset($settings['quality']) ? (int) $settings['quality'] : $current['quality'];
        $quality = max(1, min(100, $quality));
        $new = [
            'enabled' => !empty($settings['enabled']),
            'quality' => $quality,
            'keep_original' => !empty($settings['keep_original']),
            'lossy' => !empty($settings['lossy']),
        ];
        update_option(self::OPTION, $new);
    }

    /**
     * @param array<string, mixed> $metadata
     * @param int $attachment_id
     * @return array<string, mixed>
     */
    public function generateWebp(array $metadata, int $attachment_id): array
    {
        $settings = $this->settings();
        if (!$settings['enabled']) {
            return $metadata;
        }

        $result = $this->processAttachment($attachment_id, $metadata, $settings);
        return $result['metadata'];
    }

    /**
     * @param string $file
     * @param array<string, mixed> $settings
     * @return bool True when a WebP file was newly created or updated.
     */
    public function convert(string $file, array $settings, bool $force = false): bool
    {
        $info = pathinfo($file);
        $ext = strtolower($info['extension'] ?? '');
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            return false;
        }

        $webpFile = $this->webpPath($file);
        $existingTime = file_exists($webpFile) ? @filemtime($webpFile) : false;
        $sourceTime = @filemtime($file);
        $needsConversion = $force || !$existingTime || !$sourceTime || $sourceTime > $existingTime;

        $converted = false;

        if ($needsConversion && class_exists('Imagick')) {
            try {
                $imagick = new \Imagick($file);
                if ($settings['lossy']) {
                    $imagick->setImageCompressionQuality($settings['quality']);
                }
                $imagick->setImageFormat('webp');
                $converted = (bool) $imagick->writeImage($webpFile);
            } catch (\ImagickException $e) {
                error_log('[FP Performance Suite] Imagick failed to convert to WebP: ' . $e->getMessage());
            } catch (\Throwable $e) {
                error_log('[FP Performance Suite] Imagick runtime error: ' . $e->getMessage());
            } finally {
                if (isset($imagick) && $imagick instanceof \Imagick) {
                    $imagick->clear();
                    $imagick->destroy();
                }
            }
        }

        if (!$converted && $needsConversion && function_exists('imagewebp')) {
            $image = $this->createImageResource($file, $ext);
            if ($image !== null) {
                if (function_exists('imagepalettetotruecolor')) {
                    imagepalettetotruecolor($image);
                }
                if ($ext === 'png') {
                    if (function_exists('imagealphablending')) {
                        imagealphablending($image, false);
                    }
                    if (function_exists('imagesavealpha')) {
                        imagesavealpha($image, true);
                    }
                }
                $converted = imagewebp($image, $webpFile, $settings['quality']);
                imagedestroy($image);
                if (!$converted) {
                    error_log('[FP Performance Suite] GD failed to convert to WebP for ' . $file);
                }
            }
        }

        return $converted;
    }

    public function bulkConvert(int $limit = 20, int $offset = 0): array
    {
        $query = new WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => ['image/jpeg', 'image/png'],
            'posts_per_page' => $limit,
            'offset' => $offset,
            'fields' => 'ids',
        ]);
        $settings = $this->settings();
        $converted = 0;

        foreach ($query->posts as $attachment_id) {
            $metadata = wp_get_attachment_metadata($attachment_id);
            $metadata = is_array($metadata) ? $metadata : [];
            $result = $this->processAttachment((int) $attachment_id, $metadata, $settings);
            if (!empty($result['converted'])) {
                $converted++;
            }
            if ($metadata !== $result['metadata']) {
                wp_update_attachment_metadata($attachment_id, $result['metadata']);
            }
        }

        return [
            'converted' => $converted,
            'total' => (int) $query->found_posts,
        ];
    }

    /**
     * @param array<string,mixed> $metadata
     */
    private function processAttachment(int $attachmentId, array $metadata, array $settings): array
    {
        $converted = false;
        $hasWebp = false;
        $file = get_attached_file($attachmentId);
        $baseDir = '';

        $settingsSignature = [
            'quality' => (int) $settings['quality'],
            'lossy' => (bool) $settings['lossy'],
        ];
        $storedSignature = get_post_meta($attachmentId, self::SETTINGS_META, true);
        $force = !is_array($storedSignature)
            || (int) ($storedSignature['quality'] ?? -1) !== $settingsSignature['quality']
            || (bool) ($storedSignature['lossy'] ?? true) !== $settingsSignature['lossy'];

        if ($file && file_exists($file)) {
            $baseDir = dirname($file);
            if ($this->convert($file, $settings, $force)) {
                $converted = true;
            }
            $webpFile = $this->webpPath($file);
            if (file_exists($webpFile)) {
                $hasWebp = true;
                if (!$settings['keep_original']) {
                    if (!empty($metadata['file'])) {
                        $metadata['file'] = $this->withWebpExtension($metadata['file']);
                    }
                    if (!empty($metadata['original_image'])) {
                        $metadata['original_image'] = $this->withWebpExtension($metadata['original_image']);
                    }
                    $filesize = $this->safeFilesize($webpFile);
                    if ($filesize !== null) {
                        $metadata['filesize'] = $filesize;
                        if (isset($metadata['original_image_filesize'])) {
                            $metadata['original_image_filesize'] = $filesize;
                        }
                    }
                    $metadata['mime-type'] = self::WEBP_MIME;
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                }
            }
        }

        if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $sizeKey => $sizeData) {
                if (empty($sizeData['file'])) {
                    continue;
                }
                $path = $baseDir !== '' ? path_join($baseDir, $sizeData['file']) : '';
                if ($path === '' || !file_exists($path)) {
                    continue;
                }
                if ($this->convert($path, $settings, $force)) {
                    $converted = true;
                }
                $sizeWebp = $this->webpPath($path);
                if (file_exists($sizeWebp)) {
                    $hasWebp = true;
                    if (!$settings['keep_original']) {
                        $metadata['sizes'][$sizeKey]['file'] = $this->withWebpExtension($sizeData['file']);
                        $metadata['sizes'][$sizeKey]['mime-type'] = self::WEBP_MIME;
                        if (isset($metadata['sizes'][$sizeKey]['filesize'])) {
                            $sizeFilesize = $this->safeFilesize($sizeWebp);
                            if ($sizeFilesize !== null) {
                                $metadata['sizes'][$sizeKey]['filesize'] = $sizeFilesize;
                            }
                        }
                        if (file_exists($path)) {
                            @unlink($path);
                        }
                    }
                }
            }
        }

        if ($hasWebp) {
            update_post_meta($attachmentId, self::CONVERSION_META, '1');
            update_post_meta($attachmentId, self::SETTINGS_META, $settingsSignature);
            if (!$settings['keep_original'] && !empty($metadata['file'])) {
                update_attached_file($attachmentId, $metadata['file']);
            }
        } else {
            delete_post_meta($attachmentId, self::CONVERSION_META);
            delete_post_meta($attachmentId, self::SETTINGS_META);
        }

        return ['metadata' => $metadata, 'converted' => $converted];
    }

    public function coverage(): float
    {
        $attachments = wp_count_attachments('image');
        $count = 0;
        if (is_object($attachments) && property_exists($attachments, 'inherit')) {
            $count = (int) $attachments->inherit;
        }
        if ($count === 0) {
            return 100.0;
        }
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
            self::CONVERSION_META,
            '1'
        );
        if ($query === false) {
            return 0.0;
        }
        $webp = (int) $wpdb->get_var($query);
        return min(100.0, ($webp / $count) * 100);
    }

    public function status(): array
    {
        $settings = $this->settings();
        return [
            'enabled' => $settings['enabled'],
            'quality' => $settings['quality'],
            'coverage' => $this->coverage(),
        ];
    }

    /**
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

    private function webpPath(string $file): string
    {
        $info = pathinfo($file);
        $dir = $info['dirname'] ?? '';
        $filename = $info['filename'] ?? '';
        return ($dir !== '' ? $dir . '/' : '') . $filename . '.webp';
    }

    private function withWebpExtension(string $file): string
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

    private function safeFilesize(string $file): ?int
    {
        $size = @filesize($file);
        if ($size === false) {
            return null;
        }
        return (int) $size;
    }
}
