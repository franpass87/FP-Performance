<?php

namespace FP\PerfSuite\Admin\Pages\IntelligenceDashboard;

use function array_filter;
use function count;
use function sprintf;

/**
 * Formatta i dati per la dashboard Intelligence
 * 
 * @package FP\PerfSuite\Admin\Pages\IntelligenceDashboard
 * @author Francesco Passeri
 */
class DashboardFormatter
{
    /**
     * Ottieni breakdown delle esclusioni
     */
    public function getExclusionsBreakdown(array $exclusions): string
    {
        $automatic = count(array_filter($exclusions, fn($e) => $e['type'] === 'automatic'));
        $manual = count(array_filter($exclusions, fn($e) => $e['type'] === 'manual'));
        
        return sprintf('%d auto, %d manuali', $automatic, $manual);
    }

    /**
     * Ottieni status delle performance
     */
    public function getPerformanceStatus(int $score): string
    {
        if ($score >= 80) return 'Eccellente';
        if ($score >= 60) return 'Buono';
        if ($score >= 40) return 'Migliorabile';
        return 'Critico';
    }

    /**
     * Ottieni status del score
     */
    public function getScoreStatus(int $score): string
    {
        if ($score >= 80) return 'Eccellente';
        if ($score >= 60) return 'Buono';
        if ($score >= 40) return 'Migliorabile';
        return 'Critico';
    }

    /**
     * Ottieni raccomandazioni recenti
     */
    public function getRecentRecommendations(): array
    {
        $reporter = new \FP\PerfSuite\Services\Intelligence\IntelligenceReporter();
        $fullReport = $reporter->generateComprehensiveReport(7);
        
        return array_slice($fullReport['recommendations'] ?? [], 0, 3);
    }

    /**
     * Ottieni status del sistema
     */
    public function getSystemStatus(string $system): string
    {
        switch ($system) {
            case 'smart_detection':
                return get_option('fp_ps_intelligence_enabled', false) ? 'Attivo' : 'Inattivo';
            case 'performance_monitor':
                $monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
                return $monitor->isEnabled() ? 'Attivo' : 'Inattivo';
            case 'cache_system':
                $cacheSettings = get_option('fp_ps_page_cache', []);
                return !empty($cacheSettings['enabled']) ? 'Attivo' : 'Inattivo';
            case 'cdn_integration':
                $cdnSync = new \FP\PerfSuite\Services\Intelligence\CDNExclusionSync();
                $validation = $cdnSync->validateCDNConfiguration();
                return $validation['providers_detected'] > 0 ? 'Rilevato' : 'Non rilevato';
            default:
                return 'Sconosciuto';
        }
    }

    /**
     * Ottieni colore priorità
     */
    public function getPriorityColor(string $priority): string
    {
        switch ($priority) {
            case 'high': return '#dc3545';
            case 'medium': return '#ffc107';
            case 'low': return '#28a745';
            default: return '#6c757d';
        }
    }

    /**
     * Ottieni label priorità
     */
    public function getPriorityLabel(string $priority): string
    {
        switch ($priority) {
            case 'high': return 'Priorità Alta';
            case 'medium': return 'Priorità Media';
            case 'low': return 'Priorità Bassa';
            default: return 'Priorità Sconosciuta';
        }
    }
}

