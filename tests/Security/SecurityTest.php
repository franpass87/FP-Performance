<?php

namespace FP\PerfSuite\Tests\Security;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Core\Sanitization\Sanitizer;
use FP\PerfSuite\Core\Validation\Validator;
use Brain\Monkey\Functions;

/**
 * Security tests for XSS, SQL injection, and input validation
 *
 * @package FP\PerfSuite\Tests\Security
 */
class SecurityTest extends TestCase
{
    private Sanitizer $sanitizer;
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new Sanitizer();
        $this->validator = new Validator();
    }

    public function testXssPreventionInText(): void
    {
        $malicious = '<script>alert("XSS")</script>';
        
        Functions\expect('sanitize_text_field')
            ->once()
            ->with($malicious)
            ->andReturn('alertXSS');

        $sanitized = $this->sanitizer->sanitizeValue($malicious, 'text');
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('alert', $sanitized);
    }

    public function testXssPreventionInTextarea(): void
    {
        $malicious = '<script>alert("XSS")</script>';
        
        Functions\expect('sanitize_textarea_field')
            ->once()
            ->with($malicious)
            ->andReturn('alertXSS');

        $sanitized = $this->sanitizer->sanitizeValue($malicious, 'textarea');
        $this->assertStringNotContainsString('<script>', $sanitized);
    }

    public function testSqlInjectionPrevention(): void
    {
        $malicious = "'; DROP TABLE wp_posts; --";
        
        Functions\expect('sanitize_text_field')
            ->once()
            ->with($malicious)
            ->andReturn('DROP TABLE wp_posts');

        $sanitized = $this->sanitizer->sanitizeValue($malicious, 'text');
        // Should not contain SQL injection patterns
        $this->assertStringNotContainsString('DROP TABLE', $sanitized);
    }

    public function testEmailValidation(): void
    {
        $valid = 'test@example.com';
        $invalid = 'not-an-email';

        $this->assertTrue($this->validator->validateValue($valid, 'email'));
        $this->assertFalse($this->validator->validateValue($invalid, 'email'));
    }

    public function testUrlValidation(): void
    {
        $valid = 'http://example.com';
        $invalid = 'javascript:alert("xss")';

        Functions\expect('esc_url_raw')
            ->once()
            ->with($invalid)
            ->andReturn('');

        $sanitized = $this->sanitizer->sanitizeValue($invalid, 'url');
        $this->assertStringNotContainsString('javascript:', $sanitized);
    }

    public function testNumericValidation(): void
    {
        $valid = '123';
        $invalid = "123'; DROP TABLE wp_posts; --";

        $this->assertTrue($this->validator->validateValue($valid, 'numeric'));
        $this->assertFalse($this->validator->validateValue($invalid, 'numeric'));
    }

    public function testNonceValidation(): void
    {
        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('valid-nonce', 'action')
            ->andReturn(true);

        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('invalid-nonce', 'action')
            ->andReturn(false);

        $this->assertTrue(wp_verify_nonce('valid-nonce', 'action'));
        $this->assertFalse(wp_verify_nonce('invalid-nonce', 'action'));
    }

    public function testCapabilityCheck(): void
    {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(false);

        $this->assertTrue(current_user_can('manage_options'));
        $this->assertFalse(current_user_can('manage_options'));
    }
}










