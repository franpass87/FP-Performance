<?php

/**
 * Logger Service
 * 
 * Injectable logger service implementing PSR-3 compatible interface.
 * Replaces static Logger class with dependency-injectable service.
 *
 * @package FP\PerfSuite\Core\Logging
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Logging;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

class Logger implements LoggerInterface
{
    /** @var LogHandler[] Array of log handlers */
    private array $handlers = [];
    
    /** @var OptionsRepositoryInterface Options repository */
    private OptionsRepositoryInterface $options;
    
    /** @var string Minimum log level */
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
     * @param OptionsRepositoryInterface $options Options repository
     * @param LogHandler[] $handlers Array of log handlers
     */
    public function __construct(OptionsRepositoryInterface $options, array $handlers = [])
    {
        $this->options = $options;
        
        // Default handler if none provided
        if (empty($handlers)) {
            $handlers = [new FileLogHandler('DEBUG')];
        }
        
        $this->handlers = $handlers;
        
        // Get minimum log level from options
        $this->minLevel = strtoupper($this->options->get('log_level', 'ERROR'));
    }
    
    /**
     * Add a log handler
     * 
     * @param LogHandler $handler Log handler
     * @return void
     */
    public function addHandler(LogHandler $handler): void
    {
        $this->handlers[] = $handler;
    }
    
    /**
     * System is unusable
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log('EMERGENCY', $message, $context);
    }
    
    /**
     * Action must be taken immediately
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('ALERT', $message, $context);
    }
    
    /**
     * Critical conditions
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }
    
    /**
     * Runtime errors that do not require immediate action
     * 
     * @param string $message
     * @param array $context
     * @param \Throwable|null $exception Optional exception
     * @return void
     */
    public function error(string $message, array $context = [], ?\Throwable $exception = null): void
    {
        // Add exception to context if provided
        if ($exception !== null) {
            $context['exception'] = LogFormatter::formatException($exception);
            $context['exception_class'] = get_class($exception);
        }
        
        $this->log('ERROR', $message, $context);
        
        // Trigger WordPress action for backward compatibility
        do_action('fp_ps_log_error', $message, $exception, $context);
    }
    
    /**
     * Exceptional occurrences that are not errors
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
        do_action('fp_ps_log_warning', $message, $context);
    }
    
    /**
     * Normal but significant events
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('NOTICE', $message, $context);
    }
    
    /**
     * Interesting events
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
        do_action('fp_ps_log_info', $message, $context);
    }
    
    /**
     * Detailed debug information
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        // Check if debug logging is enabled
        if (!$this->shouldLog('DEBUG')) {
            return;
        }
        
        $this->log('DEBUG', $message, $context);
        do_action('fp_ps_log_debug', $message, $context);
    }
    
    /**
     * Log with an arbitrary level
     * 
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, string $message, array $context = []): void
    {
        $levelUpper = strtoupper((string) $level);
        
        // Check if we should log at this level
        if (!$this->shouldLog($levelUpper)) {
            return;
        }
        
        // Pass to all handlers
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($levelUpper)) {
                $handler->handle($levelUpper, $message, $context);
            }
        }
    }
    
    /**
     * Check if we should log at this level
     * 
     * @param string $level Log level
     * @return bool
     */
    private function shouldLog(string $level): bool
    {
        // Always log errors and warnings
        if (in_array($level, ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY', 'WARNING'])) {
            return true;
        }
        
        // Check minimum level
        $minPriority = self::LEVEL_PRIORITIES[$this->minLevel] ?? 0;
        $levelPriority = self::LEVEL_PRIORITIES[$level] ?? 0;
        
        if ($levelPriority < $minPriority) {
            return false;
        }
        
        // Special handling for DEBUG
        if ($level === 'DEBUG') {
            // Check if debug is disabled via constant
            if (defined('FP_PS_DISABLE_DEBUG_LOGS') && FP_PS_DISABLE_DEBUG_LOGS) {
                return false;
            }
            
            // Only log if WP_DEBUG is enabled or explicitly configured
            if (!defined('WP_DEBUG') || !WP_DEBUG) {
                return $this->minLevel === 'DEBUG';
            }
        }
        
        return true;
    }
    
    /**
     * Set minimum log level
     * 
     * @param string $level Log level
     * @return void
     */
    public function setLevel(string $level): void
    {
        $this->minLevel = strtoupper($level);
        $this->options->set('log_level', $this->minLevel);
    }
}