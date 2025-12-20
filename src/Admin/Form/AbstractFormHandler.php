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

        return match($type) {
            'text' => sanitize_text_field($value),
            'textarea' => sanitize_textarea_field($value),
            'email' => sanitize_email($value),
            'url' => esc_url_raw($value),
            'int' => absint($value),
            'float' => (float) $value,
            'bool' => !empty($value),
            'array' => array_map('sanitize_text_field', (array) $value),
            'html' => wp_kses_post($value),
            'raw' => $value, // Solo per casi speciali, usare con cautela
            default => sanitize_text_field($value)
        };
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
        return ErrorHandler::getAdminErrorMessage($e);
    }

    /**
     * Restituisce messaggio di successo
     * 
     * @param string $message Messaggio
     * @return string Messaggio formattato
     */
    protected function successMessage(string $message): string
    {
        return sprintf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            esc_html($message)
        );
    }

    /**
     * Restituisce messaggio di errore
     * 
     * @param string $message Messaggio
     * @return string Messaggio formattato
     */
    protected function errorMessage(string $message): string
    {
        return sprintf(
            '<div class="notice notice-error is-dismissible"><p>%s</p></div>',
            esc_html($message)
        );
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
     * @return string Messaggio di risultato (vuoto se nessun form processato)
     */
    abstract public function handle(): string;
}

