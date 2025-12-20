<?php

namespace FP\PerfSuite\Services\DB\PluginOptimizers;

use FP\PerfSuite\Utils\Logger;

/**
 * Ottimizzatore per WooCommerce
 * 
 * @package FP\PerfSuite\Services\DB\PluginOptimizers
 * @author Francesco Passeri
 */
class WooCommerceOptimizer
{
    /**
     * Analizza dati WooCommerce
     */
    public function analyze(): array
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
            'recommendations' => $this->getRecommendations($analysis),
        ];
    }

    /**
     * Pulisci dati WooCommerce
     */
    public function cleanup(array $tasks = []): array
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
     * Genera raccomandazioni
     */
    private function getRecommendations(array $analysis): array
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















