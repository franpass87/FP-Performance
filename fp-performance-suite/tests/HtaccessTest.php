<?php

use FP\PerfSuite\Utils\Htaccess;
use FP\PerfSuite\Utils\Fs;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

final class HtaccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['__wp_options'] = [];
    }

    public function testIsSupportedLoadsModRewriteHelperWhenMissing(): void
    {
        if (function_exists('got_mod_rewrite')) {
            $this->markTestSkipped('got_mod_rewrite already defined');
        }

        $helperDir = ABSPATH . 'wp-admin/includes';
        if (!is_dir($helperDir)) {
            mkdir($helperDir, 0777, true);
        }

        $helperFile = $helperDir . '/misc.php';
        file_put_contents($helperFile, "<?php\nfunction got_mod_rewrite() { return true; }\n");

        $fs = new class extends Fs {
            public function exists(string $file): bool
            {
                return false;
            }
        };

        $htaccess = new Htaccess($fs);

        $this->assertTrue($htaccess->isSupported());

        @unlink($helperFile);
    }
}
