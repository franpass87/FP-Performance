<?php

namespace FP\PerfSuite\Admin\Pages\IntelligenceDashboard;

use FP\PerfSuite\Services\Intelligence\PerformanceBasedExclusionDetector;
use FP\PerfSuite\Services\Intelligence\CacheAutoConfigurator;
use FP\PerfSuite\Services\Intelligence\AssetOptimizationIntegrator;
use FP\PerfSuite\Services\Intelligence\CDNExclusionSync;
use FP\PerfSuite\Services\Intelligence\IntelligenceReporter;

use function delete_transient;
use function sprintf;
use function implode;
use function __;

/**
 * Gestisce i form POST della pagina Intelligence Dashboard
 * 
 * @package FP\PerfSuite\Admin\Pages\IntelligenceDashboard
 * @author Francesco Passeri
 */
class FormHandler
{
    /**
     * Gestisce il refresh della cache
     */
    public function handleCacheRefresh(): string
    {
        delete_transient('fp_ps_intelligence_dashboard_data');
        return __('Cache dashboard aggiornata con successo! I dati sono stati rigenerati.', 'fp-performance-suite');
    }

    /**
     * Esegui ottimizzazione automatica
     */
    public function runAutoOptimization(): string
    {
        $results = [];
        
        // 1. Performance-based exclusions
        $performanceDetector = new PerformanceBasedExclusionDetector();
        $performanceResults = $performanceDetector->autoApplyPerformanceExclusions(false);
        $results[] = sprintf('%d esclusioni performance applicate', $performanceResults['applied']);
        
        // 2. Cache auto-configuration
        $cacheConfigurator = new CacheAutoConfigurator();
        $cacheResults = $cacheConfigurator->autoConfigureCacheRules();
        $results[] = sprintf('%d ottimizzazioni cache applicate', $cacheResults['optimizations_applied']);
        
        // 3. Asset optimization
        $assetIntegrator = new AssetOptimizationIntegrator();
        $assetResults = $assetIntegrator->applySmartAssetExclusions();
        $results[] = sprintf('%d esclusioni asset applicate', $assetResults['js_exclusions_applied'] + $assetResults['css_exclusions_applied']);
        
        // 4. CDN sync
        $cdnSync = new CDNExclusionSync();
        $cdnResults = $cdnSync->syncExclusionsWithCDN();
        $results[] = sprintf('%d provider CDN sincronizzati', $cdnResults['cdn_providers_synced']);
        
        return '✅ Ottimizzazione completata: ' . implode(', ', $results);
    }

    /**
     * Applica esclusioni basate su performance
     */
    public function applyPerformanceExclusions(): string
    {
        $performanceDetector = new PerformanceBasedExclusionDetector();
        $results = $performanceDetector->autoApplyPerformanceExclusions(false);
        
        return sprintf('✅ %d esclusioni performance applicate con successo!', $results['applied']);
    }

    /**
     * Auto-configura cache
     */
    public function autoConfigureCache(): string
    {
        $cacheConfigurator = new CacheAutoConfigurator();
        $results = $cacheConfigurator->autoConfigureCacheRules();
        
        return sprintf('✅ %d ottimizzazioni cache applicate con successo!', $results['optimizations_applied']);
    }

    /**
     * Ottimizza asset
     */
    public function optimizeAssets(): string
    {
        $assetIntegrator = new AssetOptimizationIntegrator();
        $results = $assetIntegrator->applySmartAssetExclusions();
        
        return sprintf('✅ %d esclusioni asset applicate con successo!', $results['js_exclusions_applied'] + $results['css_exclusions_applied']);
    }

    /**
     * Sincronizza esclusioni CDN
     */
    public function syncCDNExclusions(): string
    {
        $cdnSync = new CDNExclusionSync();
        $results = $cdnSync->syncExclusionsWithCDN();
        
        return sprintf('✅ %d provider CDN sincronizzati con successo!', $results['cdn_providers_synced']);
    }

    /**
     * Genera report intelligence
     */
    public function generateIntelligenceReport(): string
    {
        $reporter = new IntelligenceReporter();
        $report = $reporter->generateComprehensiveReport(30);
        
        // Salva report per visualizzazione
        update_option('fp_ps_intelligence_last_report', $report);
        
        return sprintf('✅ Report intelligence generato con score %d%%!', $report['summary']['overall_score'] ?? 0);
    }
}

