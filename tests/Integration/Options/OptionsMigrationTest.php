<?php

namespace FP\PerfSuite\Tests\Integration\Options;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Core\Options\OptionsRepository;
use Brain\Monkey\Functions;

/**
 * Test options migration
 *
 * @package FP\PerfSuite\Tests\Integration\Options
 */
class OptionsMigrationTest extends TestCase
{
    private OptionsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OptionsRepository('fp_ps_test_');
    }

    public function testOptionsMigration(): void
    {
        // Simulate old option format
        Functions\expect('get_option')
            ->once()
            ->with('fp_ps_test_old_option', null)
            ->andReturn(['old' => 'data']);

        Functions\expect('update_option')
            ->once()
            ->with('fp_ps_test_new_option', ['old' => 'data'], true)
            ->andReturn(true);

        Functions\expect('delete_option')
            ->once()
            ->with('fp_ps_test_old_option')
            ->andReturn(true);

        // Migration logic would go here
        $oldValue = $this->repository->get('old_option');
        if ($oldValue) {
            $this->repository->set('new_option', $oldValue);
            $this->repository->delete('old_option');
        }
    }
}










