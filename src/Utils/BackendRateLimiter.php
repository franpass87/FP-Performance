<?php

namespace FP\PerfSuite\Utils;

use FP\PerfSuite\Utils\Logger;

/**
 * Backend Rate Limiter
 * 
 * Gestisce il rate limiting per le operazioni di backend per prevenire
 * sovraccarico durante aggiornamenti di configurazione simultanei.
 * 
 * @since 1.6.0
 */
class BackendRateLimiter
{
    private const TRANSIENT_PREFIX = 'fp_ps_backend_';
    private const DEFAULT_LIMITS = [
        'backend_config' => ['max' => 5, 'window' => 300], // 5 per 5 minuti
        'optimization_settings' => ['max' => 3, 'window' => 300], // 3 per 5 minuti
        'admin_operations' => ['max' => 10, 'window' => 60], // 10 per minuto
    ];
    
    /**
     * Verifica se un'operazione di backend è permessa
     * 
     * @param string $operation Tipo di operazione
     * @param int|null $maxAttempts Limite massimo (opzionale)
     * @param int|null $windowSeconds Finestra temporale (opzionale)
     * @return bool True se permessa, false altrimenti
     */
    public static function isAllowed(string $operation, ?int $maxAttempts = null, ?int $windowSeconds = null): bool
    {
        $limits = self::DEFAULT_LIMITS[$operation] ?? ['max' => 5, 'window' => 300];
        $maxAttempts = $maxAttempts ?? $limits['max'];
        $windowSeconds = $windowSeconds ?? $limits['window'];
        
        $key = self::TRANSIENT_PREFIX . sanitize_key($operation);
        $data = get_transient($key);
        
        if (!$data || !is_array($data)) {
            $data = [
                'count' => 0,
                'first' => time(),
                'last' => time(),
            ];
        }
        
        // Reset se la finestra temporale è scaduta
        $elapsed = time() - $data['first'];
        if ($elapsed > $windowSeconds) {
            $data = [
                'count' => 0,
                'first' => time(),
                'last' => time(),
            ];
        }
        
        // Controlla se limite raggiunto
        if ($data['count'] >= $maxAttempts) {
            $remainingTime = $windowSeconds - $elapsed;
            Logger::warning(sprintf(
                'Backend rate limit exceeded for operation "%s". Try again in %d seconds.',
                $operation,
                max(0, $remainingTime)
            ));
            
            do_action('fp_ps_backend_rate_limit_exceeded', $operation, $data);
            return false;
        }
        
        // Incrementa contatore
        $data['count']++;
        $data['last'] = time();
        set_transient($key, $data, $windowSeconds);
        
        Logger::debug(sprintf(
            'Backend rate limit check for "%s": %d/%d attempts',
            $operation,
            $data['count'],
            $maxAttempts
        ));
        
        return true;
    }
    
    /**
     * Resetta il rate limit per un'operazione
     * 
     * @param string $operation Tipo di operazione
     * @return bool True se reset riuscito
     */
    public static function reset(string $operation): bool
    {
        $key = self::TRANSIENT_PREFIX . sanitize_key($operation);
        $result = delete_transient($key);
        
        if ($result) {
            Logger::debug('Backend rate limit reset', ['operation' => $operation]);
        }
        
        return $result;
    }
    
    /**
     * Ottiene lo stato attuale del rate limit
     * 
     * @param string $operation Tipo di operazione
     * @return array Stato del rate limit
     */
    public static function getStatus(string $operation): array
    {
        $key = self::TRANSIENT_PREFIX . sanitize_key($operation);
        $data = get_transient($key);
        
        if (!$data || !is_array($data)) {
            return [
                'count' => 0,
                'first' => 0,
                'last' => 0,
                'remaining' => 0,
                'reset_in' => 0,
            ];
        }
        
        $limits = self::DEFAULT_LIMITS[$operation] ?? ['max' => 5, 'window' => 300];
        $elapsed = time() - $data['first'];
        $remaining = max(0, $limits['max'] - $data['count']);
        $resetIn = max(0, $limits['window'] - $elapsed);
        
        return [
            'count' => $data['count'],
            'first' => $data['first'],
            'last' => $data['last'],
            'remaining' => $remaining,
            'reset_in' => $resetIn,
        ];
    }
    
    /**
     * Esegue un'operazione di backend con rate limiting
     * 
     * @param string $operation Tipo di operazione
     * @param callable $callback Operazione da eseguire
     * @return mixed Risultato dell'operazione o false se rate limitato
     */
    public static function executeWithLimit(string $operation, callable $callback)
    {
        if (!self::isAllowed($operation)) {
            Logger::debug('Backend operation rate limited', ['operation' => $operation]);
            return false;
        }
        
        try {
            $result = $callback();
            Logger::debug('Backend operation executed', ['operation' => $operation]);
            return $result;
        } catch (\Throwable $e) {
            Logger::error('Backend operation failed', [
                'operation' => $operation,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Pulisce tutti i rate limit scaduti
     * 
     * @return int Numero di rate limit puliti
     */
    public static function cleanupExpired(): int
    {
        global $wpdb;
        
        $cleaned = 0;
        $expired = $wpdb->get_results($wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE %s 
             AND option_value < %d",
            '_transient_' . self::TRANSIENT_PREFIX . '%',
            time()
        ));
        
        foreach ($expired as $transient) {
            $optionName = str_replace('_transient_', '', $transient->option_name);
            if (delete_transient($optionName)) {
                $cleaned++;
            }
        }
        
        if ($cleaned > 0) {
            Logger::info('Cleaned up expired backend rate limits', ['count' => $cleaned]);
        }
        
        return $cleaned;
    }
}
