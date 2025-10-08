<?php

namespace FP\PerfSuite\Services\Media;

use FP\PerfSuite\Services\Media\WebP\WebPAttachmentProcessor;
use FP\PerfSuite\Services\Media\WebP\WebPBatchProcessor;
use FP\PerfSuite\Services\Media\WebP\WebPImageConverter;
use FP\PerfSuite\Services\Media\WebP\WebPPathHelper;
use FP\PerfSuite\Services\Media\WebP\WebPQueue;
use FP\PerfSuite\Utils\Fs;
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

        if (!$this->settings()['enabled']) {
            return;
        }

        add_filter('wp_generate_attachment_metadata', [$this, 'generateWebp'], 10, 2);
        add_filter('wp_update_attachment_metadata', [$this, 'generateWebp'], 10, 2);
    }

    /**
     * Get current settings
     *
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
     * Generate WebP on attachment upload/update
     *
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
     * Calculate WebP coverage percentage
     *
     * @return float Percentage of images converted to WebP
     */
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

    /**
     * Get conversion status
     *
     * @return array{enabled:bool,quality:int,coverage:float}
     */
    public function status(): array
    {
        $settings = $this->settings();
        return [
            'enabled' => $settings['enabled'],
            'quality' => $settings['quality'],
            'coverage' => $this->coverage(),
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
}
