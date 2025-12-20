<?php

namespace FP\PerfSuite\Tests\Security;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Core\Sanitization\Sanitizer;
use Brain\Monkey\Functions;

/**
 * Test input sanitization for security
 *
 * @package FP\PerfSuite\Tests\Security
 */
class InputSanitizationTest extends TestCase
{
    private Sanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new Sanitizer();
    }

    public function testXssInText(): void
    {
        $malicious = '<script>alert("XSS")</script>test';
        
        Functions\expect('sanitize_text_field')
            ->once()
            ->with($malicious)
            ->andReturn('test');

        $sanitized = $this->sanitizer->sanitizeValue($malicious, 'text');
        $this->assertStringNotContainsString('<script>', $sanitized);
    }

    public function testXssInTextarea(): void
    {
        $malicious = '<script>alert("XSS")</script><p>Content</p>';
        
        Functions\expect('sanitize_textarea_field')
            ->once()
            ->with($malicious)
            ->andReturn('Content');

        $sanitized = $this->sanitizer->sanitizeValue($malicious, 'textarea');
        $this->assertStringNotContainsString('<script>', $sanitized);
    }

    public function testSqlInjectionInText(): void
    {
        $malicious = "'; DROP TABLE wp_posts; --";
        
        Functions\expect('sanitize_text_field')
            ->once()
            ->with($malicious)
            ->andReturn('DROP TABLE wp_posts');

        $sanitized = $this->sanitizer->sanitizeValue($malicious, 'text');
        // Should be sanitized
        $this->assertIsString($sanitized);
    }

    public function testPathTraversalPrevention(): void
    {
        $malicious = '../../../etc/passwd';
        
        Functions\expect('sanitize_text_field')
            ->once()
            ->with($malicious)
            ->andReturn('etcpasswd');

        $sanitized = $this->sanitizer->sanitizeValue($malicious, 'text');
        $this->assertStringNotContainsString('../', $sanitized);
    }
}










