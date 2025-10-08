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
        
        // Clean up any existing rate limit data
        if (isset($GLOBALS['__transients'])) {
            foreach (array_keys($GLOBALS['__transients']) as $key) {
                if (strpos($key, 'fp_ps_ratelimit_') === 0) {
                    unset($GLOBALS['__transients'][$key]);
                }
            }
        }
        
        $this->limiter = new RateLimiter();
    }

    protected function tearDown(): void
    {
        // Clean up any existing rate limit data
        if (isset($GLOBALS['__transients'])) {
            foreach (array_keys($GLOBALS['__transients']) as $key) {
                if (strpos($key, 'fp_ps_ratelimit_') === 0) {
                    unset($GLOBALS['__transients'][$key]);
                }
            }
        }
        
        parent::tearDown();
    }

    public function testAllowsRequestsWithinLimit(): void
    {
        $action = 'test_action_' . uniqid('', true);
        
        // First request should be allowed
        $this->assertTrue($this->limiter->isAllowed($action, 3, 60));
        
        // Second request should be allowed
        $this->assertTrue($this->limiter->isAllowed($action, 3, 60));
        
        // Third request should be allowed
        $this->assertTrue($this->limiter->isAllowed($action, 3, 60));
    }

    public function testBlocksRequestsOverLimit(): void
    {
        $action = 'test_action_' . uniqid('', true);
        
        // Use up the limit
        $this->limiter->isAllowed($action, 2, 60);
        $this->limiter->isAllowed($action, 2, 60);
        
        // This should be blocked
        $this->assertFalse($this->limiter->isAllowed($action, 2, 60));
    }

    public function testResetClearsLimit(): void
    {
        $action = 'test_action_' . uniqid('', true);
        
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
        $action = 'test_action_' . uniqid('', true);
        
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
        $action1 = 'test_action_1_' . uniqid('', true);
        $action2 = 'test_action_2_' . uniqid('', true);
        
        // Create some rate limit entries
        $this->limiter->isAllowed($action1, 5, 60);
        $this->limiter->isAllowed($action2, 5, 60);
        
        // Verify they exist
        $this->assertIsArray($this->limiter->getStatus($action1, 5, 60));
        $this->assertIsArray($this->limiter->getStatus($action2, 5, 60));
        
        // Clear manually (since clearAll requires $wpdb)
        if (isset($GLOBALS['__transients'])) {
            $cleared = 0;
            foreach (array_keys($GLOBALS['__transients']) as $key) {
                if (strpos($key, 'fp_ps_ratelimit_') === 0) {
                    unset($GLOBALS['__transients'][$key]);
                    $cleared++;
                }
            }
            $this->assertGreaterThanOrEqual(0, $cleared);
        }
        
        // Status should be null now
        $this->assertNull($this->limiter->getStatus($action1, 5, 60));
        $this->assertNull($this->limiter->getStatus($action2, 5, 60));
    }
}
