<?php

namespace FP\PerfSuite\Services\AI\Analyzer;

/**
 * Calcola valori e categorizza dati per l'analisi
 * 
 * @package FP\PerfSuite\Services\AI\Analyzer
 * @author Francesco Passeri
 */
class AnalysisCalculator
{
    /**
     * Parse memory limit
     */
    public function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
                // fall through
            case 'm':
                $value *= 1024;
                // fall through
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Categorizza memoria
     */
    public function categorizeMemory(int $bytes): string
    {
        $mb = $bytes / 1024 / 1024;
        
        if ($mb >= 512) {
            return 'high';
        } elseif ($mb >= 256) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Categorizza dimensione database
     */
    public function categorizeDBSize(float $sizeMB): string
    {
        if ($sizeMB < 100) {
            return 'small';
        } elseif ($sizeMB < 500) {
            return 'medium';
        }
        
        return 'large';
    }

    /**
     * Calcola uno score di ottimizzazione
     */
    public function calculateOptimizationScore(array $analysis): int
    {
        $score = 50; // Base
        
        // +10 se hosting è rilevato
        if ($analysis['hosting']['detected']) {
            $score += 10;
        }
        
        // +15 se risorse sono buone
        if ($analysis['resources']['memory_category'] === 'high') {
            $score += 15;
        } elseif ($analysis['resources']['memory_category'] === 'medium') {
            $score += 10;
        }
        
        // +10 se database è ottimizzato
        if ($analysis['database']['size_category'] === 'small') {
            $score += 10;
        } elseif ($analysis['database']['size_category'] === 'medium') {
            $score += 5;
        }
        
        // +10 se non ci sono troppi plugin
        if ($analysis['plugins']['active'] < 20) {
            $score += 10;
        } elseif ($analysis['plugins']['active'] < 30) {
            $score += 5;
        }
        
        // -10 se c'è già un plugin di caching
        if ($analysis['plugins']['caching']) {
            $score -= 10;
        }
        
        return min(100, max(0, $score));
    }
}















