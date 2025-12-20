<?php

namespace FP\PerfSuite\Tests\Unit\Core;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Core\Sanitization\Sanitizer;
use Brain\Monkey\Functions;

/**
 * Test Sanitizer
 *
 * @package FP\PerfSuite\Tests\Unit\Core
 */
class SanitizerTest extends TestCase
{
    private Sanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new Sanitizer();
    }

    public function testSanitizeText(): void
    {
        Functions\expect('sanitize_text_field')
            ->once()
            ->with('<script>alert("xss")</script>')
            ->andReturn('alertxss');

        $result = $this->sanitizer->sanitizeValue('<script>alert("xss")</script>', 'text');
        $this->assertEquals('alertxss', $result);
    }

    public function testSanitizeTextarea(): void
    {
        Functions\expect('sanitize_textarea_field')
            ->once()
            ->with('Test content')
            ->andReturn('Test content');

        $result = $this->sanitizer->sanitizeValue('Test content', 'textarea');
        $this->assertEquals('Test content', $result);
    }

    public function testSanitizeEmail(): void
    {
        Functions\expect('sanitize_email')
            ->once()
            ->with('TEST@EXAMPLE.COM')
            ->andReturn('test@example.com');

        $result = $this->sanitizer->sanitizeValue('TEST@EXAMPLE.COM', 'email');
        $this->assertEquals('test@example.com', $result);
    }

    public function testSanitizeUrl(): void
    {
        Functions\expect('esc_url_raw')
            ->once()
            ->with('http://example.com')
            ->andReturn('http://example.com');

        $result = $this->sanitizer->sanitizeValue('http://example.com', 'url');
        $this->assertEquals('http://example.com', $result);
    }

    public function testSanitizeKey(): void
    {
        Functions\expect('sanitize_key')
            ->once()
            ->with('Test Key')
            ->andReturn('test-key');

        $result = $this->sanitizer->sanitizeValue('Test Key', 'key');
        $this->assertEquals('test-key', $result);
    }

    public function testSanitizeInteger(): void
    {
        $result = $this->sanitizer->sanitizeValue('123', 'integer');
        $this->assertIsInt($result);
        $this->assertEquals(123, $result);
    }

    public function testSanitizeFloat(): void
    {
        $result = $this->sanitizer->sanitizeValue('123.45', 'float');
        $this->assertIsFloat($result);
        $this->assertEquals(123.45, $result);
    }

    public function testSanitizeBoolean(): void
    {
        $this->assertTrue($this->sanitizer->sanitizeValue('1', 'boolean'));
        $this->assertTrue($this->sanitizer->sanitizeValue('true', 'boolean'));
        $this->assertTrue($this->sanitizer->sanitizeValue(true, 'boolean'));
        $this->assertFalse($this->sanitizer->sanitizeValue('0', 'boolean'));
        $this->assertFalse($this->sanitizer->sanitizeValue('false', 'boolean'));
        $this->assertFalse($this->sanitizer->sanitizeValue(false, 'boolean'));
    }

    public function testSanitizeArray(): void
    {
        $value = ['item1', 'item2'];
        $result = $this->sanitizer->sanitizeValue($value, 'array');
        $this->assertIsArray($result);
        $this->assertEquals($value, $result);
    }

    public function testSanitizeArrayFromString(): void
    {
        $result = $this->sanitizer->sanitizeValue('not-array', 'array');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testSanitizeMultipleFields(): void
    {
        Functions\expect('sanitize_text_field')
            ->once()
            ->with('Test Name')
            ->andReturn('Test Name');
        
        Functions\expect('sanitize_email')
            ->once()
            ->with('test@example.com')
            ->andReturn('test@example.com');

        $data = [
            'name' => 'Test Name',
            'email' => 'test@example.com',
        ];
        $rules = [
            'name' => 'text',
            'email' => 'email',
        ];

        $result = $this->sanitizer->sanitize($data, $rules);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
    }

    public function testSanitizeUnknownType(): void
    {
        $result = $this->sanitizer->sanitizeValue('test', 'unknown_type');
        $this->assertEquals('test', $result);
    }
}










