<?php

namespace FP\PerfSuite\Services\Intelligence;

use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Utils\Logger;

/**
 * Performance-Based Exclusion Detector
 * 
 * Integra il Performance Monitor con Smart Exclusion per rilevare
 * automaticamente pagine problematiche e suggerire esclusioni
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class PerformanceBasedExclusionDetector
{
    private PerformanceMonitor $monitor;
    private SmartExclusionDetector $smartDetector;

    public function __construct()
    {
        $this->monitor = PerformanceMonitor::instance();
        $this->smartDetector = new SmartExclusionDetector();
    }

    /**
     * Rileva pagine problematiche basandosi sulle metriche di performance
     */
    public function detectProblematicPages(int $days = 7, array $thresholds = []): array
    {
        $defaultThresholds = [
            'slow_load_time' => 3.0, // secondi
            'high_queries' => 50,    // query database
            'high_memory' => 64,     // MB
            'error_rate' => 0.1,     // 10% errori
        ];

        $thresholds = array_merge($defaultThresholds, $thresholds);

        $recentMetrics = $this->monitor->getRecent(1000);
        $cutoff = time() - ($days * DAY_IN_SECONDS);
        
        // Filtra metriche recenti
        $filteredMetrics = array_filter($recentMetrics, function($metric) use ($cutoff) {
            return isset($metric['timestamp']) && $metric['timestamp'] >= $cutoff;
        });

        if (empty($filteredMetrics)) {
            return [
                'problematic_pages' => [],
                'suggestions' => [],
                'stats' => [
                    'total_pages' => 0,
                    'problematic_count' => 0,
                    'suggestions_count' => 0,
                ]
            ];
        }

        // Raggruppa per URL
        $urlMetrics = [];
        foreach ($filteredMetrics as $metric) {
            $url = $metric['url'] ?? '/';
            if (!isset($urlMetrics[$url])) {
                $urlMetrics[$url] = [
                    'load_times' => [],
                    'queries' => [],
                    'memory' => [],
                    'errors' => 0,
                    'total_requests' => 0,
                ];
            }

            $urlMetrics[$url]['total_requests']++;
            
            if (isset($metric['load_time'])) {
                $urlMetrics[$url]['load_times'][] = $metric['load_time'];
            }
            if (isset($metric['db_queries'])) {
                $urlMetrics[$url]['queries'][] = $metric['db_queries'];
            }
            if (isset($metric['memory_peak'])) {
                $urlMetrics[$url]['memory'][] = $metric['memory_peak'] / 1024 / 1024; // Convert to MB
            }
            if (isset($metric['has_error']) && $metric['has_error']) {
                $urlMetrics[$url]['errors']++;
            }
        }

        $problematicPages = [];
        $suggestions = [];

        foreach ($urlMetrics as $url => $metrics) {
            $analysis = $this->analyzePageMetrics($url, $metrics, $thresholds);
            
            if ($analysis['is_problematic']) {
                $problematicPages[] = $analysis;
                
                // Genera suggerimenti per esclusioni
                if ($analysis['should_exclude_from_cache']) {
                    $suggestions[] = [
                        'url' => $url,
                        'type' => 'cache_exclusion',
                        'reason' => $analysis['cache_exclusion_reason'],
                        'confidence' => $analysis['confidence'],
                        'priority' => $analysis['priority'],
                    ];
                }
            }
        }

        // Ordina per priorità
        usort($problematicPages, fn($a, $b) => $b['priority'] - $a['priority']);
        usort($suggestions, fn($a, $b) => $b['confidence'] - $a['confidence']);

        return [
            'problematic_pages' => $problematicPages,
            'suggestions' => $suggestions,
            'stats' => [
                'total_pages' => count($urlMetrics),
                'problematic_count' => count($problematicPages),
                'suggestions_count' => count($suggestions),
            ]
        ];
    }

    /**
     * Analizza le metriche di una singola pagina
     */
    private function analyzePageMetrics(string $url, array $metrics, array $thresholds): array
    {
        $analysis = [
            'url' => $url,
            'is_problematic' => false,
            'should_exclude_from_cache' => false,
            'cache_exclusion_reason' => '',
            'confidence' => 0,
            'priority' => 0,
            'issues' => [],
            'avg_load_time' => 0,
            'avg_queries' => 0,
            'avg_memory' => 0,
            'error_rate' => 0,
            'total_requests' => $metrics['total_requests'],
        ];

        // Calcola medie
        if (!empty($metrics['load_times'])) {
            $analysis['avg_load_time'] = round(array_sum($metrics['load_times']) / count($metrics['load_times']), 3);
        }
        if (!empty($metrics['queries'])) {
            $analysis['avg_queries'] = round(array_sum($metrics['queries']) / count($metrics['queries']), 1);
        }
        if (!empty($metrics['memory'])) {
            $analysis['avg_memory'] = round(array_sum($metrics['memory']) / count($metrics['memory']), 2);
        }
        if ($metrics['total_requests'] > 0) {
            $analysis['error_rate'] = round($metrics['errors'] / $metrics['total_requests'], 3);
        }

        $issues = [];
        $confidence = 0;
        $priority = 0;

        // 1. Controlla tempi di caricamento lenti
        if ($analysis['avg_load_time'] > $thresholds['slow_load_time']) {
            $issues[] = sprintf(
                __('Tempo di caricamento lento: %s secondi (soglia: %s)', 'fp-performance-suite'),
                $analysis['avg_load_time'],
                $thresholds['slow_load_time']
            );
            $confidence += 0.3;
            $priority += 2;
        }

        // 2. Controlla query database eccessive
        if ($analysis['avg_queries'] > $thresholds['high_queries']) {
            $issues[] = sprintf(
                __('Troppe query database: %s (soglia: %s)', 'fp-performance-suite'),
                $analysis['avg_queries'],
                $thresholds['high_queries']
            );
            $confidence += 0.4;
            $priority += 3;
        }

        // 3. Controlla uso memoria eccessivo
        if ($analysis['avg_memory'] > $thresholds['high_memory']) {
            $issues[] = sprintf(
                __('Uso memoria eccessivo: %s MB (soglia: %s MB)', 'fp-performance-suite'),
                $analysis['avg_memory'],
                $thresholds['high_memory']
            );
            $confidence += 0.2;
            $priority += 1;
        }

        // 4. Controlla tasso di errori
        if ($analysis['error_rate'] > $thresholds['error_rate']) {
            $issues[] = sprintf(
                __('Alto tasso di errori: %s%% (soglia: %s%%)', 'fp-performance-suite'),
                round($analysis['error_rate'] * 100, 1),
                round($thresholds['error_rate'] * 100, 1)
            );
            $confidence += 0.5;
            $priority += 4;
        }

        // 5. Controlla se è una pagina sensibile (già rilevata da Smart Exclusion)
        $smartAnalysis = $this->smartDetector->analyzePage($url);
        if ($smartAnalysis['should_exclude_cache']) {
            $issues[] = __('Pagina sensibile rilevata da Smart Exclusion', 'fp-performance-suite');
            $confidence += 0.6;
            $priority += 5;
        }

        // Determina se è problematica
        $analysis['is_problematic'] = !empty($issues);
        $analysis['issues'] = $issues;
        $analysis['confidence'] = min(1.0, $confidence);
        $analysis['priority'] = $priority;

        // Determina se escludere dalla cache
        if ($analysis['is_problematic']) {
            $analysis['should_exclude_from_cache'] = true;
            
            // Genera ragione per esclusione
            $reasons = [];
            if ($analysis['avg_load_time'] > $thresholds['slow_load_time']) {
                $reasons[] = __('caricamento lento', 'fp-performance-suite');
            }
            if ($analysis['avg_queries'] > $thresholds['high_queries']) {
                $reasons[] = __('query eccessive', 'fp-performance-suite');
            }
            if ($analysis['error_rate'] > $thresholds['error_rate']) {
                $reasons[] = __('errori frequenti', 'fp-performance-suite');
            }
            if ($smartAnalysis['should_exclude_cache']) {
                $reasons[] = __('contenuto sensibile', 'fp-performance-suite');
            }

            $analysis['cache_exclusion_reason'] = sprintf(
                __('Pagina problematica: %s', 'fp-performance-suite'),
                implode(', ', $reasons)
            );
        }

        return $analysis;
    }

    /**
     * Applica automaticamente esclusioni basate su performance
     */
    public function autoApplyPerformanceExclusions(bool $dryRun = true): array
    {
        $detection = $this->detectProblematicPages();
        $results = [
            'applied' => 0,
            'skipped' => 0,
            'already_exists' => 0,
            'exclusions' => [],
        ];

        foreach ($detection['suggestions'] as $suggestion) {
            // Applica solo se confidence >= 0.7
            if ($suggestion['confidence'] >= 0.7) {
                if (!$dryRun) {
                    $added = $this->smartDetector->addExclusion($suggestion['url'], [
                        'type' => 'performance_based',
                        'reason' => $suggestion['reason'],
                        'confidence' => $suggestion['confidence'],
                        'priority' => $suggestion['priority'],
                    ]);
                    
                    if ($added) {
                        $results['applied']++;
                        $results['exclusions'][] = $suggestion;
                    } else {
                        $results['already_exists']++;
                    }
                } else {
                    $results['applied']++;
                    $results['exclusions'][] = $suggestion;
                }
            } else {
                $results['skipped']++;
            }
        }

        return $results;
    }

    /**
     * Ottieni report dettagliato delle performance
     */
    public function getPerformanceReport(int $days = 7): array
    {
        $detection = $this->detectProblematicPages($days);
        $trends = $this->monitor->getTrends($days);
        $stats = $this->monitor->getStats($days);

        return [
            'period_days' => $days,
            'overall_stats' => $stats,
            'trends' => $trends,
            'problematic_pages' => $detection['problematic_pages'],
            'suggestions' => $detection['suggestions'],
            'summary' => [
                'total_pages_analyzed' => $detection['stats']['total_pages'],
                'problematic_pages' => $detection['stats']['problematic_count'],
                'suggestions_generated' => $detection['stats']['suggestions_count'],
                'avg_load_time' => $stats['avg_load_time'],
                'avg_queries' => $stats['avg_queries'],
                'avg_memory' => $stats['avg_memory'],
            ]
        ];
    }

    /**
     * Rileva pattern di performance per ottimizzazione automatica
     */
    public function detectOptimizationPatterns(): array
    {
        $patterns = [
            'slow_pages' => [],
            'high_query_pages' => [],
            'memory_intensive_pages' => [],
            'error_prone_pages' => [],
        ];

        $detection = $this->detectProblematicPages();

        foreach ($detection['problematic_pages'] as $page) {
            if ($page['avg_load_time'] > 2.0) {
                $patterns['slow_pages'][] = $page;
            }
            if ($page['avg_queries'] > 30) {
                $patterns['high_query_pages'][] = $page;
            }
            if ($page['avg_memory'] > 50) {
                $patterns['memory_intensive_pages'][] = $page;
            }
            if ($page['error_rate'] > 0.05) {
                $patterns['error_prone_pages'][] = $page;
            }
        }

        return $patterns;
    }

    /**
     * Suggerisci ottimizzazioni specifiche per pagina
     */
    public function suggestPageOptimizations(string $url): array
    {
        $suggestions = [];
        
        // Analizza la pagina specifica
        $detection = $this->detectProblematicPages(30);
        $pageData = null;
        
        foreach ($detection['problematic_pages'] as $page) {
            if ($page['url'] === $url) {
                $pageData = $page;
                break;
            }
        }

        if (!$pageData) {
            return ['suggestions' => [], 'message' => __('Pagina non trovata nei dati di performance', 'fp-performance-suite')];
        }

        // Suggerimenti basati sui problemi rilevati
        if ($pageData['avg_load_time'] > 2.0) {
            $suggestions[] = [
                'type' => 'cache_exclusion',
                'reason' => __('Tempo di caricamento lento - escludi dalla cache', 'fp-performance-suite'),
                'confidence' => 0.8,
            ];
        }

        if ($pageData['avg_queries'] > 30) {
            $suggestions[] = [
                'type' => 'query_optimization',
                'reason' => __('Troppe query database - ottimizza query', 'fp-performance-suite'),
                'confidence' => 0.9,
            ];
        }

        if ($pageData['error_rate'] > 0.05) {
            $suggestions[] = [
                'type' => 'error_handling',
                'reason' => __('Alto tasso di errori - migliora gestione errori', 'fp-performance-suite'),
                'confidence' => 0.95,
            ];
        }

        return [
            'suggestions' => $suggestions,
            'page_data' => $pageData,
        ];
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // PerformanceBasedExclusionDetector non ha hook specifici da registrare
        // È utilizzato principalmente per analisi on-demand
    }
}
