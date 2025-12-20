<?php

namespace FP\PerfSuite\Tests\Unit\Core;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Core\Logging\Logger;
use FP\PerfSuite\Core\Logging\FileLogHandler;
use FP\PerfSuite\Core\Options\OptionsRepository;
use Brain\Monkey\Functions;

/**
 * Test Logger
 *
 * @package FP\PerfSuite\Tests\Unit\Core
 */
class LoggerTest extends TestCase
{
    private OptionsRepository $options;
    private Logger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->options = $this->createMock(OptionsRepository::class);
        $this->options->method('get')
            ->with('log_level', 'ERROR')
            ->willReturn('DEBUG');
        
        $this->logger = new Logger($this->options, []);
    }

    public function testEmergency(): void
    {
        $handler = $this->createMock(FileLogHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with('EMERGENCY', 'Test message', []);

        $logger = new Logger($this->options, [$handler]);
        $logger->emergency('Test message');
    }

    public function testAlert(): void
    {
        $handler = $this->createMock(FileLogHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with('ALERT', 'Test message', []);

        $logger = new Logger($this->options, [$handler]);
        $logger->alert('Test message');
    }

    public function testCritical(): void
    {
        $handler = $this->createMock(FileLogHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with('CRITICAL', 'Test message', []);

        $logger = new Logger($this->options, [$handler]);
        $logger->critical('Test message');
    }

    public function testError(): void
    {
        $handler = $this->createMock(FileLogHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with('ERROR', 'Test message', []);

        $logger = new Logger($this->options, [$handler]);
        $logger->error('Test message');
    }

    public function testWarning(): void
    {
        $handler = $this->createMock(FileLogHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with('WARNING', 'Test message', []);

        $logger = new Logger($this->options, [$handler]);
        $logger->warning('Test message');
    }

    public function testNotice(): void
    {
        $handler = $this->createMock(FileLogHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with('NOTICE', 'Test message', []);

        $logger = new Logger($this->options, [$handler]);
        $logger->notice('Test message');
    }

    public function testInfo(): void
    {
        $handler = $this->createMock(FileLogHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with('INFO', 'Test message', []);

        $logger = new Logger($this->options, [$handler]);
        $logger->info('Test message');
    }

    public function testDebug(): void
    {
        $handler = $this->createMock(FileLogHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with('DEBUG', 'Test message', []);

        $logger = new Logger($this->options, [$handler]);
        $logger->debug('Test message');
    }

    public function testLogWithContext(): void
    {
        $handler = $this->createMock(FileLogHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with('INFO', 'Test message', ['key' => 'value']);

        $logger = new Logger($this->options, [$handler]);
        $logger->info('Test message', ['key' => 'value']);
    }

    public function testAddHandler(): void
    {
        $handler = $this->createMock(FileLogHandler::class);
        $this->logger->addHandler($handler);
        
        $handler->expects($this->once())
            ->method('handle')
            ->with('INFO', 'Test message', []);

        $this->logger->info('Test message');
    }

    public function testLogLevelFiltering(): void
    {
        $this->options = $this->createMock(OptionsRepository::class);
        $this->options->method('get')
            ->with('log_level', 'ERROR')
            ->willReturn('ERROR');

        $handler = $this->createMock(FileLogHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with('ERROR', 'Error message', []);
        $handler->expects($this->never())
            ->method('handle')
            ->with('DEBUG', $this->anything(), $this->anything());

        $logger = new Logger($this->options, [$handler]);
        $logger->error('Error message');
        $logger->debug('Debug message'); // Should not be logged
    }
}










