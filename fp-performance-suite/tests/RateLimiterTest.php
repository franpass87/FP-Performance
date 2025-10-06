<?php

use FP\PerfSuite\Utils\RateLimiter;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

final class RateLimiterTest extends TestCase
{
    private RateLimiter $limiter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->limiter = new RateLimiter();
        
        // Clean up any existing rate limit data
        RateLimiter::clearAll();
    }

    protected function tearDown(): void
    {
        RateLimiter::clearAll();
        parent::tearDown();
    }

    public function testAllowsRequestsWithinLimit(): void
    {
        $action = 'test_action_' . time();
        
        // First request should be allowed
        $this->assertTrue($this->limiter->isAllowed($action, 3, 60));
        
        // Second request should be allowed
        $this->assertTrue($this->limiter->isAllowed($action, 3, 60));
        
        // Third request should be allowed
        $this->assertTrue($this->limiter->isAllowed($action, 3, 60));
    }

    public function testBlocksRequestsOverLimit(): void
    {
        $action = 'test_action_' . time();
        
        // Use up the limit
        $this->limiter->isAllowed($action, 2, 60);
        $this->limiter->isAllowed($action, 2, 60);
        
        // This should be blocked
        $this->assertFalse($this->limiter->isAllowed($action, 2, 60));
    }

    public function testResetClearsLimit(): void
    {
        $action = 'test_action_' . time();
        
        // Use up the limit
        $this->limiter->isAllowed($action, 1, 60);
        
        // Should be blocked
        $this->assertFalse($this->limiter->isAllowed($action, 1, 60));
        
        // Reset
        $this->limiter->reset($action);
        
        // Should be allowed again
        $this->assertTrue($this->limiter->isAllowed($action, 1, 60));
    }

    public function testGetStatus(): void
    {
        $action = 'test_action_' . time();
        
        // Make some requests
        $this->limiter->isAllowed($action, 5, 60);
        $this->limiter->isAllowed($action, 5, 60);
        
        $status = $this->limiter->getStatus($action, 5, 60);
        
        $this->assertIsArray($status);
        $this->assertArrayHasKey('count', $status);
        $this->assertArrayHasKey('remaining', $status);
        $this->assertSame(2, $status['count']);
        $this->assertSame(3, $status['remaining']);
    }

    public function testGetStatusReturnsNullWhenNoData(): void
    {
        $status = $this->limiter->getStatus('nonexistent_action', 5, 60);
        $this->assertNull($status);
    }

    public function testClearAll(): void
    {
        $action1 = 'test_action_1_' . time();
        $action2 = 'test_action_2_' . time();
        
        // Create some rate limit entries
        $this->limiter->isAllowed($action1, 5, 60);
        $this->limiter->isAllowed($action2, 5, 60);
        
        // Clear all
        $cleared = RateLimiter::clearAll();
        
        $this->assertGreaterThanOrEqual(0, $cleared);
        
        // Status should be null now
        $this->assertNull($this->limiter->getStatus($action1, 5, 60));
        $this->assertNull($this->limiter->getStatus($action2, 5, 60));
    }
}
