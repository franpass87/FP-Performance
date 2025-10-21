<?php

namespace FP\PerfSuite\Events;

/**
 * Event fired when cache is cleared
 */
class CacheClearedEvent extends Event
{
    public function name(): string
    {
        return 'cache_cleared';
    }

    /**
     * Get number of files deleted
     */
    public function getFilesDeleted(): int
    {
        return (int)$this->get('files_deleted', 0);
    }

    /**
     * Get cache type
     */
    public function getCacheType(): string
    {
        return (string)$this->get('cache_type', 'page');
    }
}
