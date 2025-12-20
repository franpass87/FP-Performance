<?php

namespace FP\PerfSuite\Tests\Unit;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Shortcodes;
use Brain\Monkey\Functions;

/**
 * Test Shortcodes
 *
 * @package FP\PerfSuite\Tests\Unit
 */
class ShortcodesTest extends TestCase
{
    private Shortcodes $shortcodes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->shortcodes = new Shortcodes();
    }

    public function testBoot(): void
    {
        Functions\expect('add_shortcode')
            ->once()
            ->with('fp_performance_status', \Mockery::type('array'));

        $this->shortcodes->boot();
    }

    public function testRenderStatus(): void
    {
        global $wpdb;
        $wpdb = $this->createMock(\stdClass::class);
        $wpdb->method('get_var')
            ->with('SELECT NOW()')
            ->willReturn('2025-01-01 12:00:00');

        Functions\expect('sanitize_text_field')
            ->once()
            ->with('2025-01-01 12:00:00')
            ->andReturn('2025-01-01 12:00:00');

        Functions\expect('esc_html')
            ->twice()
            ->andReturnUsing(function($value) {
                return $value;
            });

        $output = $this->shortcodes->renderStatus();
        
        $this->assertStringContainsString('FP-Performance', $output);
        $this->assertStringContainsString('v1.8.0', $output);
    }
}










