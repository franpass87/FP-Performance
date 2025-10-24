<?php

namespace FP\PerfSuite\Services\Media\WebP;

use FP\PerfSuite\Utils\Logger;

use function get_attached_file;
use function is_array;
use function wp_get_attachment_metadata;
use function wp_update_attachment_metadata;

/**
 * WebP Batch Processing Engine
 *
 * Processes batches of images for WebP conversion via cron
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class WebPBatchProcessor
{
    private const CRON_CHUNK = 2; // Riduciamo per processing più veloce

    private WebPQueue $queue;
    private WebPAttachmentProcessor $attachmentProcessor;

    public function __construct(WebPQueue $queue, WebPAttachmentProcessor $attachmentProcessor)
    {
        $this->queue = $queue;
        $this->attachmentProcessor = $attachmentProcessor;
    }

    /**
     * Process queued batch
     *
     * @param array{quality:int,lossy:bool,keep_original:bool} $settings
     */
    public function processBatch(array $settings): void
    {
        error_log('FP Performance Suite: WebPBatchProcessor::processBatch called');
        $state = $this->queue->getState();
        error_log('FP Performance Suite: Queue state: ' . print_r($state, true));

        if ($state === null) {
            error_log('FP Performance Suite: No queue state found');
            return;
        }

        $remaining = $state['limit'] - $state['processed'];

        if ($remaining <= 0) {
            error_log('FP Performance Suite: No remaining items to process, clearing queue');
            $this->queue->clear();
            return;
        }

        $chunk = min($remaining, self::CRON_CHUNK);
        $batchOffset = $state['offset'] + $state['processed'];

        error_log("FP Performance Suite: Processing chunk of $chunk items starting at offset $batchOffset");

        $attachmentIds = $this->queue->getNextBatch($chunk, $batchOffset);
        error_log('FP Performance Suite: Found attachment IDs: ' . print_r($attachmentIds, true));

        if (empty($attachmentIds)) {
            error_log('FP Performance Suite: No more attachments to process, clearing queue');
            $this->queue->clear();
            return;
        }

        $converted = $this->processAttachments($attachmentIds, $settings);

        $processedThisRun = count($attachmentIds);
        error_log("FP Performance Suite: Processed $processedThisRun attachments, converted $converted");

        $this->queue->updateState([
            'processed' => $state['processed'] + $processedThisRun,
            'converted' => $state['converted'] + $converted,
        ]);

        $newState = $this->queue->getState();
        error_log('FP Performance Suite: New state after processing: ' . print_r($newState, true));

        // Check if we're done
        if ($newState && ($newState['processed'] >= $newState['limit'] || $processedThisRun < $chunk)) {
            error_log('FP Performance Suite: Batch processing completed');
            $this->queue->clear();
            return;
        }

        // Non programmare il prossimo batch qui - lasciamo che il polling JavaScript gestisca il prossimo batch
        error_log('FP Performance Suite: Batch processing paused, waiting for next poll');
    }

    /**
     * Process multiple attachments
     *
     * @param array<int, int> $attachmentIds
     * @param array{quality:int,lossy:bool,keep_original:bool} $settings
     * @return int Number of successfully converted attachments
     */
    private function processAttachments(array $attachmentIds, array $settings): int
    {
        $converted = 0;

        foreach ($attachmentIds as $attachmentId) {
            $metadata = wp_get_attachment_metadata($attachmentId);
            $metadata = is_array($metadata) ? $metadata : [];

            $result = $this->attachmentProcessor->process($attachmentId, $metadata, $settings);

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
     * Set chunk size for testing
     *
     * @param int $size
     */
    public function setChunkSize(int $size): void
    {
        // For future implementation if needed
    }

    /**
     * Register the service
     */
    public function register(): void
    {
        // WebPBatchProcessor is a utility class that doesn't need WordPress hooks
        // It's used by other services for batch processing
        Logger::debug('WebP Batch Processor registered');
    }
}
