<?php

namespace FP\PerfSuite\Services\DB\PluginOptimizers;

use FP\PerfSuite\Utils\Logger;

/**
 * Ottimizzatore per Elementor
 * 
 * @package FP\PerfSuite\Services\DB\PluginOptimizers
 * @author Francesco Passeri
 */
class ElementorOptimizer
{
    /**
     * Analizza dati Elementor
     */
    public function analyze(): array
    {
        global $wpdb;
        
        $analysis = [
            'css_cache' => 0,
            'elementor_data' => 0,
            'revisions' => 0,
        ];
        
        // Conta post meta Elementor
        $analysis['elementor_data'] = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} 
             WHERE meta_key LIKE '_elementor_%'"
        );
        
        // Revisioni con dati Elementor (molto grandi)
        $analysis['revisions'] = (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT p.ID) 
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = 'revision' 
             AND pm.meta_key = '_elementor_data'"
        );
        
        // CSS generati da Elementor (opzioni)
        $cssOptions = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->options} 
             WHERE option_name LIKE '_elementor_css_%' 
             OR option_name LIKE 'elementor_css_%'"
        );
        $analysis['css_cache'] = (int) $cssOptions;
        
        // Stima: revisioni Elementor sono MOLTO grandi (50KB+ ciascuna)
        $estimatedSize = ($analysis['revisions'] * 50) + 
                         ($analysis['css_cache'] * 10) +
                         ($analysis['elementor_data'] * 2);
        
        return [
            'plugin_name' => 'Elementor',
            'analysis' => $analysis,
            'total_items' => array_sum($analysis),
            'potential_savings_mb' => round($estimatedSize / 1024, 2),
            'recommendations' => $this->getRecommendations($analysis),
        ];
    }

    /**
     * Pulisci dati Elementor
     */
    public function cleanup(array $tasks = []): array
    {
        global $wpdb;
        
        $results = [];
        
        if (in_array('revisions', $tasks, true)) {
            // Elimina revisioni con dati Elementor
            $revisions = $wpdb->get_col(
                "SELECT DISTINCT p.ID 
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                 WHERE p.post_type = 'revision' 
                 AND pm.meta_key = '_elementor_data'
                 LIMIT 100"
            );
            
            foreach ($revisions as $revisionId) {
                wp_delete_post($revisionId, true);
            }
            
            $results['revisions'] = ['deleted' => count($revisions)];
            Logger::info('Elementor revisions cleaned', ['count' => count($revisions)]);
        }
        
        if (in_array('css_cache', $tasks, true)) {
            // Elimina CSS cache vecchia
            $deleted = $wpdb->query(
                "DELETE FROM {$wpdb->options} 
                 WHERE option_name LIKE '_elementor_css_%' 
                 OR option_name LIKE 'elementor_css_%'"
            );
            
            $results['css_cache'] = ['deleted' => (int) $deleted];
            Logger::info('Elementor CSS cache cleaned', ['count' => $deleted]);
            
            // Rigenera CSS
            if (class_exists('\Elementor\Plugin')) {
                \Elementor\Plugin::$instance->files_manager->clear_cache();
            }
        }
        
        return $results;
    }

    /**
     * Genera raccomandazioni
     */
    private function getRecommendations(array $analysis): array
    {
        $recommendations = [];
        
        if ($analysis['revisions'] > 100) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => sprintf(
                    '%d revisioni Elementor (molto grandi). Eliminale per recuperare spazio significativo.',
                    $analysis['revisions']
                ),
                'action' => 'clean_elementor_revisions',
            ];
        }
        
        if ($analysis['css_cache'] > 100) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Pulisci e rigenera la cache CSS di Elementor.',
                'action' => 'regenerate_elementor_css',
            ];
        }
        
        return $recommendations;
    }
}















