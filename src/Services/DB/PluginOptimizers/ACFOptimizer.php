<?php

namespace FP\PerfSuite\Services\DB\PluginOptimizers;

/**
 * Ottimizzatore per Advanced Custom Fields
 * 
 * @package FP\PerfSuite\Services\DB\PluginOptimizers
 * @author Francesco Passeri
 */
class ACFOptimizer
{
    /**
     * Analizza dati ACF
     */
    public function analyze(): array
    {
        global $wpdb;
        
        $analysis = [
            'acf_postmeta' => 0,
            'unused_field_groups' => 0,
        ];
        
        // Conta meta ACF
        $analysis['acf_postmeta'] = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} 
             WHERE meta_key LIKE '_field_%' OR meta_key LIKE 'field_%'"
        );
        
        // Field groups non pubblicati/bozze
        $analysis['draft_field_groups'] = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_type = 'acf-field-group' 
             AND post_status IN ('draft', 'auto-draft', 'trash')"
        );
        
        $estimatedSize = $analysis['acf_postmeta'] * 1;
        
        return [
            'plugin_name' => 'Advanced Custom Fields',
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
        
        if ($analysis['draft_field_groups'] > 0) {
            $recommendations[] = [
                'type' => 'info',
                'message' => sprintf(
                    '%d field group in bozza o cestino. Eliminali se non necessari.',
                    $analysis['draft_field_groups']
                ),
                'action' => 'clean_draft_field_groups',
            ];
        }
        
        return $recommendations;
    }
}















