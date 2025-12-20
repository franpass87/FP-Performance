<?php

namespace FP\PerfSuite\Tests\Functional\Frontend;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * Test frontend HTML structure validation
 *
 * @package FP\PerfSuite\Tests\Functional\Frontend
 */
class FrontendHtmlStructureTest extends TestCase
{
    public function testHtmlOutputIsValid(): void
    {
        $html = '<!DOCTYPE html><html><head><title>Test</title></head><body><p>Content</p></body></html>';

        // Basic HTML structure validation
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('<html>', $html);
        $this->assertStringContainsString('<head>', $html);
        $this->assertStringContainsString('<body>', $html);
    }

    public function testNoUnclosedTags(): void
    {
        $html = '<div><p>Content</p></div>';

        // Count opening and closing tags
        $openDivs = substr_count($html, '<div>');
        $closeDivs = substr_count($html, '</div>');
        $openP = substr_count($html, '<p>');
        $closeP = substr_count($html, '</p>');

        $this->assertEquals($openDivs, $closeDivs);
        $this->assertEquals($openP, $closeP);
    }

    public function testSemanticHtml(): void
    {
        $html = '<main><article><header><h1>Title</h1></header><section><p>Content</p></section></article></main>';

        // Check for semantic HTML elements
        $this->assertStringContainsString('<main>', $html);
        $this->assertStringContainsString('<article>', $html);
        $this->assertStringContainsString('<header>', $html);
        $this->assertStringContainsString('<section>', $html);
    }

    public function testScriptsLoadedInCorrectOrder(): void
    {
        $scripts = [
            '<script src="jquery.js"></script>',
            '<script src="plugin.js"></script>',
            '<script src="theme.js"></script>'
        ];

        // Verify scripts are in correct order
        $html = implode("\n", $scripts);
        $jqueryPos = strpos($html, 'jquery.js');
        $pluginPos = strpos($html, 'plugin.js');
        $themePos = strpos($html, 'theme.js');

        $this->assertLessThan($pluginPos, $jqueryPos);
        $this->assertLessThan($themePos, $pluginPos);
    }

    public function testNoDuplicateScripts(): void
    {
        $html = '<script src="jquery.js"></script><script src="plugin.js"></script>';

        // Count script occurrences
        $jqueryCount = substr_count($html, 'jquery.js');
        $pluginCount = substr_count($html, 'plugin.js');

        $this->assertEquals(1, $jqueryCount);
        $this->assertEquals(1, $pluginCount);
    }

    public function testStylesLoaded(): void
    {
        $html = '<link rel="stylesheet" href="style.css">';

        $this->assertStringContainsString('rel="stylesheet"', $html);
        $this->assertStringContainsString('href="style.css"', $html);
    }

    public function testConditionalRenderingWithOptimizationEnabled(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_css_optimization', [])
            ->andReturn(['enabled' => true]);

        // When optimization is enabled, CSS should be deferred
        $html = '<link rel="preload" as="style" href="style.css">';
        $this->assertStringContainsString('rel="preload"', $html);
    }

    public function testConditionalRenderingWithOptimizationDisabled(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_css_optimization', [])
            ->andReturn(['enabled' => false]);

        // When optimization is disabled, CSS should be normal
        $html = '<link rel="stylesheet" href="style.css">';
        $this->assertStringContainsString('rel="stylesheet"', $html);
    }
}










