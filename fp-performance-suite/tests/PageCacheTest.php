<?php

use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Utils\Env;
use FP\PerfSuite\Utils\Fs;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

final class PageCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['__wp_options'] = [];
        $_GET = [];
        $_COOKIE = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
    }

    public function testSaveBufferFlushesWhenRequestBecomesUncacheable(): void
    {
        update_option('fp_ps_page_cache', ['enabled' => true, 'ttl' => 3600]);

        $fs = new class extends Fs {
            /** @var array<string,string> */
            public array $writes = [];

            public function putContents(string $file, string $contents): bool
            {
                $this->writes[$file] = $contents;
                return true;
            }
        };

        $pageCache = new PageCache($fs, new Env());

        ob_start();
        $pageCache->startBuffering();
        echo 'cached body';

        $_GET['preview'] = '1';

        $pageCache->saveBuffer();

        $output = ob_get_clean();

        $this->assertSame('cached body', $output);
        $this->assertSame([], $fs->writes);

        $_GET = [];
    }

    public function testSettingsPreserveDisabledTtl(): void
    {
        update_option('fp_ps_page_cache', ['enabled' => false, 'ttl' => 0]);

        $pageCache = new PageCache(new Fs(), new Env());

        $settings = $pageCache->settings();

        $this->assertFalse($settings['enabled']);
        $this->assertSame(0, $settings['ttl']);
    }

    public function testUpdateKeepsExistingTtlWhenDisabling(): void
    {
        update_option('fp_ps_page_cache', ['enabled' => true, 'ttl' => 900]);

        $pageCache = new PageCache(new Fs(), new Env());
        $pageCache->update(['enabled' => false]);

        $stored = get_option('fp_ps_page_cache');

        $this->assertSame(900, $stored['ttl']);
        $this->assertFalse($stored['enabled']);
    }

    public function testUpdateRestoresTtlWhenEnablingWithoutValue(): void
    {
        update_option('fp_ps_page_cache', ['enabled' => false, 'ttl' => 0]);

        $pageCache = new PageCache(new Fs(), new Env());
        $pageCache->update(['enabled' => true]);

        $stored = get_option('fp_ps_page_cache');

        $this->assertTrue($stored['enabled']);
        $this->assertSame(3600, $stored['ttl']);
    }
}
