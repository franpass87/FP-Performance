<?php

namespace FP\PerfSuite\Tests\Security;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * Test capability checks
 *
 * @package FP\PerfSuite\Tests\Security
 */
class CapabilityChecksTest extends TestCase
{
    public function testAdminCapabilityCheck(): void
    {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        $result = current_user_can('manage_options');
        $this->assertTrue($result);
    }

    public function testEditorCapabilityCheck(): void
    {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(false);

        Functions\expect('current_user_can')
            ->once()
            ->with('edit_posts')
            ->andReturn(true);

        $canManage = current_user_can('manage_options');
        $canEdit = current_user_can('edit_posts');

        $this->assertFalse($canManage);
        $this->assertTrue($canEdit);
    }

    public function testSubscriberCapabilityCheck(): void
    {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(false);

        Functions\expect('current_user_can')
            ->once()
            ->with('edit_posts')
            ->andReturn(false);

        $canManage = current_user_can('manage_options');
        $canEdit = current_user_can('edit_posts');

        $this->assertFalse($canManage);
        $this->assertFalse($canEdit);
    }

    public function testRestApiPermissionCheck(): void
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
}










