<?php

/**
 * Logger Adapter
 * 
 * Adapter to bridge old static Logger calls to new injectable Logger service.
 * Provides backward compatibility during migration.
 *
 * @package FP\PerfSuite\Core\Logging
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Logging;

/**
 * Static adapter for backward compatibility
 * 
 * @deprecated Use injectable LoggerInterface instead
 */
class LoggerAdapter
{
    /** @var LoggerInterface|null Logger instance */
    private static ?LoggerInterface $instance = null;
    
    /**
     * Get logger instance
     * 
     * @return LoggerInterface
     */
    private static function getInstance(): LoggerInterface
    {
        if (self::$instance === null) {
            // Try to get from container if available
            if (class_exists('\FP\PerfSuite\Plugin')) {
                try {
                    $container = \FP\PerfSuite\Plugin::container();
                    if ($container->has(\FP\PerfSuite\Core\Logging\LoggerInterface::class)) {
                        self::$instance = $container->get(\FP\PerfSuite\Core\Logging\LoggerInterface::class);
                    }
                } catch (\Throwable $e) {
                    // Fallback to creating new instance
                }
            }
            
            // Fallback: create new instance with default options
            if (self::$instance === null) {
                $options = new \FP\PerfSuite\Core\Options\OptionsRepository('fp_ps_');
                $handlers = [new FileLogHandler('DEBUG')];
                self::$instance = new Logger($options, $handlers);
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Set logger instance (for testing)
     * 
     * @param LoggerInterface $logger
     * @return void
     */
    public static function setInstance(LoggerInterface $logger): void
    {
        self::$instance = $logger;
    }
    
    /**
     * Log error message (static method for backward compatibility)
     * 
     * @param string $message
     * @param \Throwable|array|null $secondParam
     * @param array $context
     * @return void
     */
    public static function error(string $message, $secondParam = null, array $context = []): void
    {
        $exception = null;
        $actualContext = $context;
        
        if ($secondParam instanceof \Throwable) {
            $exception = $secondParam;
        } elseif (is_array($secondParam)) {
            $actualContext = $secondParam;
        }
        
        self::getInstance()->error($message, $actualContext, $exception);
    }
    
    /**
     * Log warning message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function warning(string $message, array $context = []): void
    {
        self::getInstance()->warning($message, $context);
    }
    
    /**
     * Log info message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function info(string $message, array $context = []): void
    {
        self::getInstance()->info($message, $context);
    }
    
    /**
     * Log debug message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function debug(string $message, array $context = []): void
    {
        self::getInstance()->debug($message, $context);
    }
}









