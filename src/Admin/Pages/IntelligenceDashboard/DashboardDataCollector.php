<?php

namespace FP\PerfSuite\Admin\Pages\IntelligenceDashboard;

use FP\PerfSuite\Services\Intelligence\IntelligenceReporter;
use FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;

use function get_transient;
use function set_transient;
use const MINUTE_IN_SECONDS;

/**
 * Raccoglie i dati per la dashboard Intelligence
 * 
 * @package FP\PerfSuite\Admin\Pages\IntelligenceDashboard
 * @author Francesco Passeri
 */
class DashboardDataCollector
{
    private DashboardFormatter $formatter;

    public function __construct(DashboardFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Ottiene i dati della dashboard
     */
    public function getDashboardData(): array
    {
        // BUGFIX: Cache dati dashboard per evitare timeout (>30s) su operazioni pesanti
        // Cache valida per 5 minuti, poi rigenera
        $cache_key = 'fp_ps_intelligence_dashboard_data';
        $cached_data = get_transient($cache_key);
        
        if ($cached_data !== false && is_array($cached_data)) {
            return $cached_data;
        }
        
        // Se cache scaduta o non esiste, genera dati (può essere lento)
        try {
            // BUGFIX #15c: Aumentato timeout da 30 a 90 secondi per Intelligence Report molto complesso
            set_time_limit(90);
            
            $reporter = new IntelligenceReporter();
            $dashboardReport = $reporter->generateDashboardReport();
            
            $smartDetector = new SmartExclusionDetector();
            $appliedExclusions = $smartDetector->getAppliedExclusions();
            
            $data = [
                'overall_score' => $dashboardReport['overall_score'] ?? 0,
                'exclusions_count' => $dashboardReport['exclusions_count'] ?? 0,
                'exclusions_breakdown' => $this->formatter->getExclusionsBreakdown($appliedExclusions),
                'performance_score' => $dashboardReport['performance_score'] ?? 0,
                'performance_status' => $this->formatter->getPerformanceStatus($dashboardReport['performance_score'] ?? 0),
                'recommendations_count' => $dashboardReport['recommendations_count'] ?? 0,
                'action_required' => $dashboardReport['action_required'] ?? false,
                'recent_recommendations' => $this->formatter->getRecentRecommendations(),
                'smart_detection_status' => $this->formatter->getSystemStatus('smart_detection'),
                'performance_monitor_status' => $this->formatter->getSystemStatus('performance_monitor'),
                'cache_system_status' => $this->formatter->getSystemStatus('cache_system'),
                'cdn_integration_status' => $this->formatter->getSystemStatus('cdn_integration'),
            ];
            
            // Cache per 5 minuti (300 secondi)
            set_transient($cache_key, $data, 5 * MINUTE_IN_SECONDS);
            
            return $data;
            
        } catch (\Throwable $e) {
            // BUGFIX: Fallback in caso di errore per non bloccare la pagina
            return [
                'overall_score' => 0,
                'exclusions_count' => 0,
                'exclusions_breakdown' => '<p>⚠️ Errore caricamento dati</p>',
                'performance_score' => 0,
                'performance_status' => 'Errore',
                'recommendations_count' => 0,
                'action_required' => false,
                'recent_recommendations' => [],
                'smart_detection_status' => 'Errore: ' . $e->getMessage(),
                'performance_monitor_status' => 'N/A',
                'cache_system_status' => 'N/A',
                'cdn_integration_status' => 'N/A',
                'error' => true,
            ];
        }
    }
}















