<?php

namespace FP\PerfSuite\Tests\Unit\Services;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager\CriticalPageDetector;
use FP\PerfSuite\Core\Options\OptionsRepository;
use Brain\Monkey\Functions;

/**
 * Test ThirdPartyScriptManager
 *
 * @package FP\PerfSuite\Tests\Unit\Services
 */
class ThirdPartyScriptManagerTest extends TestCase
{
    private ThirdPartyScriptManager $manager;
    private OptionsRepository $optionsRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->optionsRepo = new OptionsRepository('fp_ps_test_');
        $this->manager = new ThirdPartyScriptManager($this->optionsRepo);
    }

    public function testRegister(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        Functions\expect('add_filter')
            ->once()
            ->with('script_loader_tag', \Mockery::type('array'), 15, 3);

        Functions\expect('add_action')
            ->once()
            ->with('wp_footer', \Mockery::type('array'), 5);

        // Mock settings to be enabled
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn(['enabled' => true]);

        $this->manager->register();
    }

    public function testRegisterDisabled(): void
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn(['enabled' => false]);

        // Should not register hooks if disabled
        Functions\expect('add_filter')
            ->never();

        $this->manager->register();
    }

    public function testRegisterInAdmin(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(true);

        Functions\expect('get_option')
            ->once()
            ->andReturn(['enabled' => true]);

        // Should not register in admin
        Functions\expect('add_filter')
            ->never();

        $this->manager->register();
    }

    public function testSettings(): void
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn([]);

        $settings = $this->manager->settings();
        
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('enabled', $settings);
        $this->assertArrayHasKey('delay_all', $settings);
        $this->assertArrayHasKey('scripts', $settings);
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
            'delay_timeout' => 3000,
        ];

        $this->manager->update($newSettings);
    }

    public function testDetectScripts(): void
    {
        $html = '<script src="https://www.googletagmanager.com/gtag/js"></script>';
        
        $detected = $this->manager->detectScripts($html);
        
        $this->assertIsArray($detected);
    }
}

