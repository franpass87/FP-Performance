<?php

namespace FP\PerfSuite\Admin\Pages\Diagnostics;

use FP\PerfSuite\Admin\Form\AbstractFormHandler;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\InstallationRecovery;
use FP\PerfSuite\Utils\ErrorHandler;
use FP\PerfSuite\Utils\Htaccess;
use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Admin\NoticeManager;

use function delete_option;
use function update_option;
use function __;
use function sprintf;
use function esc_html;
use function implode;
use function array_map;
use function basename;

/**
 * Gestisce le submission dei form per Diagnostics page
 * 
 * @package FP\PerfSuite\Admin\Pages\Diagnostics
 * @author Francesco Passeri
 */
class FormHandler extends AbstractFormHandler
{
    /**
     * Gestisce tutte le azioni POST
     * 
     * @return string Messaggio di risultato (vuoto se nessun form processato)
     */
    public function handle(): string
    {
        if (!$this->isPost()) {
            return '';
        }

        if (!$this->verifyNonce('fp_ps_nonce', 'fp_ps_diagnostics')) {
            return '';
        }

        $action = $this->sanitizeInput('action', 'text') ?? '';
        if (empty($action)) {
            return '';
        }

        try {
            return match($action) {
                'run_diagnostics' => $this->formatNotice($this->runDiagnostics()),
                'fix_permissions' => $this->formatNotice($this->fixPermissions()),
                'clear_error' => $this->formatNotice($this->clearActivationError()),
                'validate_htaccess' => $this->formatNotice($this->validateHtaccess()),
                'repair_htaccess' => $this->formatNotice($this->repairHtaccess()),
                'restore_htaccess' => $this->formatNotice($this->restoreHtaccess()),
                'reset_htaccess' => $this->formatNotice($this->resetHtaccess()),
                'delete_htaccess_backup' => $this->formatNotice($this->deleteHtaccessBackup()),
                default => ''
            };
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Diagnostics form');
        }
    }

    /**
     * Formatta un array di notice in stringa
     */
    private function formatNotice(array $notice): string
    {
        if (empty($notice['message'])) {
            return '';
        }
        
        $type = $notice['type'] ?? 'info';
        return match($type) {
            'success' => $this->successMessage($notice['message']),
            'error' => $this->errorMessage($notice['message']),
            'warning' => sprintf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', esc_html($notice['message'])),
            default => sprintf('<div class="notice notice-info is-dismissible"><p>%s</p></div>', esc_html($notice['message']))
        };
    }

    private function runDiagnostics(): array
    {
        try {
            $diagnostics = InstallationRecovery::runDiagnostics();
            update_option('fp_ps_last_diagnostics', $diagnostics, false);
            
            NoticeManager::success(__('Diagnostica completata con successo. Vedi i risultati qui sotto.', 'fp-performance-suite'));
            return [
                'type' => 'success',
                'message' => __('Diagnostica completata con successo. Vedi i risultati qui sotto.', 'fp-performance-suite')
            ];
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Diagnostics failed');
            $message = sprintf(__('Errore durante la diagnostica: %s', 'fp-performance-suite'), $e->getMessage());
            NoticeManager::error($message);
            return [
                'type' => 'error',
                'message' => $message
            ];
        }
    }

    private function fixPermissions(): array
    {
        try {
            $result = InstallationRecovery::attemptRecovery(['type' => 'permissions']);
            
            if ($result) {
                $message = __('Permessi corretti con successo!', 'fp-performance-suite');
                NoticeManager::success($message);
                return ['type' => 'success', 'message' => $message];
            } else {
                $message = __('Non è stato possibile correggere automaticamente i permessi. Contatta il supporto hosting.', 'fp-performance-suite');
                NoticeManager::warning($message);
                return ['type' => 'warning', 'message' => $message];
            }
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Permission fix failed');
            $message = sprintf(__('Errore durante la correzione dei permessi: %s', 'fp-performance-suite'), $e->getMessage());
            NoticeManager::error($message);
            return ['type' => 'error', 'message' => $message];
        }
    }

    private function clearActivationError(): array
    {
        delete_option('fp_perfsuite_activation_error');
        $message = __('Errore di attivazione cancellato.', 'fp-performance-suite');
        NoticeManager::success($message);
        return ['type' => 'success', 'message' => $message];
    }

    private function validateHtaccess(): array
    {
        try {
            $htaccess = new Htaccess(new Fs());
            $validation = $htaccess->validate();
            
            if ($validation['valid']) {
                $message = __('✅ Il file .htaccess è valido e non presenta errori.', 'fp-performance-suite');
                NoticeManager::success($message);
                return ['type' => 'success', 'message' => $message];
            } else {
                $errors = implode('<br>', array_map('esc_html', $validation['errors']));
                $message = sprintf(__('❌ Trovati errori nel file .htaccess:<br>%s', 'fp-performance-suite'), $errors);
                NoticeManager::error($message);
                return ['type' => 'error', 'message' => $message];
            }
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Htaccess validation failed');
            $message = sprintf(__('Errore durante la validazione: %s', 'fp-performance-suite'), $e->getMessage());
            NoticeManager::error($message);
            return ['type' => 'error', 'message' => $message];
        }
    }

