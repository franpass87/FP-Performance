<?php

namespace FP\PerfSuite\Http\Ajax;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\CriticalCss;

/**
 * Critical CSS AJAX Handlers
 *
 * @package FP\PerfSuite\Http\Ajax
 * @author Francesco Passeri
 */
class CriticalCssAjax
{
    private ServiceContainer $container;
    
    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }
    
    public function register(): void
    {
        add_action('wp_ajax_fp_ps_generate_critical_css', [$this, 'generateCriticalCss']);
    }
    
    /**
     * Generate Critical CSS via AJAX
     */
    public function generateCriticalCss(): void
    {
        check_ajax_referer('fp_ps_generate_critical_css', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['error' => __('Permessi insufficienti', 'fp-performance-suite')], 403);
            return;
        }
        
        try {
            $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : home_url('/');
            
            if (empty($url)) {
                wp_send_json_error(['error' => __('URL non valido', 'fp-performance-suite')]);
                return;
            }
            
            $criticalCss = $this->container->get(CriticalCss::class);
            $result = $criticalCss->generate($url);
            
            if ($result['success']) {
                wp_send_json_success([
                    'css' => $result['css'],
                    'size' => $result['size'],
                    'note' => $result['note'] ?? '',
                ]);
            } else {
                wp_send_json_error([
                    'error' => $result['error'] ?? __('Errore durante la generazione', 'fp-performance-suite'),
                ]);
            }
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'error' => sprintf(
                    __('Errore: %s', 'fp-performance-suite'),
                    $e->getMessage()
                ),
            ]);
        }
    }
}
