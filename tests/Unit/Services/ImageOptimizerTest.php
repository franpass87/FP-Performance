<?php

namespace FP\PerfSuite\Tests\Unit\Services;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Services\Assets\ImageOptimizer;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;

/**
 * Test ImageOptimizer
 *
 * @package FP\PerfSuite\Tests\Unit\Services
 */
class ImageOptimizerTest extends TestCase
{
    private $optionsRepo;
    private $imageOptimizer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->optionsRepo = \Mockery::mock(OptionsRepositoryInterface::class);
        $this->imageOptimizer = new ImageOptimizer(true, $this->optionsRepo);
    }

    public function testInit(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        Filters\expectAdded('wp_get_attachment_image_attributes')
            ->once()
            ->with(\Mockery::type('array'), 10, 3);

        Filters\expectAdded('the_content')
            ->once()
            ->with(\Mockery::type('array'));

        Actions\expectAdded('wp_head')
            ->once()
            ->with(\Mockery::type('array'), 26);

        $this->imageOptimizer->init();
    }

    public function testAddLazyLoading(): void
    {
        $attr = ['src' => 'image.jpg'];
        $attachment = \Mockery::mock('WP_Post');
        $size = 'medium';

        $result = $this->imageOptimizer->addLazyLoading($attr, $attachment, $size);

        $this->assertArrayHasKey('loading', $result);
        $this->assertEquals('lazy', $result['loading']);
        $this->assertArrayHasKey('decoding', $result);
        $this->assertEquals('async', $result['decoding']);
    }

    public function testAddLazyLoadingWhenDisabled(): void
    {
        $optimizer = new ImageOptimizer(false, $this->optionsRepo);
        
        $attr = ['src' => 'image.jpg'];
        $attachment = \Mockery::mock('WP_Post');
        $size = 'medium';

        $result = $optimizer->addLazyLoading($attr, $attachment, $size);

        $this->assertArrayNotHasKey('loading', $result);
        $this->assertArrayNotHasKey('decoding', $result);
    }

    public function testOptimizeContentImages(): void
    {
        $content = '<img src="image.jpg" alt="Test">';

        $result = $this->imageOptimizer->optimizeContentImages($content);

        $this->assertStringContainsString('loading="lazy"', $result);
        $this->assertStringContainsString('decoding="async"', $result);
    }

    public function testOptimizeContentImagesMultiple(): void
    {
        $content = '<img src="image1.jpg"><p>Text</p><img src="image2.jpg">';

        $result = $this->imageOptimizer->optimizeContentImages($content);

        $this->assertStringContainsString('loading="lazy"', $result);
        $this->assertEquals(2, substr_count($result, 'loading="lazy"'));
    }

    public function testAddImageOptimizationScripts(): void
    {
        ob_start();
        $this->imageOptimizer->addImageOptimizationScripts();
        $output = ob_get_clean();

        $this->assertStringContainsString('<script>', $output);
        $this->assertStringContainsString('IntersectionObserver', $output);
        $this->assertStringContainsString('data-src', $output);
    }

    public function testAddImageOptimizationScriptsWhenDisabled(): void
    {
        $optimizer = new ImageOptimizer(false, $this->optionsRepo);

        ob_start();
        $optimizer->addImageOptimizationScripts();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    public function testGetImageMetrics(): void
    {
        $metrics = $this->imageOptimizer->getImageMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('lazy_loading_enabled', $metrics);
        $this->assertTrue($metrics['lazy_loading_enabled']);
    }
}










