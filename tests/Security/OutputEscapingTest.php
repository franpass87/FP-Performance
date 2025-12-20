<?php

namespace FP\PerfSuite\Tests\Security;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * Test output escaping
 *
 * @package FP\PerfSuite\Tests\Security
 */
class OutputEscapingTest extends TestCase
{
    public function testEscHtml(): void
    {
        $unsafe = '<script>alert("xss")</script>';
        
        Functions\expect('esc_html')
            ->once()
            ->with($unsafe)
            ->andReturn('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;');

        $escaped = esc_html($unsafe);
        $this->assertStringNotContainsString('<script>', $escaped);
    }

    public function testEscAttr(): void
    {
        $unsafe = '"><script>alert("xss")</script>';
        
        Functions\expect('esc_attr')
            ->once()
            ->with($unsafe)
            ->andReturn('&quot;&gt;&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;');

        $escaped = esc_attr($unsafe);
        $this->assertStringNotContainsString('<script>', $escaped);
    }

    public function testEscUrl(): void
    {
        $unsafe = 'javascript:alert("xss")';
        
        Functions\expect('esc_url')
            ->once()
            ->with($unsafe)
            ->andReturn('');

        $escaped = esc_url($unsafe);
        $this->assertStringNotContainsString('javascript:', $escaped);
    }
}










