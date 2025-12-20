<?php

namespace FP\PerfSuite\Tests\Unit\Services;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager\CriticalPageDetector;
use Brain\Monkey\Functions;

/**
 * Test CriticalPageDetector
 *
 * @package FP\PerfSuite\Tests\Unit\Services
 */
class CriticalPageDetectorTest extends TestCase
{
    private CriticalPageDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new CriticalPageDetector();
    }

    public function testIsCriticalPageWooCommerceCart(): void
    {
        Functions\expect('function_exists')
            ->with('is_woocommerce')
            ->andReturn(true);

        Functions\expect('is_cart')
            ->once()
            ->andReturn(true);

        $result = $this->detector->isCriticalPage();
        $this->assertTrue($result);
    }

    public function testIsCriticalPageWooCommerceCheckout(): void
    {
        Functions\expect('function_exists')
            ->with('is_woocommerce')
            ->andReturn(true);

        Functions\expect('is_cart')
            ->once()
            ->andReturn(false);

        Functions\expect('is_checkout')
            ->once()
            ->andReturn(true);

        $result = $this->detector->isCriticalPage();
        $this->assertTrue($result);
    }

    public function testIsCriticalPageAccountPage(): void
    {
        Functions\expect('function_exists')
            ->with('is_woocommerce')
            ->andReturn(true);

        Functions\expect('is_cart')
            ->once()
            ->andReturn(false);

        Functions\expect('is_checkout')
            ->once()
            ->andReturn(false);

        Functions\expect('is_account_page')
            ->once()
            ->andReturn(true);

        $result = $this->detector->isCriticalPage();
        $this->assertTrue($result);
    }

    public function testIsCriticalPageUrlPattern(): void
    {
        Functions\expect('function_exists')
            ->with('is_woocommerce')
            ->andReturn(false);

        Functions\expect('function_exists')
            ->with('edd_is_checkout')
            ->andReturn(false);

        Functions\expect('is_page')
            ->once()
            ->andReturn(false);

        $_SERVER['REQUEST_URI'] = '/checkout';

        $result = $this->detector->isCriticalPage();
        $this->assertTrue($result);
    }

    public function testIsNotCriticalPage(): void
    {
        Functions\expect('function_exists')
            ->with('is_woocommerce')
            ->andReturn(false);

        Functions\expect('function_exists')
            ->with('edd_is_checkout')
            ->andReturn(false);

        Functions\expect('is_page')
            ->once()
            ->andReturn(false);

        $_SERVER['REQUEST_URI'] = '/blog';

        $result = $this->detector->isCriticalPage();
        $this->assertFalse($result);
    }
}










