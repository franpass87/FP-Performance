<?php

namespace FP\PerfSuite\Services\DB\PluginOptimizers;

/**
 * Ottimizzatore per Contact Form 7
 * 
 * @package FP\PerfSuite\Services\DB\PluginOptimizers
 * @author Francesco Passeri
 */
class CF7Optimizer
{
    /**
     * Analizza Contact Form 7
     */
    public function analyze(): array
    {
        global $wpdb;
        
        $analysis = [
            'cf7_data' => 0,
            'flamingo_messages' => 0,
        ];
        
        // Meta CF7
        $analysis['cf7_data'] = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} 
             WHERE meta_key LIKE '_wpcf7_%'"
        );
        
        // Flamingo (se installato) - messaggi vecchi
        if ($this->isPluginActive('flamingo/flamingo.php')) {
            $oldDate = date('Y-m-d H:i:s', strtotime('-90 days'));
            $analysis['flamingo_messages'] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} 
                 WHERE post_type = 'flamingo_inbound' 
                 AND post_date < %s",
                $oldDate
            ));
        }
        
        $estimatedSize = ($analysis['flamingo_messages'] * 5);
        
        return [
            'plugin_name' => 'Contact Form 7',
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
        
        if ($analysis['flamingo_messages'] > 500) {
            $recommendations[] = [
                'type' => 'info',
                'message' => sprintf(
                    '%d messaggi Flamingo vecchi (>90 giorni). Archiviali o eliminali.',
                    $analysis['flamingo_messages']
                ),
                'action' => 'clean_old_flamingo',
            ];
        }
        
        return $recommendations;
    }

    /**
     * Verifica se un plugin è attivo
     */
    private function isPluginActive(string $plugin): bool
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active($plugin);
    }
}















