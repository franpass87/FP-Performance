<?php

/**
 * File Log Handler
 * 
 * Writes logs to WordPress debug.log file
 *
 * @package FP\PerfSuite\Core\Logging
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Logging;

class FileLogHandler implements LogHandler
{
    /** @var string Minimum log level to handle */
    private string $minLevel;
    
    /** @var array<string, int> Log level priorities */
    private const LEVEL_PRIORITIES = [
        'DEBUG' => 0,
        'INFO' => 1,
        'NOTICE' => 2,
        'WARNING' => 3,
        'ERROR' => 4,
        'CRITICAL' => 5,
        'ALERT' => 6,
        'EMERGENCY' => 7,
    ];
    
    /**
     * Constructor
     * 
     * @param string $minLevel Minimum log level (default: 'DEBUG')
     */
    public function __construct(string $minLevel = 'DEBUG')
    {
        $this->minLevel = strtoupper($minLevel);
    }
    
    /**
     * Handle a log record
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Log context
     * @return void
     */
    public function handle(string $level, string $message, array $context = []): void
    {
        if (!$this->canHandle($level)) {
            return;
        }
        
        // Only log if WordPress debug logging is enabled
        if (!defined('WP_DEBUG') || !WP_DEBUG || !defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
            return;
        }
        
        $formatted = LogFormatter::format($level, $message, $context);
        error_log($formatted);
    }
    
    /**
     * Check if handler can handle the given level
     * 
     * @param string $level Log level
     * @return bool
     */
    public function canHandle(string $level): bool
    {
        $levelUpper = strtoupper($level);
        $minPriority = self::LEVEL_PRIORITIES[$this->minLevel] ?? 0;
        $levelPriority = self::LEVEL_PRIORITIES[$levelUpper] ?? 0;
        
        return $levelPriority >= $minPriority;
    }
}









