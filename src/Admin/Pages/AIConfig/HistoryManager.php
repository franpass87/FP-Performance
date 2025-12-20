<?php

namespace FP\PerfSuite\Admin\Pages\AIConfig;

use function get_option;
use function update_option;
use function time;
use function array_slice;

/**
 * Gestisce la storia delle analisi AI
 * 
 * @package FP\PerfSuite\Admin\Pages\AIConfig
 * @author Francesco Passeri
 */
class HistoryManager
{
    /**
     * Salva un'analisi nella storia
     */
    public function save(array $analysis, int $score): void
    {
        $history = get_option('fp_ps_ai_analysis_history', []);
        
        $history[] = [
            'timestamp' => time(),
            'score' => $score,
            'hosting' => $analysis['hosting']['name'] ?? 'Unknown',
            'memory' => $analysis['resources']['memory_limit'] ?? 'N/A',
            'db_size' => $analysis['database']['size_mb'] ?? 0,
        ];
        
        // Keep only last 10 analyses
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }
        
        update_option('fp_ps_ai_analysis_history', $history);
    }

    /**
     * Recupera la storia completa
     */
    public function getHistory(): array
    {
        return get_option('fp_ps_ai_analysis_history', []);
    }
}
















