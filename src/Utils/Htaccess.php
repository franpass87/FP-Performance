<?php

namespace FP\PerfSuite\Utils;

use FP\PerfSuite\Utils\Htaccess\BackupManager;
use FP\PerfSuite\Utils\Htaccess\SectionManager;
use FP\PerfSuite\Utils\Htaccess\Validator;
use FP\PerfSuite\Utils\Htaccess\Repairer;

class Htaccess
{
    private Fs $fs;
    private BackupManager $backupManager;
    private SectionManager $sectionManager;
    private Validator $validator;
    private Repairer $repairer;

    public function __construct(Fs $fs)
    {
        $this->fs = $fs;
        $this->backupManager = new BackupManager($fs);
        $this->sectionManager = new SectionManager($fs, $this->backupManager);
        $this->validator = new Validator($fs);
        $this->repairer = new Repairer($fs, $this->backupManager);
    }

    public function isSupported(): bool
    {
        if (!function_exists('got_mod_rewrite')) {
            $helper = ABSPATH . 'wp-admin/includes/misc.php';
            if (is_readable($helper)) {
                require_once $helper;
            }
        }

        if (!function_exists('got_mod_rewrite') || !got_mod_rewrite()) {
            return false;
        }

        $file = ABSPATH . '.htaccess';
        if ($this->fs->exists($file)) {
            return is_writable($file);
        }

        return is_writable(ABSPATH);
    }

    public function backup(string $file): ?string
    {
        return $this->backupManager->backup($file);
    }

    public function injectRules(string $section, string $rules): bool
    {
        return $this->sectionManager->injectRules($section, $rules);
    }

    public function removeSection(string $section): bool
    {
        return $this->sectionManager->removeSection($section);
    }

    public function hasSection(string $section): bool
    {
        return $this->sectionManager->hasSection($section);
    }

    public function getBackups(): array
    {
        return $this->backupManager->getBackups();
    }

    public function restore(string $backupPath): bool
    {
        return $this->backupManager->restore($backupPath);
    }

    public function validate(?string $content = null): array
    {
        return $this->validator->validate($content);
    }
    
    // Metodi validate(), validateLine(), validateMarkers() rimossi - ora gestiti da Validator

    public function repairCommonIssues(): array
    {
        return $this->repairer->repairCommonIssues();
    }

    /**
     * Ripristina il file .htaccess alle regole WordPress standard
     * 
     * @return bool True se il reset ha avuto successo
     */
    public function resetToWordPressDefault(): bool
    {
        $file = ABSPATH . '.htaccess';
        
        try {
            // Crea backup prima di resettare
            if ($this->fs->exists($file)) {
                $this->backup($file);
            }

            // Regole WordPress standard
            $defaultRules = "# BEGIN WordPress\n";
            $defaultRules .= "RewriteEngine On\n";
            $defaultRules .= "RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n";
            $defaultRules .= "RewriteBase /\n";
            $defaultRules .= "RewriteRule ^index\.php$ - [L]\n";
            $defaultRules .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
            $defaultRules .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
            $defaultRules .= "RewriteRule . /index.php [L]\n";
            $defaultRules .= "# END WordPress\n";

            $result = $this->fs->putContents($file, $defaultRules);
            
            if ($result) {
                Logger::info('.htaccess reset to WordPress defaults');
                do_action('fp_ps_htaccess_reset');
            }

            return $result;
        } catch (\Throwable $e) {
            Logger::error('Failed to reset .htaccess', $e);
            return false;
        }
    }

    /**
     * Ottiene informazioni sul file .htaccess corrente
     * 
     * @return array Informazioni sul file
     */
    public function getFileInfo(): array
    {
        $file = ABSPATH . '.htaccess';
        
        $info = [
            'exists' => false,
            'path' => $file,
            'writable' => false,
            'size' => 0,
            'size_formatted' => '0 B',
            'modified' => null,
            'modified_formatted' => null,
            'lines' => 0,
            'sections' => [],
        ];

        if (!$this->fs->exists($file)) {
            return $info;
        }

        $info['exists'] = true;
        $info['writable'] = is_writable($file);
        $info['size'] = filesize($file);
        $info['size_formatted'] = size_format($info['size']);
        
        $modified = filemtime($file);
        if ($modified) {
            $info['modified'] = $modified;
            $info['modified_formatted'] = date_i18n('d/m/Y H:i:s', $modified);
        }

        try {
            $content = $this->fs->getContents($file);
            $info['lines'] = count(explode("\n", $content));
            
            // Trova tutte le sezioni
            preg_match_all('/# BEGIN (.+)$/m', $content, $matches);
            if (!empty($matches[1])) {
                $info['sections'] = $matches[1];
            }
        } catch (\Throwable $e) {
            Logger::error('Failed to read .htaccess info', $e);
        }

        return $info;
    }

    /**
     * Elimina un backup specifico
     * 
     * @param string $backupPath Percorso del backup da eliminare
     * @return bool True se l'eliminazione ha avuto successo
     */
    public function deleteBackup(string $backupPath): bool
    {
        return $this->backupManager->deleteBackup($backupPath);
    }
    
    // Metodo deleteBackup() rimosso - ora gestito da BackupManager
}
