<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\Capabilities;

abstract class AbstractPage
{
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    abstract public function slug(): string;

    abstract public function title(): string;

    public function capability(): string
    {
        return $this->requiredCapability();
    }

    abstract public function view(): string;

    abstract protected function content(): string;

    public function render(): void
    {
        $required_capability = $this->capability();
        
        // Fallback di sicurezza: se la capability è vuota o non valida, usa manage_options
        if (empty($required_capability) || !is_string($required_capability)) {
            $required_capability = 'manage_options';
        }
        
        // Se l'utente è un super admin, permetti sempre l'accesso
        if (is_super_admin()) {
            // Continua con il rendering
        } elseif (!current_user_can($required_capability)) {
            // Log per debug
            error_log('[FP Performance Suite] Accesso negato alla pagina ' . $this->slug());
            error_log('[FP Performance Suite] Capability richiesta: ' . $required_capability);
            error_log('[FP Performance Suite] Utente corrente: ' . wp_get_current_user()->user_login);
            error_log('[FP Performance Suite] Ruoli utente: ' . implode(', ', wp_get_current_user()->roles));
            
            // Messaggio di errore dettagliato
            $message = esc_html__('Non hai il permesso di accedere a questa pagina.', 'fp-performance-suite');
            
            // Se l'utente ha manage_options ma non la capability richiesta, c'è un problema di configurazione
            if (current_user_can('manage_options')) {
                $message .= '<br><br>';
                $message .= '<div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0;">';
                $message .= '<strong>' . esc_html__('⚠️ Problema di Configurazione Rilevato', 'fp-performance-suite') . '</strong><br>';
                $message .= esc_html__('Sei un amministratore ma le impostazioni del plugin potrebbero essere corrotte.', 'fp-performance-suite') . '<br><br>';
                $message .= '<strong>' . esc_html__('Informazioni di debug:', 'fp-performance-suite') . '</strong><br>';
                $message .= esc_html__('Capability richiesta:', 'fp-performance-suite') . ' <code>' . esc_html($required_capability) . '</code><br>';
                $message .= esc_html__('I tuoi ruoli:', 'fp-performance-suite') . ' <code>' . esc_html(implode(', ', wp_get_current_user()->roles)) . '</code><br>';
                $message .= '</div>';
                
                // Pulsante per reimpostare le impostazioni
                $message .= '<a href="' . esc_url(wp_nonce_url(admin_url('admin.php?page=fp-performance-suite-settings&fp_reset_permissions=1'), 'fp_reset_permissions')) . '" class="button button-primary" style="margin-right: 10px;">';
                $message .= esc_html__('🔄 Reimposta Permessi', 'fp-performance-suite');
                $message .= '</a>';
                
                $message .= '<a href="' . esc_url(admin_url('admin.php?page=fp-performance-suite-diagnostics')) . '" class="button">';
                $message .= esc_html__('🔍 Esegui Diagnostica Completa', 'fp-performance-suite');
                $message .= '</a>';
            }
            
            wp_die($message);
        }

        try {
            $view = $this->view();
            $data = $this->data();
            $data['content'] = $this->content();
            if (is_readable($view)) {
                $pageData = $data;
                include $view;
            }
        } catch (\Throwable $e) {
            // Log the error
            error_log('[FP Performance Suite] Error rendering page ' . $this->slug() . ': ' . $e->getMessage());
            error_log('[FP Performance Suite] Stack trace: ' . $e->getTraceAsString());
            
            // Display user-friendly error message
            ?>
            <div class="wrap">
                <h1><?php echo esc_html($this->title()); ?></h1>
                <div class="notice notice-error">
                    <p>
                        <strong><?php esc_html_e('Errore:', 'fp-performance-suite'); ?></strong>
                        <?php esc_html_e('Si è verificato un errore durante il caricamento di questa pagina.', 'fp-performance-suite'); ?>
                    </p>
                    <details>
                        <summary style="cursor: pointer;"><?php esc_html_e('Dettagli tecnici', 'fp-performance-suite'); ?></summary>
                        <pre style="background: #f0f0f1; padding: 10px; overflow: auto;"><?php echo esc_html($e->getMessage()); ?></pre>
                        <p><strong><?php esc_html_e('File:', 'fp-performance-suite'); ?></strong> <?php echo esc_html($e->getFile()); ?></p>
                        <p><strong><?php esc_html_e('Linea:', 'fp-performance-suite'); ?></strong> <?php echo esc_html($e->getLine()); ?></p>
                    </details>
                </div>
            </div>
            <?php
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function data(): array
    {
        return [];
    }

    protected function requiredCapability(): string
    {
        return Capabilities::required();
    }
}
