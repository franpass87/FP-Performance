<?php

namespace FP\PerfSuite\Tests\Unit\Services;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Services\Assets\CSSOptimizer;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Brain\Monkey\Actions;

/**
 * Test CSSOptimizer
 *
 * @package FP\PerfSuite\Tests\Unit\Services
 */
class CSSOptimizerTest extends TestCase
{
    private $optionsRepo;
    private $cssOptimizer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->optionsRepo = \Mockery::mock(OptionsRepositoryInterface::class);
        $this->cssOptimizer = new CSSOptimizer($this->optionsRepo);
    }

    public function testRegisterWhenEnabled(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        $this->optionsRepo->shouldReceive('get')
            ->with('fp_ps_css_optimization', [])
            ->andReturn(['enabled' => true]);

        Filters\expectAdded('style_loader_tag')
            ->once()
            ->with(\Mockery::type('array'), 15, 4);

        Actions\expectAdded('wp_head')
            ->twice()
            ->with(\Mockery::type('array'), \Mockery::any());

        $this->cssOptimizer->register();
    }

    public function testRegisterWhenDisabled(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        $this->optionsRepo->shouldReceive('get')
            ->with('fp_ps_css_optimization', [])
            ->andReturn(['enabled' => false]);

        Filters\expectAdded('style_loader_tag')
            ->never();

        $this->cssOptimizer->register();
    }

    public function testRegisterInAdmin(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(true);

        Filters\expectAdded('style_loader_tag')
            ->never();

        $this->cssOptimizer->register();
    }

    public function testDeferNonCriticalCSS(): void
    {
        $html = '<link rel="stylesheet" href="test.css">';
        $handle = 'test-style';
        $href = 'http://example.com/test.css';
        $media = 'all';

        $this->optionsRepo->shouldReceive('get')
            ->with('fp_ps_css_optimization', [])
            ->andReturn([
                'enabled' => true,
                'defer_non_critical' => true
            ]);

        // Mock isCriticalCSS to return false
        $result = $this->cssOptimizer->deferNonCriticalCSS($html, $handle, $href, $media);

        $this->assertStringContainsString('rel="preload"', $result);
        $this->assertStringContainsString('data-fp-deferred="true"', $result);
        $this->assertStringContainsString('<noscript>', $result);
    }

    public function testDeferNonCriticalCSSWhenDisabled(): void
    {
        $html = '<link rel="stylesheet" href="test.css">';
        $handle = 'test-style';
        $href = 'http://example.com/test.css';
        $media = 'all';

        $this->optionsRepo->shouldReceive('get')
            ->with('fp_ps_css_optimization', [])
            ->andReturn([
                'enabled' => true,
                'defer_non_critical' => false
            ]);

        $result = $this->cssOptimizer->deferNonCriticalCSS($html, $handle, $href, $media);

        $this->assertEquals($html, $result);
    }

    public function testDeferNonCriticalCSSAlreadyDeferred(): void
    {
        $html = '<link rel="stylesheet" href="test.css" data-fp-deferred="true">';
        $handle = 'test-style';
        $href = 'http://example.com/test.css';
        $media = 'all';

        $this->optionsRepo->shouldReceive('get')
            ->with('fp_ps_css_optimization', [])
            ->andReturn([
                'enabled' => true,
                'defer_non_critical' => true
            ]);

        $result = $this->cssOptimizer->deferNonCriticalCSS($html, $handle, $href, $media);

        $this->assertEquals($html, $result);
    }
}










