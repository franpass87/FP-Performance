<?php

namespace FP\PerfSuite\Initialization;

/**
 * Gestisce la creazione e configurazione delle directory necessarie
 * 
 * @package FP\PerfSuite\Initialization
 * @author Francesco Passeri
 */
class DirectoryManager
{
    /**
     * Assicura che le directory necessarie esistano e siano scrivibili
     */
    public function ensureRequiredDirectories(): void
    {
        $uploadDir = wp_upload_dir();
        
        if (!is_array($uploadDir) || empty($uploadDir['basedir'])) {
            return;
        }
        
        $baseDir = $uploadDir['basedir'];

        $requiredDirs = [
            $baseDir . '/fp-performance-suite',
            $baseDir . '/fp-performance-suite/cache',
            $baseDir . '/fp-performance-suite/logs',
        ];

        foreach ($requiredDirs as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
                
                // Crea file .htaccess per proteggere le directory
                $htaccessFile = $dir . '/.htaccess';
                if (!file_exists($htaccessFile)) {
                    file_put_contents($htaccessFile, "Order deny,allow\nDeny from all\n");
                }
            }
        }
    }
}
















