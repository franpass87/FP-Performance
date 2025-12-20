<?php

namespace FP\PerfSuite\Services\DB\PluginOptimizers;

/**
 * Ottimizzatore per Yoast SEO
 * 
 * @package FP\PerfSuite\Services\DB\PluginOptimizers
 * @author Francesco Passeri
 */
class YoastOptimizer
{
    /**
     * Analizza dati Yoast SEO
     */
    public function analyze(): array
    {
        global $wpdb;
        
        $analysis = [
            'indexable_rows' => 0,
            'indexable_hierarchy' => 0,
            'seo_links' => 0,
        ];
        
        // Tabelle Yoast
        $indexableTable = $wpdb->prefix . 'yoast_indexable';
        $hierarchyTable = $wpdb->prefix . 'yoast_indexable_hierarchy';
        $linksTable = $wpdb->prefix . 'yoast_seo_links';
        
        if ($this->tableExists($indexableTable)) {
            $analysis['indexable_rows'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$indexableTable}`");
        }
        
        if ($this->tableExists($hierarchyTable)) {
            $analysis['indexable_hierarchy'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$hierarchyTable}`");
        }
        
        if ($this->tableExists($linksTable)) {
            $analysis['seo_links'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$linksTable}`");
        }
        
        // Meta ridondanti
        $redundantMeta = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} 
             WHERE meta_key LIKE '_yoast_wpseo_%'"
        );
        
        $analysis['yoast_postmeta'] = $redundantMeta;
        
        $estimatedSize = ($analysis['indexable_rows'] * 5) + 
                         ($redundantMeta * 2);
        
        return [
            'plugin_name' => 'Yoast SEO',
            'analysis' => $analysis,
            'total_items' => array_sum($analysis),
            'potential_savings_mb' => round($estimatedSize / 1024, 2),
            'recommendations' => $this->getRecommendations($analysis),
        ];
    }

    /**
     * Genera raccomandazioni
     */
    private function getRecommendations(array $analysis): array
    {
        $recommendations = [];
        
        if ($analysis['indexable_rows'] > 10000) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Database Yoast molto grande. Ottimizza le tabelle regolarmente.',
                'action' => 'optimize_yoast_tables',
            ];
        }
        
        return $recommendations;
    }

    /**
     * Verifica se una tabella esiste
     */
    private function tableExists(string $table): bool
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $table
        )) > 0;
    }
}

