<?php

namespace FP\PerfSuite\Services\Media\WebP;

use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\RateLimiter;
use WP_Query;

use function __;
use function delete_option;
use function get_option;
use function max;
use function min;
use function time;
use function update_option;
use function wp_next_scheduled;
use function wp_schedule_single_event;

/**
 * WebP Conversion Queue Manager
 *
 * Manages the queue for bulk WebP conversions
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class WebPQueue
{
    private const QUEUE_OPTION = 'fp_ps_webp_queue';
    private const CRON_HOOK = 'fp_ps_webp_process_batch';

    private RateLimiter $rateLimiter;

    public function __construct(?RateLimiter $rateLimiter = null)
    {
        $this->rateLimiter = $rateLimiter ?? new RateLimiter();
    }

    /**
     * Initialize bulk conversion queue
     *
     * @param int $limit Maximum number of images to convert
     * @param int $offset Starting offset
     * @return array{converted:int,total:int,queued:bool,error?:string}
     */
    public function initializeBulkConversion(int $limit = 20, int $offset = 0): array
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

        $total = $this->countQueuedImages($offset, $limit);

        if ($total <= 0) {
            $this->clear();
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

        $this->scheduleBatch();

        return [
            'converted' => 0,
            'total' => $total,
            'queued' => true,
        ];
    }

    /**
     * Get current queue state
     *
     * @return array{limit:int,offset:int,processed:int,converted:int,total:int}|null
     */
    public function getState(): ?array
    {
        $state = get_option(self::QUEUE_OPTION);

        if (!is_array($state)) {
            return null;
        }

        return [
            'limit' => max(1, (int) ($state['limit'] ?? 0)),
            'offset' => max(0, (int) ($state['offset'] ?? 0)),
            'processed' => max(0, (int) ($state['processed'] ?? 0)),
            'converted' => max(0, (int) ($state['converted'] ?? 0)),
            'total' => max(0, (int) ($state['total'] ?? 0)),
        ];
    }

    /**
     * Update queue state
     *
     * @param array{limit?:int,offset?:int,processed?:int,converted?:int,total?:int} $updates
     */
    public function updateState(array $updates): void
    {
        $state = $this->getState();

        if ($state === null) {
            return;
        }

        foreach ($updates as $key => $value) {
            if (array_key_exists($key, $state)) {
                $state[$key] = (int) $value;
            }
        }

        update_option(self::QUEUE_OPTION, $state, false);
    }

    /**
     * Clear queue
     */
    public function clear(): void
    {
        delete_option(self::QUEUE_OPTION);
    }

    /**
     * Schedule next batch processing
     */
    public function scheduleBatch(): void
    {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_single_event(time() + 1, self::CRON_HOOK);
        }
    }

    /**
     * Get attachment IDs for next batch
     *
     * @param int $batchSize Number of images to process
     * @param int $batchOffset Starting offset for this batch
     * @return array<int, int> Attachment IDs
     */
    public function getNextBatch(int $batchSize, int $batchOffset): array
    {
        $query = new WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => ['image/jpeg', 'image/png'],
            'posts_per_page' => $batchSize,
            'offset' => $batchOffset,
            'fields' => 'ids',
            'no_found_rows' => true,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_fp_ps_webp_generated',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => '_fp_ps_webp_generated',
                    'value' => '1',
                    'compare' => '!=',
                ],
            ],
        ]);

        if (empty($query->posts)) {
            return [];
        }

        return array_map('intval', $query->posts);
    }

    /**
     * Count queued images
     *
     * @param int $offset Starting offset
     * @param int $limit Maximum count
     * @return int
     */
    private function countQueuedImages(int $offset, int $limit): int
    {
        $query = new WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => ['image/jpeg', 'image/png'],
            'posts_per_page' => 1,
            'offset' => $offset,
            'fields' => 'ids',
            'no_found_rows' => false,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_fp_ps_webp_generated',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => '_fp_ps_webp_generated',
                    'value' => '1',
                    'compare' => '!=',
                ],
            ],
        ]);

        $total = (int) $query->found_posts;

        if ($total <= $offset) {
            return 0;
        }

        return min($limit, $total - $offset);
    }

    /**
     * Check if queue is active
     */
    public function isActive(): bool
    {
        return $this->getState() !== null;
    }

    /**
     * Get cron hook name
     */
    public function getCronHook(): string
    {
        return self::CRON_HOOK;
    }
}
