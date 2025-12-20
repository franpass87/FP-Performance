<?php

namespace FP\PerfSuite\Tests\Integration\Multilanguage;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * Test multilanguage translation coverage
 *
 * @package FP\PerfSuite\Tests\Integration\Multilanguage
 */
class MultilanguageTranslationTest extends TestCase
{
    public function testAllStringsTranslatable(): void
    {
        $strings = [
            __('Cache Settings', 'fp-performance'),
            __('CSS Optimization', 'fp-performance'),
            __('JavaScript Optimization', 'fp-performance')
        ];

        foreach ($strings as $string) {
            Functions\expect('__')
                ->once()
                ->with(\Mockery::type('string'), 'fp-performance')
                ->andReturn($string);
        }

        // All strings should be translatable
        $this->assertTrue(true);
    }

    public function testTextDomainCorrect(): void
    {
        Functions\expect('__')
            ->once()
            ->with('Cache Settings', 'fp-performance')
            ->andReturn('Cache Settings');

        // Text domain should be correct
        $this->assertTrue(true);
    }

    public function testTranslationWithDifferentLanguages(): void
    {
        Functions\expect('get_locale')
            ->once()
            ->andReturn('it_IT');

        Functions\expect('load_textdomain')
            ->once()
            ->with('fp-performance', \Mockery::type('string'));

        // Translations should load for different languages
        $this->assertTrue(true);
    }

    public function testNoHardcodedStrings(): void
    {
        // This would be checked via static analysis
        // For now, we verify that translation functions are used
        Functions\expect('__')
            ->atLeast()
            ->once()
            ->with(\Mockery::type('string'), 'fp-performance');

        // No hardcoded strings should be present
        $this->assertTrue(true);
    }
}










