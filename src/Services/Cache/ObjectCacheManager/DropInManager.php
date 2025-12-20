<?php

namespace FP\PerfSuite\Services\Cache\ObjectCacheManager;

/**
 * Gestisce l'installazione e rimozione del drop-in
 * 
 * @package FP\PerfSuite\Services\Cache\ObjectCacheManager
 * @author Francesco Passeri
 */
class DropInManager
{
    private const DROP_IN_FILE = 'object-cache.php';
    private string $dropInPath;
    private DropInGenerator $generator;

    public function __construct(DropInGenerator $generator)
    {
        $this->dropInPath = WP_CONTENT_DIR . '/' . self::DROP_IN_FILE;
        $this->generator = $generator;
    }

    /**
     * Installa il drop-in
     */
    public function install(string $backend): array
    {
        // Verifica se esiste già un'implementazione di plugin
        if ($this->hasPluginImplementation()) {
            return [
                'success' => false,
                'message' => 'Esiste già un\'implementazione di object cache installata da un plugin. Rimuovila prima di continuare.',
            ];
        }

        // Genera il contenuto del drop-in
        $content = $this->generator->generate($backend);

        // Scrive il file
        if ($this->safeDropInWrite($this->dropInPath, $content)) {
            return [
                'success' => true,
                'message' => sprintf('Object cache drop-in installato con successo (backend: %s).', $backend),
            ];
        }

        return [
            'success' => false,
            'message' => 'Impossibile scrivere il file drop-in. Verifica i permessi della directory wp-content.',
        ];
    }

    /**
     * Disinstalla il drop-in
     */
    public function uninstall(): array
    {
        if (!file_exists($this->dropInPath)) {
            return [
                'success' => true,
                'message' => 'Il drop-in non è installato.',
            ];
        }

        // Verifica che sia il nostro drop-in
        $content = file_get_contents($this->dropInPath);
        if (strpos($content, 'FP Performance Suite') === false) {
            return [
                'success' => false,
                'message' => 'Il file drop-in esistente non è stato creato da FP Performance Suite. Rimuovilo manualmente.',
            ];
        }

        // Rimuovi il file
        if (@unlink($this->dropInPath)) {
            return [
                'success' => true,
                'message' => 'Object cache drop-in rimosso con successo.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Impossibile rimuovere il file drop-in. Verifica i permessi della directory wp-content.',
        ];
    }

    /**
     * Verifica se esiste un'implementazione di plugin
     */
    public function hasPluginImplementation(): bool
    {
        if (!file_exists($this->dropInPath)) {
            return false;
        }

        $content = file_get_contents($this->dropInPath);
        
        // Controlla se è generato da FP Performance Suite
        if (strpos($content, 'FP Performance Suite') !== false) {
            return false;
        }

        // Se esiste e non è nostro, probabilmente è di un plugin
        return true;
    }

    /**
     * Scrive il drop-in in modo sicuro
     */
    private function safeDropInWrite(string $filePath, string $content): bool
    {
        // Crea un file temporaneo
        $tempFile = $filePath . '.tmp';
        
        // Scrive il contenuto nel file temporaneo
        if (file_put_contents($tempFile, $content) === false) {
            return false;
        }
        
        // Verifica che il file sia leggibile
        if (!is_readable($tempFile)) {
            @unlink($tempFile);
            return false;
        }
        
        // Rinomina il file temporaneo nel file finale
        if (!@rename($tempFile, $filePath)) {
            @unlink($tempFile);
            return false;
        }
        
        return true;
    }

    /**
     * Verifica se il drop-in è installato
     */
    public function isInstalled(): bool
    {
        return file_exists($this->dropInPath);
    }
}















