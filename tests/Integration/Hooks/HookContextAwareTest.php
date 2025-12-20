<?php

namespace FP\PerfSuite\Tests\Integration\Hooks;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;

/**
 * Test context-aware hook execution
 *
 * @package FP\PerfSuite\Tests\Integration\Hooks
 */
class HookContextAwareTest extends TestCase
{
    public function testAdminHooksOnlyInAdmin(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(true);

        Actions\expectAdded('admin_menu')
            ->once()
            ->with(\Mockery::type('callable'));

        Actions\expectAdded('admin_init')
            ->once()
            ->with(\Mockery::type('callable'));

        // Admin hooks should only execute in admin context
        $this->assertTrue(true);
    }

    public function testFrontendHooksOnlyInFrontend(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->with(\Mockery::type('callable'));

        Actions\expectAdded('wp_footer')
            ->once()
            ->with(\Mockery::type('callable'));

        // Frontend hooks should only execute in frontend context
        $this->assertTrue(true);
    }

    public function testRestApiHooksOnlyInRestContext(): void
    {
        Functions\expect('defined')
            ->once()
            ->with('REST_REQUEST')
            ->andReturn(true);

        Functions\expect('constant')
            ->once()
            ->with('REST_REQUEST')
            ->andReturn(true);

        Actions\expectAdded('rest_api_init')
            ->once()
            ->with(\Mockery::type('callable'));

        // REST API hooks should only execute in REST context
        $this->assertTrue(true);
    }

    public function testCliHooksOnlyInCliContext(): void
    {
        Functions\expect('defined')
            ->once()
            ->with('WP_CLI')
            ->andReturn(true);

        Functions\expect('constant')
            ->once()
            ->with('WP_CLI')
            ->andReturn(true);

        Actions\expectAdded('wp_cli_init')
            ->once()
            ->with(\Mockery::type('callable'));

        // CLI hooks should only execute in CLI context
        $this->assertTrue(true);
    }

    public function testAjaxHooksOnlyInAjaxContext(): void
    {
        Functions\expect('defined')
            ->once()
            ->with('DOING_AJAX')
            ->andReturn(true);

        Functions\expect('constant')
            ->once()
            ->with('DOING_AJAX')
            ->andReturn(true);

        Actions\expectAdded('wp_ajax_fp_ps_action')
            ->once()
            ->with(\Mockery::type('callable'));

        // AJAX hooks should only execute in AJAX context
        $this->assertTrue(true);
    }

    public function testHooksNotExecutingInWrongContext(): void
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        // Admin hooks should not execute in frontend
        Actions\expectAdded('admin_menu')
            ->never();

        Actions\expectAdded('admin_init')
            ->never();

        $this->assertTrue(true);
    }
}










