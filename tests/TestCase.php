<?php

namespace FP\PerfSuite\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Brain\Monkey;

/**
 * Base Test Case for FP Performance Suite
 *
 * @package FP\PerfSuite\Tests
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    /**
     * Tear down after each test
     */
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Get a mock WordPress function
     *
     * @param string $function Function name
     * @return \Mockery\Expectation
     */
    protected function mockWordPressFunction(string $function)
    {
        return Monkey\Functions\expect($function);
    }

    /**
     * Get a mock WordPress filter
     *
     * @param string $filter Filter name
     * @return \Mockery\Expectation
     */
    protected function mockWordPressFilter(string $filter)
    {
        return Monkey\Filters\expectApplied($filter);
    }

    /**
     * Get a mock WordPress action
     *
     * @param string $action Action name
     * @return \Mockery\Expectation
     */
    protected function mockWordPressAction(string $action)
    {
        return Monkey\Actions\expectDone($action);
    }
}










