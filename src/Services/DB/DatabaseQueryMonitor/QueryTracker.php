<?php

namespace FP\PerfSuite\Services\DB\DatabaseQueryMonitor;

/**
 * Traccia le query database
 * 
 * @package FP\PerfSuite\Services\DB\DatabaseQueryMonitor
 * @author Francesco Passeri
 */
class QueryTracker
{
    private array $queries = [];
    private array $slowQueries = [];
    private float $totalQueryTime = 0;
    private int $duplicateCount = 0;
    private float $slowQueryThreshold;

    public function __construct(float $slowQueryThreshold = 0.005)
    {
        $this->slowQueryThreshold = $slowQueryThreshold;
    }

    /**
     * Registra una query
     */
    public function trackQuery(string $query, float $queryTime, string $caller): void
    {
        $queryHash = md5($query);
        
        // Registra la query
        if (!isset($this->queries[$queryHash])) {
            $this->queries[$queryHash] = [
                'query' => $query,
                'caller' => $caller,
                'count' => 0,
                'total_time' => 0,
                'max_time' => 0,
                'min_time' => PHP_FLOAT_MAX,
            ];
        }
        
        // Aggiorna le statistiche
        $this->queries[$queryHash]['count']++;
        $this->queries[$queryHash]['total_time'] += $queryTime;
        $this->queries[$queryHash]['max_time'] = max($this->queries[$queryHash]['max_time'], $queryTime);
        $this->queries[$queryHash]['min_time'] = min($this->queries[$queryHash]['min_time'], $queryTime);
        
        // Aggiorna il tempo totale
        $this->totalQueryTime += $queryTime;
        
        // Query lenta?
        if ($queryTime > $this->slowQueryThreshold) {
            $this->slowQueries[] = [
                'query' => $query,
                'time' => $queryTime,
                'caller' => $caller,
                'timestamp' => time(),
            ];
        }
        
        // Query duplicate?
        if ($this->queries[$queryHash]['count'] > 1) {
            $this->duplicateCount++;
        }
    }

    /**
     * Ottiene tutte le query tracciate
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * Ottiene le query lente
     */
    public function getSlowQueries(): array
    {
        return $this->slowQueries;
    }

    /**
     * Ottiene il tempo totale delle query
     */
    public function getTotalQueryTime(): float
    {
        return $this->totalQueryTime;
    }

    /**
     * Ottiene il conteggio delle query duplicate
     */
    public function getDuplicateCount(): int
    {
        return $this->duplicateCount;
    }

    /**
     * Resetta le statistiche
     */
    public function reset(): void
    {
        $this->queries = [];
        $this->slowQueries = [];
        $this->totalQueryTime = 0;
        $this->duplicateCount = 0;
    }
}
















