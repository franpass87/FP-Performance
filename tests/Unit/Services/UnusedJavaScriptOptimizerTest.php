<?php

namespace FP\PerfSuite\Tests\Unit\Services;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer;
use FP\PerfSuite\Core\Options\OptionsRepository;
use Brain\Monkey\Functions;

/**
 * Test UnusedJavaScriptOptimizer
 *
 * @package FP\PerfSuite\Tests\Unit\Services
 */
class UnusedJavaScriptOptimizerTest extends TestCase
{
    private UnusedJavaScriptOptimizer $optimizer;
    private OptionsRepository $optionsRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->optionsRepo = new OptionsRepository('fp_ps_test_');
        $this->optimizer = new UnusedJavaScriptOptimizer(false, $this->optionsRepo);
    }

    public function testInitDisabled(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        Functions\expect('get_option')
            ->once()
            ->andReturn(['enabled' => false]);

        Functions\expect('add_action')
            ->never();

        $this->optimizer->init();
    }

    public function testInitInAdmin(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(true);

        Functions\expect('add_action')
            ->never();

        $this->optimizer->init();
    }

    public function testInitEnabled(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        Functions\expect('get_option')
            ->once()
            ->andReturn(['enabled' => true]);

        Functions\expect('add_action')
            ->once()
            ->with('wp_enqueue_scripts', \Mockery::type('array'), 996);

        Functions\expect('add_action')
            ->once()
            ->with('wp_footer', \Mockery::type('array'), 44);

        $this->optimizer->init();
    }

    public function testSettings(): void
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn(['enabled' => true, 'aggressive_mode' => false]);

        $settings = $this->optimizer->settings();
        
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('enabled', $settings);
        $this->assertArrayHasKey('aggressive_mode', $settings);
    }

    public function testUpdateSettings(): void
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn([]);

        Functions\expect('update_option')
            ->once()
            ->andReturn(true);

        $newSettings = [
            'enabled' => true,
            'aggressive_mode' => true,
        ];

        $result = $this->optimizer->updateSettings($newSettings);
        $this->assertTrue($result);
    }

    public function testGetUnusedJSMetrics(): void
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn(['enabled' => true]);

        $metrics = $this->optimizer->getUnusedJSMetrics();
        
        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('aggressive_mode', $metrics);
        $this->assertArrayHasKey('optimization_enabled', $metrics);
    }
}

