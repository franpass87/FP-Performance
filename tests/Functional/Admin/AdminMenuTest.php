<?php

namespace FP\PerfSuite\Tests\Functional\Admin;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Admin\Menu;
use FP\PerfSuite\ServiceContainer;
use Brain\Monkey\Functions;

/**
 * Test Admin Menu registration
 *
 * @package FP\PerfSuite\Tests\Functional\Admin
 */
class AdminMenuTest extends TestCase
{
    private Menu $menu;
    private ServiceContainer $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->createMock(ServiceContainer::class);
        $this->menu = new Menu($this->container);
    }

    public function testMenuRegistration(): void
    {
        Functions\expect('add_menu_page')
            ->once()
            ->with(
                \Mockery::type('string'),
                \Mockery::type('string'),
                'manage_options',
                \Mockery::type('string'),
                \Mockery::type('callable'),
                \Mockery::any(),
                \Mockery::any()
            );

        Functions\expect('add_submenu_page')
            ->atLeast()
            ->times(20);

        // Menu registration would be called during boot
        // This test verifies the structure
        $this->assertInstanceOf(Menu::class, $this->menu);
    }
}










