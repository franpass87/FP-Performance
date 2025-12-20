<?php

namespace FP\PerfSuite\Utils\Htaccess;

use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Logger;
use function glob;
use function is_file;
use function filesize;
use function basename;
use function str_replace;
use function usort;
use function strcmp;
use const ABSPATH;

/**
 * Gestisce i backup del file .htaccess
 * 
 * @package FP\PerfSuite\Utils\Htaccess
 * @author Francesco Passeri
 */
class BackupManager
{
    private const MAX_BACKUPS = 3;
    private Fs $fs;

    public function __construct(Fs $fs)
    {
        $this->fs = $fs;
    }

    /**
     * Crea un backup del file
     */
    public function backup(string $file): ?string
    {
        try {
            if (!$this->fs->exists($file)) {
                return null;
            }
            $this->pruneBackups($file);
            $backup = $file . '.bak-' . gmdate('YmdHis');
            $this->fs->copy($file, $backup, true);
            Logger::info('.htaccess backup created', ['backup' => basename($backup)]);
            return $backup;
        } catch (\Throwable $e) {
            Logger::error('Failed to back up .htaccess', $e);
            return null;
        }
    }

    /**
     * Rimuove i backup più vecchi mantenendo solo MAX_BACKUPS
     */
    private function pruneBackups(string $file): void
    {
        $pattern = $file . '.bak-*';
        $backups = glob($pattern);

        if (!is_array($backups) || count($backups) < self::MAX_BACKUPS) {
            return;
        }

        // Ordina per data (più vecchi prima)
        usort($backups, function ($a, $b) {
            return strcmp($a, $b);
        });

        // Rimuovi i più vecchi
        $toRemove = count($backups) - self::MAX_BACKUPS;
        for ($i = 0; $i < $toRemove; $i++) {
            @unlink($backups[$i]);
        }
    }

    /**
     * Ottiene la lista di tutti i backup disponibili
     */
    public function getBackups(): array
    {
        $file = ABSPATH . '.htaccess';
        $pattern = $file . '.bak-*';
        $backups = glob($pattern);

        if (!is_array($backups)) {
            return [];
        }

        $result = [];
        foreach ($backups as $backup) {
            if (!is_file($backup)) {
                continue;
            }

            $timestamp = str_replace($file . '.bak-', '', $backup);
            $result[] = [
                'path' => $backup,
                'filename' => basename($backup),
                'timestamp' => $timestamp,
                'date' => \DateTime::createFromFormat('YmdHis', $timestamp),
                'size' => filesize($backup),
                'readable_date' => $this->formatBackupDate($timestamp),
            ];
        }

        // Ordina per timestamp decrescente (più recenti prima)
        usort($result, function ($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });

        return $result;
    }

    /**
     * Formatta la data del backup
     */
    private function formatBackupDate(string $timestamp): string
    {
        $date = \DateTime::createFromFormat('YmdHis', $timestamp);
        if ($date) {
            return $date->format('d/m/Y H:i:s');
        }
        return $timestamp;
    }

    /**
     * Ripristina un backup
     */
    public function restore(string $backupPath): bool
    {
        $file = ABSPATH . '.htaccess';
        
        try {
            // SICUREZZA: Validazione del path per prevenire Path Traversal
            $realBackupPath = realpath($backupPath);
            
            if ($realBackupPath === false) {
                Logger::error('Backup path does not exist', ['path' => $backupPath]);
                return false;
            }
            
            // SICUREZZA: Il backup DEVE essere nella stessa directory di .htaccess
            $expectedDir = dirname($file);
            $realBackupDir = dirname($realBackupPath);
            $expectedRealDir = realpath($expectedDir);
            
            if ($realBackupDir !== $expectedRealDir) {
                Logger::error('Security: Backup path outside allowed directory', [
                    'path' => $backupPath,
                    'real_dir' => $realBackupDir,
                    'expected_dir' => $expectedRealDir,
                ]);
                return false;
            }
            
            // SICUREZZA: Il nome deve matchare il pattern .htaccess.bak-YYYYMMDDHHMMSS
            $basename = basename($realBackupPath);
            if (!preg_match('/^\.htaccess\.bak-\d{14}$/', $basename)) {
                Logger::error('Security: Invalid backup filename format', [
                    'path' => $backupPath,
                    'basename' => $basename,
                ]);
                return false;
            }
            
            if (!$this->fs->exists($realBackupPath)) {
                Logger::error('.htaccess backup not found', ['path' => $realBackupPath]);
                return false;
            }

            // Crea un backup del file corrente prima di ripristinare
            if ($this->fs->exists($file)) {
                $currentBackup = $this->backup($file);
                if ($currentBackup === null) {
                    Logger::warning('Could not create backup before restore, proceeding anyway');
                }
            }

            // Ripristina il backup (usa il path validato)
            $result = $this->fs->copy($realBackupPath, $file, true);
            
            if ($result) {
                Logger::info('.htaccess restored from backup', ['backup' => $basename]);
                do_action('fp_ps_htaccess_restored', $realBackupPath);
            }

            return $result;
        } catch (\Throwable $e) {
            Logger::error('Failed to restore .htaccess from backup', $e);
            return false;
        }
    }

    /**
     * Elimina un backup
     */
    public function deleteBackup(string $backupPath): bool
    {
        try {
            if (!$this->fs->exists($backupPath)) {
                return false;
            }

            $result = $this->fs->delete($backupPath);

            if ($result) {
                Logger::info('.htaccess backup deleted', ['backup' => basename($backupPath)]);
            }

            return $result;
        } catch (\Throwable $e) {
            Logger::error('Failed to delete .htaccess backup', $e);
            return false;
        }
    }
}

