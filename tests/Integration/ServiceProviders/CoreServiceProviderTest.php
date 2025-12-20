<?php

namespace FP\PerfSuite\Tests\Integration\ServiceProviders;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Providers\CoreServiceProvider;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Validation\ValidatorInterface;
use FP\PerfSuite\Core\Sanitization\SanitizerInterface;
use Brain\Monkey\Functions;

/**
 * Test CoreServiceProvider registration
 *
 * @package FP\PerfSuite\Tests\Integration\ServiceProviders
 */
class CoreServiceProviderTest extends TestCase
{
    private Container $container;
    private CoreServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->provider = new CoreServiceProvider();
    }

    public function testShouldLoad(): void
    {
        $this->assertTrue($this->provider->shouldLoad());
    }

    public function testPriority(): void
    {
        $priority = $this->provider->priority();
        $this->assertIsInt($priority);
        $this->assertGreaterThan(0, $priority);
    }

    public function testRegisterOptionsRepository(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        $this->provider->register($this->container);

        $this->assertTrue($this->container->has(OptionsRepositoryInterface::class));
        $repository = $this->container->get(OptionsRepositoryInterface::class);
        $this->assertInstanceOf(OptionsRepositoryInterface::class, $repository);
    }

    public function testRegisterLogger(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn('ERROR');

        $this->provider->register($this->container);

        $this->assertTrue($this->container->has(LoggerInterface::class));
        $logger = $this->container->get(LoggerInterface::class);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testRegisterValidator(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        $this->provider->register($this->container);

        $this->assertTrue($this->container->has(ValidatorInterface::class));
        $validator = $this->container->get(ValidatorInterface::class);
        $this->assertInstanceOf(ValidatorInterface::class, $validator);
    }

    public function testRegisterSanitizer(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        $this->provider->register($this->container);

        $this->assertTrue($this->container->has(SanitizerInterface::class));
        $sanitizer = $this->container->get(SanitizerInterface::class);
        $this->assertInstanceOf(SanitizerInterface::class, $sanitizer);
    }

    public function testBoot(): void
    {
        Functions\expect('get_option')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        $this->provider->register($this->container);
        $this->provider->boot($this->container);

        // Boot should complete without errors
        $this->assertTrue(true);
    }
}










