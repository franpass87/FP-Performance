<?php

namespace FP\PerfSuite\Tests\Functional\REST;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Http\Routes;
use FP\PerfSuite\Kernel\Container;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Brain\Monkey\Actions;

/**
 * Test REST API endpoints
 *
 * @package FP\PerfSuite\Tests\Functional\REST
 */
class RestApiTest extends TestCase
{
    private Routes $routes;
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->routes = new Routes($this->container);
    }

    public function testRoutesRegistration(): void
    {
        Functions\expect('register_rest_route')
            ->atLeast()
            ->times(11)
            ->with('fp-ps/v1', \Mockery::type('string'), \Mockery::type('array'));

        Functions\expect('register_rest_route')
            ->once()
            ->with('fp-performance/v1', '/status', \Mockery::type('array'));

        $this->routes->register();
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
                return isset($args['methods']) && $args['methods'] === 'POST';
            }));

        $this->routes->register();
    }

    public function testDbCleanupEndpoint(): void
    {
        Functions\expect('register_rest_route')
            ->atLeast()
            ->once()
            ->with('fp-ps/v1', '/db/cleanup', \Mockery::on(function($args) {
                return isset($args['methods']) && $args['methods'] === 'POST';
            }));

        $this->routes->register();
    }

    public function testPermissionCheck(): void
    {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        Functions\expect('wp_verify_nonce')
            ->once()
            ->andReturn(true);

        $request = $this->createMock(\WP_REST_Request::class);
        $request->method('get_header')
            ->with('X-WP-Nonce')
            ->willReturn('valid-nonce');

        $result = $this->routes->permissionCheck($request);
        $this->assertTrue($result);
    }

    public function testPermissionCheckFailsWithoutCapability(): void
    {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(false);

        $request = $this->createMock(\WP_REST_Request::class);
        $result = $this->routes->permissionCheck($request);
        $this->assertFalse($result);
    }

    public function testPermissionCheckFailsWithInvalidNonce(): void
    {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        Functions\expect('wp_verify_nonce')
            ->once()
            ->andReturn(false);

        $request = $this->createMock(\WP_REST_Request::class);
        $request->method('get_header')
            ->with('X-WP-Nonce')
            ->willReturn('invalid-nonce');

        $result = $this->routes->permissionCheck($request);
        $this->assertFalse($result);
    }
}










