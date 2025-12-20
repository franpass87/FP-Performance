<?php

namespace FP\PerfSuite\Tests\Unit\Core;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Core\Options\OptionsRepository;
use FP\PerfSuite\Core\Validation\Validator;
use FP\PerfSuite\Core\Sanitization\Sanitizer;
use Brain\Monkey\Functions;

/**
 * Test OptionsRepository
 *
 * @package FP\PerfSuite\Tests\Unit\Core
 */
class OptionsRepositoryTest extends TestCase
{
    private OptionsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OptionsRepository('fp_ps_test_');
    }

    public function testGetOptionWithDefault(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_test_my_option', 'default_value')
            ->andReturn('default_value');

        $value = $this->repository->get('my_option', 'default_value');
        $this->assertEquals('default_value', $value);
    }

    public function testGetOptionWithoutPrefix(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_test_my_option', null)
            ->andReturn('stored_value');

        $value = $this->repository->get('my_option');
        $this->assertEquals('stored_value', $value);
    }

    public function testGetOptionWithPrefix(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_test_my_option', null)
            ->andReturn('stored_value');

        $value = $this->repository->get('fp_ps_test_my_option');
        $this->assertEquals('stored_value', $value);
    }

    public function testSetOption(): void
    {
        Functions\expect('update_option')
            ->once()
            ->with('fp_ps_test_my_option', 'test_value', true)
            ->andReturn(true);

        $result = $this->repository->set('my_option', 'test_value');
        $this->assertTrue($result);
    }

    public function testSetOptionWithAutoload(): void
    {
        Functions\expect('update_option')
            ->once()
            ->with('fp_ps_test_my_option', 'test_value', false)
            ->andReturn(true);

        $result = $this->repository->set('my_option', 'test_value', false);
        $this->assertTrue($result);
    }

    public function testDeleteOption(): void
    {
        Functions\expect('delete_option')
            ->once()
            ->with('fp_ps_test_my_option')
            ->andReturn(true);

        $result = $this->repository->delete('my_option');
        $this->assertTrue($result);
    }

    public function testHasOption(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_test_my_option', false)
            ->andReturn('value');

        $result = $this->repository->has('my_option');
        $this->assertTrue($result);
    }

    public function testHasOptionNotExists(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_test_my_option', false)
            ->andReturn(false);

        $result = $this->repository->has('my_option');
        $this->assertFalse($result);
    }

    public function testSetDefaults(): void
    {
        $defaults = [
            'option1' => 'default1',
            'option2' => 'default2',
        ];

        $this->repository->setDefaults($defaults);

        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_test_option1', 'default1')
            ->andReturn('default1');

        $value = $this->repository->get('option1');
        $this->assertEquals('default1', $value);
    }

    public function testCache(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_test_my_option', null)
            ->andReturn('cached_value');

        // First call should hit WordPress
        $value1 = $this->repository->get('my_option');
        $this->assertEquals('cached_value', $value1);

        // Second call should use cache (no get_option call)
        $value2 = $this->repository->get('my_option');
        $this->assertEquals('cached_value', $value2);
    }

    public function testClearCache(): void
    {
        Functions\expect('get_option')
            ->twice()
            ->with('fp_ps_test_my_option', null)
            ->andReturn('value');

        $this->repository->get('my_option');
        $this->repository->clearCache();
        $this->repository->get('my_option');
    }

    public function testSetWithSanitizer(): void
    {
        $sanitizer = new Sanitizer();
        $repository = new OptionsRepository('fp_ps_test_', null, $sanitizer);

        Functions\expect('update_option')
            ->once()
            ->with('fp_ps_test_my_option', 'sanitized_value', true)
            ->andReturn(true);

        $result = $repository->set('my_option', '<script>alert("xss")</script>');
        $this->assertTrue($result);
    }

    public function testSetWithValidator(): void
    {
        $validator = new Validator();
        $repository = new OptionsRepository('fp_ps_test_', $validator);

        Functions\expect('update_option')
            ->once()
            ->with('fp_ps_test_my_option', 'valid_value', true)
            ->andReturn(true);

        $repository->setTypeHint('my_option', 'string');
        $result = $repository->set('my_option', 'valid_value');
        $this->assertTrue($result);
    }
}










