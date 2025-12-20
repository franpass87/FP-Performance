<?php

namespace FP\PerfSuite\Tests\Unit\Services;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Services\Assets\FontOptimizer;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;

/**
 * Test FontOptimizer
 *
 * @package FP\PerfSuite\Tests\Unit\Services
 */
class FontOptimizerTest extends TestCase
{
    private $optionsRepo;
    private $fontOptimizer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->optionsRepo = \Mockery::mock(OptionsRepositoryInterface::class);
        $this->fontOptimizer = new FontOptimizer(true, true, $this->optionsRepo);
    }

    public function testInitInFrontend(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        Actions\expectAdded('wp_head')
            ->once()
            ->with(\Mockery::type('array'), 25);

        Filters\expectAdded('style_loader_tag')
            ->once()
            ->with(\Mockery::type('array'), 12, 2);

        $this->fontOptimizer->init();
    }

    public function testInitInAdmin(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(true);

        Actions\expectAdded('wp_head')
            ->never();

        $this->fontOptimizer->init();
    }

    public function testAddFontOptimizationsWithPreload(): void
    {
        $this->optionsRepo->shouldReceive('get')
            ->with('fp_ps_critical_fonts', [])
            ->andReturn([
                [
                    'url' => 'http://example.com/font.woff2',
                    'type' => 'woff2'
                ]
            ]);

        ob_start();
        $this->fontOptimizer->addFontOptimizations();
        $output = ob_get_clean();

        $this->assertStringContainsString('rel="preload"', $output);
        $this->assertStringContainsString('as="font"', $output);
        $this->assertStringContainsString('font-display: swap', $output);
    }

    public function testAddFontOptimizationsWithoutPreload(): void
    {
        $optimizer = new FontOptimizer(false, true, $this->optionsRepo);

        ob_start();
        $optimizer->addFontOptimizations();
        $output = ob_get_clean();

        $this->assertStringNotContainsString('rel="preload"', $output);
        $this->assertStringContainsString('font-display: swap', $output);
    }

    public function testOptimizeFontLoading(): void
    {
        $tag = '<link rel="stylesheet" href="font.css">';
        $handle = 'theme-font';

        $result = $this->fontOptimizer->optimizeFontLoading($tag, $handle);

        $this->assertStringContainsString('rel="preload"', $result);
        $this->assertStringContainsString('as="style"', $result);
    }

    public function testOptimizeFontLoadingNonFont(): void
    {
        $tag = '<link rel="stylesheet" href="style.css">';
        $handle = 'theme-style';

        $result = $this->fontOptimizer->optimizeFontLoading($tag, $handle);

        $this->assertEquals($tag, $result);
    }

    public function testGetCriticalFonts(): void
    {
        $fonts = [
            [
                'url' => 'http://example.com/font1.woff2',
                'type' => 'woff2'
            ],
            [
                'url' => 'http://example.com/font2.woff2',
                'type' => 'woff2'
            ]
        ];

        $this->optionsRepo->shouldReceive('get')
            ->with('fp_ps_critical_fonts', [])
            ->andReturn($fonts);

        $reflection = new \ReflectionClass($this->fontOptimizer);
        $method = $reflection->getMethod('getCriticalFonts');
        $method->setAccessible(true);

        $result = $method->invoke($this->fontOptimizer);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }
}










