<?php

/**
 * Log Handler Interface
 * 
 * Defines contract for log handlers (file, database, remote, etc.)
 *
 * @package FP\PerfSuite\Core\Logging
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Logging;

interface LogHandler
{
    /**
     * Handle a log record
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Log context
     * @return void
     */
    public function handle(string $level, string $message, array $context = []): void;
    
    /**
     * Check if handler can handle the given level
     * 
     * @param string $level Log level
     * @return bool
     */
    public function canHandle(string $level): bool;
}









