<?php

namespace FP\PerfSuite\Utils;

/**
 * Benchmarking utility for performance testing
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Benchmark
{
    private static array $timers = [];
    private static array $counters = [];

    /**
     * Start a timer
     */
    public static function start(string $name): void
    {
        self::$timers[$name] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true),
        ];
    }

    /**
     * Stop a timer and return duration
     */
    public static function stop(string $name): ?float
    {
        if (!isset(self::$timers[$name]['start'])) {
            return null;
        }

        $end = microtime(true);
        $memoryEnd = memory_get_usage(true);

        self::$timers[$name]['end'] = $end;
        self::$timers[$name]['duration'] = $end - self::$timers[$name]['start'];
        self::$timers[$name]['memory_end'] = $memoryEnd;
        self::$timers[$name]['memory_delta'] = $memoryEnd - self::$timers[$name]['memory_start'];

        return self::$timers[$name]['duration'];
    }

    /**
     * Measure a callable
     */
    public static function measure(string $name, callable $callback)
    {
        self::start($name);
        $result = $callback();
        self::stop($name);

        return $result;
    }

    /**
     * Get timer result
     */
    public static function get(string $name): ?array
    {
        return self::$timers[$name] ?? null;
    }

    /**
     * Get all timers
     */
    public static function getAll(): array
    {
        return self::$timers;
    }

    /**
     * Increment a counter
     */
    public static function increment(string $name, int $amount = 1): int
    {
        if (!isset(self::$counters[$name])) {
            self::$counters[$name] = 0;
        }

        self::$counters[$name] += $amount;
        return self::$counters[$name];
    }

    /**
     * Get counter value
     */
    public static function getCounter(string $name): int
    {
        return self::$counters[$name] ?? 0;
    }

    /**
     * Reset all benchmarks
     */
    public static function reset(): void
    {
        self::$timers = [];
        self::$counters = [];
    }

    /**
     * Format duration for display
     */
    public static function formatDuration(float $seconds): string
    {
        if ($seconds < 0.001) {
            return number_format($seconds * 1000000, 0) . ' μs';
        }

        if ($seconds < 1) {
            return number_format($seconds * 1000, 2) . ' ms';
        }

        return number_format($seconds, 3) . ' s';
    }

    /**
     * Format memory for display
     */
    public static function formatMemory(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    /**
     * Generate report
     */
    public static function report(): string
    {
        $output = "=== Benchmark Report ===\n\n";

        if (!empty(self::$timers)) {
            $output .= "Timers:\n";
            foreach (self::$timers as $name => $data) {
                if (isset($data['duration'])) {
                    $output .= sprintf(
                        "  %s: %s (memory: %s)\n",
                        $name,
                        self::formatDuration($data['duration']),
                        self::formatMemory($data['memory_delta'] ?? 0)
                    );
                }
            }
            $output .= "\n";
        }

        if (!empty(self::$counters)) {
            $output .= "Counters:\n";
            foreach (self::$counters as $name => $value) {
                $output .= sprintf("  %s: %d\n", $name, $value);
            }
        }

        return $output;
    }

    /**
     * Log report to error log
     */
    public static function logReport(): void
    {
        Logger::debug(self::report());
    }
}
