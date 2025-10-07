<?php

namespace FP\PerfSuite\Services\Media\WebP;

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
    private const CRON_CHUNK = 5;

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
        $state = $this->queue->getState();

        if ($state === null) {
            return;
        }

        $remaining = $state['limit'] - $state['processed'];

        if ($remaining <= 0) {
            $this->queue->clear();
            return;
        }

        $chunk = min($remaining, self::CRON_CHUNK);
        $batchOffset = $state['offset'] + $state['processed'];

        $attachmentIds = $this->queue->getNextBatch($chunk, $batchOffset);

        if (empty($attachmentIds)) {
            $this->queue->clear();
            return;
        }

        $converted = $this->processAttachments($attachmentIds, $settings);

        $processedThisRun = count($attachmentIds);
        
        $this->queue->updateState([
            'processed' => $state['processed'] + $processedThisRun,
            'converted' => $state['converted'] + $converted,
        ]);

        $newState = $this->queue->getState();

        // Check if we're done
        if ($newState && ($newState['processed'] >= $newState['limit'] || $processedThisRun < $chunk)) {
            $this->queue->clear();
            return;
        }

        // Schedule next batch
        $this->queue->scheduleBatch();
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
}