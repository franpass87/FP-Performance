<?php

namespace FP\PerfSuite\Utils\Htaccess;

use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Logger;
use function explode;
use function trim;
use function stripos;
use function preg_replace;
use function implode;
use function do_action;
use const ABSPATH;

/**
 * Ripara errori comuni nel file .htaccess
 * 
 * @package FP\PerfSuite\Utils\Htaccess
 * @author Francesco Passeri
 */
class Repairer
{
    private Fs $fs;
    private BackupManager $backupManager;

    public function __construct(Fs $fs, BackupManager $backupManager)
    {
        $this->fs = $fs;
        $this->backupManager = $backupManager;
    }

    /**
     * Ripara errori comuni
     */
    public function repairCommonIssues(): array
    {
        $file = ABSPATH . '.htaccess';
        $fixes = [];
        $errors = [];

        try {
            if (!$this->fs->exists($file)) {
                return [
                    'success' => false,
                    'fixes' => [],
                    'errors' => ['File .htaccess non trovato'],
                ];
            }

            $content = $this->fs->getContents($file);
            $originalContent = $content;
            $lines = explode("\n", $content);
            $hasRewriteRules = false;
            $hasRewriteEngine = false;

            // Analizza il contenuto
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (stripos($trimmed, 'RewriteRule') === 0 || stripos($trimmed, 'RewriteCond') === 0) {
                    $hasRewriteRules = true;
                }
                if (stripos($trimmed, 'RewriteEngine') === 0) {
                    $hasRewriteEngine = true;
                }
            }

            // Fix 1: Aggiungi RewriteEngine On se manca
            if ($hasRewriteRules && !$hasRewriteEngine) {
                $updated = preg_replace(
                    '/(RewriteRule|RewriteCond)/i',
                    "RewriteEngine On\n$1",
                    $content,
                    1
                );
                if ($updated !== $content) {
                    $content = $updated;
                    $fixes[] = 'Aggiunto "RewriteEngine On" mancante';
                }
            }

            // Fix 2: Rimuovi righe duplicate
            $uniqueLines = [];
            $removedDuplicates = 0;
            foreach ($lines as $line) {
                $trimmed = trim($line);
                // Non rimuovere linee vuote o commenti
                if (empty($trimmed) || strpos($trimmed, '#') === 0) {
                    $uniqueLines[] = $line;
                    continue;
                }
                
                if (!in_array($line, $uniqueLines, true)) {
                    $uniqueLines[] = $line;
                } else {
                    $removedDuplicates++;
                }
            }

            if ($removedDuplicates > 0) {
                $content = implode("\n", $uniqueLines);
                $fixes[] = "Rimosse {$removedDuplicates} righe duplicate";
            }

            // Fix 3: Correggi markers non bilanciati
            $fixedMarkers = $this->fixUnbalancedMarkers($content);
            if ($fixedMarkers !== $content) {
                $content = $fixedMarkers;
                $fixes[] = 'Corretti markers BEGIN/END non bilanciati';
            }

            // Se ci sono modifiche, salva il file
            if ($content !== $originalContent) {
                $this->backupManager->backup($file);
                $result = $this->fs->putContents($file, $content);
                
                if (!$result) {
                    return [
                        'success' => false,
                        'fixes' => $fixes,
                        'errors' => ['Impossibile salvare il file riparato'],
                    ];
                }

                Logger::info('.htaccess repaired', ['fixes' => $fixes]);
                do_action('fp_ps_htaccess_repaired', $fixes);
            }

            return [
                'success' => true,
                'fixes' => $fixes,
                'errors' => [],
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to repair .htaccess', $e);
            return [
                'success' => false,
                'fixes' => $fixes,
                'errors' => ['Errore durante la riparazione: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Corregge markers BEGIN/END non bilanciati
     */
    private function fixUnbalancedMarkers(string $content): string
    {
        $lines = explode("\n", $content);
        $beginCount = 0;
        $endCount = 0;
        $fixed = [];

        foreach ($lines as $line) {
            if (preg_match('/^# BEGIN /', $line)) {
                $beginCount++;
            } elseif (preg_match('/^# END /', $line)) {
                $endCount++;
            }
            $fixed[] = $line;
        }

        // Se ci sono più BEGIN che END, aggiungi END mancanti
        if ($beginCount > $endCount) {
            $missing = $beginCount - $endCount;
            for ($i = 0; $i < $missing; $i++) {
                $fixed[] = '# END FP Performance Suite';
            }
        }

        return implode("\n", $fixed);
    }
}