    private function repairHtaccess(): array
    {
        try {
            $htaccess = new Htaccess(new Fs());
            $result = $htaccess->repairCommonIssues();
            
            if ($result['success']) {
                if (empty($result['fixes'])) {
                    $message = __('✅ Nessun problema rilevato. Il file .htaccess è già corretto.', 'fp-performance-suite');
                    NoticeManager::info($message);
                    return ['type' => 'info', 'message' => $message];
                } else {
                    $fixes = implode('<br>', array_map('esc_html', $result['fixes']));
                    $message = sprintf(__('✅ File .htaccess riparato con successo!<br>Correzioni applicate:<br>%s', 'fp-performance-suite'), $fixes);
                    NoticeManager::success($message);
                    return ['type' => 'success', 'message' => $message];
                }
            } else {
                $errors = implode('<br>', array_map('esc_html', $result['errors']));
                $message = sprintf(__('❌ Impossibile riparare il file .htaccess:<br>%s', 'fp-performance-suite'), $errors);
                NoticeManager::error($message);
                return ['type' => 'error', 'message' => $message];
            }
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Htaccess repair failed');
            $message = sprintf(__('Errore durante la riparazione: %s', 'fp-performance-suite'), $e->getMessage());
            NoticeManager::error($message);
            return ['type' => 'error', 'message' => $message];
        }
    }

    private function restoreHtaccess(): array
    {
        try {
            $backupPath = $this->sanitizeInput('backup_path', 'text') ?? '';
            
            if (empty($backupPath)) {
                $message = __('Nessun backup selezionato.', 'fp-performance-suite');
                NoticeManager::error($message);
                return ['type' => 'error', 'message' => $message];
            }

            $htaccess = new Htaccess(new Fs());
            $result = $htaccess->restore($backupPath);
            
            if ($result) {
                $message = sprintf(__('✅ File .htaccess ripristinato dal backup: %s', 'fp-performance-suite'), basename($backupPath));
                NoticeManager::success($message);
                return ['type' => 'success', 'message' => $message];
            } else {
                $message = __('❌ Impossibile ripristinare il backup. Verifica che il file esista.', 'fp-performance-suite');
                NoticeManager::error($message);
                return ['type' => 'error', 'message' => $message];
            }
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Htaccess restore failed');
            $message = sprintf(__('Errore durante il ripristino: %s', 'fp-performance-suite'), $e->getMessage());
            NoticeManager::error($message);
            return ['type' => 'error', 'message' => $message];
        }
    }

    private function resetHtaccess(): array
    {
        try {
            if (!$this->sanitizeInput('confirm_reset', 'bool')) {
                $message = __('Devi confermare il reset.', 'fp-performance-suite');
                NoticeManager::error($message);
                return ['type' => 'error', 'message' => $message];
            }

            $htaccess = new Htaccess(new Fs());
            $result = $htaccess->resetToWordPressDefault();
            
            if ($result) {
                $message = __('✅ File .htaccess resettato alle regole WordPress standard.', 'fp-performance-suite');
                NoticeManager::success($message);
                return ['type' => 'success', 'message' => $message];
            } else {
                $message = __('❌ Impossibile resettare il file .htaccess.', 'fp-performance-suite');
                NoticeManager::error($message);
                return ['type' => 'error', 'message' => $message];
            }
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Htaccess reset failed');
            $message = sprintf(__('Errore durante il reset: %s', 'fp-performance-suite'), $e->getMessage());
            NoticeManager::error($message);
            return ['type' => 'error', 'message' => $message];
        }
    }

    private function deleteHtaccessBackup(): array
    {
        try {
            $backupPath = $this->sanitizeInput('backup_path', 'text') ?? '';
            
            if (empty($backupPath)) {
                $message = __('Nessun backup selezionato.', 'fp-performance-suite');
                NoticeManager::error($message);
                return ['type' => 'error', 'message' => $message];
            }

            $htaccess = new Htaccess(new Fs());
            $result = $htaccess->deleteBackup($backupPath);
            
            if ($result) {
                $message = sprintf(__('🗑️ Backup eliminato: %s', 'fp-performance-suite'), basename($backupPath));
                NoticeManager::success($message);
                return ['type' => 'success', 'message' => $message];
            } else {
                $message = __('❌ Impossibile eliminare il backup.', 'fp-performance-suite');
                NoticeManager::error($message);
                return ['type' => 'error', 'message' => $message];
            }
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Htaccess backup deletion failed');
            $message = sprintf(__('Errore durante l\'eliminazione del backup: %s', 'fp-performance-suite'), $e->getMessage());
            NoticeManager::error($message);
            return ['type' => 'error', 'message' => $message];
        }
    }
}
















