<?php

use FP\PerfSuite\ServiceContainer;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

final class ServiceContainerTest extends TestCase
{
    private ServiceContainer $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new ServiceContainer();
    }

    protected function tearDown(): void
    {
        $this->container->clearSettingsCache();
        parent::tearDown();
    }

    public function testSetAndGet(): void
    {
        $this->container->set('test_service', fn() => 'test_value');
        
        $this->assertTrue($this->container->has('test_service'));
        $this->assertSame('test_value', $this->container->get('test_service'));
    }

    public function testLazyLoading(): void
    {
        $called = false;
        
        $this->container->set('lazy_service', function() use (&$called) {
            $called = true;
            return 'lazy_value';
        });
        
        // Factory should not be called yet
        $this->assertFalse($called);
        
        // Now it should be called
        $this->container->get('lazy_service');
        $this->assertTrue($called);
    }

    public function testSingletonBehavior(): void
    {
        $counter = 0;
        
        $this->container->set('singleton_service', function() use (&$counter) {
            $counter++;
            return new stdClass();
        });
        
        $instance1 = $this->container->get('singleton_service');
        $instance2 = $this->container->get('singleton_service');
        
        // Factory should only be called once
        $this->assertSame(1, $counter);
        $this->assertSame($instance1, $instance2);
    }

    public function testThrowsExceptionForUnknownService(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service "unknown_service" not found');
        
        $this->container->get('unknown_service');
    }

    public function testCachedSettings(): void
    {
        update_option('test_option', ['key' => 'value']);
        
        $settings1 = $this->container->getCachedSettings('test_option', ['default' => 'val']);
        $settings2 = $this->container->getCachedSettings('test_option', ['default' => 'val']);
        
        $this->assertSame($settings1, $settings2);
        $this->assertSame('value', $settings1['key']);
    }

    public function testInvalidateSettingsCache(): void
    {
        update_option('test_option', ['key' => 'original']);
        
        $settings1 = $this->container->getCachedSettings('test_option');
        $this->assertSame('original', $settings1['key']);
        
        // Update option
        update_option('test_option', ['key' => 'updated']);
        
        // Without invalidation, cache still returns old value
        $settings2 = $this->container->getCachedSettings('test_option');
        $this->assertSame('original', $settings2['key']);
        
        // After invalidation, should return new value
        $this->container->invalidateSettingsCache('test_option');
        $settings3 = $this->container->getCachedSettings('test_option');
        $this->assertSame('updated', $settings3['key']);
    }

    public function testClearSettingsCache(): void
    {
        update_option('test_option_1', ['key' => 'value1']);
        update_option('test_option_2', ['key' => 'value2']);
        
        // Cache both
        $this->container->getCachedSettings('test_option_1');
        $this->container->getCachedSettings('test_option_2');
        
        // Clear all cache
        $this->container->clearSettingsCache();
        
        // Update options
        update_option('test_option_1', ['key' => 'updated1']);
        update_option('test_option_2', ['key' => 'updated2']);
        
        // Should return updated values
        $settings1 = $this->container->getCachedSettings('test_option_1');
        $settings2 = $this->container->getCachedSettings('test_option_2');
        
        $this->assertSame('updated1', $settings1['key']);
        $this->assertSame('updated2', $settings2['key']);
    }
}
