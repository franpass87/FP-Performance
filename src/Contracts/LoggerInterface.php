<?php

namespace FP\PerfSuite\Contracts;

/**
 * Interface for logging implementations
 * 
 * @deprecated Use FP\PerfSuite\Core\Logging\LoggerInterface instead
 * This interface is kept for backward compatibility
 */
interface LoggerInterface
{
    /**
     * Log error message
     */
    public static function error(string $message, ?\Throwable $e = null): void;

    /**
     * Log warning message
     */
    public static function warning(string $message): void;

    /**
     * Log info message
     */
    public static function info(string $message): void;

    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): void;
}
