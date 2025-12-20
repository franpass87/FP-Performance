<?php

namespace FP\PerfSuite\Admin\Menu;

use FP\PerfSuite\ServiceContainer;

use function current_user_can;
use function wp_verify_nonce;
use function wp_send_json_error;
use function wp_send_json_success;
use function sanitize_key;
use function sprintf;
use function __;
use function wp_unslash;

/**
 * Gestisce le raccomandazioni automatiche
 * 
 * @package FP\PerfSuite\Admin\Menu
 * @author Francesco Passeri
 */
class RecommendationHandler
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Handler AJAX per applicare le raccomandazioni automaticamente
     */
    public function applyRecommendation(): void
    {
        // Verifica permessi
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Non hai i permessi per eseguire questa azione.', 'fp-performance-suite'),
            ]);
            return;
        }

        // Verifica nonce
        $nonce = wp_unslash($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce, 'fp_ps_apply_recommendation')) {
            wp_send_json_error([
                'message' => __('Verifica di sicurezza fallita. Ricarica la pagina e riprova.', 'fp-performance-suite'),
            ]);
            return;
        }

        // Ottieni action_id
        $actionId = sanitize_key(wp_unslash($_POST['action_id'] ?? ''));
        if (empty($actionId)) {
            wp_send_json_error([
                'message' => __('ID azione non valido.', 'fp-performance-suite'),
            ]);
            return;
        }

        // Applica la raccomandazione
        try {
            $applicator = $this->container->get(\FP\PerfSuite\Services\Monitoring\RecommendationApplicator::class);
            $result = $applicator->apply($actionId);

            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message'],
                ]);
            } else {
                wp_send_json_error([
                    'message' => $result['message'],
                ]);
            }
        } catch (\Throwable $e) {
            wp_send_json_error([
                'message' => sprintf(
                    __('Errore imprevisto: %s', 'fp-performance-suite'),
                    $e->getMessage()
                ),
            ]);
        }
    }
}















