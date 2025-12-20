<?php

namespace FP\PerfSuite\Tests\Integration\Container;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Core\Options\OptionsRepository;
use FP\PerfSuite\Core\Validation\Validator;
use FP\PerfSuite\Core\Sanitization\Sanitizer;

/**
 * Test Container dependency resolution
 *
 * @package FP\PerfSuite\Tests\Integration\Container
 */
class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
    }

    public function testBindSingleton(): void
    {
        $this->container->singleton('test.service', function() {
            return new \stdClass();
        });

        $instance1 = $this->container->get('test.service');
        $instance2 = $this->container->get('test.service');

        $this->assertSame($instance1, $instance2);
    }

    public function testBindInstance(): void
    {
        $instance = new \stdClass();
        $this->container->instance('test.service', $instance);

        $retrieved = $this->container->get('test.service');
        $this->assertSame($instance, $retrieved);
    }

    public function testHas(): void
    {
        $this->assertFalse($this->container->has('test.service'));

        $this->container->singleton('test.service', function() {
            return new \stdClass();
        });

        $this->assertTrue($this->container->has('test.service'));
    }

    public function testResolveDependencies(): void
    {
        $this->container->singleton(OptionsRepository::class, function($c) {
            return new OptionsRepository('fp_ps_test_');
        });

        $this->container->singleton(Validator::class, function() {
            return new Validator();
        });

        $this->container->singleton(Sanitizer::class, function() {
            return new Sanitizer();
        });

        $options = $this->container->get(OptionsRepository::class);
        $this->assertInstanceOf(OptionsRepository::class, $options);
    }

    public function testAlias(): void
    {
        $this->container->singleton('concrete', function() {
            return new \stdClass();
        });

        $this->container->alias('interface', 'concrete');

        $instance1 = $this->container->get('concrete');
        $instance2 = $this->container->get('interface');

        $this->assertSame($instance1, $instance2);
    }

    public function testGetNonExistentService(): void
    {
        $this->expectException(\Exception::class);
        $this->container->get('non.existent.service');
    }
}










