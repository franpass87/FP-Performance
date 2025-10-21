<?php

namespace FP\PerfSuite\Http\Ajax;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\Logger;

use function check_ajax_referer;
use function current_user_can;
use function update_option;
use function wp_send_json_error;
use function wp_send_json_success;

/**
 * AI Config AJAX Handlers
 * 
 * Gestisce le azioni AJAX per la configurazione AI
 *
 * @package FP\PerfSuite\Http\Ajax
 * @author Francesco Passeri
 */
class AIConfigAjax
{
    private ServiceContainer $container;
    
    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }
    
    public function register(): void
    {
        add_action('wp_ajax_fp_ps_update_heartbeat', [$this, 'updateHeartbeat']);
        add_action('wp_ajax_fp_ps_update_exclusions', [$this, 'updateExclusions']);
    }
    
    /**
     * Aggiorna le impostazioni del Heartbeat
     */
    public function updateHeartbeat(): void
    {
        check_ajax_referer('wp_rest', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permessi insufficienti', 'fp-performance-suite')], 403);
            return;
        }
        
        // EDGE CASE BUG #36: Sanitizzazione con wp_unslash
        $interval = isset($_POST['interval']) 
            ? (int) wp_unslash($_POST['interval']) 
            : 60;
        
        // Validazione range
        if ($interval < 15) {
            $interval = 15;
        } elseif ($interval > 3600) { // Max 1 ora
            $interval = 3600;
        }
        
        Logger::info('AI Config: Updating heartbeat interval', ['interval' => $interval]);
        
        // Ottieni le impostazioni backend esistenti
        $backendSettings = get_option('fp_ps_backend', []);
        
        // Aggiorna l'intervallo del heartbeat
        $backendSettings['heartbeat_enabled'] = true;
        $backendSettings['heartbeat_interval'] = $interval;
        
        $result = update_option('fp_ps_backend', $backendSettings, true);
        
        if ($result !== false) {
            wp_send_json_success([
                'message' => __('Heartbeat interval aggiornato con successo', 'fp-performance-suite'),
                'interval' => $interval,
            ]);
        } else {
            wp_send_json_error(['message' => __('Errore durante l\'aggiornamento del heartbeat', 'fp-performance-suite')], 500);
        }
    }
    
    /**
     * Aggiorna le esclusioni
     */
    public function updateExclusions(): void
    {
        check_ajax_referer('wp_rest', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permessi insufficienti', 'fp-performance-suite')], 403);
            return;
        }
        
        // EDGE CASE BUG #36: Sanitizzazione POST data
        $exclusionsJson = isset($_POST['exclusions']) 
            ? sanitize_textarea_field(wp_unslash($_POST['exclusions'])) 
            : '[]';
        
        $exclusions = json_decode($exclusionsJson, true);
        
        if (!is_array($exclusions) || json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error([
                'message' => __('Formato esclusioni non valido', 'fp-performance-suite'),
                'error' => json_last_error_msg(),
            ], 400);
            return;
        }
        
        // EDGE CASE BUG #36: Validazione array elements
        foreach ($exclusions as $exclusion) {
            if (!is_array($exclusion) || empty($exclusion['selector'])) {
                wp_send_json_error([
                    'message' => __('Esclusione non valida: richiesto campo "selector"', 'fp-performance-suite'),
                ], 400);
                return;
            }
        }
        
        Logger::info('AI Config: Updating exclusions', ['count' => count($exclusions)]);
        
        // Salva le esclusioni
        $result = update_option('fp_ps_exclusions', $exclusions, true);
        
        if ($result !== false) {
            wp_send_json_success([
                'message' => sprintf(__('Salvate %d esclusioni', 'fp-performance-suite'), count($exclusions)),
                'count' => count($exclusions),
            ]);
        } else {
            wp_send_json_error(['message' => __('Errore durante il salvataggio delle esclusioni', 'fp-performance-suite')], 500);
        }
    }
}

