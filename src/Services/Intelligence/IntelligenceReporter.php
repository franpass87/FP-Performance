<?php

namespace FP\PerfSuite\Services\Intelligence;

use FP\PerfSuite\Utils\Logger;

/**
 * Intelligence Reporter
 * 
 * Genera report avanzati sull'efficacia delle esclusioni
 * e suggerisce ottimizzazioni basate sui dati raccolti
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class IntelligenceReporter
{
    private SmartExclusionDetector $smartDetector;
    private PerformanceBasedExclusionDetector $performanceDetector;
    private CacheAutoConfigurator $cacheConfigurator;

    public function __construct()
    {
        $this->smartDetector = new SmartExclusionDetector();
        $this->performanceDetector = new PerformanceBasedExclusionDetector();
        $this->cacheConfigurator = new CacheAutoConfigurator();
    }

    /**
     * Genera report completo di intelligence
     */
    public function generateComprehensiveReport(int $days = 30): array
    {
        $report = [
            'period' => $days,
            'generated_at' => time(),
            'summary' => [],
            'exclusions' => [],
            'performance' => [],
            'cache_optimization' => [],
            'recommendations' => [],
            'trends' => [],
        ];

        try {
            // 1. Analisi esclusioni
            $report['exclusions'] = $this->analyzeExclusions();
            
            // 2. Analisi performance
            $report['performance'] = $this->analyzePerformance($days);
            
            // 3. Analisi ottimizzazione cache
            $report['cache_optimization'] = $this->cacheConfigurator->generateCacheReport();
            
            // 4. Genera raccomandazioni
            $report['recommendations'] = $this->generateRecommendations($report);
            
            // 5. Analizza trend
            $report['trends'] = $this->analyzeTrends($days);
            
            // 6. Calcola summary
            $report['summary'] = $this->calculateSummary($report);

            Logger::info('Intelligence report generated', [
                'period_days' => $days,
                'exclusions_count' => $report['exclusions']['total'],
                'performance_score' => $report['performance']['score'],
            ]);

        } catch (\Exception $e) {
            Logger::error('Intelligence report generation failed', ['error' => $e->getMessage()]);
            $report['error'] = $e->getMessage();
        }

        return $report;
    }

    /**
     * Analizza l'efficacia delle esclusioni
     */
    private function analyzeExclusions(): array
    {
        $appliedExclusions = $this->smartDetector->getAppliedExclusions();
        
        $analysis = [
            'total' => count($appliedExclusions),
            'by_type' => [],
            'by_confidence' => [],
            'effectiveness' => [],
            'recent_additions' => [],
        ];

        // Raggruppa per tipo
        foreach ($appliedExclusions as $exclusion) {
            $type = $exclusion['type'] ?? 'unknown';
            $analysis['by_type'][$type] = ($analysis['by_type'][$type] ?? 0) + 1;
        }

        // Raggruppa per confidence
        foreach ($appliedExclusions as $exclusion) {
            $confidence = $exclusion['confidence'] ?? 0;
            if ($confidence >= 0.9) {
                $analysis['by_confidence']['high'] = ($analysis['by_confidence']['high'] ?? 0) + 1;
            } elseif ($confidence >= 0.7) {
                $analysis['by_confidence']['medium'] = ($analysis['by_confidence']['medium'] ?? 0) + 1;
            } else {
                $analysis['by_confidence']['low'] = ($analysis['by_confidence']['low'] ?? 0) + 1;
            }
        }

        // Analizza efficacia (esclusioni recenti)
        $recentCutoff = time() - (7 * DAY_IN_SECONDS);
        $recentExclusions = array_filter($appliedExclusions, function($exclusion) use ($recentCutoff) {
            return ($exclusion['applied_at'] ?? 0) >= $recentCutoff;
        });

        $analysis['recent_additions'] = count($recentExclusions);
        $analysis['effectiveness'] = $this->calculateExclusionEffectiveness($appliedExclusions);

        return $analysis;
    }

    /**
     * Calcola l'efficacia delle esclusioni
     */
    private function calculateExclusionEffectiveness(array $exclusions): array
    {
        $effectiveness = [
            'score' => 0,
            'high_confidence_ratio' => 0,
            'coverage_score' => 0,
            'optimization_potential' => 0,
        ];

        if (empty($exclusions)) {
            return $effectiveness;
        }

        // Calcola ratio di confidence alta
        $highConfidence = array_filter($exclusions, fn($e) => ($e['confidence'] ?? 0) >= 0.8);
        $exclusionsCount = count($exclusions);
        $effectiveness['high_confidence_ratio'] = $exclusionsCount > 0 ? count($highConfidence) / $exclusionsCount : 0;

        // Calcola score di copertura
        $sensitivePatterns = ['/checkout', '/cart', '/login', '/account', '/payment'];
        $coveredPatterns = 0;
        
        foreach ($sensitivePatterns as $pattern) {
            foreach ($exclusions as $exclusion) {
                if (strpos($exclusion['url'], $pattern) !== false) {
                    $coveredPatterns++;
                    break;
                }
            }
        }
        
        $sensitivePatternsCount = count($sensitivePatterns);
        $effectiveness['coverage_score'] = $sensitivePatternsCount > 0 ? $coveredPatterns / $sensitivePatternsCount : 0;

        // Calcola score complessivo
        $effectiveness['score'] = round(
            ($effectiveness['high_confidence_ratio'] * 0.4) +
            ($effectiveness['coverage_score'] * 0.6),
            2
        );

        return $effectiveness;
    }

    /**
     * Analizza performance del sito
     */
    private function analyzePerformance(int $days): array
    {
        $performanceReport = $this->performanceDetector->getPerformanceReport($days);
        
        $analysis = [
            'score' => 0,
            'avg_load_time' => $performanceReport['summary']['avg_load_time'] ?? 0,
            'problematic_pages' => $performanceReport['summary']['problematic_pages'] ?? 0,
            // BUGFIX #15: optimization_potential non esiste in PerformanceDetector - calcoliamo
            'optimization_potential' => $this->calculateOptimizationPotential($performanceReport),
            'trends' => [],
        ];

        // Calcola score basato su metriche
        $loadTimeScore = $this->calculateLoadTimeScore($analysis['avg_load_time']);
        $problematicScore = $this->calculateProblematicScore($analysis['problematic_pages']);
        
        $analysis['score'] = round(($loadTimeScore + $problematicScore) / 2, 1);

        // Analizza trend
        $trends = $performanceReport['trends'] ?? [];
        if (!empty($trends)) {
            $analysis['trends'] = $this->analyzePerformanceTrendsData($trends);
        }

        return $analysis;
    }

    /**
     * Calcola score basato su tempo di caricamento
     */
    private function calculateLoadTimeScore(float $avgLoadTime): float
    {
        if ($avgLoadTime <= 1.0) return 100;
        if ($avgLoadTime <= 2.0) return 80;
        if ($avgLoadTime <= 3.0) return 60;
        if ($avgLoadTime <= 4.0) return 40;
        return 20;
    }

    /**
     * Calcola score basato su pagine problematiche
     */
    private function calculateProblematicScore(int $problematicPages): float
    {
        if ($problematicPages === 0) return 100;
        if ($problematicPages <= 2) return 80;
        if ($problematicPages <= 5) return 60;
        if ($problematicPages <= 10) return 40;
        return 20;
    }

    /**
     * Analizza trend di performance
     */
    private function analyzePerformanceTrendsData(array $trends): array
    {
        if (count($trends) < 2) {
            return ['status' => 'insufficient_data'];
        }

        $recent = array_slice($trends, -7); // Ultimi 7 giorni
        $older = array_slice($trends, -14, 7); // 7 giorni precedenti

        // Protezione divisione per zero
        $recentCount = count($recent);
        $olderCount = count($older);
        
        $recentAvg = $recentCount > 0 ? array_sum(array_column($recent, 'avg_load_time')) / $recentCount : 0;
        $olderAvg = $olderCount > 0 ? array_sum(array_column($older, 'avg_load_time')) / $olderCount : 0;

        // Calcola miglioramento solo se olderAvg > 0
        $improvement = 0;
        if ($olderAvg > 0) {
            $improvement = (($olderAvg - $recentAvg) / $olderAvg) * 100;
        } elseif ($recentAvg > 0 && $olderAvg === 0) {
            // Se c'erano zero dati prima e ora ci sono, è un peggioramento
            $improvement = -100;
        }

        return [
            'status' => $improvement > 5 ? 'improving' : ($improvement < -5 ? 'declining' : 'stable'),
            'improvement_percentage' => round($improvement, 1),
            'recent_avg' => round($recentAvg, 3),
            'older_avg' => round($olderAvg, 3),
        ];
    }

    /**
     * Genera raccomandazioni basate sui dati
     */
    private function generateRecommendations(array $report): array
    {
        $recommendations = [];

        // Raccomandazioni basate su esclusioni
        if ($report['exclusions']['total'] < 5) {
            $recommendations[] = [
                'category' => 'exclusions',
                'priority' => 'medium',
                'title' => __('Aumenta le esclusioni automatiche', 'fp-performance-suite'),
                'description' => __('Il sistema ha rilevato poche esclusioni. Considera di eseguire il rilevamento automatico per migliorare la protezione.', 'fp-performance-suite'),
                'action' => 'run_auto_detection',
            ];
        }

        // Raccomandazioni basate su performance
        if ($report['performance']['score'] < 70) {
            $recommendations[] = [
                'category' => 'performance',
                'priority' => 'high',
                'title' => __('Ottimizza le performance del sito', 'fp-performance-suite'),
                'description' => sprintf(
                    __('Il score di performance è %s. Considera di ottimizzare le pagine problematiche.', 'fp-performance-suite'),
                    $report['performance']['score']
                ),
                'action' => 'optimize_performance',
            ];
        }

        // Raccomandazioni basate su cache
        if ($report['cache_optimization']['configuration_score'] < 80) {
            $recommendations[] = [
                'category' => 'cache',
                'priority' => 'medium',
                'title' => __('Migliora la configurazione della cache', 'fp-performance-suite'),
                'description' => __('La configurazione della cache può essere ottimizzata per migliorare le performance.', 'fp-performance-suite'),
                'action' => 'optimize_cache_config',
            ];
        }

        // Raccomandazioni basate su trend
        if (isset($report['trends']['performance']['status']) && $report['trends']['performance']['status'] === 'declining') {
            $recommendations[] = [
                'category' => 'trends',
                'priority' => 'high',
                'title' => __('Performance in declino', 'fp-performance-suite'),
                'description' => __('Le performance del sito stanno peggiorando. È necessario un intervento immediato.', 'fp-performance-suite'),
                'action' => 'investigate_declining_performance',
            ];
        }

        return $recommendations;
    }

    /**
     * Analizza trend nel tempo
     */
    private function analyzeTrends(int $days): array
    {
        $trends = [
            'exclusions' => $this->analyzeExclusionTrends($days),
            'performance' => $this->analyzePerformanceTrends($days),
            'cache_hits' => $this->analyzeCacheHitTrends($days),
        ];

        return $trends;
    }

    /**
     * Analizza trend delle esclusioni
     */
    private function analyzeExclusionTrends(int $days): array
    {
        $appliedExclusions = $this->smartDetector->getAppliedExclusions();
        $cutoff = time() - ($days * DAY_IN_SECONDS);
        
        $recentExclusions = array_filter($appliedExclusions, function($exclusion) use ($cutoff) {
            return ($exclusion['applied_at'] ?? 0) >= $cutoff;
        });

        return [
            'total_recent' => count($recentExclusions),
            'automatic_recent' => count(array_filter($recentExclusions, fn($e) => $e['type'] === 'automatic')),
            'manual_recent' => count(array_filter($recentExclusions, fn($e) => $e['type'] === 'manual')),
            'trend' => $days > 0 && count($recentExclusions) > ($days / 2) ? 'increasing' : 'stable',
        ];
    }

    /**
     * Analizza trend delle performance
     */
    private function analyzePerformanceTrends(int $days): array
    {
        $performanceReport = $this->performanceDetector->getPerformanceReport($days);
        $trends = $performanceReport['trends'] ?? [];

        if (empty($trends)) {
            return ['status' => 'insufficient_data'];
        }

        return $this->analyzePerformanceTrendsData($trends);
    }

    /**
     * Analizza trend dei cache hit
     */
    private function analyzeCacheHitTrends(int $days): array
    {
        // Questa funzione richiederebbe dati di cache hit che potrebbero non essere disponibili
        // Per ora restituiamo dati simulati
        return [
            'status' => 'not_available',
            'message' => __('Dati di cache hit non disponibili', 'fp-performance-suite'),
        ];
    }

    /**
     * Calcola summary del report
     */
    private function calculateSummary(array $report): array
    {
        $summary = [
            'overall_score' => 0,
            'status' => 'good',
            'key_metrics' => [],
            'action_required' => false,
        ];

        // Calcola score complessivo
        $scores = [];
        
        if (isset($report['exclusions']['effectiveness']['score'])) {
            $scores[] = $report['exclusions']['effectiveness']['score'];
        }
        
        if (isset($report['performance']['score'])) {
            $scores[] = $report['performance']['score'];
        }
        
        if (isset($report['cache_optimization']['configuration_score'])) {
            $scores[] = $report['cache_optimization']['configuration_score'];
        }

        $scoresCount = count($scores);
        if ($scoresCount > 0) {
            $summary['overall_score'] = round(array_sum($scores) / $scoresCount, 1);
        }

        // Determina status
        if ($summary['overall_score'] >= 80) {
            $summary['status'] = 'excellent';
        } elseif ($summary['overall_score'] >= 60) {
            $summary['status'] = 'good';
        } elseif ($summary['overall_score'] >= 40) {
            $summary['status'] = 'needs_improvement';
        } else {
            $summary['status'] = 'critical';
        }

        // Determina se serve azione
        $summary['action_required'] = $summary['overall_score'] < 60 || 
                                    count($report['recommendations']) > 3;

        // Key metrics
        $summary['key_metrics'] = [
            'exclusions_total' => $report['exclusions']['total'] ?? 0,
            'performance_score' => $report['performance']['score'] ?? 0,
            'cache_score' => $report['cache_optimization']['configuration_score'] ?? 0,
            'recommendations_count' => count($report['recommendations']),
        ];

        return $summary;
    }

    /**
     * Genera report semplificato per dashboard
     */
    public function generateDashboardReport(): array
    {
        $report = $this->generateComprehensiveReport(7); // Ultimi 7 giorni
        
        return [
            'overall_score' => $report['summary']['overall_score'],
            'status' => $report['summary']['status'],
            'exclusions_count' => $report['exclusions']['total'],
            'performance_score' => $report['performance']['score'],
            'recommendations_count' => count($report['recommendations']),
            'action_required' => $report['summary']['action_required'],
            'last_updated' => $report['generated_at'],
        ];
    }

    /**
     * Esporta report in formato JSON
     */
    public function exportReport(int $days = 30, string $format = 'json'): string
    {
        $report = $this->generateComprehensiveReport($days);
        
        switch ($format) {
            case 'json':
                return json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            case 'csv':
                return $this->exportToCsv($report);
            
            default:
                throw new \InvalidArgumentException('Formato non supportato: ' . $format);
        }
    }

    /**
     * Esporta report in formato CSV
     */
    private function exportToCsv(array $report): string
    {
        $csv = "Metric,Value\n";
        $csv .= "Overall Score," . ($report['summary']['overall_score'] ?? 0) . "\n";
        $csv .= "Exclusions Total," . ($report['exclusions']['total'] ?? 0) . "\n";
        $csv .= "Performance Score," . ($report['performance']['score'] ?? 0) . "\n";
        $csv .= "Cache Score," . ($report['cache_optimization']['configuration_score'] ?? 0) . "\n";
        $csv .= "Recommendations," . count($report['recommendations']) . "\n";
        
        return $csv;
    }
    
    /**
     * BUGFIX #15: Calcola potenziale di ottimizzazione in base ai dati performance
     */
    private function calculateOptimizationPotential(array $performanceReport): int
    {
        $avgLoadTime = $performanceReport['summary']['avg_load_time'] ?? 0;
        $problematicPages = $performanceReport['summary']['problematic_pages'] ?? 0;
        
        // Se load time basso e poche pagine problematiche = basso potenziale
        if ($avgLoadTime < 1.5 && $problematicPages < 3) {
            return 20; // Già ottimizzato
        }
        
        // Se load time medio e qualche pagina problematica = medio potenziale
        if ($avgLoadTime < 3.0 && $problematicPages < 10) {
            return 50; // Ottimizzazione moderata
        }
        
        // Se load time alto o molte pagine problematiche = alto potenziale
        return 80; // Grande margine di miglioramento
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // IntelligenceReporter non ha hook specifici da registrare
        // È utilizzato principalmente per reportistica on-demand
    }
}