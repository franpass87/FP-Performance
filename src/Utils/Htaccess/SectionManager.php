<?php

namespace FP\PerfSuite\Utils\Htaccess;

use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Logger;
use function preg_quote;
use function preg_match;
use function preg_replace;
use function do_action;
use const ABSPATH;
use const PHP_EOL;

/**
 * Gestisce le sezioni del file .htaccess
 * 
 * @package FP\PerfSuite\Utils\Htaccess
 * @author Francesco Passeri
 */
class SectionManager
{
    private Fs $fs;
    private BackupManager $backupManager;

    public function __construct(Fs $fs, BackupManager $backupManager)
    {
        $this->fs = $fs;
        $this->backupManager = $backupManager;
    }

    /**
     * Inietta regole in una sezione
     */
    public function injectRules(string $section, string $rules): bool
    {
        $file = ABSPATH . '.htaccess';
        
        try {
            $markerStart = "# BEGIN {$section}";
            $markerEnd = "# END {$section}";
            $block = $markerStart . PHP_EOL . $rules . PHP_EOL . $markerEnd;

            if (!$this->fs->exists($file)) {
                $this->backupManager->backup($file);
                return $this->fs->putContents($file, $block . PHP_EOL);
            }

            $existing = $this->fs->getContents($file);
            $pattern = sprintf('/%s\s*.*?\s*%s\n?/s', preg_quote($markerStart, '/'), preg_quote($markerEnd, '/'));

            if (preg_match($pattern, $existing)) {
                $updated = preg_replace($pattern, $block . PHP_EOL, $existing, 1);
            } else {
                $trimmed = rtrim($existing);
                $prefix = $trimmed === '' ? '' : $trimmed . PHP_EOL . PHP_EOL;
                $updated = $prefix . $block . PHP_EOL;
            }

            $this->backupManager->backup($file);
            $result = $this->fs->putContents($file, $updated);
            if ($result) {
                Logger::info('.htaccess rules injected', ['section' => $section]);
                do_action('fp_ps_htaccess_updated', $section, $rules);
            }
            return $result;
        } catch (\Throwable $e) {
            Logger::error('Failed to inject .htaccess rules', $e);
            return false;
        }
    }

    /**
     * Rimuove una sezione
     */
    public function removeSection(string $section): bool
    {
        $file = ABSPATH . '.htaccess';
        
        try {
            if (!$this->fs->exists($file)) {
                return false;
            }
            $existing = $this->fs->getContents($file);
            $markerStart = "# BEGIN {$section}";
            $markerEnd = "# END {$section}";
            $pattern = sprintf('/%s\s*.*?\s*%s\n?/s', preg_quote($markerStart, '/'), preg_quote($markerEnd, '/'));
            if (preg_match($pattern, $existing) !== 1) {
                return false;
            }
            $updated = preg_replace($pattern, '', $existing, 1);
            $this->backupManager->backup($file);
            $result = $this->fs->putContents($file, (string) $updated);
            if ($result) {
                Logger::info('.htaccess section removed', ['section' => $section]);
                do_action('fp_ps_htaccess_section_removed', $section);
            }
            return $result;
        } catch (\Throwable $e) {
            Logger::error('Failed to remove .htaccess section', $e);
            return false;
        }
    }

    /**
     * Verifica se una sezione esiste
     */
    public function hasSection(string $section): bool
    {
        $file = ABSPATH . '.htaccess';
        
        try {
            if (!$this->fs->exists($file)) {
                return false;
            }
            $contents = $this->fs->getContents($file);
            $markerStart = "# BEGIN {$section}";
            $markerEnd = "# END {$section}";
            $pattern = sprintf('/%s\s*.*?\s*%s/s', preg_quote($markerStart, '/'), preg_quote($markerEnd, '/'));
            return preg_match($pattern, $contents) === 1;
        } catch (\Throwable $e) {
            Logger::error('Failed to read .htaccess', $e);
            return false;
        }
    }
}















