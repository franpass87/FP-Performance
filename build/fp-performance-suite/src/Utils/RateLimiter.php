<?php

namespace FP\PerfSuite\Utils;

/**
 * Rate limiting utility to prevent abuse of resource-intensive operations.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class RateLimiter
{
    private const TRANSIENT_PREFIX = 'fp_ps_ratelimit_';

    /**
     * Check if an action is allowed within rate limits
     *
     * @param string $action Unique action identifier
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $windowSeconds Time window in seconds
     * @return bool True if action is allowed, false otherwise
     */
    public function isAllowed(string $action, int $maxAttempts = 5, int $windowSeconds = 3600): bool
    {
        $key = self::TRANSIENT_PREFIX . sanitize_key($action);
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
                'Rate limit exceeded for action "%s". Try again in %d seconds.',
                $action,
                max(0, $remainingTime)
            ));

            do_action('fp_ps_rate_limit_exceeded', $action, $data);
            return false;
        }

        // Incrementa contatore
        $data['count']++;
        $data['last'] = time();
        set_transient($key, $data, $windowSeconds);

        Logger::debug(sprintf(
            'Rate limit check for "%s": %d/%d attempts',
            $action,
            $data['count'],
            $maxAttempts
        ));

        return true;
    }

    /**
     * Reset rate limit for a specific action
     */
    public function reset(string $action): void
    {
        $key = self::TRANSIENT_PREFIX . sanitize_key($action);
        delete_transient($key);
        Logger::info("Rate limit reset for action: {$action}");
    }

    /**
     * Get current rate limit status
     *
     * @return array{count: int, maxAttempts: int, remaining: int, resetAt: int}|null
     */
    public function getStatus(string $action, int $maxAttempts = 5, int $windowSeconds = 3600): ?array
    {
        $key = self::TRANSIENT_PREFIX . sanitize_key($action);
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
     * Clear all rate limit data
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

        Logger::info("Cleared all rate limit data ({$count} entries)");
        return (int) $count;
    }
}
