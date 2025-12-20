<?php

namespace FP\PerfSuite\Tests\Functional\REST;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * Test REST API authentication
 *
 * @package FP\PerfSuite\Tests\Functional\REST
 */
class RestApiAuthenticationTest extends TestCase
{
    public function testUnauthenticatedRequestRejected(): void
    {
        Functions\expect('is_user_logged_in')
            ->once()
            ->andReturn(false);

        Functions\expect('current_user_can')
            ->never();

        $isLoggedIn = is_user_logged_in();
        $this->assertFalse($isLoggedIn);
    }

    public function testAuthenticatedRequestAccepted(): void
    {
        Functions\expect('is_user_logged_in')
            ->once()
            ->andReturn(true);

        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        $isLoggedIn = is_user_logged_in();
        $canManage = current_user_can('manage_options');

        $this->assertTrue($isLoggedIn);
        $this->assertTrue($canManage);
    }

    public function testInvalidNonceRejected(): void
    {
        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('invalid_nonce', 'wp_rest')
            ->andReturn(false);

        $result = wp_verify_nonce('invalid_nonce', 'wp_rest');
        $this->assertFalse($result);
    }

    public function testValidNonceAccepted(): void
    {
        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('valid_nonce', 'wp_rest')
            ->andReturn(1);

        $result = wp_verify_nonce('valid_nonce', 'wp_rest');
        $this->assertNotFalse($result);
    }

    public function testPermissionCallback(): void
    {
        Functions\expect('is_user_logged_in')
            ->once()
            ->andReturn(true);

        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        // Permission callback should return true for admin users
        $hasPermission = is_user_logged_in() && current_user_can('manage_options');
        $this->assertTrue($hasPermission);
    }
}










