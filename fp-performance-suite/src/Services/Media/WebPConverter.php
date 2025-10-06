<?php

namespace FP\PerfSuite\Services\Media;

use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\RateLimiter;
use WP_Query;
use function add_action;
use function delete_option;
use function delete_post_meta;
use function get_attached_file;
use function get_option;
use function get_post_meta;
use function path_join;
use function update_attached_file;
use function update_post_meta;
use function update_option;
use function wp_get_attachment_metadata;
use function wp_next_scheduled;
use function wp_schedule_single_event;
use function wp_update_attachment_metadata;

class WebPConverter
{
    private const OPTION = 'fp_ps_webp';
    private const CONVERSION_META = '_fp_ps_webp_generated';
    private const SETTINGS_META = '_fp_ps_webp_settings';
    private const WEBP_MIME = 'image/webp';
    private const QUEUE_OPTION = 'fp_ps_webp_queue';
    private const CRON_HOOK = 'fp_ps_webp_process_batch';
    private const CRON_CHUNK = 5;
    private Fs $fs;
    private RateLimiter $rateLimiter;

    public function __construct(Fs $fs, ?RateLimiter $rateLimiter = null)
    {
        $this->fs = $fs;
        $this->rateLimiter = $rateLimiter ?? new RateLimiter();
    }

    public function register(): void
    {
        add_action(self::CRON_HOOK, [$this, 'runQueue']);

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
                Logger::error('Imagick failed to convert to WebP', $e);
            } catch (\Throwable $e) {
                Logger::error('Imagick runtime error during WebP conversion', $e);
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
                    Logger::warning("GD failed to convert to WebP: {$file}");
                }
            }
        }

        if ($converted) {
            Logger::debug('WebP conversion successful', ['file' => basename($file)]);
            do_action('fp_ps_webp_converted', $file);
        }

        return $converted;
    }

    /**
     * @return array{converted:int,total:int,queued:bool}
     */
    public function bulkConvert(int $limit = 20, int $offset = 0): array
    {
        // Rate limiting: max 3 bulk conversions per 30 minutes
        if (!$this->rateLimiter->isAllowed('webp_bulk_convert', 3, 1800)) {
            return [
                'error' => __('Too many bulk conversions. Please try again in 30 minutes.', 'fp-performance-suite'),
                'converted' => 0,
                'total' => 0,
                'queued' => false,
            ];
        }

        $limit = max(1, $limit);
        $offset = max(0, $offset);

        $total = $this->queuedTotal($offset, $limit);

        if ($total <= 0) {
            delete_option(self::QUEUE_OPTION);
            Logger::info('No images to convert to WebP');

            return [
                'converted' => 0,
                'total' => 0,
                'queued' => false,
            ];
        }
        
        Logger::info("Starting WebP bulk conversion: {$total} images");
        do_action('fp_ps_webp_bulk_start', $total);

        $state = [
            'limit' => $limit,
            'offset' => $offset,
            'processed' => 0,
            'converted' => 0,
            'total' => $total,
        ];

        update_option(self::QUEUE_OPTION, $state, false);

        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_single_event(time() + 1, self::CRON_HOOK);
        }

        return [
            'converted' => 0,
            'total' => $total,
            'queued' => true,
        ];
    }

    private function queuedTotal(int $offset, int $limit): int
    {
        $query = new WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => ['image/jpeg', 'image/png'],
            'posts_per_page' => 1,
            'offset' => $offset,
            'fields' => 'ids',
            'no_found_rows' => false,
        ]);

        $total = (int) $query->found_posts;

        if ($total <= $offset) {
            return 0;
        }

        return min($limit, $total - $offset);
    }

    /**
     * Process a queued WebP conversion batch.
     */
    public function runQueue(): void
    {
        $state = get_option(self::QUEUE_OPTION);

        if (!is_array($state)) {
            return;
        }

        $limit = max(1, (int) ($state['limit'] ?? 0));
        $offset = max(0, (int) ($state['offset'] ?? 0));
        $processed = max(0, (int) ($state['processed'] ?? 0));
        $convertedSoFar = max(0, (int) ($state['converted'] ?? 0));

        $remaining = $limit - $processed;

        if ($remaining <= 0) {
            delete_option(self::QUEUE_OPTION);
            return;
        }

        $chunk = min($remaining, self::CRON_CHUNK);
        $batchOffset = $offset + $processed;

        $query = new WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => ['image/jpeg', 'image/png'],
            'posts_per_page' => $chunk,
            'offset' => $batchOffset,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        if (empty($query->posts)) {
            delete_option(self::QUEUE_OPTION);
            return;
        }

        $ids = array_map('intval', $query->posts);
        $converted = $this->convertBatch($ids, $this->settings());

        $processedThisRun = count($ids);
        $state['processed'] = $processed + $processedThisRun;
        $state['converted'] = $convertedSoFar + $converted;

        if ($state['processed'] >= $limit || $processedThisRun < $chunk) {
            delete_option(self::QUEUE_OPTION);
            return;
        }

        update_option(self::QUEUE_OPTION, $state, false);

        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_single_event(time() + 1, self::CRON_HOOK);
        }
    }

    /**
     * @param array<int, int> $attachmentIds
     * @return int
     */
    private function convertBatch(array $attachmentIds, array $settings): int
    {
        $converted = 0;

        foreach ($attachmentIds as $attachmentId) {
            $metadata = wp_get_attachment_metadata($attachmentId);
            $metadata = is_array($metadata) ? $metadata : [];
            $result = $this->processAttachment($attachmentId, $metadata, $settings);
            if (!empty($result['converted'])) {
                $converted++;
            }
            if ($metadata !== $result['metadata']) {
                wp_update_attachment_metadata($attachmentId, $result['metadata']);
            }
        }

        return $converted;
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
