<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Utils\Logger;

/**
 * Plugin-Specific Database Optimizer
 * 
 * Ottimizzazioni specifiche per plugin popolari:
 * - WooCommerce: sessioni, ordini bozza, log
 * - Yoast SEO: meta ridondanti
 * - Advanced Custom Fields: serializzazione
 * - Elementor: revisioni CSS/JS, cache
 * - WordPress Core: revisioni, orfani
 * 
 * @package FP\PerfSuite\Services\DB
 * @author Francesco Passeri
 */
class PluginSpecificOptimizer
{
    /**
     * Analizza opportunità di pulizia per plugin installati
     */
    public function analyzeInstalledPlugins(): array
    {
        $opportunities = [];
        
        // WooCommerce
        if ($this->isPluginActive('woocommerce/woocommerce.php')) {
            $opportunities['woocommerce'] = $this->analyzeWooCommerce();
        }
        
        // Yoast SEO
        if ($this->isPluginActive('wordpress-seo/wp-seo.php')) {
            $opportunities['yoast'] = $this->analyzeYoast();
        }
        
        // Advanced Custom Fields
        if ($this->isPluginActive('advanced-custom-fields/acf.php') || 
            $this->isPluginActive('advanced-custom-fields-pro/acf.php')) {
            $opportunities['acf'] = $this->analyzeACF();
        }
        
        // Elementor
        if ($this->isPluginActive('elementor/elementor.php')) {
            $opportunities['elementor'] = $this->analyzeElementor();
        }
        
        // Contact Form 7
        if ($this->isPluginActive('contact-form-7/wp-contact-form-7.php')) {
            $opportunities['cf7'] = $this->analyzeContactForm7();
        }
        
        // Calcola totale potenziale risparmio
        $totalSavings = 0;
        $totalItems = 0;
        
        foreach ($opportunities as $plugin => $data) {
            $totalSavings += $data['potential_savings_mb'] ?? 0;
            $totalItems += $data['total_items'] ?? 0;
        }
        
        return [
            'opportunities' => $opportunities,
            'total_plugins_analyzed' => count($opportunities),
            'total_potential_savings_mb' => round($totalSavings, 2),
            'total_items_to_clean' => $totalItems,
        ];
    }
    
