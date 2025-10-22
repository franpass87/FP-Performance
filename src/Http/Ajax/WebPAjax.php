<?php

namespace FP\PerfSuite\Http\Ajax;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Media\WebPConverter;

/**
 * WebP Conversion AJAX Handlers
 *
 * @package FP\PerfSuite\Http\Ajax
 * @author Francesco Passeri
 */
class WebPAjax
{
    private ServiceContainer $container;
    
    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }
    
    public function register(): void
    {
        add_action('wp_ajax_fp_ps_webp_queue_status', [$this, 'getQueueStatus']);
        add_action('wp_ajax_fp_ps_webp_bulk_convert', [$this, 'startBulkConversion']);
    }
    
    /**
     * Get current queue status via AJAX
     */
    public function getQueueStatus(): void
    {
        check_ajax_referer('fp_ps_webp_status', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti', 403);
            return;
        }
        
        try {
            error_log('FP Performance Suite: getQueueStatus called');
            $converter = $this->container->get(WebPConverter::class);
            $queue = $converter->getQueue();
            $state = $queue->getState();
            error_log('FP Performance Suite: Queue state: ' . print_r($state, true));
            
            if ($state === null) {
                wp_send_json_success([
                    'active' => false,
                    'processed' => 0,
                    'total' => 0,
                    'percent' => 100,
                ]);
                return;
            }
            
            $percent = $state['total'] > 0 
                ? round(($state['processed'] / $state['total']) * 100)
                : 0;
            
            wp_send_json_success([
                'active' => true,
                'processed' => $state['processed'],
                'converted' => $state['converted'],
                'total' => $state['total'],
                'percent' => $percent,
            ]);
        } catch (\Exception $e) {
            wp_send_json_error('Errore: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Start bulk conversion via AJAX
     */
    public function startBulkConversion(): void
    {
        check_ajax_referer('fp_ps_media', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti', 403);
            return;
        }
        
        try {
            $limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 20;
            $offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;
            
            error_log("FP Performance Suite: bulkConvert called with limit=$limit, offset=$offset");
            $converter = $this->container->get(WebPConverter::class);
            $result = $converter->bulkConvert($limit, $offset);
            error_log('FP Performance Suite: bulkConvert result: ' . print_r($result, true));
            
            if (!empty($result['error'])) {
                wp_send_json_error($result['error'], 429);
                return;
            }
            
            wp_send_json_success($result);
        } catch (\Exception $e) {
            wp_send_json_error('Errore: ' . $e->getMessage(), 500);
        }
    }
}
