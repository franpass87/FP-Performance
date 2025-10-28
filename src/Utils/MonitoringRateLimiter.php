<?php

namespace FP\PerfSuite\Utils;

use FP\PerfSuite\Utils\Logger;

/**
 * Monitoring Rate Limiter
 * 
 * Gestisce il rate limiting per le operazioni di monitoring per prevenire
 * sovraccarico del database durante la scrittura di metriche performance.
 * 
 * @since 1.6.0
 */
class MonitoringRateLimiter
{
    private const TRANSIENT_PREFIX = 'fp_ps_monitoring_';
    private const DEFAULT_LIMITS = [
        'performance_metrics' => ['max' => 10, 'window' => 60], // 10 per minuto
        'web_vitals' => ['max' => 20, 'window' => 60], // 20 per minuto
        'core_web_vitals' => ['max' => 15, 'window' => 60], // 15 per minuto
        'monitoring_data' => ['max' => 5, 'window' => 60], // 5 per minuto
    ];
    
    /**
     * Verifica se un'operazione di monitoring è permessa
     * 
     * @param string $operation Tipo di operazione
     * @param int|null $maxAttempts Limite massimo (opzionale)
     * @param int|null $windowSeconds Finestra temporale (opzionale)
     * @return bool True se permessa, false altrimenti
     */
    public static function isAllowed(string $operation, ?int $maxAttempts = null, ?int $windowSeconds = null): bool
    {
        $limits = self::DEFAULT_LIMITS[$operation] ?? ['max' => 5, 'window' => 60];
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
                'Monitoring rate limit exceeded for operation "%s". Try again in %d seconds.',
                $operation,
                max(0, $remainingTime)
            ));
            
            do_action('fp_ps_monitoring_rate_limit_exceeded', $operation, $data);
            return false;
        }
        
        // Incrementa contatore
        $data['count']++;
        $data['last'] = time();
        set_transient($key, $data, $windowSeconds);
        
        Logger::debug(sprintf(
            'Monitoring rate limit check for "%s": %d/%d attempts',
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
            Logger::debug('Monitoring rate limit reset', ['operation' => $operation]);
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
        
        $limits = self::DEFAULT_LIMITS[$operation] ?? ['max' => 5, 'window' => 60];
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
     * Esegue un'operazione di monitoring con rate limiting
     * 
     * @param string $operation Tipo di operazione
     * @param callable $callback Operazione da eseguire
     * @return mixed Risultato dell'operazione o false se rate limitato
     */
    public static function executeWithLimit(string $operation, callable $callback)
    {
        if (!self::isAllowed($operation)) {
            Logger::debug('Monitoring operation rate limited', ['operation' => $operation]);
            return false;
        }
        
        try {
            $result = $callback();
            Logger::debug('Monitoring operation executed', ['operation' => $operation]);
            return $result;
        } catch (\Throwable $e) {
            Logger::error('Monitoring operation failed', [
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
            Logger::info('Cleaned up expired monitoring rate limits', ['count' => $cleaned]);
        }
        
        return $cleaned;
    }
}