    /**
     * Analizza dati WooCommerce
     */
    private function analyzeWooCommerce(): array
    {
        global $wpdb;
        
        $analysis = [
            'sessions' => 0,
            'pending_orders' => 0,
            'failed_orders' => 0,
            'logs' => 0,
            'webhooks_logs' => 0,
        ];
        
        // Sessioni WooCommerce
        $sessionTable = $wpdb->prefix . 'woocommerce_sessions';
        if ($this->tableExists($sessionTable)) {
            $analysis['sessions'] = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM `{$sessionTable}` 
                     WHERE session_expiry < %d",
                    time()
                )
            );
        }
        
        // Ordini pending/failed vecchi (>30 giorni)
        $oldDate = date('Y-m-d H:i:s', strtotime('-30 days'));
        $analysis['pending_orders'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_type = 'shop_order' 
             AND post_status = 'wc-pending' 
             AND post_date < %s",
            $oldDate
        ));
        
        $analysis['failed_orders'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_type = 'shop_order' 
             AND post_status = 'wc-failed' 
             AND post_date < %s",
            $oldDate
        ));
        
        // Log WooCommerce
        $logTable = $wpdb->prefix . 'woocommerce_log';
        if ($this->tableExists($logTable)) {
            $analysis['logs'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$logTable}`");
        }
        
        // Action Scheduler (usato da WooCommerce)
        $actionSchedulerTable = $wpdb->prefix . 'actionscheduler_logs';
        if ($this->tableExists($actionSchedulerTable)) {
            $analysis['action_scheduler_logs'] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM `{$actionSchedulerTable}` 
                 WHERE log_date_gmt < %s",
                date('Y-m-d H:i:s', strtotime('-7 days'))
            ));
        }
        
        // Stima dimensione
        $estimatedSize = ($analysis['sessions'] * 2) + 
                         ($analysis['pending_orders'] * 50) + 
                         ($analysis['failed_orders'] * 50) +
                         ($analysis['logs'] * 1);
        
        return [
            'plugin_name' => 'WooCommerce',
            'analysis' => $analysis,
            'total_items' => array_sum($analysis),
            'potential_savings_mb' => round($estimatedSize / 1024, 2),
            'recommendations' => $this->getWooCommerceRecommendations($analysis),
        ];
    }
    
    /**
     * Analizza dati Yoast SEO
     */
    private function analyzeYoast(): array
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
            'recommendations' => $this->getYoastRecommendations($analysis),
        ];
    }
    
    /**
     * Analizza dati Advanced Custom Fields
     */
    private function analyzeACF(): array
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
            'recommendations' => $this->getACFRecommendations($analysis),
        ];
    }
    
    /**
     * Analizza dati Elementor
     */
    private function analyzeElementor(): array
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
            'recommendations' => $this->getElementorRecommendations($analysis),
        ];
    }
    
    /**
     * Analizza Contact Form 7
     */
    private function analyzeContactForm7(): array
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
            'recommendations' => $this->getCF7Recommendations($analysis),
        ];
    }
    
    /**
     * Pulisci dati WooCommerce
     */
    public function cleanupWooCommerce(array $tasks = []): array
    {
        global $wpdb;
        
        $results = [];
        
        if (in_array('sessions', $tasks, true)) {
            $sessionTable = $wpdb->prefix . 'woocommerce_sessions';
            if ($this->tableExists($sessionTable)) {
                $deleted = $wpdb->query($wpdb->prepare(
                    "DELETE FROM `{$sessionTable}` WHERE session_expiry < %d",
                    time()
                ));
                $results['sessions'] = ['deleted' => (int) $deleted];
                Logger::info('WooCommerce sessions cleaned', ['count' => $deleted]);
            }
        }
        
        if (in_array('old_orders', $tasks, true)) {
            $oldDate = date('Y-m-d H:i:s', strtotime('-30 days'));
            $orders = $wpdb->get_col($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} 
                 WHERE post_type = 'shop_order' 
                 AND post_status IN ('wc-pending', 'wc-failed') 
                 AND post_date < %s 
                 LIMIT 100",
                $oldDate
            ));
            
            foreach ($orders as $orderId) {
                wp_delete_post($orderId, true);
            }
            
            $results['old_orders'] = ['deleted' => count($orders)];
            Logger::info('WooCommerce old orders cleaned', ['count' => count($orders)]);
        }
        
        if (in_array('action_scheduler', $tasks, true)) {
            $logTable = $wpdb->prefix . 'actionscheduler_logs';
            if ($this->tableExists($logTable)) {
                $deleted = $wpdb->query($wpdb->prepare(
                    "DELETE FROM `{$logTable}` WHERE log_date_gmt < %s",
                    date('Y-m-d H:i:s', strtotime('-7 days'))
                ));
                $results['action_scheduler'] = ['deleted' => (int) $deleted];
                Logger::info('Action Scheduler logs cleaned', ['count' => $deleted]);
            }
        }
        
        return $results;
    }
    
    /**
     * Pulisci dati Elementor
     */
    public function cleanupElementor(array $tasks = []): array
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
     * Verifica se un plugin è attivo
     */
    private function isPluginActive(string $plugin): bool
    {
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active($plugin);
    }
    
    /**
     * Verifica se una tabella esiste
     */
    private function tableExists(string $table): bool
    {
        global $wpdb;
        return (bool) $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table
        ));
    }
    
    /**
     * Raccomandazioni WooCommerce
     */
    private function getWooCommerceRecommendations(array $analysis): array
    {
        $recommendations = [];
        
        if ($analysis['sessions'] > 1000) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => sprintf(
                    '%d sessioni scadute. Puliscile per migliorare le performance.',
                    $analysis['sessions']
                ),
                'action' => 'clean_sessions',
            ];
        }
        
        if ($analysis['pending_orders'] > 50 || $analysis['failed_orders'] > 50) {
            $recommendations[] = [
                'type' => 'info',
                'message' => sprintf(
                    '%d ordini vecchi (pending/failed). Considera di pulirli.',
                    $analysis['pending_orders'] + $analysis['failed_orders']
                ),
                'action' => 'clean_old_orders',
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Raccomandazioni Yoast
     */
    private function getYoastRecommendations(array $analysis): array
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
     * Raccomandazioni ACF
     */
    private function getACFRecommendations(array $analysis): array
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
    
    /**
     * Raccomandazioni Elementor
     */
    private function getElementorRecommendations(array $analysis): array
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
    
    /**
     * Raccomandazioni CF7
     */
    private function getCF7Recommendations(array $analysis): array
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
}

