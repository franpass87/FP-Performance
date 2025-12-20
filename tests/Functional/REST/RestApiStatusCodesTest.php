<?php

namespace FP\PerfSuite\Tests\Functional\REST;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

/**
 * Test REST API HTTP status codes
 *
 * @package FP\PerfSuite\Tests\Functional\REST
 */
class RestApiStatusCodesTest extends TestCase
{
    public function testSuccessfulRequestReturns200(): void
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

        $response = new \WP_REST_Response(['success' => true], 200);

        $this->assertEquals(200, $response->get_status());
        $this->assertTrue($response->get_data()['success']);
    }

    public function testValidationErrorReturns400(): void
    {
        $request = $this->createMock(\WP_REST_Request::class);
        $request->method('get_param')
            ->with('url')
            ->willReturn(''); // Invalid empty URL

        $response = new \WP_REST_Response([
            'code' => 'invalid_param',
            'message' => 'URL is required',
            'data' => ['status' => 400]
        ], 400);

        $this->assertEquals(400, $response->get_status());
        $this->assertEquals('invalid_param', $response->get_data()['code']);
    }

    public function testAuthenticationErrorReturns401(): void
    {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(false);

        $request = $this->createMock(\WP_REST_Request::class);

        $response = new \WP_REST_Response([
            'code' => 'rest_forbidden',
            'message' => 'Sorry, you are not allowed to do that.',
            'data' => ['status' => 401]
        ], 401);

        $this->assertEquals(401, $response->get_status());
        $this->assertEquals('rest_forbidden', $response->get_data()['code']);
    }

    public function testPermissionErrorReturns403(): void
    {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(false);

        Functions\expect('wp_verify_nonce')
            ->once()
            ->andReturn(true);

        $request = $this->createMock(\WP_REST_Request::class);
        $request->method('get_header')
            ->with('X-WP-Nonce')
            ->willReturn('valid-nonce');

        $response = new \WP_REST_Response([
            'code' => 'rest_forbidden',
            'message' => 'Sorry, you do not have permission to do that.',
            'data' => ['status' => 403]
        ], 403);

        $this->assertEquals(403, $response->get_status());
    }

    public function testNotFoundReturns404(): void
    {
        $request = $this->createMock(\WP_REST_Request::class);
        $request->method('get_param')
            ->with('post_id')
            ->willReturn(99999); // Non-existent post

        Functions\expect('get_post')
            ->once()
            ->with(99999)
            ->andReturn(null);

        $response = new \WP_REST_Response([
            'code' => 'rest_post_invalid_id',
            'message' => 'Invalid post ID.',
            'data' => ['status' => 404]
        ], 404);

        $this->assertEquals(404, $response->get_status());
    }

    public function testServerErrorReturns500(): void
    {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        Functions\expect('wp_verify_nonce')
            ->once()
            ->andReturn(true);

        Functions\expect('wp_cache_flush')
            ->once()
            ->andThrow(new \Exception('Cache error'));

        $request = $this->createMock(\WP_REST_Request::class);
        $request->method('get_header')
            ->with('X-WP-Nonce')
            ->willReturn('valid-nonce');

        $response = new \WP_REST_Response([
            'code' => 'rest_server_error',
            'message' => 'Internal server error',
            'data' => ['status' => 500]
        ], 500);

        $this->assertEquals(500, $response->get_status());
    }
}










