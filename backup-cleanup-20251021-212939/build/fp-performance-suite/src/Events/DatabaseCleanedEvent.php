<?php

namespace FP\PerfSuite\Events;

/**
 * Event fired when database cleanup completes
 */
class DatabaseCleanedEvent extends Event
{
    public function name(): string
    {
        return 'database_cleaned';
    }

    /**
     * Check if this was a dry run
     */
    public function isDryRun(): bool
    {
        return (bool)$this->get('dry_run', true);
    }

    /**
     * Get cleanup results
     */
    public function getResults(): array
    {
        return (array)$this->get('results', []);
    }

    /**
     * Get total items deleted
     */
    public function getTotalDeleted(): int
    {
        $results = $this->getResults();
        return array_sum(array_column($results, 'deleted'));
    }

    /**
     * Get cleanup scope
     */
    public function getScope(): array
    {
        return (array)$this->get('scope', []);
    }
}
