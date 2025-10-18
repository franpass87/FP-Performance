<?php

/**
 * Diagnostics Page
 * 
 * Pagina per la diagnostica del sistema e risoluzione problemi
 *
 * @package FP\PerfSuite\Admin\Pages
 * @author Francesco Passeri
 */

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Utils\InstallationRecovery;
use FP\PerfSuite\Utils\Logger;

class Diagnostics extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-diagnostics';
    }

    public function capability(): string
    {
        return 'manage_options';
    }

    public function title(): string
    {
        return __('System Diagnostics', 'fp-performance-suite');
    }

    public function menuTitle(): string
    {
        return __('Diagnostics', 'fp-performance-suite');
    }

    public function view(): string
    {
        return '';
    }

    protected function content(): string
    {
        return '';
    }

    protected function handleActions(): void
    {
        if (!isset($_POST['fp_ps_nonce']) || !wp_verify_nonce($_POST['fp_ps_nonce'], 'fp_ps_diagnostics')) {
            return;
        }

        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'run_diagnostics':
                $this->runDiagnostics();
                break;
            
            case 'fix_permissions':
                $this->fixPermissions();
                break;
            
            case 'clear_error':
                $this->clearActivationError();
                break;
        }
    }

    private function runDiagnostics(): void
    {
        try {
            $diagnostics = InstallationRecovery::runDiagnostics();
            update_option('fp_ps_last_diagnostics', $diagnostics, false);
            
            $this->addNotice(
                'success',
                __('Diagnostica completata con successo. Vedi i risultati qui sotto.', 'fp-performance-suite')
            );
        } catch (\Throwable $e) {
            Logger::error('Diagnostics failed', $e);
            $this->addNotice(
                'error',
                sprintf(__('Errore durante la diagnostica: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function fixPermissions(): void
    {
        try {
            $result = InstallationRecovery::attemptRecovery(['type' => 'permissions']);
            
            if ($result) {
                $this->addNotice(
                    'success',
                    __('Permessi corretti con successo!', 'fp-performance-suite')
                );
            } else {
                $this->addNotice(
                    'warning',
                    __('Non è stato possibile correggere automaticamente i permessi. Contatta il supporto hosting.', 'fp-performance-suite')
                );
            }
        } catch (\Throwable $e) {
            Logger::error('Permission fix failed', $e);
            $this->addNotice(
                'error',
                sprintf(__('Errore durante la correzione dei permessi: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function clearActivationError(): void
    {
        delete_option('fp_perfsuite_activation_error');
        $this->addNotice(
            'success',
            __('Errore di attivazione cancellato.', 'fp-performance-suite')
        );
    }

    public function render(): void
    {
        $diagnostics = get_option('fp_ps_last_diagnostics');
        $activationError = get_option('fp_perfsuite_activation_error');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($this->title()); ?></h1>
            
            <?php $this->renderNotices(); ?>

            <div class="card" style="max-width: 100%;">
                <h2>🔍 <?php _e('Diagnostica di Sistema', 'fp-performance-suite'); ?></h2>
                <p>
                    <?php _e('Esegui una diagnostica completa del sistema per identificare eventuali problemi di configurazione o incompatibilità.', 'fp-performance-suite'); ?>
                </p>
                
                <form method="post" style="margin-top: 20px;">
                    <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                    <input type="hidden" name="action" value="run_diagnostics">
                    <button type="submit" class="button button-primary">
                        <?php _e('Esegui Diagnostica', 'fp-performance-suite'); ?>
                    </button>
                </form>

                <?php if (!empty($diagnostics)): ?>
                <div style="margin-top: 30px;">
                    <h3><?php _e('Ultimo Report Diagnostico', 'fp-performance-suite'); ?></h3>
                    <?php echo InstallationRecovery::generateDiagnosticReport($diagnostics); ?>
                    
                    <div style="margin-top: 20px;">
                        <h4><?php _e('Dettagli Completi:', 'fp-performance-suite'); ?></h4>
                        <pre style="background: #f0f0f1; padding: 15px; overflow: auto; max-height: 400px; font-size: 12px; border-radius: 4px;"><?php echo esc_html(json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($activationError)): ?>
            <div class="card" style="max-width: 100%; margin-top: 20px;">
                <h2>⚠️ <?php _e('Errore di Attivazione Rilevato', 'fp-performance-suite'); ?></h2>
                <p>
                    <?php _e('È stato rilevato un errore durante l\'ultima attivazione del plugin:', 'fp-performance-suite'); ?>
                </p>
                
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0;">
                    <p><strong><?php _e('Messaggio:', 'fp-performance-suite'); ?></strong></p>
                    <p><?php echo esc_html($activationError['message'] ?? 'N/A'); ?></p>
                    
                    <?php if (!empty($activationError['solution'])): ?>
                    <p style="margin-top: 15px;"><strong><?php _e('Soluzione Suggerita:', 'fp-performance-suite'); ?></strong></p>
                    <p><?php echo esc_html($activationError['solution']); ?></p>
                    <?php endif; ?>
                </div>

                <form method="post" style="margin-top: 20px;">
                    <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                    <input type="hidden" name="action" value="clear_error">
                    <button type="submit" class="button">
                        <?php _e('Cancella Errore', 'fp-performance-suite'); ?>
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <div class="card" style="max-width: 100%; margin-top: 20px;">
                <h2>🔧 <?php _e('Strumenti di Recupero', 'fp-performance-suite'); ?></h2>
                <p>
                    <?php _e('Utilizza questi strumenti per tentare di risolvere automaticamente problemi comuni.', 'fp-performance-suite'); ?>
                </p>

                <form method="post" style="margin-top: 20px;">
                    <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                    <input type="hidden" name="action" value="fix_permissions">
                    <button type="submit" class="button button-secondary">
                        <?php _e('Ripara Permessi Directory', 'fp-performance-suite'); ?>
                    </button>
                </form>
            </div>

            <div class="card" style="max-width: 100%; margin-top: 20px;">
                <h2>ℹ️ <?php _e('Informazioni di Sistema', 'fp-performance-suite'); ?></h2>
                <table class="widefat" style="margin-top: 15px;">
                    <tbody>
                        <tr>
                            <td style="width: 200px;"><strong><?php _e('Versione PHP:', 'fp-performance-suite'); ?></strong></td>
                            <td><?php echo esc_html(PHP_VERSION); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Versione WordPress:', 'fp-performance-suite'); ?></strong></td>
                            <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Versione Plugin:', 'fp-performance-suite'); ?></strong></td>
                            <td><?php echo esc_html(defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Memory Limit:', 'fp-performance-suite'); ?></strong></td>
                            <td><?php echo esc_html(ini_get('memory_limit')); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Max Execution Time:', 'fp-performance-suite'); ?></strong></td>
                            <td><?php echo esc_html(ini_get('max_execution_time')); ?>s</td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Upload Max Filesize:', 'fp-performance-suite'); ?></strong></td>
                            <td><?php echo esc_html(ini_get('upload_max_filesize')); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Post Max Size:', 'fp-performance-suite'); ?></strong></td>
                            <td><?php echo esc_html(ini_get('post_max_size')); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Server Software:', 'fp-performance-suite'); ?></strong></td>
                            <td><?php echo esc_html($_SERVER['SERVER_SOFTWARE'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Sistema Operativo:', 'fp-performance-suite'); ?></strong></td>
                            <td><?php echo esc_html(PHP_OS); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card" style="max-width: 100%; margin-top: 20px;">
                <h2>📦 <?php _e('Estensioni PHP', 'fp-performance-suite'); ?></h2>
                <?php
                $requiredExtensions = ['json', 'mbstring', 'fileinfo', 'gd', 'imagick', 'curl', 'zip'];
                ?>
                <table class="widefat" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th><?php _e('Estensione', 'fp-performance-suite'); ?></th>
                            <th><?php _e('Stato', 'fp-performance-suite'); ?></th>
                            <th><?php _e('Note', 'fp-performance-suite'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requiredExtensions as $ext): ?>
                        <tr>
                            <td><code><?php echo esc_html($ext); ?></code></td>
                            <td>
                                <?php if (extension_loaded($ext)): ?>
                                    <span style="color: green;">✅ <?php _e('Attiva', 'fp-performance-suite'); ?></span>
                                <?php else: ?>
                                    <span style="color: red;">❌ <?php _e('Non Attiva', 'fp-performance-suite'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $notes = [
                                    'json' => __('Richiesta - Gestione dati JSON', 'fp-performance-suite'),
                                    'mbstring' => __('Richiesta - Manipolazione stringhe multibyte', 'fp-performance-suite'),
                                    'fileinfo' => __('Richiesta - Rilevamento tipo file', 'fp-performance-suite'),
                                    'gd' => __('Consigliata - Conversione immagini WebP', 'fp-performance-suite'),
                                    'imagick' => __('Consigliata - Conversione immagini avanzata', 'fp-performance-suite'),
                                    'curl' => __('Consigliata - Chiamate HTTP', 'fp-performance-suite'),
                                    'zip' => __('Opzionale - Gestione archivi', 'fp-performance-suite'),
                                ];
                                echo esc_html($notes[$ext] ?? '');
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}
