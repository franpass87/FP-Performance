<?php

use FP\PerfSuite\ValueObjects\CacheSettings;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';

final class CacheSettingsTest extends TestCase
{
    public function testConstructorWithValidValues(): void
    {
        $settings = new CacheSettings(true, 3600);
        
        $this->assertTrue($settings->enabled);
        $this->assertSame(3600, $settings->ttl);
    }

    public function testThrowsExceptionForNegativeTtl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CacheSettings(true, -1);
    }

    public function testThrowsExceptionForTooLowTtl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CacheSettings(true, 30); // Less than MIN_TTL (60)
    }

    public function testAllowsZeroTtlWhenDisabled(): void
    {
        $settings = new CacheSettings(false, 0);
        
        $this->assertFalse($settings->enabled);
        $this->assertSame(0, $settings->ttl);
    }

    public function testFromArray(): void
    {
        $settings = CacheSettings::fromArray([
            'enabled' => true,
            'ttl' => 7200,
        ]);
        
        $this->assertTrue($settings->enabled);
        $this->assertSame(7200, $settings->ttl);
    }

    public function testToArray(): void
    {
        $settings = new CacheSettings(true, 3600);
        $array = $settings->toArray();
        
        $this->assertArrayHasKey('enabled', $array);
        $this->assertArrayHasKey('ttl', $array);
        $this->assertTrue($array['enabled']);
        $this->assertSame(3600, $array['ttl']);
    }

    public function testWithEnabled(): void
    {
        $settings = new CacheSettings(false, 3600);
        $updated = $settings->withEnabled(true);
        
        $this->assertFalse($settings->enabled); // Original unchanged
        $this->assertTrue($updated->enabled); // New instance
        $this->assertSame(3600, $updated->ttl);
    }

    public function testWithTtl(): void
    {
        $settings = new CacheSettings(true, 3600);
        $updated = $settings->withTtl(7200);
        
        $this->assertSame(3600, $settings->ttl); // Original unchanged
        $this->assertSame(7200, $updated->ttl); // New instance
    }

    public function testIsActive(): void
    {
        $active = new CacheSettings(true, 3600);
        $disabledButHasTtl = new CacheSettings(false, 3600);
        $enabledButNoTtl = new CacheSettings(true, 0);
        
        $this->assertTrue($active->isActive());
        $this->assertFalse($disabledButHasTtl->isActive());
        $this->assertFalse($enabledButNoTtl->isActive());
    }

    public function testGetTtlHumanReadable(): void
    {
        $settings = new CacheSettings(true, 3600);
        $this->assertStringContainsString('hour', $settings->getTtlHumanReadable());
        
        $settings2 = new CacheSettings(true, 300);
        $this->assertStringContainsString('minute', $settings2->getTtlHumanReadable());
    }
}
