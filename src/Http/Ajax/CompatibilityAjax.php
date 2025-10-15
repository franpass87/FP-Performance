<?php

namespace FP\PerfSuite\Http\Ajax;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Compatibility\ThemeCompatibility;

/**
 * Compatibility AJAX Handlers
 *
 * @package FP\PerfSuite\Http\Ajax
 * @author Francesco Passeri
 */
class CompatibilityAjax
{
    private ServiceContainer $container;
    
    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }
    
    public function register(): void
    {
        add_action('wp_ajax_fp_ps_apply_compatibility', [$this, 'applyCompatibility']);
        add_action('wp_ajax_fp_ps_dismiss_compat_notice', [$this, 'dismissNotice']);
    }
    
    /**
     * Apply compatibility configuration via AJAX
     */
    public function applyCompatibility(): void
    {
        check_ajax_referer('fp_ps_apply_compat', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti', 403);
        }
        
        try {
            $compat = $this->container->get(ThemeCompatibility::class);
            
            // Enable auto-apply
            $compat->update([
                'auto_apply' => true,
            ]);
            
            // Apply rules
            $compat->applyCompatibilityRules();
            
            wp_send_json_success([
                'message' => 'Configurazione applicata con successo',
            ]);
        } catch (\Exception $e) {
            wp_send_json_error('Errore: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Dismiss compatibility notice
     */
    public function dismissNotice(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti', 403);
        }
        
        set_transient('fp_ps_compat_notice_shown', true, MONTH_IN_SECONDS);
        
        wp_send_json_success();
    }
}
