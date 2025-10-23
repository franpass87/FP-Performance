<?php

namespace FP\PerfSuite\Utils;

/**
 * Mobile Rate Limiter Utility
 * 
 * Gestisce rate limiting specifico per operazioni mobile
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class MobileRateLimiter
{
    private const TRANSIENT_PREFIX = 'fp_ps_mobile_ratelimit_';
    private const DEFAULT_LIMITS = [
        'responsive_images' => ['max' => 10, 'window' => 300], // 10 per 5 minuti
        'touch_optimizations' => ['max' => 5, 'window' => 180], // 5 per 3 minuti
        'mobile_cache' => ['max' => 3, 'window' => 600], // 3 per 10 minuti
        'mobile_css' => ['max' => 8, 'window' => 240], // 8 per 4 minuti
    ];

    /**
     * Controlla se operazione mobile è permessa
     * 
     * @param string $operation Tipo operazione mobile
     * @param int|null $maxAttempts Massimo tentativi (usa default se null)
     * @param int|null $windowSeconds Finestra temporale (usa default se null)
     * @return bool True se permesso
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
                'Mobile rate limit exceeded for operation "%s". Try again in %d seconds.',
                $operation,
                max(0, $remainingTime)
            ));

            do_action('fp_ps_mobile_rate_limit_exceeded', $operation, $data);
            return false;
        }

        // Incrementa contatore
        $data['count']++;
        $data['last'] = time();
        set_transient($key, $data, $windowSeconds);

        Logger::debug(sprintf(
            'Mobile rate limit check for "%s": %d/%d attempts',
            $operation,
            $data['count'],
            $maxAttempts
        ));

        return true;
    }

    /**
     * Reset rate limit per operazione mobile
     * 
     * @param string $operation Tipo operazione
     */
    public static function reset(string $operation): void
    {
        $key = self::TRANSIENT_PREFIX . sanitize_key($operation);
        delete_transient($key);
        Logger::info("Mobile rate limit reset for operation: {$operation}");
    }

    /**
     * Ottiene status rate limit mobile
     * 
     * @param string $operation Tipo operazione
     * @return array|null Status o null se non presente
     */
    public static function getStatus(string $operation): ?array
    {
        $limits = self::DEFAULT_LIMITS[$operation] ?? ['max' => 5, 'window' => 300];
        $maxAttempts = $limits['max'];
        $windowSeconds = $limits['window'];
        
        $key = self::TRANSIENT_PREFIX . sanitize_key($operation);
        $data = get_transient($key);

        if (!$data || !is_array($data)) {
            return null;
        }

        $elapsed = time() - $data['first'];
        $remaining = max(0, $maxAttempts - $data['count']);
        $resetAt = $data['first'] + $windowSeconds;

        return [
            'count' => (int) $data['count'],
            'maxAttempts' => $maxAttempts,
            'remaining' => $remaining,
            'resetAt' => $resetAt,
            'resetIn' => max(0, $resetAt - time()),
        ];
    }

    /**
     * Pulisce tutti i rate limit mobile
     * 
     * @return int Numero di rate limit puliti
     */
    public static function clearAll(): int
    {
        global $wpdb;

        $pattern = self::TRANSIENT_PREFIX . '%';
        $count = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . $pattern
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_timeout_' . $pattern
            )
        );

        Logger::info("Cleared all mobile rate limit data ({$count} entries)");
        return (int) $count;
    }

    /**
     * Ottiene tutti i rate limit attivi
     * 
     * @return array Lista rate limit attivi
     */
    public static function getAllActive(): array
    {
        $active = [];
        
        foreach (array_keys(self::DEFAULT_LIMITS) as $operation) {
            $status = self::getStatus($operation);
            if ($status && $status['count'] > 0) {
                $active[$operation] = $status;
            }
        }
        
        return $active;
    }
}
