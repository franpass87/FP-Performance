<?php

namespace FP\PerfSuite\Tests\Functional\REST;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Http\Routes;
use FP\PerfSuite\Kernel\Container;
use Brain\Monkey\Functions;

/**
 * Test all REST API endpoints
 *
 * @package FP\PerfSuite\Tests\Functional\REST
 */
class RestApiEndpointsTest extends TestCase
{
    private Routes $routes;
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->routes = new Routes($this->container);
    }

    public function testLogsTailEndpoint(): void
    {
        Functions\expect('register_rest_route')
            ->atLeast()
            ->once()
            ->with('fp-ps/v1', '/logs/tail', \Mockery::on(function($args) {
                return isset($args['methods']) && $args['methods'] === 'GET';
            }));

        $this->routes->register();
    }

    public function testDebugToggleEndpoint(): void
    {
        Functions\expect('register_rest_route')
            ->atLeast()
            ->once()
            ->with('fp-ps/v1', '/debug/toggle', \Mockery::on(function($args) {
                return isset($args['methods']) && $args['methods'] === 'POST';
            }));

        $this->routes->register();
    }

    public function testPresetApplyEndpoint(): void
    {
        Functions\expect('register_rest_route')
            ->atLeast()
            ->once()
            ->with('fp-ps/v1', '/preset/apply', \Mockery::on(function($args) {
                return isset($args['methods']) && 
                       $args['methods'] === 'POST' &&
                       isset($args['args']['id']);
            }));

        $this->routes->register();
    }

    public function testPresetRollbackEndpoint(): void
    {
        Functions\expect('register_rest_route')
            ->atLeast()
            ->once()
            ->with('fp-ps/v1', '/preset/rollback', \Mockery::on(function($args) {
                return isset($args['methods']) && $args['methods'] === 'POST';
            }));

        $this->routes->register();
    }

    public function testCachePurgeUrlEndpoint(): void
    {
        Functions\expect('register_rest_route')
            ->atLeast()
            ->once()
            ->with('fp-ps/v1', '/cache/purge-url', \Mockery::on(function($args) {
                return isset($args['methods']) && 
                       $args['methods'] === 'POST' &&
                       isset($args['args']['url']);
            }));

        $this->routes->register();
    }

    public function testCachePurgePostEndpoint(): void
    {
        Functions\expect('register_rest_route')
            ->atLeast()
            ->once()
            ->with('fp-ps/v1', '/cache/purge-post', \Mockery::on(function($args) {
                return isset($args['methods']) && 
                       $args['methods'] === 'POST' &&
                       isset($args['args']['post_id']);
            }));

        $this->routes->register();
    }

    public function testCachePurgePatternEndpoint(): void
    {
        Functions\expect('register_rest_route')
            ->atLeast()
            ->once()
            ->with('fp-ps/v1', '/cache/purge-pattern', \Mockery::on(function($args) {
                return isset($args['methods']) && 
                       $args['methods'] === 'POST' &&
                       isset($args['args']['pattern']);
            }));

        $this->routes->register();
    }

    public function testDbCleanupEndpoint(): void
    {
        Functions\expect('register_rest_route')
            ->atLeast()
            ->once()
            ->with('fp-ps/v1', '/db/cleanup', \Mockery::on(function($args) {
                return isset($args['methods']) && 
                       $args['methods'] === 'POST' &&
                       isset($args['args']['scope']);
            }));

        $this->routes->register();
    }

    public function testScoreEndpoint(): void
    {
        Functions\expect('register_rest_route')
            ->atLeast()
            ->once()
            ->with('fp-ps/v1', '/score', \Mockery::on(function($args) {
                return isset($args['methods']) && $args['methods'] === 'GET';
            }));

        $this->routes->register();
    }

    public function testProgressEndpoint(): void
    {
        Functions\expect('register_rest_route')
            ->atLeast()
            ->once()
            ->with('fp-ps/v1', '/progress', \Mockery::on(function($args) {
                return isset($args['methods']) && $args['methods'] === 'GET';
            }));

        $this->routes->register();
    }

    public function testStatusEndpoint(): void
    {
        Functions\expect('register_rest_route')
            ->once()
            ->with('fp-performance/v1', '/status', \Mockery::on(function($args) {
                return isset($args['methods']) && 
                       $args['methods'] === 'GET' &&
                       $args['permission_callback'] === '__return_true';
            }));

        $this->routes->register();
    }
}










