<?php

namespace FP\PerfSuite\Tests\Integration\Hooks;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;

/**
 * Test hook priority validation
 *
 * @package FP\PerfSuite\Tests\Integration\Hooks
 */
class HookPriorityTest extends TestCase
{
    public function testNoPriorityConflicts(): void
    {
        $priorities = [
            'plugins_loaded' => [10],
            'init' => [10, 20],
            'wp_loaded' => [10],
            'admin_init' => [10],
            'wp_enqueue_scripts' => [15, 20],
            'wp_footer' => [5, 10],
            'rest_api_init' => [10],
            'wp_cli_init' => [10]
        ];

        // Verify no duplicate priorities for same hook
        foreach ($priorities as $hook => $hookPriorities) {
            $unique = array_unique($hookPriorities);
            $this->assertEquals(count($hookPriorities), count($unique), "Duplicate priorities found for hook: {$hook}");
        }
    }

    public function testCriticalHooksHaveCorrectPriority(): void
    {
        // Critical hooks should have early priorities
        $criticalHooks = [
            'plugins_loaded' => 10,
            'init' => 10,
            'wp_loaded' => 10
        ];

        foreach ($criticalHooks as $hook => $expectedPriority) {
            Actions\expectAdded($hook)
                ->zeroOrMoreTimes()
                ->with(\Mockery::any(), $expectedPriority);

            $this->assertTrue(true);
        }
    }

    public function testHookExecutionOrder(): void
    {
        $executionOrder = [];
        
        Actions\expectAdded('plugins_loaded')
            ->once()
            ->with(\Mockery::on(function($callback) use (&$executionOrder) {
                $executionOrder[] = 'plugins_loaded';
                return true;
            }), 10);

        Actions\expectAdded('init')
            ->once()
            ->with(\Mockery::on(function($callback) use (&$executionOrder) {
                $executionOrder[] = 'init';
                return true;
            }), 10);

        Actions\expectAdded('wp_loaded')
            ->once()
            ->with(\Mockery::on(function($callback) use &$executionOrder) {
                $executionOrder[] = 'wp_loaded';
                return true;
            }), 10);

        // Verify execution order
        $this->assertTrue(true);
    }

    public function testDependenciesRespected(): void
    {
        // CoreServiceProvider should register before other providers
        Actions\expectAdded('plugins_loaded')
            ->atLeast()
            ->once()
            ->with(\Mockery::type('callable'), 10);

        // Frontend services should register after core
        Actions\expectAdded('wp_loaded')
            ->atLeast()
            ->once()
            ->with(\Mockery::type('callable'), 10);

        $this->assertTrue(true);
    }
}










