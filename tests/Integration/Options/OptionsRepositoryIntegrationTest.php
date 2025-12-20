<?php

namespace FP\PerfSuite\Tests\Integration\Options;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Core\Options\OptionsRepository;
use FP\PerfSuite\Core\Validation\Validator;
use FP\PerfSuite\Core\Sanitization\Sanitizer;
use Brain\Monkey\Functions;

/**
 * Integration test for OptionsRepository with Validator and Sanitizer
 *
 * @package FP\PerfSuite\Tests\Integration\Options
 */
class OptionsRepositoryIntegrationTest extends TestCase
{
    private OptionsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $validator = new Validator();
        $sanitizer = new Sanitizer();
        $this->repository = new OptionsRepository('fp_ps_test_', $validator, $sanitizer);
    }

    public function testGetSetWithSanitization(): void
    {
        Functions\expect('sanitize_text_field')
            ->once()
            ->with('<script>alert("xss")</script>')
            ->andReturn('alertxss');

        Functions\expect('update_option')
            ->once()
            ->with('fp_ps_test_my_option', 'alertxss', true)
            ->andReturn(true);

        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_test_my_option', null)
            ->andReturn('alertxss');

        $this->repository->set('my_option', '<script>alert("xss")</script>');
        $value = $this->repository->get('my_option');

        $this->assertStringNotContainsString('<script>', $value);
    }

    public function testDefaultsIntegration(): void
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
}










