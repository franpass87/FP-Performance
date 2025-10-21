<?php

namespace FP\PerfSuite\Services\Media;

use FP\PerfSuite\Services\Media\WebP\WebPAttachmentProcessor;
use FP\PerfSuite\Services\Media\WebP\WebPBatchProcessor;
use FP\PerfSuite\Services\Media\WebP\WebPImageConverter;
use FP\PerfSuite\Services\Media\WebP\WebPPathHelper;
use FP\PerfSuite\Services\Media\WebP\WebPQueue;
use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\RateLimiter;

use function add_action;
use function add_filter;
use function get_option;
use function is_array;
use function max;
use function min;
use function update_option;
use function wp_count_attachments;
use function wp_get_attachment_metadata;
use function wp_get_upload_dir;
use function wp_parse_args;
use function wp_update_attachment_metadata;

/**
 * WebP Conversion Orchestrator
 *
 * Coordinates WebP conversion modules for automatic and bulk conversions
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class WebPConverter
{
    private const OPTION = 'fp_ps_webp';
    private const CONVERSION_META = '_fp_ps_webp_generated';

    private Fs $fs;
    private WebPImageConverter $imageConverter;
    private WebPQueue $queue;
    private WebPBatchProcessor $batchProcessor;
    private WebPAttachmentProcessor $attachmentProcessor;
    private WebPPathHelper $pathHelper;

    public function __construct(
        Fs $fs,
        ?RateLimiter $rateLimiter = null,
        ?WebPImageConverter $imageConverter = null,
        ?WebPQueue $queue = null,
        ?WebPAttachmentProcessor $attachmentProcessor = null,
        ?WebPBatchProcessor $batchProcessor = null,
        ?WebPPathHelper $pathHelper = null
    ) {
        $this->fs = $fs;
        $this->pathHelper = $pathHelper ?? new WebPPathHelper();
        $this->imageConverter = $imageConverter ?? new WebPImageConverter();
        $this->queue = $queue ?? new WebPQueue($rateLimiter);
        $this->attachmentProcessor = $attachmentProcessor ?? new WebPAttachmentProcessor(
            $this->imageConverter,
            $this->pathHelper
        );
        $this->batchProcessor = $batchProcessor ?? new WebPBatchProcessor(
            $this->queue,
            $this->attachmentProcessor
        );
    }

    public function register(): void
    {
        add_action($this->queue->getCronHook(), [$this, 'runQueue']);

        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        add_filter('wp_generate_attachment_metadata', [$this, 'generateWebp'], 10, 2);
        add_filter('wp_update_attachment_metadata', [$this, 'generateWebp'], 10, 2);

        // Register automatic WebP delivery if enabled
        if (!empty($settings['auto_deliver'])) {
            $this->registerDelivery();
        }
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,quality:int,keep_original:bool,lossy:bool,auto_deliver:bool}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'quality' => 82,
            'keep_original' => true,
            'lossy' => true,
            'auto_deliver' => true,
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
            'auto_deliver' => isset($settings['auto_deliver']) ? !empty($settings['auto_deliver']) : $current['auto_deliver'],
        ];

        update_option(self::OPTION, $new);
    }

    /**
     * Generate WebP on attachment upload/update
     *
     * @param array<string, mixed> $metadata
     * @param int|string $attachment_id
     * @return array<string, mixed>
     */
    public function generateWebp(array $metadata, int|string $attachment_id): array
    {
        // Ensure attachment_id is an integer (WordPress may pass it as string)
        $attachment_id = (int) $attachment_id;
        
        $settings = $this->settings();
        if (!$settings['enabled']) {
            return $metadata;
        }

        $result = $this->attachmentProcessor->process($attachment_id, $metadata, $settings);
        return $result['metadata'];
    }

    /**
     * Convert single file to WebP
     *
     * @param string $file File path
     * @param array<string, mixed> $settings Conversion settings
     * @param bool $force Force reconversion
     * @return bool True if conversion was successful
     *
     * @deprecated Use WebPImageConverter::convert() directly
     */
    public function convert(string $file, array $settings, bool $force = false): bool
    {
        $webpFile = $this->pathHelper->getWebPPath($file);
        return $this->imageConverter->convert($file, $webpFile, $settings, $force);
    }

    /**
     * Start bulk conversion
     *
     * @param int $limit Maximum number of images
     * @param int $offset Starting offset
     * @return array{converted:int,total:int,queued:bool,error?:string}
     */
    public function bulkConvert(int $limit = 20, int $offset = 0): array
    {
        // EDGE CASE BUG #39: Timeout protection per operazioni batch
        if (function_exists('set_time_limit') && !ini_get('safe_mode')) {
            @set_time_limit(300); // 5 minuti per batch
        }
        
        return $this->queue->initializeBulkConversion($limit, $offset);
    }

    /**
     * Process queued batch (called by cron)
     */
    public function runQueue(): void
    {
        $settings = $this->settings();
        $this->batchProcessor->processBatch($settings);
    }

    /**
     * Count total images in media library
     *
     * @return int Total number of images
     */
    public function countTotalImages(): int
    {
        $attachments = wp_count_attachments('image');
        $count = 0;

        if (is_object($attachments)) {
            // Sum all image statuses (inherit, publish, private, etc.)
            foreach ($attachments as $status => $num) {
                if ($status !== 'trash') {
                    $count += (int) $num;
                }
            }
        }

        return $count;
    }

    /**
     * Count converted images (with WebP)
     *
     * @return int Number of images with WebP version
     */
    public function countConvertedImages(): int
    {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
            self::CONVERSION_META,
            '1'
        );

        if ($query === false) {
            return 0;
        }

        return (int) $wpdb->get_var($query);
    }

    /**
     * Calculate WebP coverage percentage
     *
     * @return float Percentage of images converted to WebP
     */
    public function coverage(): float
    {
        $total = $this->countTotalImages();

        if ($total === 0) {
            return 100.0;
        }

        $webp = $this->countConvertedImages();
        return min(100.0, ($webp / $total) * 100);
    }

    /**
     * Get conversion status
     *
     * @return array{enabled:bool,quality:int,coverage:float,auto_deliver:bool,total_images:int,converted_images:int}
     */
    public function status(): array
    {
        $settings = $this->settings();
        $totalImages = $this->countTotalImages();
        $convertedImages = $this->countConvertedImages();
        
        return [
            'enabled' => $settings['enabled'],
            'quality' => $settings['quality'],
            'coverage' => $totalImages > 0 ? min(100.0, ($convertedImages / $totalImages) * 100) : 100.0,
            'auto_deliver' => $settings['auto_deliver'],
            'total_images' => $totalImages,
            'converted_images' => $convertedImages,
        ];
    }

    // Getters for modular components (useful for testing and extension)

    public function getImageConverter(): WebPImageConverter
    {
        return $this->imageConverter;
    }

    public function getQueue(): WebPQueue
    {
        return $this->queue;
    }

    public function getBatchProcessor(): WebPBatchProcessor
    {
        return $this->batchProcessor;
    }

    public function getAttachmentProcessor(): WebPAttachmentProcessor
    {
        return $this->attachmentProcessor;
    }

    public function getPathHelper(): WebPPathHelper
    {
        return $this->pathHelper;
    }

    /**
     * Register WebP delivery filters
     */
    private function registerDelivery(): void
    {
        if (!$this->shouldDeliverWebP()) {
            return;
        }

        // Rewrite attachment image sources
        add_filter('wp_get_attachment_image_src', [$this, 'filterAttachmentImageSrc'], 10, 4);
        
        // Rewrite srcset for responsive images
        add_filter('wp_calculate_image_srcset', [$this, 'filterImageSrcset'], 10, 5);
        
        // Rewrite images in post content
        add_filter('the_content', [$this, 'filterContentImages'], 20);

        Logger::info('WebP automatic delivery enabled');
        do_action('fp_ps_webp_delivery_registered');
    }

    /**
     * Check if WebP should be delivered to client
     *
     * @return bool True if WebP delivery is supported
     */
    private function shouldDeliverWebP(): bool
    {
        // Check if client accepts WebP
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $supportsWebP = strpos($accept, 'image/webp') !== false;

        // Allow filtering
        $supportsWebP = apply_filters('fp_ps_webp_delivery_supported', $supportsWebP);

        return $supportsWebP;
    }

    /**
     * Rewrite single image URL to WebP if available
     *
     * @param string $url Original image URL
     * @return string WebP URL if available, original otherwise
     */
    private function rewriteImageUrl(string $url): string
    {
        // Skip external URLs
        $uploadDir = wp_get_upload_dir();
        $baseUrl = $uploadDir['baseurl'] ?? '';
        
        if ($baseUrl === '' || strpos($url, $baseUrl) === false) {
            return $url;
        }

        // Get filesystem path
        $relativePath = str_replace($baseUrl, '', $url);
        $uploadPath = $uploadDir['basedir'] ?? '';
        $filePath = $uploadPath . $relativePath;

        // Get WebP filesystem path
        $webpPath = $this->pathHelper->getWebPPath($filePath);

        // Check if WebP exists
        if (!file_exists($webpPath)) {
            return $url;
        }

        // Build WebP URL correctly (replace extension in URL, not in path)
        // Remove original extension and add .webp
        $pathInfo = pathinfo($url);
        $webpUrl = ($pathInfo['dirname'] !== '.' ? $pathInfo['dirname'] . '/' : '') . 
                   ($pathInfo['filename'] ?? '') . '.webp';

        // Log delivery (only in debug mode to avoid spam)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            Logger::debug('WebP delivered', [
                'original' => basename($url),
                'webp' => basename($webpUrl),
            ]);
        }

        return $webpUrl;
    }

    /**
     * Filter attachment image source to serve WebP
     *
     * @param array|false $image Image data or false
     * @param int|string $attachment_id Attachment ID
     * @param string|int[] $size Image size
     * @param bool $icon Whether to use icon
     * @return array|false Modified image data
     */
    public function filterAttachmentImageSrc($image, int|string $attachment_id, $size, bool $icon)
    {
        if (!is_array($image) || empty($image[0])) {
            return $image;
        }

        $image[0] = $this->rewriteImageUrl($image[0]);

        return $image;
    }

    /**
     * Filter image srcset to serve WebP
     *
     * @param array $sources Srcset sources
     * @param array $size_array Size array
     * @param string $image_src Image source URL
     * @param array $image_meta Image metadata
     * @param int|string $attachment_id Attachment ID
     * @return array Modified srcset sources
     */
    public function filterImageSrcset(array $sources, array $size_array, string $image_src, array $image_meta, int|string $attachment_id): array
    {
        foreach ($sources as $key => $source) {
            if (!empty($source['url'])) {
                $sources[$key]['url'] = $this->rewriteImageUrl($source['url']);
            }
        }

        return $sources;
    }

    /**
     * Filter post content to serve WebP images
     *
     * @param string $content Post content
     * @return string Modified content
     */
    public function filterContentImages(string $content): string
    {
        if (empty($content) || strpos($content, '<img') === false) {
            return $content;
        }

        // Match all img tags
        $pattern = '/<img([^>]+)src=["\']([^"\']+)["\']([^>]*)>/i';
        
        $content = preg_replace_callback($pattern, function($matches) {
            $beforeSrc = $matches[1];
            $src = $matches[2];
            $afterSrc = $matches[3];

            // Rewrite main src
            $newSrc = $this->rewriteImageUrl($src);

            // Build new img tag
            $newTag = '<img' . $beforeSrc . 'src="' . $newSrc . '"' . $afterSrc . '>';

            // Also rewrite srcset if present
            if (strpos($afterSrc, 'srcset=') !== false) {
                $newTag = preg_replace_callback(
                    '/srcset=["\']([^"\']+)["\']/i',
                    function($srcsetMatch) {
                        $srcset = $srcsetMatch[1];
                        $sources = explode(',', $srcset);
                        $rewritten = [];

                        foreach ($sources as $source) {
                            $trimmed = trim($source);
                            if ($trimmed === '') {
                                continue;
                            }
                            $parts = preg_split('/\s+/', $trimmed);
                            if (!empty($parts[0])) {
                                $parts[0] = $this->rewriteImageUrl($parts[0]);
                                $rewritten[] = implode(' ', $parts);
                            }
                        }

                        return 'srcset="' . implode(', ', $rewritten) . '"';
                    },
                    $newTag
                );
            }

            return $newTag;
        }, $content);

        return $content;
    }
}
