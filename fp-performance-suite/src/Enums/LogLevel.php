<?php

namespace FP\PerfSuite\Enums;

/**
 * Log Level Enumeration
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
enum LogLevel: string
{
    case ERROR = 'ERROR';
    case WARNING = 'WARNING';
    case INFO = 'INFO';
    case DEBUG = 'DEBUG';

    /**
     * Get numeric priority (lower = more severe)
     */
    public function priority(): int
    {
        return match($this) {
            self::ERROR => 1,
            self::WARNING => 2,
            self::INFO => 3,
            self::DEBUG => 4,
        };
    }

    /**
     * Get color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::ERROR => '#ef4444',
            self::WARNING => '#f59e0b',
            self::INFO => '#3b82f6',
            self::DEBUG => '#6b7280',
        };
    }

    /**
     * Get emoji
     */
    public function emoji(): string
    {
        return match($this) {
            self::ERROR => '🔴',
            self::WARNING => '⚠️',
            self::INFO => 'ℹ️',
            self::DEBUG => '🔍',
        };
    }

    /**
     * Check if this level should log
     */
    public function shouldLog(LogLevel $configuredLevel): bool
    {
        return $this->priority() <= $configuredLevel->priority();
    }

    /**
     * Get all levels
     */
    public static function all(): array
    {
        return [
            self::ERROR,
            self::WARNING,
            self::INFO,
            self::DEBUG,
        ];
    }
}
