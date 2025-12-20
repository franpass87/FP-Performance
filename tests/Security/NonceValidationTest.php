<?php

namespace FP\PerfSuite\Tests\Security;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * Test nonce validation
 *
 * @package FP\PerfSuite\Tests\Security
 */
class NonceValidationTest extends TestCase
{
    public function testNonceValidationSuccess(): void
    {
        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('valid_nonce', 'fp_ps_action')
            ->andReturn(1);

        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        $result = wp_verify_nonce('valid_nonce', 'fp_ps_action');
        $this->assertNotFalse($result);
    }

    public function testNonceValidationFailure(): void
    {
        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('invalid_nonce', 'fp_ps_action')
            ->andReturn(false);

        $result = wp_verify_nonce('invalid_nonce', 'fp_ps_action');
        $this->assertFalse($result);
    }

    public function testNonceValidationExpired(): void
    {
        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('expired_nonce', 'fp_ps_action')
            ->andReturn(false);

        $result = wp_verify_nonce('expired_nonce', 'fp_ps_action');
        $this->assertFalse($result);
    }

    public function testNonceCreation(): void
    {
        Functions\expect('wp_create_nonce')
            ->once()
            ->with('fp_ps_action')
            ->andReturn('generated_nonce_12345');

        $nonce = wp_create_nonce('fp_ps_action');
        $this->assertNotEmpty($nonce);
        $this->assertIsString($nonce);
    }
}










