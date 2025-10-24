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

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\InstallationRecovery;
use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\Htaccess;
use FP\PerfSuite\Utils\Fs;

use function __;
use function _e;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html__;
use function wp_kses_post;
use function wp_verify_nonce;
use function wp_nonce_field;
use function wp_die;
use function sanitize_text_field;
use function update_option;
use function delete_option;
use function get_option;
use function current_user_can;
use function extension_loaded;
use function get_bloginfo;
use function ini_get;
use function size_format;

class Diagnostics extends AbstractPage
{
    private array $notices = [];

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

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

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [
                __('Tools', 'fp-performance-suite'),
                __('Diagnostics', 'fp-performance-suite'),
            ],
        ];
    }

    protected function content(): string
    {
        $diagnostics = get_option('fp_ps_last_diagnostics');
        $activationError = get_option('fp_perfsuite_activation_error');
        
        // Render notices se presenti
        $noticesHtml = '';
        if (!empty($this->notices)) {
            ob_start();
            $this->renderNotices();
            $noticesHtml = ob_get_clean();
        }
        
        ob_start();
        ?>
        
        <?php echo $noticesHtml; ?>
        
        <!-- Diagnostic Section -->
        <section class="fp-ps-card">
            <h2>🔍 <?php _e('Diagnostica di Sistema', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php _e('Esegui una diagnostica completa del sistema per identificare eventuali problemi di configurazione o incompatibilità.', 'fp-performance-suite'); ?>
            </p>
            
            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                <input type="hidden" name="action" value="run_diagnostics">
                <button type="submit" class="button button-primary button-large">
                    🔍 <?php _e('Esegui Diagnostica', 'fp-performance-suite'); ?>
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
        </section>

        <?php if (!empty($activationError)): ?>
        <!-- Activation Error Section -->
        <section class="fp-ps-card">
            <h2>⚠️ <?php _e('Errore di Attivazione Rilevato', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php _e('È stato rilevato un errore durante l\'ultima attivazione del plugin.', 'fp-performance-suite'); ?>
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
                    🗑️ <?php _e('Cancella Errore', 'fp-performance-suite'); ?>
                </button>
            </form>
        </section>
        <?php endif; ?>

        <!-- Recovery Tools Section -->
        <section class="fp-ps-card">
            <h2>🔧 <?php _e('Strumenti di Recupero', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php _e('Utilizza questi strumenti per tentare di risolvere automaticamente problemi comuni.', 'fp-performance-suite'); ?>
            </p>

            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                <input type="hidden" name="action" value="fix_permissions">
                <button type="submit" class="button button-secondary">
                    🔧 <?php _e('Ripara Permessi Directory', 'fp-performance-suite'); ?>
                </button>
            </form>
        </section>

        <!-- System Information Section -->
        <section class="fp-ps-card">
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
        </section>

        <!-- PHP Extensions Section -->
        <section class="fp-ps-card">
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
                                'gd' => __('Consigliata - Elaborazione immagini', 'fp-performance-suite'),
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
        </section>

        <?php
        // Sezione gestione .htaccess
        $htaccess = new Htaccess(new Fs());
        $htaccessInfo = $htaccess->getFileInfo();
        $htaccessBackups = $htaccess->getBackups();
        ?>

        <!-- .htaccess Management Section -->
        <section class="fp-ps-card">
            <h2>📝 <?php _e('Gestione File .htaccess', 'fp-performance-suite'); ?></h2>
            
            <?php if (!$htaccessInfo['exists']): ?>
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0;">
                    <p><strong>⚠️ <?php _e('File .htaccess non trovato', 'fp-performance-suite'); ?></strong></p>
                    <p><?php _e('Il file .htaccess non esiste nella directory root di WordPress. Questo è normale se usi server Nginx.', 'fp-performance-suite'); ?></p>
                </div>
            <?php else: ?>
                <?php echo $this->renderHtaccessManagement($htaccessInfo, $htaccessBackups); ?>
            <?php endif; ?>
        </section>
        
        <?php
        return ob_get_clean();
    }

    /**
     * Aggiunge un messaggio di notifica
     */
    private function addNotice(string $type, string $message): void
    {
        $this->notices[] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Renderizza i messaggi di notifica
     */
    private function renderNotices(): void
    {
        foreach ($this->notices as $notice) {
            $class = 'notice notice-' . esc_attr($notice['type']);
            echo '<div class="' . $class . ' is-dismissible"><p>' . wp_kses_post($notice['message']) . '</p></div>';
        }
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
            
            case 'validate_htaccess':
                $this->validateHtaccess();
                break;
            
            case 'repair_htaccess':
                $this->repairHtaccess();
                break;
            
            case 'restore_htaccess':
                $this->restoreHtaccess();
                break;
            
            case 'reset_htaccess':
                $this->resetHtaccess();
                break;
            
            case 'delete_htaccess_backup':
                $this->deleteHtaccessBackup();
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

    private function validateHtaccess(): void
    {
        try {
            $htaccess = new Htaccess(new Fs());
            $validation = $htaccess->validate();
            
            if ($validation['valid']) {
                $this->addNotice(
                    'success',
                    __('✅ Il file .htaccess è valido e non presenta errori.', 'fp-performance-suite')
                );
            } else {
                $errors = implode('<br>', array_map('esc_html', $validation['errors']));
                $this->addNotice(
                    'error',
                    sprintf(__('❌ Trovati errori nel file .htaccess:<br>%s', 'fp-performance-suite'), $errors)
                );
            }
        } catch (\Throwable $e) {
            Logger::error('Htaccess validation failed', $e);
            $this->addNotice(
                'error',
                sprintf(__('Errore durante la validazione: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function repairHtaccess(): void
    {
        try {
            $htaccess = new Htaccess(new Fs());
            $result = $htaccess->repairCommonIssues();
            
            if ($result['success']) {
                if (empty($result['fixes'])) {
                    $this->addNotice(
                        'info',
                        __('✅ Nessun problema rilevato. Il file .htaccess è già corretto.', 'fp-performance-suite')
                    );
                } else {
                    $fixes = implode('<br>', array_map('esc_html', $result['fixes']));
                    $this->addNotice(
                        'success',
                        sprintf(__('✅ File .htaccess riparato con successo!<br>Correzioni applicate:<br>%s', 'fp-performance-suite'), $fixes)
                    );
                }
            } else {
                $errors = implode('<br>', array_map('esc_html', $result['errors']));
                $this->addNotice(
                    'error',
                    sprintf(__('❌ Impossibile riparare il file .htaccess:<br>%s', 'fp-performance-suite'), $errors)
                );
            }
        } catch (\Throwable $e) {
            Logger::error('Htaccess repair failed', $e);
            $this->addNotice(
                'error',
                sprintf(__('Errore durante la riparazione: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function restoreHtaccess(): void
    {
        try {
            $backupPath = sanitize_text_field($_POST['backup_path'] ?? '');
            
            if (empty($backupPath)) {
                $this->addNotice('error', __('Nessun backup selezionato.', 'fp-performance-suite'));
                return;
            }

            $htaccess = new Htaccess(new Fs());
            $result = $htaccess->restore($backupPath);
            
            if ($result) {
                $this->addNotice(
                    'success',
                    sprintf(__('✅ File .htaccess ripristinato dal backup: %s', 'fp-performance-suite'), basename($backupPath))
                );
            } else {
                $this->addNotice(
                    'error',
                    __('❌ Impossibile ripristinare il backup. Verifica che il file esista.', 'fp-performance-suite')
                );
            }
        } catch (\Throwable $e) {
            Logger::error('Htaccess restore failed', $e);
            $this->addNotice(
                'error',
                sprintf(__('Errore durante il ripristino: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function resetHtaccess(): void
    {
        try {
            if (empty($_POST['confirm_reset'])) {
                $this->addNotice('error', __('Devi confermare il reset.', 'fp-performance-suite'));
                return;
            }

            $htaccess = new Htaccess(new Fs());
            $result = $htaccess->resetToWordPressDefault();
            
            if ($result) {
                $this->addNotice(
                    'success',
                    __('✅ File .htaccess resettato alle regole WordPress standard.', 'fp-performance-suite')
                );
            } else {
                $this->addNotice(
                    'error',
                    __('❌ Impossibile resettare il file .htaccess.', 'fp-performance-suite')
                );
            }
        } catch (\Throwable $e) {
            Logger::error('Htaccess reset failed', $e);
            $this->addNotice(
                'error',
                sprintf(__('Errore durante il reset: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function deleteHtaccessBackup(): void
    {
        try {
            $backupPath = sanitize_text_field($_POST['backup_path'] ?? '');
            
            if (empty($backupPath)) {
                $this->addNotice('error', __('Nessun backup selezionato.', 'fp-performance-suite'));
                return;
            }

            $htaccess = new Htaccess(new Fs());
            $result = $htaccess->deleteBackup($backupPath);
            
            if ($result) {
                $this->addNotice(
                    'success',
                    sprintf(__('🗑️ Backup eliminato: %s', 'fp-performance-suite'), basename($backupPath))
                );
            } else {
                $this->addNotice(
                    'error',
                    __('❌ Impossibile eliminare il backup.', 'fp-performance-suite')
                );
            }
        } catch (\Throwable $e) {
            Logger::error('Htaccess backup deletion failed', $e);
            $this->addNotice(
                'error',
                sprintf(__('Errore durante l\'eliminazione: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    public function render(): void
    {
        // Gestisce le azioni POST prima del rendering
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }

        // Usa il template standard di AbstractPage
        parent::render();
    }

    /**
     * Render .htaccess management section
     */
    private function renderHtaccessManagement(array $htaccessInfo, array $htaccessBackups): string
    {
        ob_start();
        ?>
        <!-- Informazioni file -->
        <div style="background: #f0f0f1; padding: 15px; margin: 15px 0; border-radius: 4px;">
            <h3 style="margin-top: 0;">📊 <?php _e('Informazioni File', 'fp-performance-suite'); ?></h3>
            <table class="widefat">
                <tbody>
                    <tr>
                        <td style="width: 200px;"><strong><?php _e('Percorso:', 'fp-performance-suite'); ?></strong></td>
                        <td><code><?php echo esc_html($htaccessInfo['path']); ?></code></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Scrivibile:', 'fp-performance-suite'); ?></strong></td>
                        <td>
                            <?php if ($htaccessInfo['writable']): ?>
                                <span style="color: green;">✅ <?php _e('Sì', 'fp-performance-suite'); ?></span>
                            <?php else: ?>
                                <span style="color: red;">❌ <?php _e('No', 'fp-performance-suite'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Dimensione:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html($htaccessInfo['size_formatted']); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Ultima modifica:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html($htaccessInfo['modified_formatted'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Righe totali:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html($htaccessInfo['lines']); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Sezioni trovate:', 'fp-performance-suite'); ?></strong></td>
                        <td>
                            <?php if (!empty($htaccessInfo['sections'])): ?>
                                <?php foreach ($htaccessInfo['sections'] as $section): ?>
                                    <span style="background: #2271b1; color: white; padding: 2px 6px; border-radius: 3px; margin-right: 5px; font-size: 11px; display: inline-block; margin-bottom: 3px;">
                                        <?php echo esc_html($section); ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php _e('Nessuna sezione', 'fp-performance-suite'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Strumenti di validazione e riparazione -->
        <div style="margin: 20px 0;">
            <h3>🔧 <?php _e('Strumenti di Diagnostica e Riparazione', 'fp-performance-suite'); ?></h3>
            <p><?php _e('Usa questi strumenti per verificare e riparare eventuali problemi nel file .htaccess.', 'fp-performance-suite'); ?></p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-top: 15px;">
                <!-- Valida -->
                <form method="post" style="margin: 0;">
                    <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                    <input type="hidden" name="action" value="validate_htaccess">
                    <button type="submit" class="button button-secondary" style="width: 100%; min-height: 80px; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; line-height: 1.3;">
                        <span style="font-size: 24px; display: block; margin-bottom: 6px;">✓</span>
                        <span style="font-size: 14px; font-weight: 500; white-space: nowrap;"><?php _e('Valida Sintassi', 'fp-performance-suite'); ?></span>
                    </button>
                </form>

                <!-- Ripara -->
                <form method="post" style="margin: 0;">
                    <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                    <input type="hidden" name="action" value="repair_htaccess">
                    <button type="submit" class="button button-primary" style="width: 100%; min-height: 80px; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; line-height: 1.3;">
                        <span style="font-size: 24px; display: block; margin-bottom: 6px;">🔧</span>
                        <span style="font-size: 14px; font-weight: 500; white-space: nowrap;"><?php _e('Ripara Automaticamente', 'fp-performance-suite'); ?></span>
                    </button>
                </form>
            </div>

            <div style="background: #e7f5fe; border-left: 4px solid #00a0d2; padding: 12px; margin: 15px 0; font-size: 13px;">
                <strong>ℹ️ <?php _e('Info:', 'fp-performance-suite'); ?></strong>
                <?php _e('La riparazione automatica crea sempre un backup prima di modificare il file.', 'fp-performance-suite'); ?>
            </div>
        </div>

        <!-- Backup disponibili -->
        <?php if (!empty($htaccessBackups)): ?>
        <div style="margin: 20px 0;">
            <h3>💾 <?php _e('Backup Disponibili', 'fp-performance-suite'); ?></h3>
            <p><?php printf(__('Trovati %d backup del file .htaccess.', 'fp-performance-suite'), count($htaccessBackups)); ?></p>
            
            <table class="widefat" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th><?php _e('Data Backup', 'fp-performance-suite'); ?></th>
                        <th><?php _e('Dimensione', 'fp-performance-suite'); ?></th>
                        <th><?php _e('Nome File', 'fp-performance-suite'); ?></th>
                        <th><?php _e('Azioni', 'fp-performance-suite'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($htaccessBackups as $backup): ?>
                    <tr>
                        <td><?php echo esc_html($backup['readable_date']); ?></td>
                        <td><?php echo esc_html(size_format($backup['size'])); ?></td>
                        <td><code style="font-size: 11px;"><?php echo esc_html($backup['filename']); ?></code></td>
                        <td>
                            <form method="post" style="display: inline-block; margin-right: 5px;">
                                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                                <input type="hidden" name="action" value="restore_htaccess">
                                <input type="hidden" name="backup_path" value="<?php echo esc_attr($backup['path']); ?>">
                                <button type="submit" class="button button-small" onclick="return confirm('<?php esc_attr_e('Sei sicuro di voler ripristinare questo backup?', 'fp-performance-suite'); ?>');">
                                    🔄 <?php _e('Ripristina', 'fp-performance-suite'); ?>
                                </button>
                            </form>
                            <form method="post" style="display: inline-block;">
                                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                                <input type="hidden" name="action" value="delete_htaccess_backup">
                                <input type="hidden" name="backup_path" value="<?php echo esc_attr($backup['path']); ?>">
                                <button type="submit" class="button button-small" onclick="return confirm('<?php esc_attr_e('Sei sicuro di voler eliminare questo backup?', 'fp-performance-suite'); ?>');">
                                    🗑️ <?php _e('Elimina', 'fp-performance-suite'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="background: #f0f0f1; padding: 15px; margin: 15px 0; border-radius: 4px;">
            <p><strong>💾 <?php _e('Nessun backup disponibile', 'fp-performance-suite'); ?></strong></p>
            <p><?php _e('I backup vengono creati automaticamente quando modifichi il file .htaccess tramite questo plugin.', 'fp-performance-suite'); ?></p>
        </div>
        <?php endif; ?>

        <!-- Reset alle regole WordPress -->
        <div style="margin: 20px 0; padding: 20px; background: #fff3cd; border-left: 4px solid #ff9800; border-radius: 4px;">
            <h3 style="margin-top: 0; color: #d63638;">⚠️ <?php _e('Zona Pericolosa', 'fp-performance-suite'); ?></h3>
            <p><strong><?php _e('Reset alle Regole WordPress Standard', 'fp-performance-suite'); ?></strong></p>
            <p><?php _e('Questa operazione sovrascriverà completamente il file .htaccess con le regole WordPress standard.', 'fp-performance-suite'); ?></p>
            
            <form method="post" style="margin-top: 15px;">
                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                <input type="hidden" name="action" value="reset_htaccess">
                <label style="display: block; margin-bottom: 10px;">
                    <input type="checkbox" name="confirm_reset" value="1" required>
                    <?php _e('Confermo di voler resettare il file .htaccess', 'fp-performance-suite'); ?>
                </label>
                <button type="submit" class="button button-secondary" style="background: #d63638; border-color: #d63638; color: white;" onclick="return confirm('<?php esc_attr_e('ATTENZIONE! Questa operazione resetterà completamente il file .htaccess. Sei sicuro?', 'fp-performance-suite'); ?>');">
                    🔥 <?php _e('Reset Completo .htaccess', 'fp-performance-suite'); ?>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * DEPRECATED - Old render method - keep for reference but now uses parent::render()
     */
    private function renderOld_DO_NOT_USE(): void
    {
        // Verifica permessi
        if (!current_user_can($this->capability())) {
            wp_die(esc_html__('Non hai i permessi per accedere a questa pagina.', 'fp-performance-suite'));
        }

        // Gestisce le azioni POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }

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
                                    'gd' => __('Consigliata - Elaborazione immagini', 'fp-performance-suite'),
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

            <?php
            // Sezione gestione .htaccess
            $htaccess = new Htaccess(new Fs());
            $htaccessInfo = $htaccess->getFileInfo();
            $htaccessBackups = $htaccess->getBackups();
            ?>

            <div class="card" style="max-width: 100%; margin-top: 20px;">
                <h2>📝 <?php _e('Gestione File .htaccess', 'fp-performance-suite'); ?></h2>
                
                <?php if (!$htaccessInfo['exists']): ?>
                    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0;">
                        <p><strong>⚠️ <?php _e('File .htaccess non trovato', 'fp-performance-suite'); ?></strong></p>
                        <p><?php _e('Il file .htaccess non esiste nella directory root di WordPress. Questo è normale se usi server Nginx.', 'fp-performance-suite'); ?></p>
                    </div>
                <?php else: ?>
                    <!-- Informazioni file -->
                    <div style="background: #f0f0f1; padding: 15px; margin: 15px 0; border-radius: 4px;">
                        <h3 style="margin-top: 0;">📊 <?php _e('Informazioni File', 'fp-performance-suite'); ?></h3>
                        <table class="widefat">
                            <tbody>
                                <tr>
                                    <td style="width: 200px;"><strong><?php _e('Percorso:', 'fp-performance-suite'); ?></strong></td>
                                    <td><code><?php echo esc_html($htaccessInfo['path']); ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('Scrivibile:', 'fp-performance-suite'); ?></strong></td>
                                    <td>
                                        <?php if ($htaccessInfo['writable']): ?>
                                            <span style="color: green;">✅ <?php _e('Sì', 'fp-performance-suite'); ?></span>
                                        <?php else: ?>
                                            <span style="color: red;">❌ <?php _e('No', 'fp-performance-suite'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('Dimensione:', 'fp-performance-suite'); ?></strong></td>
                                    <td><?php echo esc_html($htaccessInfo['size_formatted']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('Ultima modifica:', 'fp-performance-suite'); ?></strong></td>
                                    <td><?php echo esc_html($htaccessInfo['modified_formatted'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('Righe totali:', 'fp-performance-suite'); ?></strong></td>
                                    <td><?php echo esc_html($htaccessInfo['lines']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('Sezioni trovate:', 'fp-performance-suite'); ?></strong></td>
                                    <td>
                                        <?php if (!empty($htaccessInfo['sections'])): ?>
                                            <?php foreach ($htaccessInfo['sections'] as $section): ?>
                                                <span style="background: #2271b1; color: white; padding: 2px 6px; border-radius: 3px; margin-right: 5px; font-size: 11px; display: inline-block; margin-bottom: 3px;">
                                                    <?php echo esc_html($section); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <?php _e('Nessuna sezione', 'fp-performance-suite'); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Strumenti di validazione e riparazione -->
                    <div style="margin: 20px 0;">
                        <h3>🔧 <?php _e('Strumenti di Diagnostica e Riparazione', 'fp-performance-suite'); ?></h3>
                        <p><?php _e('Usa questi strumenti per verificare e riparare eventuali problemi nel file .htaccess.', 'fp-performance-suite'); ?></p>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-top: 15px;">
                            <!-- Valida -->
                            <form method="post" style="margin: 0;">
                                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                                <input type="hidden" name="action" value="validate_htaccess">
                                <button type="submit" class="button button-secondary" style="width: 100%; min-height: 80px; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; line-height: 1.3;">
                                    <span style="font-size: 24px; display: block; margin-bottom: 6px;">✓</span>
                                    <span style="font-size: 14px; font-weight: 500; white-space: nowrap;"><?php _e('Valida Sintassi', 'fp-performance-suite'); ?></span>
                                </button>
                            </form>

                            <!-- Ripara -->
                            <form method="post" style="margin: 0;">
                                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                                <input type="hidden" name="action" value="repair_htaccess">
                                <button type="submit" class="button button-primary" style="width: 100%; min-height: 80px; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; line-height: 1.3;">
                                    <span style="font-size: 24px; display: block; margin-bottom: 6px;">🔧</span>
                                    <span style="font-size: 14px; font-weight: 500; white-space: nowrap;"><?php _e('Ripara Automaticamente', 'fp-performance-suite'); ?></span>
                                </button>
                            </form>
                        </div>

                        <div style="background: #e7f5fe; border-left: 4px solid #00a0d2; padding: 12px; margin: 15px 0; font-size: 13px;">
                            <strong>ℹ️ <?php _e('Info:', 'fp-performance-suite'); ?></strong>
                            <?php _e('La riparazione automatica crea sempre un backup prima di modificare il file. Include la correzione di: markers non bilanciati, righe duplicate, RewriteEngine mancante.', 'fp-performance-suite'); ?>
                        </div>
                    </div>

                    <!-- Backup disponibili -->
                    <?php if (!empty($htaccessBackups)): ?>
                    <div style="margin: 20px 0;">
                        <h3>💾 <?php _e('Backup Disponibili', 'fp-performance-suite'); ?></h3>
                        <p><?php printf(__('Trovati %d backup del file .htaccess.', 'fp-performance-suite'), count($htaccessBackups)); ?></p>
                        
                        <table class="widefat" style="margin-top: 15px;">
                            <thead>
                                <tr>
                                    <th><?php _e('Data Backup', 'fp-performance-suite'); ?></th>
                                    <th><?php _e('Dimensione', 'fp-performance-suite'); ?></th>
                                    <th><?php _e('Nome File', 'fp-performance-suite'); ?></th>
                                    <th><?php _e('Azioni', 'fp-performance-suite'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($htaccessBackups as $backup): ?>
                                <tr>
                                    <td><?php echo esc_html($backup['readable_date']); ?></td>
                                    <td><?php echo esc_html(size_format($backup['size'])); ?></td>
                                    <td><code style="font-size: 11px;"><?php echo esc_html($backup['filename']); ?></code></td>
                                    <td>
                                        <form method="post" style="display: inline-block; margin-right: 5px;">
                                            <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                                            <input type="hidden" name="action" value="restore_htaccess">
                                            <input type="hidden" name="backup_path" value="<?php echo esc_attr($backup['path']); ?>">
                                            <button type="submit" class="button button-small" onclick="return confirm('<?php esc_attr_e('Sei sicuro di voler ripristinare questo backup? Il file corrente verrà salvato in un nuovo backup.', 'fp-performance-suite'); ?>');">
                                                🔄 <?php _e('Ripristina', 'fp-performance-suite'); ?>
                                            </button>
                                        </form>
                                        <form method="post" style="display: inline-block;">
                                            <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                                            <input type="hidden" name="action" value="delete_htaccess_backup">
                                            <input type="hidden" name="backup_path" value="<?php echo esc_attr($backup['path']); ?>">
                                            <button type="submit" class="button button-small" onclick="return confirm('<?php esc_attr_e('Sei sicuro di voler eliminare questo backup?', 'fp-performance-suite'); ?>');">
                                                🗑️ <?php _e('Elimina', 'fp-performance-suite'); ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div style="background: #f0f0f1; padding: 15px; margin: 15px 0; border-radius: 4px;">
                        <p><strong>💾 <?php _e('Nessun backup disponibile', 'fp-performance-suite'); ?></strong></p>
                        <p><?php _e('I backup vengono creati automaticamente quando modifichi il file .htaccess tramite questo plugin.', 'fp-performance-suite'); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Reset alle regole WordPress -->
                    <div style="margin: 20px 0; padding: 20px; background: #fff3cd; border-left: 4px solid #ff9800; border-radius: 4px;">
                        <h3 style="margin-top: 0; color: #d63638;">⚠️ <?php _e('Zona Pericolosa', 'fp-performance-suite'); ?></h3>
                        <p><strong><?php _e('Reset alle Regole WordPress Standard', 'fp-performance-suite'); ?></strong></p>
                        <p><?php _e('Questa operazione sovrascriverà completamente il file .htaccess con le regole WordPress standard. TUTTE le personalizzazioni verranno perse (ma salvate in un backup).', 'fp-performance-suite'); ?></p>
                        
                        <form method="post" style="margin-top: 15px;">
                            <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                            <input type="hidden" name="action" value="reset_htaccess">
                            <label style="display: block; margin-bottom: 10px;">
                                <input type="checkbox" name="confirm_reset" value="1" required>
                                <?php _e('Confermo di voler resettare il file .htaccess alle regole WordPress standard', 'fp-performance-suite'); ?>
                            </label>
                            <button type="submit" class="button button-secondary" style="background: #d63638; border-color: #d63638; color: white;" onclick="return confirm('<?php esc_attr_e('ATTENZIONE! Questa operazione resetterà completamente il file .htaccess. Sei ASSOLUTAMENTE sicuro?', 'fp-performance-suite'); ?>');">
                                🔥 <?php _e('Reset Completo .htaccess', 'fp-performance-suite'); ?>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
