<?php

namespace FP\PerfSuite\Admin\Menu;

use function get_option;
use function delete_option;
use function current_user_can;
use function wp_verify_nonce;
use function wp_send_json_error;
use function wp_send_json_success;
use function esc_html;
use function sanitize_key;
use function wp_kses_post;
use function absint;
use function update_user_meta;
use function get_current_user_id;
use function get_bloginfo;
use function date_i18n;
use function _e;
use function esc_attr;
use function __;
use function wp_create_nonce;
use function sanitize_text_field;
use function wp_unslash;
use const PHP_VERSION;

/**
 * Gestisce i notice admin per il plugin
 * 
 * @package FP\PerfSuite\Admin\Menu
 * @author Francesco Passeri
 */
class NoticeManager
{
    /**
     * Mostra eventuali errori di attivazione nell'area admin
     */
    public function showActivationErrors(): void
    {
        $error = get_option('fp_perfsuite_activation_error');
        
        if (!is_array($error) || empty($error)) {
            return;
        }

        // Mostra il notice solo agli amministratori
        if (!current_user_can('manage_options')) {
            return;
        }

        // SICUREZZA: Sanitizza TUTTI i valori prima dell'output
        $errorMessage = esc_html($error['message'] ?? 'Errore sconosciuto');
        $errorType = sanitize_key($error['type'] ?? 'unknown');
        $solution = wp_kses_post($error['solution'] ?? 'Contatta il supporto.');
        $phpVersion = esc_html($error['php_version'] ?? PHP_VERSION);
        $wpVersion = esc_html($error['wp_version'] ?? get_bloginfo('version'));
        $file = isset($error['file']) ? esc_html($error['file']) : '';
        $line = isset($error['line']) ? absint($error['line']) : 0;
        $time = isset($error['time']) ? absint($error['time']) : time();

        // Determina l'icona e il colore in base al tipo di errore
        $noticeClass = 'notice-error';
        $icon = '❌';
        
        if (in_array($errorType, ['php_version', 'php_extension'], true)) {
            $icon = '⚠️';
        } elseif ($errorType === 'permissions') {
            $icon = '🔒';
            $noticeClass = 'notice-warning';
        }

        ?>
        <div class="notice <?php echo esc_attr($noticeClass); ?> is-dismissible fp-ps-activation-error" style="border-left-width: 4px; padding: 12px;">
            <h3 style="margin-top: 0;">
                <?php echo $icon; ?> 
                <?php _e('FP Performance Suite: Errore Critico all\'Installazione', 'fp-performance-suite'); ?>
            </h3>
            
            <p style="font-size: 14px; margin: 10px 0;">
                <strong><?php _e('Errore:', 'fp-performance-suite'); ?></strong> 
                <?php echo $errorMessage; ?>
            </p>

            <?php if (!empty($solution)): ?>
            <div style="background: #fff; border-left: 3px solid #00a0d2; padding: 10px; margin: 10px 0;">
                <p style="margin: 0;">
                    <strong>💡 <?php _e('Soluzione:', 'fp-performance-suite'); ?></strong><br>
                    <?php echo esc_html($solution); ?>
                </p>
            </div>
            <?php endif; ?>

            <details style="margin-top: 10px;">
                <summary style="cursor: pointer; color: #2271b1;">
                    <?php _e('Dettagli tecnici (clicca per espandere)', 'fp-performance-suite'); ?>
                </summary>
                <div style="background: #f0f0f1; padding: 10px; margin-top: 10px; font-family: monospace; font-size: 12px;">
                    <p><strong><?php _e('Versione PHP:', 'fp-performance-suite'); ?></strong> <?php echo $phpVersion; ?></p>
                    <p><strong><?php _e('Versione WordPress:', 'fp-performance-suite'); ?></strong> <?php echo $wpVersion; ?></p>
                    <?php if (!empty($file)): ?>
                    <p><strong><?php _e('File:', 'fp-performance-suite'); ?></strong> <?php echo $file; ?></p>
                    <p><strong><?php _e('Linea:', 'fp-performance-suite'); ?></strong> <?php echo $line > 0 ? $line : 'N/A'; ?></p>
                    <?php endif; ?>
                    <p><strong><?php _e('Data:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(date('Y-m-d H:i:s', $time)); ?></p>
                </div>
            </details>

            <p style="margin-top: 15px;">
                <button type="button" class="button button-secondary fp-ps-dismiss-error" data-nonce="<?php echo esc_attr(wp_create_nonce('fp_ps_dismiss_error')); ?>">
                    <?php _e('Dismiss', 'fp-performance-suite'); ?>
                </button>
            </p>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.fp-ps-dismiss-error').on('click', function() {
                    const button = $(this);
                    const nonce = button.data('nonce');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fp_ps_dismiss_activation_error',
                            nonce: nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                $('.fp-ps-activation-error').fadeOut(300, function() {
                                    $(this).remove();
                                });
                            } else {
                                alert(response.data.message || 'Errore durante la dismissione');
                            }
                        },
                        error: function() {
                            alert('Errore di comunicazione con il server');
                        }
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * Dismissione dell'errore di attivazione via AJAX
     */
    public function dismissActivationError(): void
    {
        // SICUREZZA: Sanitizza il nonce PRIMA di verificarlo
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        
        if (empty($nonce) || !wp_verify_nonce($nonce, 'fp_ps_dismiss_error')) {
            wp_send_json_error(['message' => 'Nonce non valido']);
            return;
        }

        // Verifica i permessi
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
            return;
        }

        // Rimuovi l'opzione
        delete_option('fp_perfsuite_activation_error');
        
        wp_send_json_success(['message' => 'Errore dismisso con successo']);
    }
    
    /**
     * Dismissione del notice Salient via AJAX
     */
    public function dismissSalientNotice(): void
    {
        // SICUREZZA: Sanitizza il nonce PRIMA di verificarlo
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        
        if (empty($nonce) || !wp_verify_nonce($nonce, 'fp_ps_dismiss_salient')) {
            wp_send_json_error(['message' => 'Nonce non valido']);
            return;
        }

        // Verifica i permessi
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
            return;
        }

        // Salva la preferenza per l'utente corrente
        update_user_meta(get_current_user_id(), 'fp_ps_dismiss_salient_notice', true);
        
        wp_send_json_success(['message' => 'Notice dismisso con successo']);
    }
}

