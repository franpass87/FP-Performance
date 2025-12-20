<?php

/**
 * Logger Interface
 * 
 * PSR-3 compatible logger interface for dependency injection.
 *
 * @package FP\PerfSuite\Core\Logging
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Logging;

interface LoggerInterface
{
    /**
     * System is unusable
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency(string $message, array $context = []): void;
    
    /**
     * Action must be taken immediately
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert(string $message, array $context = []): void;
    
    /**
     * Critical conditions
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical(string $message, array $context = []): void;
    
    /**
     * Runtime errors that do not require immediate action
     * 
     * @param string $message
     * @param array $context
     * @param \Throwable|null $exception Optional exception
     * @return void
     */
    public function error(string $message, array $context = [], ?\Throwable $exception = null): void;
    
    /**
     * Exceptional occurrences that are not errors
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning(string $message, array $context = []): void;
    
    /**
     * Normal but significant events
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice(string $message, array $context = []): void;
    
    /**
     * Interesting events
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info(string $message, array $context = []): void;
    
    /**
     * Detailed debug information
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug(string $message, array $context = []): void;
    
    /**
     * Log with an arbitrary level
     * 
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, string $message, array $context = []): void;
}