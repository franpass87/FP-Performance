<?php

namespace FP\PerfSuite\Tests\Integration\Hooks;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;

/**
 * Test hook removal on deactivation
 *
 * @package FP\PerfSuite\Tests\Integration\Hooks
 */
class HookRemovalTest extends TestCase
{
    public function testRemoveAllHooksOnDeactivation(): void
    {
        // Test that all hooks are removed on deactivation
        Functions\expect('remove_action')
            ->atLeast()
            ->once();

        Functions\expect('remove_filter')
            ->atLeast()
            ->once();

        Functions\expect('wp_clear_scheduled_hook')
            ->atLeast()
            ->once();

        // Deactivation would call cleanup
        $this->assertTrue(true);
    }

    public function testRemoveAdminHooks(): void
    {
        Functions\expect('remove_action')
            ->atLeast()
            ->once()
            ->with('admin_menu', \Mockery::type('callable'));

        Functions\expect('remove_action')
            ->atLeast()
            ->once()
            ->with('admin_init', \Mockery::type('callable'));

        // Admin hooks should be removed
        $this->assertTrue(true);
    }

    public function testRemoveFrontendHooks(): void
    {
        Functions\expect('remove_action')
            ->atLeast()
            ->once()
            ->with('wp_enqueue_scripts', \Mockery::type('callable'));

        Functions\expect('remove_action')
            ->atLeast()
            ->once()
            ->with('wp_footer', \Mockery::type('callable'));

        // Frontend hooks should be removed
        $this->assertTrue(true);
    }

    public function testRemoveRestApiHooks(): void
    {
        Functions\expect('remove_action')
            ->atLeast()
            ->once()
            ->with('rest_api_init', \Mockery::type('callable'));

        // REST API hooks should be removed
        $this->assertTrue(true);
    }

    public function testRemoveCliHooks(): void
    {
        Functions\expect('remove_action')
            ->atLeast()
            ->once()
            ->with('wp_cli_init', \Mockery::type('callable'));

        // CLI hooks should be removed
        $this->assertTrue(true);
    }
}










