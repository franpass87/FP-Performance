<?php

namespace FP\PerfSuite\Services\DB\DatabaseQueryMonitor;

/**
 * Fornisce raccomandazioni basate sull'analisi delle query
 * 
 * @package FP\PerfSuite\Services\DB\DatabaseQueryMonitor
 * @author Francesco Passeri
 */
class QueryRecommendations
{
    /**
     * Analizza le query e fornisce raccomandazioni
     */
    public function analyzeAndRecommend(array $stats): array
    {
        $recommendations = [];
        
        // Troppe query?
        if ($stats['total_queries'] > 100) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Numero elevato di query',
                'message' => sprintf(
                    'Rilevate %d query database. Questo può rallentare il caricamento della pagina.',
                    $stats['total_queries']
                ),
                'suggestions' => [
                    'Attiva Object Caching (Redis, Memcached o APCu)',
                    'Disabilita plugin non necessari',
                    'Usa un plugin di query monitoring come Query Monitor per identificare i plugin problematici',
                    'Considera l\'uso di lazy loading per i dati non essenziali',
                ],
                'priority' => $stats['total_queries'] > 150 ? 'high' : 'medium',
            ];
        }
        
        // Query lente?
        if ($stats['slow_queries'] > 0) {
            $recommendations[] = [
                'type' => 'error',
                'title' => 'Query lente rilevate',
                'message' => sprintf(
                    'Rilevate %d query che impiegano più tempo del normale.',
                    $stats['slow_queries']
                ),
                'suggestions' => [
                    'Aggiungi indici alle colonne utilizzate nelle WHERE clauses',
                    'Ottimizza le query complesse',
                    'Usa EXPLAIN per analizzare le query lente',
                    'Considera l\'uso di query caching',
                ],
                'priority' => 'high',
            ];
        }
        
        // Query duplicate?
        if ($stats['duplicate_queries'] > 0) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Query duplicate rilevate',
                'message' => sprintf(
                    'Rilevate %d query duplicate. Questo può essere ottimizzato.',
                    $stats['duplicate_queries']
                ),
                'suggestions' => [
                    'Usa Object Caching per evitare query duplicate',
                    'Implementa query caching per le query più frequenti',
                    'Rivedi il codice per evitare query ridondanti',
                ],
                'priority' => 'medium',
            ];
        }
        
        // Tempo totale eccessivo?
        if ($stats['total_query_time'] > 0.5) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Tempo totale query elevato',
                'message' => sprintf(
                    'Il tempo totale delle query è %.4f secondi. Questo può rallentare il caricamento.',
                    $stats['total_query_time']
                ),
                'suggestions' => [
                    'Ottimizza le query più lente',
                    'Usa Object Caching',
                    'Considera l\'uso di database query caching',
                ],
                'priority' => 'medium',
            ];
        }
        
        return $recommendations;
    }
}
















