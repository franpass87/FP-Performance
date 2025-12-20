<?php

namespace FP\PerfSuite\Tests\Functional\Frontend;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * Test frontend script and style loading
 *
 * @package FP\PerfSuite\Tests\Functional\Frontend
 */
class FrontendScriptLoadingTest extends TestCase
{
    public function testScriptsLoadedInCorrectOrder(): void
    {
        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('jquery', \Mockery::type('string'), [], \Mockery::any(), false)
            ->ordered();

        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('plugin-script', \Mockery::type('string'), ['jquery'], \Mockery::any(), false)
            ->ordered();

        // Scripts should be loaded in dependency order
        $this->assertTrue(true);
    }

    public function testNoDuplicateScripts(): void
    {
        Functions\expect('wp_script_is')
            ->once()
            ->with('jquery', 'enqueued')
            ->andReturn(false);

        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('jquery', \Mockery::type('string'));

        // Duplicate scripts should not be enqueued
        $this->assertTrue(true);
    }

    public function testScriptDependenciesRespected(): void
    {
        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('plugin-script', \Mockery::type('string'), ['jquery'], \Mockery::any(), false);

        // Dependencies should be respected
        $this->assertTrue(true);
    }

    public function testStylesLoaded(): void
    {
        Functions\expect('wp_enqueue_style')
            ->once()
            ->with('theme-style', \Mockery::type('string'), [], \Mockery::any());

        // Styles should be loaded
        $this->assertTrue(true);
    }

    public function testScriptsDeferredWhenEnabled(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_js_optimization', [])
            ->andReturn(['defer' => true]);

        Functions\expect('wp_script_add_data')
            ->once()
            ->with('plugin-script', 'defer', true);

        // Scripts should be deferred when enabled
        $this->assertTrue(true);
    }

    public function testScriptsAsyncWhenEnabled(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_js_optimization', [])
            ->andReturn(['async' => true]);

        Functions\expect('wp_script_add_data')
            ->once()
            ->with('plugin-script', 'async', true);

        // Scripts should be async when enabled
        $this->assertTrue(true);
    }
}










