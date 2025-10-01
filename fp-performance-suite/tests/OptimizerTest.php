<?php

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Utils\Semaphore;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

final class OptimizerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['__wp_options'] = [];
    }

    public function testSettingsCoerceStringListsToArrays(): void
    {
        update_option('fp_ps_assets', [
            'dns_prefetch' => " https://one.test \nhttps://two.test ",
            'preload' => 'https://cdn.test ',
        ]);

        $optimizer = new Optimizer(new Semaphore());
        $settings = $optimizer->settings();

        $this->assertSame(['https://one.test', 'https://two.test'], $settings['dns_prefetch']);
        $this->assertSame(['https://cdn.test'], $settings['preload']);
    }

    public function testUpdateNormalizesListsFromStringsAndArrays(): void
    {
        $optimizer = new Optimizer(new Semaphore());

        $optimizer->update([
            'dns_prefetch' => "https://one.test\n\nhttps://two.test, https://three.test",
            'preload' => ['https://cdn.test', '   '],
        ]);

        $stored = get_option('fp_ps_assets');

        $this->assertSame(['https://one.test', 'https://two.test', 'https://three.test'], $stored['dns_prefetch']);
        $this->assertSame(['https://cdn.test'], $stored['preload']);
    }

    public function testUpdateAcceptsStructuredEntriesInLists(): void
    {
        $optimizer = new Optimizer(new Semaphore());

        $optimizer->update([
            'dns_prefetch' => [
                ['href' => 'https://fonts.test'],
                ' https://api.test ',
                ['url' => ''],
            ],
            'preload' => [
                ['href' => 'https://cdn.test/app.js', 'as' => 'script'],
                ['url' => 'https://cdn.test/asset.css'],
                ['href' => ''],
            ],
        ]);

        $stored = get_option('fp_ps_assets');

        $this->assertSame(['https://fonts.test', 'https://api.test'], $stored['dns_prefetch']);
        $this->assertSame(['https://cdn.test/app.js', 'https://cdn.test/asset.css'], $stored['preload']);
    }

    public function testUpdateCoercesStringBooleans(): void
    {
        $optimizer = new Optimizer(new Semaphore());

        $optimizer->update([
            'minify_html' => 'false',
            'defer_js' => 'no',
            'async_js' => '0',
            'remove_emojis' => '',
            'combine_css' => 'yes',
            'combine_js' => 'off',
        ]);

        $stored = get_option('fp_ps_assets');

        $this->assertFalse($stored['minify_html']);
        $this->assertFalse($stored['defer_js']);
        $this->assertFalse($stored['async_js']);
        $this->assertFalse($stored['remove_emojis']);
        $this->assertTrue($stored['combine_css']);
        $this->assertFalse($stored['combine_js']);
    }

    public function testPreloadResourcesProvideStructuredHints(): void
    {
        update_option('fp_ps_assets', [
            'preload' => [
                'https://cdn.test/app.js',
                'https://cdn.test/font.woff2',
                'https://cdn.test/data',
            ],
        ]);

        $optimizer = new Optimizer(new Semaphore());

        $existing = [
            ['href' => 'https://existing.test/style.css', 'as' => 'STYLE', 'crossorigin' => 'anonymous'],
        ];

        $result = $optimizer->preloadResources($existing, 'preload');

        $this->assertSame([
            ['href' => 'https://existing.test/style.css', 'as' => 'style', 'crossorigin' => 'anonymous'],
            ['href' => 'https://cdn.test/app.js', 'as' => 'script'],
            ['href' => 'https://cdn.test/font.woff2', 'as' => 'font'],
            ['href' => 'https://cdn.test/data', 'as' => 'fetch'],
        ], $result);
    }

    public function testPreloadResourcesDeduplicateStringsAndArrays(): void
    {
        update_option('fp_ps_assets', [
            'preload' => ['https://cdn.test/app.js'],
        ]);

        $optimizer = new Optimizer(new Semaphore());

        $existing = [
            'https://cdn.test/app.js',
            ['href' => 'https://cdn.test/app.js', 'as' => 'script', 'crossorigin' => 'use-credentials'],
        ];

        $result = $optimizer->preloadResources($existing, 'preload');

        $this->assertSame([
            ['href' => 'https://cdn.test/app.js', 'as' => 'script', 'crossorigin' => 'use-credentials'],
        ], $result);
    }

    public function testUpdatePreservesExistingFlagsWhenOmitted(): void
    {
        $optimizer = new Optimizer(new Semaphore());

        $optimizer->update([
            'minify_html' => true,
            'defer_js' => true,
            'async_js' => true,
            'remove_emojis' => true,
            'combine_css' => true,
            'combine_js' => true,
        ]);

        $optimizer->update([
            'dns_prefetch' => ['https://cdn.test'],
        ]);

        $stored = get_option('fp_ps_assets');

        $this->assertTrue($stored['minify_html']);
        $this->assertTrue($stored['defer_js']);
        $this->assertTrue($stored['async_js']);
        $this->assertTrue($stored['remove_emojis']);
        $this->assertTrue($stored['combine_css']);
        $this->assertTrue($stored['combine_js']);
    }
}
