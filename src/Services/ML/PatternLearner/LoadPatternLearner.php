<?php

namespace FP\PerfSuite\Services\ML\PatternLearner;

/**
 * Apprende pattern di carico
 * 
 * @package FP\PerfSuite\Services\ML\PatternLearner
 * @author Francesco Passeri
 */
class LoadPatternLearner
{
    private StatisticsCalculator $calculator;

    public function __construct(StatisticsCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Apprende pattern di carico
     */
    public function learn(array $data): array
    {
        $patterns = [];
        
        // Pattern: carico elevato in orari specifici
        $high_load_times = $this->findHighLoadTimes($data);
        if (!empty($high_load_times)) {
            $patterns[] = [
                'type' => 'high_load_times',
                'description' => 'Carico elevato rilevato in orari specifici',
                'confidence' => $this->calculator->calculateConfidence($high_load_times),
                'data' => $high_load_times,
                'recommended_action' => 'Implementare cache aggiuntiva negli orari critici'
            ];
        }
        
        // Pattern: correlazione tra plugin e carico
        $plugin_load_correlation = $this->findPluginLoadCorrelation($data);
        if (!empty($plugin_load_correlation)) {
            $patterns[] = [
                'type' => 'plugin_load_correlation',
                'description' => 'Correlazione tra plugin attivi e carico server',
                'confidence' => $this->calculator->calculateConfidence($plugin_load_correlation),
                'data' => $plugin_load_correlation,
                'recommended_action' => 'Ottimizzare plugin che causano carico elevato'
            ];
        }
        
        return $patterns;
    }

    /**
     * Trova orari con carico elevato
     */
    private function findHighLoadTimes(array $data): array
    {
        $high_load_times = [];
        
        foreach ($data as $point) {
            if ($point['server_load'] > 0.8) {
                $hour = date('H', $point['timestamp']);
                $high_load_times[$hour] = ($high_load_times[$hour] ?? 0) + 1;
            }
        }
        
        // Filtra solo orari con almeno 3 occorrenze
        return array_filter($high_load_times, fn($count) => $count >= 3);
    }

    /**
     * Trova correlazione tra plugin e carico
     */
    private function findPluginLoadCorrelation(array $data): array
    {
        $correlations = [];
        
        // Raggruppa per numero di plugin attivi
        $plugin_groups = [];
        foreach ($data as $point) {
            $plugin_count = $point['active_plugins'] ?? 0;
            $plugin_groups[$plugin_count][] = $point['server_load'];
        }
        
        // Calcola correlazione
        foreach ($plugin_groups as $plugin_count => $loads) {
            $loadsGroupCount = count($loads);
            if ($loadsGroupCount >= 5) { // Almeno 5 punti dati
                $avg_load = array_sum($loads) / $loadsGroupCount;
                $correlations[$plugin_count] = $avg_load;
            }
        }
        
        return $correlations;
    }
}















