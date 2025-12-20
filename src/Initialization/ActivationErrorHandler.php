<?php

namespace FP\PerfSuite\Initialization;

/**
 * Gestisce la formattazione e il salvataggio degli errori di attivazione
 * 
 * @package FP\PerfSuite\Initialization
 * @author Francesco Passeri
 */
class ActivationErrorHandler
{
    /**
     * Formatta i dettagli dell'errore di attivazione
     * 
     * @param \Throwable $e L'eccezione catturata
     * @return array Dettagli dell'errore formattati
     */
    public function formatError(\Throwable $e): array
    {
        $errorType = 'unknown';
        $solution = 'Contatta il supporto con i dettagli dell\'errore.';

        // Identifica il tipo di errore e suggerisci una soluzione
        $message = $e->getMessage();
        
        if (strpos($message, 'PHP') !== false && strpos($message, 'version') !== false) {
            $errorType = 'php_version';
            $solution = 'Aggiorna PHP alla versione 7.4 o superiore tramite il pannello di hosting.';
        } elseif (strpos($message, 'extension') !== false || strpos($message, 'Estensione') !== false) {
            $errorType = 'php_extension';
            $solution = 'Abilita le estensioni PHP richieste (json, mbstring, fileinfo) tramite il pannello di hosting.';
        } elseif (strpos($message, 'permission') !== false || strpos($message, 'scrivibile') !== false) {
            $errorType = 'permissions';
            $solution = 'Verifica i permessi delle directory. La directory wp-content/uploads deve essere scrivibile (chmod 755 o 775).';
        } elseif (strpos($message, 'Class') !== false && strpos($message, 'not found') !== false) {
            $errorType = 'missing_class';
            $solution = 'Reinstalla il plugin assicurandoti che tutti i file siano stati caricati correttamente.';
        } elseif (strpos($message, 'memory') !== false) {
            $errorType = 'memory_limit';
            $solution = 'Aumenta il limite di memoria PHP (memory_limit) a almeno 128MB nel file php.ini.';
        }

        return [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'time' => time(),
            'type' => $errorType,
            'solution' => $solution,
            'trace' => $e->getTraceAsString(),
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
        ];
    }

    /**
     * Salva l'errore di attivazione
     * 
     * @param \Throwable $e L'eccezione catturata
     */
    public function saveError(\Throwable $e): void
    {
        $errorData = $this->formatError($e);
        update_option('fp_perfsuite_activation_error', $errorData, false);
    }
}
















