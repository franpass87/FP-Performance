<?php

/**
 * Abstract Form Handler
 * 
 * Base class per tutti i form handlers del plugin.
 * Fornisce funzionalità comuni: nonce verification, sanitization, error handling.
 * 
 * @package FP\PerfSuite\Admin\Form
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Admin\Form;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\ErrorHandler;

abstract class AbstractFormHandler
{
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Verifica nonce e restituisce true se valido
     * 
     * @param string $nonceKey Chiave del nonce nel POST/GET
     * @param string $action Azione del nonce
     * @return bool True se nonce valido, false altrimenti
     */
    protected function verifyNonce(string $nonceKey, string $action): bool
    {
        if (!isset($_POST[$nonceKey]) && !isset($_GET[$nonceKey])) {
            return false;
        }

        $nonce = wp_unslash($_POST[$nonceKey] ?? $_GET[$nonceKey] ?? '');
        return wp_verify_nonce($nonce, $action) !== false;
    }

    /**
     * Richiede nonce valido, altrimenti termina con wp_die
     * 
     * @param string $nonceKey Chiave del nonce
     * @param string $action Azione del nonce
     * @return void
     */
    protected function requireNonce(string $nonceKey, string $action): void
    {
        if (!$this->verifyNonce($nonceKey, $action)) {
            wp_die(
                __('Security check failed', 'fp-performance-suite'),
                __('Unauthorized', 'fp-performance-suite'),
                ['response' => 403]
            );
        }
    }

    /**
     * Sanitizza input POST/GET
     * 
     * @param string $key Chiave dell'input
     * @param string $type Tipo di sanitizzazione ('text', 'textarea', 'email', 'url', 'int', 'float', 'bool', 'array', 'html')
     * @param array|null $source Array sorgente (default: $_POST)
     * @return mixed Valore sanitizzato o null se non presente
     */
    protected function sanitizeInput(string $key, string $type = 'text', ?array $source = null): mixed
    {
        $source = $source ?? $_POST;

        if (!isset($source[$key])) {
            return null;
        }

        $value = wp_unslash($source[$key]);

        // FIX: Usa switch invece di match() per compatibilità PHP 7.4+
        switch ($type) {
            case 'text':
                return sanitize_text_field($value);
            case 'textarea':
                return sanitize_textarea_field($value);
            case 'email':
                return sanitize_email($value);
            case 'url':
                return esc_url_raw($value);
            case 'int':
                return absint($value);
            case 'float':
                return (float) $value;
            case 'bool':
                return !empty($value);
            case 'array':
                return array_map('sanitize_text_field', (array) $value);
            case 'html':
                return wp_kses_post($value);
            case 'raw':
                return $value; // Solo per casi speciali, usare con cautela
            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Sanitizza array di valori secondo schema
     * 
     * @param array $data Dati da sanitizzare
     * @param array $schema Schema: ['key' => 'type', ...]
     * @return array Array sanitizzato
     */
    protected function sanitizeArray(array $data, array $schema): array
    {
        $sanitized = [];
        foreach ($schema as $key => $type) {
            if (isset($data[$key])) {
                $sanitized[$key] = $this->sanitizeInput($key, $type, $data);
            }
        }
        return $sanitized;
    }

    /**
     * Gestisce errori con logging
     * 
     * @param \Throwable $e Eccezione
     * @param string $context Contesto dell'errore
     * @return string Messaggio di errore per l'utente
     */
    protected function handleError(\Throwable $e, string $context): string
    {
        ErrorHandler::handle($e, "Form handler error in {$context}");
        
        // Usa handleWithMessage se disponibile, altrimenti messaggio generico
        if (method_exists(ErrorHandler::class, 'handleWithMessage')) {
            return ErrorHandler::handleWithMessage($e, $context);
        }
        
        // Fallback a messaggio generico
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return sprintf(
                __('Errore in %s: %s', 'fp-performance-suite'),
                $context,
                esc_html($e->getMessage())
            );
        }
        
        return sprintf(
            __('Si è verificato un errore in %s. Controlla i log per dettagli.', 'fp-performance-suite'),
            $context
        );
    }

    /**
     * Restituisce messaggio di successo
     * 
     * @param string $message Messaggio
     * @return string Messaggio semplice (senza HTML, viene wrappato dal template)
     */
    protected function successMessage(string $message): string
    {
        // Restituisce solo il testo, il template si occupa del wrapping HTML
        return $message;
    }

    /**
     * Restituisce messaggio di errore
     * 
     * @param string $message Messaggio
     * @return string Messaggio semplice (senza HTML, viene wrappato dal template)
     */
    protected function errorMessage(string $message): string
    {
        // Restituisce solo il testo, il template si occupa del wrapping HTML
        return $message;
    }

    /**
     * Verifica se la richiesta è POST
     * 
     * @return bool
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Template method - da implementare nelle sottoclassi
     * 
     * @note Questo metodo è stato rimosso dalla classe base per permettere alle sottoclassi
     * di implementare handle() con qualsiasi firma necessaria (parametri, riferimenti, ecc.)
     * Ogni sottoclasse deve implementare il proprio metodo handle() in base alle sue esigenze.
     */
}

