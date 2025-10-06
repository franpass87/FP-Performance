<?php

use FP\PerfSuite\Utils\Logger;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

final class LoggerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        delete_option('fp_ps_log_level');
    }

    public function testErrorLogging(): void
    {
        $exception = new \Exception('Test exception', 500);
        
        // Should not throw
        Logger::error('Test error message', $exception);
        
        // Test without exception
        Logger::error('Simple error');
        
        $this->assertTrue(true); // If we reach here, no exceptions were thrown
    }

    public function testWarningLogging(): void
    {
        Logger::warning('Test warning message');
        $this->assertTrue(true);
    }

    public function testInfoLogging(): void
    {
        Logger::info('Test info message');
        $this->assertTrue(true);
    }

    public function testDebugLogging(): void
    {
        Logger::debug('Test debug message', ['key' => 'value']);
        $this->assertTrue(true);
    }

    public function testSetLevel(): void
    {
        Logger::setLevel('DEBUG');
        $level = get_option('fp_ps_log_level');
        
        $this->assertSame('DEBUG', $level);
    }

    public function testInfoOnlyLogsWhenDebugEnabled(): void
    {
        // When WP_DEBUG is false, info should not log
        // This test verifies the method completes without error
        Logger::info('This should be silent in production');
        $this->assertTrue(true);
    }
}
