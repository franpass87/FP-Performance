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
        $GLOBALS['__registered_styles'] = [];
        $GLOBALS['__enqueued_styles'] = [];
        $GLOBALS['__dequeued_styles'] = [];
        $GLOBALS['__registered_scripts'] = [];
        $GLOBALS['__enqueued_scripts'] = [];
        $GLOBALS['__dequeued_scripts'] = [];
        $GLOBALS['__home_url'] = 'https://example.com';
        unset($GLOBALS['wp_styles'], $GLOBALS['wp_scripts']);

        $uploads = wp_upload_dir();
        if (!empty($uploads['basedir'])) {
            $target = $uploads['basedir'] . '/fp-performance-suite';
            if (is_dir($target)) {
                $files = glob($target . '/*');
                if (is_array($files)) {
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                }
                rmdir($target);
            }
        }
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

    public function testApplyCombinationCombinesEligibleStyles(): void
    {
        wp_mkdir_p(WP_CONTENT_DIR . '/plugins/test');
        file_put_contents(WP_CONTENT_DIR . '/plugins/test/one.css', 'body{color:#f00;}');
        file_put_contents(WP_CONTENT_DIR . '/plugins/test/two.css', 'body{background:#000;}');

        $styleOne = new _WP_Dependency('one', 'plugins/test/one.css');
        $styleTwo = new _WP_Dependency('two', 'plugins/test/two.css', ['one']);
        $dependent = new _WP_Dependency('dependent', 'https://cdn.example.com/dependent.css', ['two']);
        $external = new _WP_Dependency('external', 'https://cdn.example.com/external.css');

        $GLOBALS['wp_styles'] = new WP_Styles();
        $GLOBALS['wp_styles']->base_url = 'https://example.com/wp-content/';
        $GLOBALS['wp_styles']->queue = ['one', 'two', 'dependent', 'external'];
        $GLOBALS['wp_styles']->registered = [
            'one' => $styleOne,
            'two' => $styleTwo,
            'dependent' => $dependent,
            'external' => $external,
        ];

        update_option('fp_ps_assets', [
            'combine_css' => true,
            'combine_js' => false,
        ]);

        $optimizer = new Optimizer(new Semaphore());
        $optimizer->applyCombination();

        $this->assertArrayHasKey('fp-ps-combined-styles', $GLOBALS['__registered_styles']);
        $this->assertContains('fp-ps-combined-styles', $GLOBALS['__enqueued_styles']);
        $this->assertSame(['one', 'two'], $GLOBALS['__dequeued_styles']);

        $registered = $GLOBALS['__registered_styles']['fp-ps-combined-styles'];
        $uploads = wp_upload_dir();
        $filename = basename($registered['src']);
        $path = $uploads['basedir'] . '/fp-performance-suite/' . $filename;

        $this->assertFileExists($path);
        $contents = file_get_contents($path);
        $this->assertIsString($contents);
        $this->assertStringContainsString('/* one */', $contents);
        $this->assertStringContainsString('/* two */', $contents);

        $this->assertSame(['fp-ps-combined-styles'], $GLOBALS['wp_styles']->registered['dependent']->deps);
    }

    public function testApplyCombinationCombinesStylesWhenSiteInSubdirectory(): void
    {
        $previousHome = $GLOBALS['__home_url'];
        $GLOBALS['__home_url'] = 'https://example.com/site';

        try {
            wp_mkdir_p(WP_CONTENT_DIR . '/plugins/test');
            file_put_contents(WP_CONTENT_DIR . '/plugins/test/sub-one.css', 'body{color:#0ff;}');
            file_put_contents(WP_CONTENT_DIR . '/plugins/test/sub-two.css', 'body{background:#333;}');

            $styleOne = new _WP_Dependency('sub-one', 'plugins/test/sub-one.css');
            $styleTwo = new _WP_Dependency('sub-two', 'plugins/test/sub-two.css', ['sub-one']);

            $GLOBALS['wp_styles'] = new WP_Styles();
            $GLOBALS['wp_styles']->base_url = 'https://example.com/site/wp-content/';
            $GLOBALS['wp_styles']->queue = ['sub-one', 'sub-two'];
            $GLOBALS['wp_styles']->registered = [
                'sub-one' => $styleOne,
                'sub-two' => $styleTwo,
            ];

            update_option('fp_ps_assets', [
                'combine_css' => true,
                'combine_js' => false,
            ]);

            $optimizer = new Optimizer(new Semaphore());
            $optimizer->applyCombination();

            $this->assertArrayHasKey('fp-ps-combined-styles', $GLOBALS['__registered_styles']);
            $this->assertContains('fp-ps-combined-styles', $GLOBALS['__enqueued_styles']);
            $this->assertSame(['sub-one', 'sub-two'], $GLOBALS['__dequeued_styles']);

            $registered = $GLOBALS['__registered_styles']['fp-ps-combined-styles'];
            $uploads = wp_upload_dir();
            $filename = basename($registered['src']);
            $path = $uploads['basedir'] . '/fp-performance-suite/' . $filename;

            $this->assertFileExists($path);
            $contents = file_get_contents($path);
            $this->assertIsString($contents);
            $this->assertStringContainsString('/* sub-one */', $contents);
            $this->assertStringContainsString('/* sub-two */', $contents);
        } finally {
            $GLOBALS['__home_url'] = $previousHome;
        }
    }

    public function testApplyCombinationReusesExistingBundleWhenSourcesAreUnchanged(): void
    {
        wp_mkdir_p(WP_CONTENT_DIR . '/plugins/test');
        file_put_contents(WP_CONTENT_DIR . '/plugins/test/one.css', 'body{color:#f00;}');
        file_put_contents(WP_CONTENT_DIR . '/plugins/test/two.css', 'body{background:#000;}');

        $setupStyles = static function (): void {
            $styleOne = new _WP_Dependency('one', 'plugins/test/one.css');
            $styleTwo = new _WP_Dependency('two', 'plugins/test/two.css', ['one']);
            $dependent = new _WP_Dependency('dependent', 'https://cdn.example.com/dependent.css', ['two']);

            $GLOBALS['wp_styles'] = new WP_Styles();
            $GLOBALS['wp_styles']->base_url = 'https://example.com/wp-content/';
            $GLOBALS['wp_styles']->queue = ['one', 'two', 'dependent'];
            $GLOBALS['wp_styles']->registered = [
                'one' => $styleOne,
                'two' => $styleTwo,
                'dependent' => $dependent,
            ];
        };

        update_option('fp_ps_assets', [
            'combine_css' => true,
            'combine_js' => false,
        ]);

        $setupStyles();
        $optimizer = new Optimizer(new Semaphore());
        $optimizer->applyCombination();

        $this->assertArrayHasKey('fp-ps-combined-styles', $GLOBALS['__registered_styles']);
        $firstSrc = $GLOBALS['__registered_styles']['fp-ps-combined-styles']['src'];
        $uploads = wp_upload_dir();
        $filename = basename($firstSrc);
        $path = $uploads['basedir'] . '/fp-performance-suite/' . $filename;

        $this->assertFileExists($path);
        $firstMtime = filemtime($path);
        $this->assertIsInt($firstMtime);

        usleep(1100000);

        $GLOBALS['__registered_styles'] = [];
        $GLOBALS['__enqueued_styles'] = [];
        $GLOBALS['__dequeued_styles'] = [];
        unset($GLOBALS['wp_styles']);

        $setupStyles();
        $optimizerSecond = new Optimizer(new Semaphore());
        $optimizerSecond->applyCombination();

        $this->assertArrayHasKey('fp-ps-combined-styles', $GLOBALS['__registered_styles']);
        $this->assertSame($firstSrc, $GLOBALS['__registered_styles']['fp-ps-combined-styles']['src']);

        clearstatcache(true, $path);
        $secondMtime = filemtime($path);
        $this->assertIsInt($secondMtime);
        $this->assertSame($firstMtime, $secondMtime);
    }

    public function testApplyCombinationOrdersBundleUsingDependenciesRatherThanQueue(): void
    {
        wp_mkdir_p(WP_CONTENT_DIR . '/plugins/test');
        file_put_contents(WP_CONTENT_DIR . '/plugins/test/alpha.css', 'body{color:#0f0;}');
        file_put_contents(WP_CONTENT_DIR . '/plugins/test/beta.css', 'body{color:#00f;}');

        $alpha = new _WP_Dependency('alpha', 'plugins/test/alpha.css');
        $beta = new _WP_Dependency('beta', 'plugins/test/beta.css', ['alpha']);
        $dependent = new _WP_Dependency('after-beta', 'https://cdn.example.com/app.css', ['beta']);

        $GLOBALS['wp_styles'] = new WP_Styles();
        $GLOBALS['wp_styles']->base_url = 'https://example.com/wp-content/';
        $GLOBALS['wp_styles']->queue = ['after-beta', 'beta', 'alpha'];
        $GLOBALS['wp_styles']->registered = [
            'alpha' => $alpha,
            'beta' => $beta,
            'after-beta' => $dependent,
        ];

        update_option('fp_ps_assets', [
            'combine_css' => true,
            'combine_js' => false,
        ]);

        $optimizer = new Optimizer(new Semaphore());
        $optimizer->applyCombination();

        $this->assertArrayHasKey('fp-ps-combined-styles', $GLOBALS['__registered_styles']);
        $this->assertContains('fp-ps-combined-styles', $GLOBALS['__enqueued_styles']);
        $this->assertContains('alpha', $GLOBALS['__dequeued_styles']);
        $this->assertContains('beta', $GLOBALS['__dequeued_styles']);

        $registered = $GLOBALS['__registered_styles']['fp-ps-combined-styles'];
        $uploads = wp_upload_dir();
        $filename = basename($registered['src']);
        $path = $uploads['basedir'] . '/fp-performance-suite/' . $filename;

        $this->assertFileExists($path);
        $contents = file_get_contents($path);
        $this->assertIsString($contents);
        $this->assertStringContainsString('/* alpha */', $contents);
        $this->assertStringContainsString('/* beta */', $contents);
        $this->assertLessThan(
            strpos((string) $contents, '/* beta */'),
            strpos((string) $contents, '/* alpha */'),
            'Alpha should appear before beta in the bundle.'
        );

        $this->assertSame(['fp-ps-combined-styles'], $GLOBALS['wp_styles']->registered['after-beta']->deps);
    }

    public function testApplyCombinationCombinesHeaderAndFooterScripts(): void
    {
        wp_mkdir_p(WP_CONTENT_DIR . '/plugins/test');
        file_put_contents(WP_CONTENT_DIR . '/plugins/test/one.js', 'console.log("one");');
        file_put_contents(WP_CONTENT_DIR . '/plugins/test/two.js', 'console.log("two");');
        file_put_contents(WP_CONTENT_DIR . '/plugins/test/footer-a.js', 'console.log("footer a");');
        file_put_contents(WP_CONTENT_DIR . '/plugins/test/footer-b.js', 'console.log("footer b");');

        $headerOne = new _WP_Dependency('one', 'plugins/test/one.js');
        $headerTwo = new _WP_Dependency('two', 'plugins/test/two.js', ['one']);
        $headerDependent = new _WP_Dependency('child-header', 'https://cdn.example.com/header.js', ['two']);
        $footerOne = new _WP_Dependency('footer-a', 'plugins/test/footer-a.js');
        $footerOne->extra['group'] = 1;
        $footerTwo = new _WP_Dependency('footer-b', 'plugins/test/footer-b.js', ['footer-a']);
        $footerTwo->extra['group'] = 1;
        $footerDependent = new _WP_Dependency('child-footer', 'https://cdn.example.com/footer.js', ['footer-b']);
        $footerDependent->extra['group'] = 1;
        $external = new _WP_Dependency('external', 'https://cdn.example.com/app.js');

        $GLOBALS['wp_scripts'] = new WP_Scripts();
        $GLOBALS['wp_scripts']->base_url = 'https://example.com/wp-content/';
        $GLOBALS['wp_scripts']->queue = ['one', 'two', 'child-header', 'footer-a', 'footer-b', 'child-footer', 'external'];
        $GLOBALS['wp_scripts']->registered = [
            'one' => $headerOne,
            'two' => $headerTwo,
            'child-header' => $headerDependent,
            'footer-a' => $footerOne,
            'footer-b' => $footerTwo,
            'child-footer' => $footerDependent,
            'external' => $external,
        ];

        update_option('fp_ps_assets', [
            'combine_css' => false,
            'combine_js' => true,
        ]);

        $optimizer = new Optimizer(new Semaphore());
        $optimizer->applyCombination();

        $this->assertArrayHasKey('fp-ps-combined-scripts', $GLOBALS['__registered_scripts']);
        $this->assertArrayHasKey('fp-ps-combined-scripts-footer', $GLOBALS['__registered_scripts']);
        $this->assertContains('fp-ps-combined-scripts', $GLOBALS['__enqueued_scripts']);
        $this->assertContains('fp-ps-combined-scripts-footer', $GLOBALS['__enqueued_scripts']);
        $this->assertContains('one', $GLOBALS['__dequeued_scripts']);
        $this->assertContains('two', $GLOBALS['__dequeued_scripts']);
        $this->assertContains('footer-a', $GLOBALS['__dequeued_scripts']);
        $this->assertContains('footer-b', $GLOBALS['__dequeued_scripts']);

        $this->assertSame(['fp-ps-combined-scripts'], $GLOBALS['wp_scripts']->registered['child-header']->deps);
        $this->assertSame(['fp-ps-combined-scripts-footer'], $GLOBALS['wp_scripts']->registered['child-footer']->deps);
    }
}
