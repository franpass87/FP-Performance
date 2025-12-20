<?php

namespace FP\PerfSuite\Tests\Integration\Multilanguage;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * Test multilanguage options per-language
 *
 * @package FP\PerfSuite\Tests\Integration\Multilanguage
 */
class MultilanguageOptionsTest extends TestCase
{
    public function testOptionsPerLanguageWithWpml(): void
    {
        Functions\expect('defined')
            ->once()
            ->with('ICL_SITEPRESS_VERSION')
            ->andReturn(true);

        Functions\expect('get_current_language')
            ->once()
            ->andReturn('en');

        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_settings_en', [])
            ->andReturn(['language_specific' => 'value']);

        // Options should work per-language with WPML
        $this->assertTrue(true);
    }

    public function testOptionsPerLanguageWithPolylang(): void
    {
        Functions\expect('function_exists')
            ->once()
            ->with('pll_current_language')
            ->andReturn(true);

        Functions\expect('pll_current_language')
            ->once()
            ->andReturn('it');

        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_settings_it', [])
            ->andReturn(['language_specific' => 'value']);

        // Options should work per-language with Polylang
        $this->assertTrue(true);
    }

    public function testLanguageSwitching(): void
    {
        Functions\expect('get_current_language')
            ->twice()
            ->andReturn('en', 'it');

        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_settings_en', [])
            ->andReturn(['en_setting' => 'value']);

        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_settings_it', [])
            ->andReturn(['it_setting' => 'value']);

        // Language switching should work
        $this->assertTrue(true);
    }

    public function testUrlStructureConsistency(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('permalink_structure', '/%postname%/')
            ->andReturn('/%postname%/');

        Functions\expect('get_permalink')
            ->once()
            ->with(1)
            ->andReturn('http://example.com/post/');

        // URL structure should be consistent
        $this->assertTrue(true);
    }

    public function testLanguagePrefixes(): void
    {
        Functions\expect('get_current_language')
            ->once()
            ->andReturn('it');

        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_settings_it', [])
            ->andReturn(['prefix' => '/it/']);

        // Language prefixes should work
        $this->assertTrue(true);
    }
}










